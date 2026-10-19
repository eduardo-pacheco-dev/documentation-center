<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AgendaEventResponseRequest;
use App\Http\Requests\Admin\StoreAgendaEventRequest;
use App\Http\Requests\Admin\UpdateAgendaEventRequest;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\AgendaEventAttendee;
use App\Notifications\AgendaEventInvitationNotification;
use App\Services\Agenda\AgendaEventSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class AgendaEventController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(private readonly AgendaEventSynchronizer $synchronizer) {}

    /**
     * Create an event on a calendar the user can write to.
     */
    public function store(StoreAgendaEventRequest $request): RedirectResponse
    {
        $calendar = AgendaCalendar::findOrFail($request->integer('agenda_calendar_id'));
        $this->authorize('update', $calendar);

        $event = $calendar->events()->create($request->eventAttributes() + [
            'user_id' => $request->user()->getKey(),
        ]);

        $created = $this->synchronizer->syncAttendees($event, $request->attendeeEmails());
        $this->synchronizer->syncReminders($event, $request->reminders());
        $this->notifyAttendees($event, $created);

        return back()->with('status', 'Evento criado.');
    }

    /**
     * Update an event, a single occurrence or the tail of a series.
     */
    public function update(UpdateAgendaEventRequest $request, AgendaEvent $agendaEvent): RedirectResponse
    {
        $calendar = AgendaCalendar::findOrFail($request->integer('agenda_calendar_id'));

        if ($calendar->getKey() !== $agendaEvent->agenda_calendar_id) {
            $this->authorize('update', $calendar);
        }

        $attributes = $request->eventAttributes();
        $scope = $request->scope();
        $recurrenceId = $request->recurrenceId();

        if ($agendaEvent->isRecurring() && $recurrenceId !== null && $scope === 'this') {
            $this->synchronizer->applyException($agendaEvent, $recurrenceId, $this->overridesFrom($attributes));

            return back()->with('status', 'Ocorrência atualizada.');
        }

        if ($agendaEvent->isRecurring() && $recurrenceId !== null && $scope === 'following') {
            $clone = $this->synchronizer->splitSeries(
                $agendaEvent,
                $recurrenceId,
                Carbon::parse($attributes['start_at']),
                Carbon::parse($attributes['end_at']),
            );
            $clone->update($attributes);

            $created = $this->synchronizer->syncAttendees($clone, $request->attendeeEmails());
            $this->synchronizer->syncReminders($clone, $request->reminders());
            $this->notifyAttendees($clone, $created);

            return back()->with('status', 'Série atualizada a partir desta ocorrência.');
        }

        $agendaEvent->update($attributes);

        $created = $this->synchronizer->syncAttendees($agendaEvent, $request->attendeeEmails());
        $this->synchronizer->syncReminders($agendaEvent, $request->reminders());
        $this->notifyAttendees($agendaEvent, $created);

        return back()->with('status', 'Evento atualizado.');
    }

    /**
     * Delete an event or cancel a single occurrence of a series.
     */
    public function destroy(Request $request, AgendaEvent $agendaEvent): RedirectResponse
    {
        $this->authorize('delete', $agendaEvent);

        $scope = (string) $request->input('scope', 'all');
        $recurrenceId = $request->filled('recurrence_id')
            ? Carbon::parse($request->string('recurrence_id')->toString())
            : null;

        if ($agendaEvent->isRecurring() && $scope === 'this' && $recurrenceId !== null) {
            $this->synchronizer->applyException($agendaEvent, $recurrenceId);

            return back()->with('status', 'Ocorrência cancelada.');
        }

        $agendaEvent->delete();

        return back()->with('status', 'Evento excluído.');
    }

    /**
     * Duplicate an event together with its guests and reminders.
     */
    public function duplicate(AgendaEvent $agendaEvent): RedirectResponse
    {
        $this->authorize('create', AgendaEvent::class);
        $this->authorize('update', $agendaEvent->calendar);

        $agendaEvent->loadMissing(['attendees', 'reminders']);

        $clone = $agendaEvent->replicate();
        $clone->title = $agendaEvent->title.' (cópia)';
        $clone->sequence = 0;
        $clone->save();

        foreach ($agendaEvent->attendees as $attendee) {
            $clone->attendees()->create($attendee->only([
                'user_id', 'email', 'name', 'status', 'is_optional', 'is_organizer', 'is_resource', 'comment', 'responded_at',
            ]));
        }

        foreach ($agendaEvent->reminders as $reminder) {
            $clone->reminders()->create($reminder->only(['method', 'minutes_before', 'trigger_at']));
        }

        return back()->with('status', 'Evento duplicado.');
    }

    /**
     * Record the attendance response of the authenticated user.
     */
    public function respond(AgendaEventResponseRequest $request, AgendaEvent $agendaEvent): RedirectResponse
    {
        $attendee = $agendaEvent->attendees()
            ->where('user_id', $request->user()->getKey())
            ->firstOrFail();

        $attendee->update([
            'status' => $request->input('status'),
            'comment' => $request->filled('comment') ? $request->string('comment')->toString() : null,
            'responded_at' => now(),
        ]);

        return back()->with('status', 'Resposta registrada.');
    }

    /**
     * Extract the display overrides of a single occurrence from the attributes.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function overridesFrom(array $attributes): array
    {
        $overrides = [];

        foreach (['title', 'description', 'location', 'meeting_url', 'is_all_day', 'status', 'visibility', 'transparency', 'color'] as $key) {
            if (array_key_exists($key, $attributes)) {
                $overrides[$key] = $attributes[$key];
            }
        }

        $overrides['start_at'] = Carbon::parse($attributes['start_at'])->toDateTimeString();
        $overrides['end_at'] = Carbon::parse($attributes['end_at'])->toDateTimeString();

        return $overrides;
    }

    /**
     * Notify the newly invited users.
     *
     * @param  list<AgendaEventAttendee>  $attendees
     */
    private function notifyAttendees(AgendaEvent $event, array $attendees): void
    {
        foreach ($attendees as $attendee) {
            if ($attendee->user !== null) {
                $attendee->user->notify(new AgendaEventInvitationNotification($event, $attendee));
            }
        }
    }
}
