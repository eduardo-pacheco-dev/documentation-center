<?php

use App\Enums\AgendaCalendarRole;
use App\Models\AgendaCalendar;
use App\Models\AgendaCalendarShare;
use App\Models\AgendaEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-02 08:00:00', config('app.timezone')));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('creates a calendar for the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/agenda/calendars', [
            'name' => 'Trabalho',
            'color' => 'tomato',
            'timezone' => 'America/Sao_Paulo',
            'default_reminders' => [
                ['method' => 'email', 'minutes_before' => 60],
            ],
        ])
        ->assertRedirect();

    $calendar = $user->agendaCalendars()->firstOrFail();

    expect($calendar->name)->toBe('Trabalho')
        ->and($calendar->color->value)->toBe('tomato')
        ->and($calendar->is_default)->toBeTrue()
        ->and($calendar->defaultReminders())->toBe([['method' => 'email', 'minutes_before' => 60]]);
});

it('updates a calendar the user owns', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create(['name' => 'Antigo']);

    $this->actingAs($user)
        ->put("/admin/agenda/calendars/{$calendar->getKey()}", [
            'name' => 'Novo nome',
            'color' => 'basil',
            'timezone' => 'UTC',
        ])
        ->assertRedirect();

    expect($calendar->refresh()->name)->toBe('Novo nome')
        ->and($calendar->color->value)->toBe('basil');
});

it('forbids updating a calendar the user only reads', function () {
    $owner = User::factory()->create();
    $reader = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();
    AgendaCalendarShare::factory()->for($calendar, 'calendar')->for($reader)->create();

    $this->actingAs($reader)
        ->put("/admin/agenda/calendars/{$calendar->getKey()}", [
            'name' => 'Hack',
            'color' => 'basil',
        ])
        ->assertForbidden();
});

it('deletes a calendar owned by the user together with its events', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    AgendaEvent::factory()->forCalendar($calendar)->create();

    $this->actingAs($user)
        ->delete("/admin/agenda/calendars/{$calendar->getKey()}")
        ->assertRedirect();

    expect(AgendaCalendar::query()->count())->toBe(0)
        ->and(AgendaEvent::query()->count())->toBe(0);
});

it('makes a calendar the default and unsets the previous one', function () {
    $user = User::factory()->create();
    $first = AgendaCalendar::factory()->for($user)->create(['is_default' => true]);
    $second = AgendaCalendar::factory()->for($user)->create(['is_default' => false]);

    $this->actingAs($user)
        ->patch("/admin/agenda/calendars/{$second->getKey()}/default")
        ->assertRedirect();

    expect($second->refresh()->is_default)->toBeTrue()
        ->and($first->refresh()->is_default)->toBeFalse();
});

it('toggles the default visibility of a calendar as json', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create(['is_visible' => true]);

    $this->actingAs($user)
        ->patchJson("/admin/agenda/calendars/{$calendar->getKey()}/visibility", ['is_visible' => false])
        ->assertOk()
        ->assertJsonPath('is_visible', false);

    expect($calendar->refresh()->is_visible)->toBeFalse();
});

it('shares a calendar with another user', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();

    $this->actingAs($owner)
        ->post("/admin/agenda/calendars/{$calendar->getKey()}/shares", [
            'email' => $guest->email,
            'role' => 'writer',
        ])
        ->assertRedirect();

    $share = $calendar->shares()->firstOrFail();

    expect($share->user_id)->toBe($guest->getKey())
        ->and($share->role)->toBe(AgendaCalendarRole::Writer);
});

it('revokes a share on the calendar', function () {
    $owner = User::factory()->create();
    $guest = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();
    $share = AgendaCalendarShare::factory()->for($calendar, 'calendar')->for($guest)->create();

    $this->actingAs($owner)
        ->delete("/admin/agenda/calendars/{$calendar->getKey()}/shares/{$share->getKey()}")
        ->assertRedirect();

    expect(AgendaCalendarShare::query()->count())->toBe(0);
});

it('lets a writer create an event on a shared calendar', function () {
    $owner = User::factory()->create();
    $writer = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();
    AgendaCalendarShare::factory()->for($calendar, 'calendar')->for($writer)->writer()->create();

    $this->actingAs($writer)
        ->post('/admin/agenda/events', [
            'agenda_calendar_id' => $calendar->getKey(),
            'title' => 'Evento compartilhado',
            'start_at' => '2026-03-06T09:00',
            'end_at' => '2026-03-06T10:00',
        ])
        ->assertRedirect();

    expect(AgendaEvent::query()->where('title', 'Evento compartilhado')->exists())->toBeTrue();
});

it('shows a shared calendar in the agenda of the reader', function () {
    $owner = User::factory()->create();
    $reader = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create(['name' => 'Compartilhado']);
    AgendaCalendarShare::factory()->for($calendar, 'calendar')->for($reader)->create();
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Evento do dono',
        'start_at' => Carbon::parse('2026-03-04 09:00:00'),
        'end_at' => Carbon::parse('2026-03-04 10:00:00'),
    ]);

    $this->actingAs($reader)
        ->get('/admin/agenda?view=list&date=2026-03-04')
        ->assertOk()
        ->assertSee('Compartilhado')
        ->assertSee('Evento do dono');
});

it('hides a calendar from the grid when it is not selected', function () {
    $user = User::factory()->create();
    $visible = AgendaCalendar::factory()->for($user)->create(['name' => 'Visível']);
    $hidden = AgendaCalendar::factory()->for($user)->create(['name' => 'Oculto']);

    AgendaEvent::factory()->forCalendar($hidden)->create([
        'title' => 'Evento oculto',
        'start_at' => Carbon::parse('2026-03-04 09:00:00'),
        'end_at' => Carbon::parse('2026-03-04 10:00:00'),
    ]);

    $this->actingAs($user)
        ->get("/admin/agenda?view=list&date=2026-03-04&calendars[]={$visible->getKey()}")
        ->assertOk()
        ->assertDontSee('Evento oculto');
});
