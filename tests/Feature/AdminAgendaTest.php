<?php

use App\Enums\AgendaAttendeeStatus;
use App\Enums\AgendaColor;
use App\Enums\AgendaEventStatus;
use App\Enums\AgendaRecurrenceFrequency;
use App\Enums\AgendaTransparency;
use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\User;
use App\Services\Agenda\AgendaEventExpander;
use App\Services\Agenda\AgendaEventSynchronizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-02 08:00:00', config('app.timezone')));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('requires an authenticated user to open the agenda', function () {
    $this->get('/admin/agenda')->assertRedirect('/login');
});

it('renders the agenda and provisions a default calendar on the first visit', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/agenda')
        ->assertOk()
        ->assertSee('Meus calendários')
        ->assertSee('Minha agenda');

    expect($user->agendaCalendars()->count())->toBe(1)
        ->and($user->agendaCalendars()->first()->is_default)->toBeTrue();
});

it('renders the month, week, day and list views', function (string $view) {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Reunião de alinhamento',
        'start_at' => Carbon::parse('2026-03-02 10:00:00'),
        'end_at' => Carbon::parse('2026-03-02 11:00:00'),
    ]);

    $this->actingAs($user)
        ->get("/admin/agenda?view={$view}&date=2026-03-02")
        ->assertOk()
        ->assertSee('Reunião de alinhamento');
})->with(['month', 'week', 'day', 'list']);

it('creates a timed event with guests and reminders', function () {
    $user = User::factory()->create();
    $guest = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Planejamento',
            'start_at' => '2026-03-03T09:00',
            'end_at' => '2026-03-03T10:30',
            'location' => 'Sala 1',
            'attendees' => [$guest->email],
            'reminders' => [
                ['method' => 'email', 'minutes_before' => 30],
            ],
        ])
        ->assertRedirect();

    $event = AgendaEvent::query()->firstOrFail();

    expect($event->title)->toBe('Planejamento')
        ->and($event->location)->toBe('Sala 1')
        ->and($event->durationInMinutes())->toBe(90)
        ->and($event->attendees()->pluck('email')->all())->toContain($guest->email)
        ->and($event->reminders()->pluck('minutes_before')->all())->toBe([30]);
});

it('normalizes all day events to exclusive day bounds', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Feriado',
            'start_at' => '2026-03-05T00:00',
            'end_at' => '2026-03-05T00:00',
            'is_all_day' => '1',
        ])
        ->assertRedirect();

    $event = AgendaEvent::query()->firstOrFail();

    expect($event->is_all_day)->toBeTrue()
        ->and($event->start_at->toDateString())->toBe('2026-03-05')
        ->and($event->end_at->toDateString())->toBe('2026-03-06');
});

it('stores the recurrence rule from the flat form fields', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Standup',
            'start_at' => '2026-03-02T09:00',
            'end_at' => '2026-03-02T09:15',
            'repeat_frequency' => 'weekly',
            'repeat_interval' => '1',
            'repeat_end_mode' => 'count',
            'repeat_count' => '5',
            'repeat_weekdays' => ['1', '3'],
        ])
        ->assertRedirect();

    $event = AgendaEvent::query()->firstOrFail();

    expect($event->isRecurring())->toBeTrue()
        ->and($event->recurrence['frequency'])->toBe('weekly')
        ->and($event->recurrence['count'])->toBe(5)
        ->and($event->recurrence['byweekday'])->toBe([1, 3]);
});

it('rejects an event whose end precedes its start', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Inválido',
            'start_at' => '2026-03-03T10:00',
            'end_at' => '2026-03-03T09:00',
        ])
        ->assertSessionHasErrors('end_at');

    expect(AgendaEvent::query()->count())->toBe(0);
});

it('does not let a user create an event on a calendar they cannot write to', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();

    $this->actingAs($stranger)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Invasão',
            'start_at' => '2026-03-03T10:00',
            'end_at' => '2026-03-03T11:00',
        ])
        ->assertForbidden();

    expect(AgendaEvent::query()->count())->toBe(0);
});

it('updates a whole series when the scope is all', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->recurring()->create([
        'title' => 'Antigo',
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    $this->actingAs($user)
        ->put("/admin/agenda/events/{$event->getKey()}", [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Novo',
            'start_at' => '2026-03-02T09:00',
            'end_at' => '2026-03-02T10:00',
            'repeat_frequency' => 'weekly',
            'repeat_interval' => '1',
            'repeat_end_mode' => 'never',
            'scope' => 'all',
        ])
        ->assertRedirect();

    expect($event->refresh()->title)->toBe('Novo')
        ->and($event->exceptions()->count())->toBe(0);
});

it('stores an override when only one occurrence of a series is updated', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->recurring()->create([
        'title' => 'Reunião',
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    $this->actingAs($user)
        ->put("/admin/agenda/events/{$event->getKey()}", [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Reunião remarcada',
            'start_at' => '2026-03-09T14:00',
            'end_at' => '2026-03-09T15:00',
            'repeat_frequency' => 'weekly',
            'repeat_interval' => '1',
            'repeat_end_mode' => 'never',
            'scope' => 'this',
            'recurrence_id' => '2026-03-09T09:00',
        ])
        ->assertRedirect();

    $exception = $event->refresh()->exceptions()->firstOrFail();

    expect($exception->is_cancelled)->toBeFalse()
        ->and($exception->overrides['title'])->toBe('Reunião remarcada');
});

it('cancels a single occurrence instead of deleting the series', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->recurring()->create([
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    $this->actingAs($user)
        ->delete("/admin/agenda/events/{$event->getKey()}", [
            'scope' => 'this',
            'recurrence_id' => '2026-03-09T09:00',
        ])
        ->assertRedirect();

    expect($event->refresh()->exists)->toBeTrue()
        ->and($event->exceptions()->where('is_cancelled', true)->count())->toBe(1);
});

it('deletes the event and its guests', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create();
    $event->attendees()->create(['email' => 'guest@example.com']);

    $this->actingAs($user)
        ->delete("/admin/agenda/events/{$event->getKey()}")
        ->assertRedirect();

    expect(AgendaEvent::query()->count())->toBe(0)
        ->and($event->attendees()->count())->toBe(0);
});

it('duplicates an event with its guests and reminders', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create(['title' => 'Original']);
    $event->attendees()->create(['email' => 'guest@example.com']);
    $event->reminders()->create(['method' => 'popup', 'minutes_before' => 15]);

    $this->actingAs($user)
        ->post("/admin/agenda/events/{$event->getKey()}/duplicate")
        ->assertRedirect();

    $clone = AgendaEvent::query()->whereKeyNot($event->getKey())->firstOrFail();

    expect($clone->title)->toBe('Original (cópia)')
        ->and($clone->attendees()->count())->toBe(1)
        ->and($clone->reminders()->count())->toBe(1);
});

it('records the attendance response of a guest', function () {
    $organizer = User::factory()->create();
    $guest = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($organizer)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create();
    $event->attendees()->create(['user_id' => $guest->getKey(), 'email' => $guest->email]);

    $this->actingAs($guest)
        ->patch("/admin/agenda/events/{$event->getKey()}/respond", [
            'status' => 'accepted',
            'comment' => 'Confirmado!',
        ])
        ->assertRedirect();

    $attendee = $event->attendees()->where('user_id', $guest->getKey())->firstOrFail();

    expect($attendee->status)->toBe(AgendaAttendeeStatus::Accepted)
        ->and($attendee->responded_at)->not->toBeNull();
});

it('moves an event through drag and drop', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create([
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    $this->actingAs($user)
        ->patchJson("/admin/agenda/events/{$event->getKey()}/move", [
            'start_at' => '2026-03-02T11:00',
            'end_at' => '2026-03-02T12:00',
            'scope' => 'all',
        ])
        ->assertOk();

    expect($event->refresh()->start_at->format('H:i'))->toBe('11:00');
});

it('expands a weekly series honoring cancelled occurrences', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->recurring()->create([
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    app(AgendaEventSynchronizer::class)->applyException($event, Carbon::parse('2026-03-09 09:00:00'));

    $occurrences = app(AgendaEventExpander::class)->expand(
        $event->refresh(),
        Carbon::parse('2026-03-01 00:00:00'),
        Carbon::parse('2026-03-31 23:59:59'),
    );

    expect($occurrences)->toHaveCount(4)
        ->and(collect($occurrences)->map(fn ($occurrence): string => $occurrence->start->toDateString())->all())
        ->toBe(['2026-03-02', '2026-03-16', '2026-03-23', '2026-03-30']);
});

it('returns the busy slots of the visible calendars', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Ocupado',
        'start_at' => Carbon::parse('2026-03-03 09:00:00'),
        'end_at' => Carbon::parse('2026-03-03 10:00:00'),
    ]);
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Livre',
        'transparency' => AgendaTransparency::Transparent,
        'start_at' => Carbon::parse('2026-03-03 11:00:00'),
        'end_at' => Carbon::parse('2026-03-03 12:00:00'),
    ]);

    $this->actingAs($user)
        ->getJson('/admin/agenda/free-busy?date=2026-03-03')
        ->assertOk()
        ->assertJsonCount(1, 'busy')
        ->assertJsonPath('busy.0.title', 'Ocupado');
});

it('filters events by the search term', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Retrospectiva',
        'start_at' => Carbon::parse('2026-03-04 09:00:00'),
        'end_at' => Carbon::parse('2026-03-04 10:00:00'),
    ]);
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Outro evento',
        'start_at' => Carbon::parse('2026-03-04 11:00:00'),
        'end_at' => Carbon::parse('2026-03-04 12:00:00'),
    ]);

    $this->actingAs($user)
        ->get('/admin/agenda?view=list&date=2026-03-04&q=Retrospectiva')
        ->assertOk()
        ->assertSee('Retrospectiva')
        ->assertDontSee('Outro evento');
});

it('paginates the agenda list when it exceeds the page size', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    foreach (range(1, 12) as $index) {
        AgendaEvent::factory()->forCalendar($calendar)->create([
            'title' => "Evento {$index}",
            'start_at' => Carbon::parse('2026-03-02 10:00:00')->addMinutes($index),
            'end_at' => Carbon::parse('2026-03-02 10:30:00')->addMinutes($index),
        ]);
    }

    $this->actingAs($user)
        ->get('/admin/agenda?view=list&date=2026-03-02')
        ->assertOk()
        ->assertSee('Página 1 de 2')
        ->assertSee('Evento 1')
        ->assertDontSee('Evento 12');

    $this->actingAs($user)
        ->get('/admin/agenda?view=list&date=2026-03-02&page=2')
        ->assertOk()
        ->assertSee('Página 2 de 2')
        ->assertSee('Evento 12');
});

it('exposes the color palette through the calendar color enum', function () {
    expect(AgendaColor::cases())->toHaveCount(11)
        ->and(AgendaColor::Tomato->hex())->toStartWith('#');
});

it('resolves the event color from the calendar when none is set', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create(['color' => AgendaColor::Peacock]);
    $event = AgendaEvent::factory()->forCalendar($calendar)->create(['color' => null]);

    expect($event->resolvedColor())->toBe(AgendaColor::Peacock);

    $event->update(['color' => AgendaColor::Grape]);

    expect($event->refresh()->resolvedColor())->toBe(AgendaColor::Grape);
});

it('marks a cancelled event through the status enum', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->cancelled()->create();

    expect($event->status)->toBe(AgendaEventStatus::Cancelled);
});

it('builds recurrence with a monthly frequency', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Fechamento',
            'start_at' => '2026-03-10T15:00',
            'end_at' => '2026-03-10T16:00',
            'repeat_frequency' => 'monthly',
            'repeat_interval' => '2',
            'repeat_end_mode' => 'until',
            'repeat_until' => '2026-12-31',
        ])
        ->assertRedirect();

    $event = AgendaEvent::query()->firstOrFail();

    expect($event->recurrence['frequency'])->toBe(AgendaRecurrenceFrequency::Monthly->value)
        ->and($event->recurrence['interval'])->toBe(2)
        ->and($event->recurrence_until->toDateString())->toBe('2026-12-31');
});
