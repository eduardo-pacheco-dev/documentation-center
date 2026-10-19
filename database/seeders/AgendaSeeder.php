<?php

namespace Database\Seeders;

use App\Enums\AgendaAttendeeStatus;
use App\Enums\AgendaCalendarRole;
use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaEventVisibility;
use App\Enums\AgendaRecurrenceFrequency;
use App\Enums\AgendaReminderMethod;
use App\Enums\AgendaTransparency;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class AgendaSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The maximum number of other users invited to an event.
     */
    private const GUEST_LIMIT = 3;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        foreach ($users as $user) {
            $others = $users->reject(fn (User $candidate): bool => $candidate->is($user))->values();

            $personal = $this->calendar(
                $user,
                'Minha agenda',
                AgendaColor::Blueberry,
                'Compromissos pessoais e profissionais.',
                0,
                [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 10]],
                isDefault: true,
            );

            $work = $this->calendar(
                $user,
                'Trabalho',
                AgendaColor::Peacock,
                'Reuniões, prazos e entregas do trabalho.',
                1,
                [
                    ['method' => AgendaReminderMethod::Email, 'minutes_before' => 30],
                    ['method' => AgendaReminderMethod::Popup, 'minutes_before' => 10],
                ],
            );

            $life = $this->calendar(
                $user,
                'Pessoal',
                AgendaColor::Sage,
                'Compromissos pessoais e familiares.',
                2,
                [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 30]],
            );

            $team = $this->calendar(
                $user,
                'Equipe',
                AgendaColor::Grape,
                'Calendário compartilhado com o time.',
                3,
                [['method' => AgendaReminderMethod::Email, 'minutes_before' => 60]],
            );

            $this->seedPersonalEvents($personal, $user, $others);
            $this->seedWorkEvents($work, $user, $others);
            $this->seedLifeEvents($life, $user, $others);
            $this->seedTeamEvents($team, $user, $others);
            $this->shareTeamCalendar($team, $others);
        }
    }

    /**
     * Create (or reuse) one calendar of the given user.
     *
     * @param  list<array{method: AgendaReminderMethod, minutes_before: int}>  $defaultReminders
     */
    private function calendar(
        User $user,
        string $name,
        AgendaColor $color,
        string $description,
        int $position,
        array $defaultReminders = [],
        bool $isDefault = false,
    ): AgendaCalendar {
        return AgendaCalendar::query()->firstOrCreate(
            [
                'user_id' => $user->getKey(),
                'name' => $name,
            ],
            [
                'description' => $description,
                'color' => $color,
                'timezone' => config('app.timezone'),
                'is_default' => $isDefault,
                'is_visible' => true,
                'position' => $position,
                'default_reminders' => array_map(
                    fn (array $reminder): array => [
                        'method' => $reminder['method']->value,
                        'minutes_before' => $reminder['minutes_before'],
                    ],
                    $defaultReminders,
                ),
            ],
        );
    }

    /**
     * Seed the events of the main calendar.
     *
     * @param  Collection<int, User>  $others
     */
    private function seedPersonalEvents(AgendaCalendar $calendar, User $owner, Collection $others): void
    {
        $monday = now()->startOfWeek(Carbon::MONDAY);

        $weekly = $this->event(
            $owner,
            $calendar,
            'Alinhamento semanal da equipe',
            $monday->copy()->setTime(9, 0),
            $monday->copy()->setTime(10, 0),
            [
                'description' => 'Revisão das prioridades da semana e dos bloqueios em aberto.',
                'location' => 'Sala de reuniões',
                'meeting_url' => 'https://meet.example.com/alinhamento-semanal',
                'color' => AgendaColor::Peacock,
                'recurrence' => [
                    'frequency' => AgendaRecurrenceFrequency::Weekly->value,
                    'interval' => 1,
                    'byweekday' => [Carbon::MONDAY],
                ],
                'recurrence_until' => $monday->copy()->addMonths(2)->toDateString(),
            ],
            $this->guests($others),
            [
                ['method' => AgendaReminderMethod::Popup, 'minutes_before' => 10],
                ['method' => AgendaReminderMethod::Email, 'minutes_before' => 30],
            ],
        );

        $this->exception($weekly, $monday->copy()->addWeek()->setTime(9, 0));
        $this->exception($weekly, $monday->copy()->addWeeks(2)->setTime(9, 0), [
            'title' => 'Alinhamento semanal (horário alterado)',
            'location' => 'Sala 2',
            'start_at' => $monday->copy()->addWeeks(2)->setTime(10, 30)->toDateTimeString(),
            'end_at' => $monday->copy()->addWeeks(2)->setTime(11, 30)->toDateTimeString(),
        ]);

        $this->event(
            $owner,
            $calendar,
            'Almoço com o time',
            today()->setTime(12, 0),
            today()->setTime(13, 30),
            [
                'description' => 'Confira a pauta: retrospectiva rápida e próximos passos do projeto.',
                'location' => 'Restaurante Central',
                'transparency' => AgendaTransparency::Transparent,
            ],
            $this->guests($others),
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 15]],
        );

        $planningStart = now()->addDays(3)->setTime(14, 0);

        $this->event(
            $owner,
            $calendar,
            'Planejamento do trimestre',
            $planningStart,
            $planningStart->copy()->addHours(2),
            [
                'description' => 'Definição das metas, entregas e responsáveis do próximo trimestre.',
                'location' => 'Auditório',
                'meeting_url' => 'https://meet.example.com/planejamento-trimestre',
                'visibility' => AgendaEventVisibility::Public,
                'color' => AgendaColor::Lavender,
                'attachments' => [
                    [
                        'title' => 'Pauta do planejamento',
                        'url' => 'https://example.com/documentos/pauta-planejamento.pdf',
                        'mime_type' => 'application/pdf',
                    ],
                ],
            ],
            $this->guests($others, 2),
            [['method' => AgendaReminderMethod::Email, 'minutes_before' => 60]],
        );

        $retroStart = now()->endOfMonth()->setTime(16, 0);

        $this->event(
            $owner,
            $calendar,
            'Retrospectiva do mês',
            $retroStart,
            $retroStart->copy()->addMinutes(90),
            [
                'description' => 'O que funcionou, o que pode melhorar e as ações para o próximo mês.',
                'location' => 'Sala de reuniões',
            ],
            $this->guests($others),
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 30]],
        );

        $dayOff = now()->addDays(20)->startOfDay();

        $this->event(
            $owner,
            $calendar,
            'Dia de folga',
            $dayOff,
            $dayOff->copy()->addDay(),
            [
                'description' => 'Dia reservado para descanso e assuntos pessoais.',
                'is_all_day' => true,
                'status' => AgendaEventStatus::Tentative,
                'transparency' => AgendaTransparency::Transparent,
                'color' => AgendaColor::Banana,
            ],
        );
    }

    /**
     * Seed the events of the work calendar.
     *
     * @param  Collection<int, User>  $others
     */
    private function seedWorkEvents(AgendaCalendar $calendar, User $owner, Collection $others): void
    {
        $dailyStart = today()->setTime(9, 30);

        $this->event(
            $owner,
            $calendar,
            'Daily da equipe',
            $dailyStart,
            $dailyStart->copy()->addMinutes(15),
            [
                'description' => 'Atualização rápida de cada pessoa do time.',
                'meeting_url' => 'https://meet.example.com/daily',
                'transparency' => AgendaTransparency::Transparent,
                'color' => AgendaColor::Basil,
                'recurrence' => [
                    'frequency' => AgendaRecurrenceFrequency::Weekly->value,
                    'interval' => 1,
                    'byweekday' => [1, 2, 3, 4, 5],
                ],
                'recurrence_until' => today()->addMonths(2)->toDateString(),
            ],
            $this->guests($others),
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 5]],
        );

        $reviewStart = now()->addDay()->setTime(15, 0);

        $this->event(
            $owner,
            $calendar,
            'Revisão de código',
            $reviewStart,
            $reviewStart->copy()->addHour(),
            [
                'description' => 'Revisão do pull request com o time de desenvolvimento.',
                'meeting_url' => 'https://meet.example.com/revisao-codigo',
                'transparency' => AgendaTransparency::Transparent,
                'color' => AgendaColor::Sage,
            ],
            $this->guests($others, 2),
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 10]],
        );

        $trainingStart = now()->addDays(7)->setTime(10, 0);

        $this->event(
            $owner,
            $calendar,
            'Treinamento interno',
            $trainingStart,
            $trainingStart->copy()->addHours(2),
            [
                'description' => 'Boas práticas de documentação e padronização dos processos internos.',
                'location' => 'Sala de treinamento',
                'status' => AgendaEventStatus::Tentative,
            ],
            $this->guests($others),
            [['method' => AgendaReminderMethod::Email, 'minutes_before' => 120]],
        );

        $proposalDeadline = now()->addDays(10)->startOfDay();

        $this->event(
            $owner,
            $calendar,
            'Prazo da proposta comercial',
            $proposalDeadline,
            $proposalDeadline->copy()->addDay(),
            [
                'description' => 'Enviar a proposta revisada para o cliente.',
                'is_all_day' => true,
                'color' => AgendaColor::Tomato,
            ],
            null,
            [['method' => AgendaReminderMethod::Email, 'minutes_before' => 1440]],
        );

        $clientStart = now()->addDays(14)->setTime(11, 0);

        $this->event(
            $owner,
            $calendar,
            'Reunião com cliente',
            $clientStart,
            $clientStart->copy()->addHour(),
            [
                'description' => 'Apresentação dos resultados e alinhamento dos próximos passos.',
                'location' => 'Sede do cliente',
                'meeting_url' => 'https://meet.example.com/cliente',
                'visibility' => AgendaEventVisibility::Private,
                'color' => AgendaColor::Tangerine,
            ],
            $this->guests($others, 2),
            [['method' => AgendaReminderMethod::Email, 'minutes_before' => 60]],
        );
    }

    /**
     * Seed the events of the personal calendar.
     *
     * @param  Collection<int, User>  $others
     */
    private function seedLifeEvents(AgendaCalendar $calendar, User $owner, Collection $others): void
    {
        $gymStart = today()->setTime(18, 0);

        $this->event(
            $owner,
            $calendar,
            'Academia',
            $gymStart,
            $gymStart->copy()->addHour(),
            [
                'description' => 'Treino de força e condicionamento.',
                'location' => 'Academia do bairro',
                'transparency' => AgendaTransparency::Transparent,
                'color' => AgendaColor::Sage,
                'recurrence' => [
                    'frequency' => AgendaRecurrenceFrequency::Weekly->value,
                    'interval' => 1,
                    'byweekday' => [2, 4],
                    'count' => 20,
                ],
            ],
            null,
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 60]],
        );

        $appointmentStart = now()->addDays(5)->setTime(8, 0);

        $this->event(
            $owner,
            $calendar,
            'Consulta médica',
            $appointmentStart,
            $appointmentStart->copy()->addHour(),
            [
                'description' => 'Consulta de rotina e revisão dos exames.',
                'location' => 'Clínica Bem Viver',
                'visibility' => AgendaEventVisibility::Private,
            ],
            null,
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 60]],
        );

        $birthday = now()->addDays(9)->startOfDay();

        $this->event(
            $owner,
            $calendar,
            'Aniversário da Ana',
            $birthday,
            $birthday->copy()->addDay(),
            [
                'description' => 'Reservar o bolo e confirmar a presença dos convidados.',
                'is_all_day' => true,
                'color' => AgendaColor::Flamingo,
            ],
        );

        $billStart = now()->startOfMonth()->addMonthNoOverflow()->day(10)->setTime(9, 0);

        $this->event(
            $owner,
            $calendar,
            'Pagar as contas do mês',
            $billStart,
            $billStart->copy()->addMinutes(30),
            [
                'description' => 'Conferir os vencimentos e pagar as contas recorrentes.',
                'color' => AgendaColor::Banana,
                'recurrence' => [
                    'frequency' => AgendaRecurrenceFrequency::Monthly->value,
                    'interval' => 1,
                    'bymonthday' => [10],
                    'count' => 12,
                ],
            ],
            null,
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 1440]],
        );
    }

    /**
     * Seed the events of the shared team calendar.
     *
     * @param  Collection<int, User>  $others
     */
    private function seedTeamEvents(AgendaCalendar $calendar, User $owner, Collection $others): void
    {
        $retroStart = now()->addDays(6)->setTime(16, 0);

        $this->event(
            $owner,
            $calendar,
            'Retrospectiva da equipe',
            $retroStart,
            $retroStart->copy()->addMinutes(90),
            [
                'description' => 'Revisão do ciclo, aprendizados e ações de melhoria.',
                'location' => 'Sala de reuniões',
                'meeting_url' => 'https://meet.example.com/retrospectiva',
            ],
            $this->guests($others, self::GUEST_LIMIT),
            [['method' => AgendaReminderMethod::Email, 'minutes_before' => 60]],
        );

        $sprintStart = now()->addDay()->setTime(10, 0);

        $this->event(
            $owner,
            $calendar,
            'Planejamento de sprint',
            $sprintStart,
            $sprintStart->copy()->addMinutes(90),
            [
                'description' => 'Seleção dos itens do próximo ciclo e definição das metas.',
                'location' => 'Sala de reuniões',
                'color' => AgendaColor::Grape,
                'recurrence' => [
                    'frequency' => AgendaRecurrenceFrequency::Weekly->value,
                    'interval' => 2,
                    'byweekday' => [Carbon::TUESDAY],
                    'count' => 8,
                ],
            ],
            $this->guests($others),
            [['method' => AgendaReminderMethod::Popup, 'minutes_before' => 15]],
        );

        $happyHourStart = now()->addDays(4)->setTime(18, 30);

        $this->event(
            $owner,
            $calendar,
            'Happy hour do time',
            $happyHourStart,
            $happyHourStart->copy()->addMinutes(90),
            [
                'description' => 'Confraternização informal do time.',
                'location' => 'Bar do Zé',
                'transparency' => AgendaTransparency::Transparent,
                'color' => AgendaColor::Tangerine,
            ],
            $this->guests($others, self::GUEST_LIMIT),
        );
    }

    /**
     * Share the team calendar with some of the other users.
     *
     * @param  Collection<int, User>  $others
     */
    private function shareTeamCalendar(AgendaCalendar $calendar, Collection $others): void
    {
        $roles = [
            AgendaCalendarRole::Writer,
            AgendaCalendarRole::Reader,
            AgendaCalendarRole::FreeBusyReader,
        ];

        foreach ($others->take(count($roles))->values() as $index => $member) {
            $calendar->shares()->firstOrCreate(
                ['user_id' => $member->getKey()],
                ['role' => $roles[$index]],
            );
        }
    }

    /**
     * Create (or reuse) one event and synchronize its related records.
     *
     * @param  array<string, mixed>  $attributes
     * @param  Collection<int, User>|null  $guests
     * @param  list<array{method: AgendaReminderMethod, minutes_before: int}>  $reminders
     */
    private function event(
        User $owner,
        AgendaCalendar $calendar,
        string $title,
        Carbon $start,
        Carbon $end,
        array $attributes = [],
        ?Collection $guests = null,
        array $reminders = [],
    ): AgendaEvent {
        $event = AgendaEvent::query()->firstOrCreate(
            [
                'agenda_calendar_id' => $calendar->getKey(),
                'title' => $title,
                'start_at' => $start,
            ],
            array_merge([
                'user_id' => $owner->getKey(),
                'end_at' => $end,
                'is_all_day' => false,
                'timezone' => $calendar->timezone,
                'status' => AgendaEventStatus::Confirmed,
                'visibility' => AgendaEventVisibility::Default,
                'transparency' => AgendaTransparency::Opaque,
                'attendees_can_invite_others' => true,
                'attendees_can_modify' => false,
                'attendees_can_see_other_attendees' => true,
                'sequence' => 0,
            ], $attributes),
        );

        $this->attendees($event, $owner, $guests);
        $this->reminders($event, $reminders);

        return $event;
    }

    /**
     * Add the organizer and the invited guests to the event.
     *
     * @param  Collection<int, User>|null  $guests
     */
    private function attendees(AgendaEvent $event, User $owner, ?Collection $guests): void
    {
        $event->attendees()->firstOrCreate(
            ['email' => strtolower($owner->email)],
            [
                'user_id' => $owner->getKey(),
                'name' => $owner->name,
                'status' => AgendaAttendeeStatus::Accepted,
                'is_organizer' => true,
                'responded_at' => now(),
            ],
        );

        foreach (($guests ?? collect())->values() as $index => $guest) {
            $status = $this->guestStatus($index);

            $event->attendees()->firstOrCreate(
                ['email' => strtolower($guest->email)],
                [
                    'user_id' => $guest->getKey(),
                    'name' => $guest->name,
                    'status' => $status,
                    'is_optional' => $index % 3 === 2,
                    'is_organizer' => false,
                    'responded_at' => $status === AgendaAttendeeStatus::NeedsAction ? null : now(),
                ],
            );
        }
    }

    /**
     * Add the configured reminders to the event.
     *
     * @param  list<array{method: AgendaReminderMethod, minutes_before: int}>  $reminders
     */
    private function reminders(AgendaEvent $event, array $reminders): void
    {
        foreach ($reminders as $reminder) {
            $event->reminders()->firstOrCreate([
                'method' => $reminder['method'],
                'minutes_before' => $reminder['minutes_before'],
            ]);
        }
    }

    /**
     * Cancel or override one occurrence of a recurring event.
     *
     * @param  array<string, mixed>  $overrides
     */
    private function exception(AgendaEvent $event, Carbon $recurrenceId, array $overrides = []): void
    {
        $event->exceptions()->firstOrCreate(
            ['recurrence_id' => $recurrenceId],
            [
                'is_cancelled' => $overrides === [],
                'overrides' => $overrides === [] ? null : $overrides,
            ],
        );
    }

    /**
     * The first users invited to an event.
     *
     * @param  Collection<int, User>  $others
     * @return Collection<int, User>
     */
    private function guests(Collection $others, int $limit = self::GUEST_LIMIT): Collection
    {
        return $others->take($limit)->values();
    }

    /**
     * A plausible response status for the guest at the given position.
     */
    private function guestStatus(int $index): AgendaAttendeeStatus
    {
        return match ($index % 4) {
            0 => AgendaAttendeeStatus::Accepted,
            1 => AgendaAttendeeStatus::NeedsAction,
            2 => AgendaAttendeeStatus::Tentative,
            default => AgendaAttendeeStatus::Declined,
        };
    }
}
