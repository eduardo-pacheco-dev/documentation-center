<?php

namespace App\Console\Commands;

use App\Enums\AgendaEventStatus;
use App\Enums\AgendaReminderMethod;
use App\Models\AgendaEventReminder;
use App\Models\User;
use App\Notifications\AgendaEventReminderNotification;
use App\Services\Agenda\AgendaEventExpander;
use App\Services\Agenda\AgendaOccurrence;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

#[Signature('agenda:dispatch-reminders {--dry-run : Lista os lembretes sem enviá-los}')]
#[Description('Dispara os lembretes por e-mail dos eventos da agenda')]
class DispatchAgendaRemindersCommand extends Command
{
    /**
     * Create a new command instance.
     */
    public function __construct(private readonly AgendaEventExpander $expander)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();
        $dryRun = (bool) $this->option('dry-run');
        $dispatched = 0;

        $reminders = AgendaEventReminder::query()
            ->where('method', AgendaReminderMethod::Email->value)
            ->whereHas('event', fn ($query) => $query->where('status', '!=', AgendaEventStatus::Cancelled->value))
            ->with(['event.calendar', 'event.user', 'event.attendees.user'])
            ->get();

        foreach ($reminders as $reminder) {
            $occurrence = $this->nextOccurrence($reminder, $now);

            if ($occurrence === null) {
                continue;
            }

            if ($reminder->sent_at !== null && $reminder->sent_at->equalTo($occurrence->start)) {
                continue;
            }

            if ($dryRun) {
                $this->line(sprintf('[dry-run] %s às %s', $occurrence->title(), $occurrence->start->toDateTimeString()));

                continue;
            }

            foreach ($this->recipients($reminder) as $recipient) {
                $recipient->notify(new AgendaEventReminderNotification(
                    $reminder->event,
                    $occurrence->start,
                    $reminder->minutes_before ?? 0,
                ));
            }

            $reminder->forceFill(['sent_at' => $occurrence->start])->save();
            $dispatched++;
        }

        $this->info($dryRun
            ? "{$dispatched} lembrete(s) pendente(s)."
            : "{$dispatched} lembrete(s) enviado(s).");

        return self::SUCCESS;
    }

    /**
     * The next occurrence whose reminder is already due.
     */
    private function nextOccurrence(AgendaEventReminder $reminder, Carbon $now): ?AgendaOccurrence
    {
        $occurrences = $this->expander->expand(
            $reminder->event,
            $now->copy()->subMinute(),
            $now->copy()->addYear(),
        );

        foreach ($occurrences as $occurrence) {
            if (! $occurrence->start->greaterThan($now)) {
                continue;
            }

            if ($reminder->triggerTimeFor($occurrence->start)->greaterThan($now)) {
                continue;
            }

            return $occurrence;
        }

        return null;
    }

    /**
     * The users that should receive the reminder.
     *
     * @return Collection<int, User>
     */
    private function recipients(AgendaEventReminder $reminder): Collection
    {
        $event = $reminder->event;
        $recipients = collect([$event->user]);

        foreach ($event->attendees as $attendee) {
            if ($attendee->user !== null) {
                $recipients->push($attendee->user);
            }
        }

        return $recipients
            ->filter(fn (?User $user): bool => $user !== null)
            ->unique(fn (User $user): int => $user->getKey())
            ->values();
    }
}
