<?php

use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\AgendaEventReminder;
use App\Models\User;
use App\Services\Agenda\AgendaIcsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    Carbon::setTestNow(Carbon::parse('2026-03-02 08:00:00', config('app.timezone')));
});

afterEach(function () {
    Carbon::setTestNow();
});

it('exports the events of the user as an ics document', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    AgendaEvent::factory()->forCalendar($calendar)->create([
        'title' => 'Evento exportado',
        'start_at' => Carbon::parse('2026-03-04 09:00:00'),
        'end_at' => Carbon::parse('2026-03-04 10:00:00'),
    ]);

    $response = $this->actingAs($user)->get('/admin/agenda/export');

    $response->assertOk()->assertHeader('Content-Type', 'text/calendar; charset=utf-8');

    $contents = $response->getContent();

    expect($contents)->toContain('BEGIN:VCALENDAR')
        ->and($contents)->toContain('SUMMARY:Evento exportado')
        ->and($contents)->toContain('END:VCALENDAR');
});

it('exports the recurrence rule and the cancelled occurrences', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->recurring()->create([
        'start_at' => Carbon::parse('2026-03-02 09:00:00'),
        'end_at' => Carbon::parse('2026-03-02 10:00:00'),
    ]);

    $event->exceptions()->create([
        'recurrence_id' => Carbon::parse('2026-03-09 09:00:00'),
        'is_cancelled' => true,
    ]);

    $fresh = AgendaEvent::query()
        ->with(['attendees', 'reminders', 'exceptions', 'user'])
        ->findOrFail($event->getKey());

    $contents = app(AgendaIcsService::class)->export(collect([$fresh]));

    expect($contents)->toContain('RRULE:FREQ=WEEKLY')
        ->and($contents)->toContain('EXDATE:');
});

it('imports events from an uploaded ics file', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();

    $ics = implode("\r\n", [
        'BEGIN:VCALENDAR',
        'VERSION:2.0',
        'BEGIN:VEVENT',
        'UID:import-1@example.com',
        'DTSTART:20260401T130000Z',
        'DTEND:20260401T140000Z',
        'SUMMARY:Evento importado',
        'DESCRIPTION:Descrição do evento',
        'LOCATION:Sala 2',
        'RRULE:FREQ=DAILY;COUNT=3',
        'BEGIN:VALARM',
        'ACTION:DISPLAY',
        'TRIGGER:-PT15M',
        'END:VALARM',
        'END:VEVENT',
        'END:VCALENDAR',
    ]);

    $path = tempnam(sys_get_temp_dir(), 'agenda').'.ics';
    file_put_contents($path, $ics);
    $file = new UploadedFile($path, 'agenda.ics', 'text/calendar', null, true);

    $this->actingAs($user)
        ->post('/admin/agenda/import', [
            'agenda_calendar_id' => $calendar->getKey(),
            'file' => $file,
        ])
        ->assertRedirect();

    $event = AgendaEvent::query()->firstOrFail();

    expect($event->title)->toBe('Evento importado')
        ->and($event->location)->toBe('Sala 2')
        ->and($event->recurrence['frequency'])->toBe('daily')
        ->and($event->recurrence['count'])->toBe(3)
        ->and($event->reminders()->first()->minutes_before)->toBe(15);
});

it('requires a writable calendar to import', function () {
    $owner = User::factory()->create();
    $stranger = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($owner)->create();

    $path = tempnam(sys_get_temp_dir(), 'agenda').'.ics';
    file_put_contents($path, "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nBEGIN:VEVENT\r\nUID:auth@example.com\r\nDTSTART:20260401T130000Z\r\nDTEND:20260401T140000Z\r\nSUMMARY:Alheio\r\nEND:VEVENT\r\nEND:VCALENDAR");
    $file = new UploadedFile($path, 'agenda.ics', 'text/calendar', null, true);

    $this->actingAs($stranger)
        ->post('/admin/agenda/import', [
            'agenda_calendar_id' => $calendar->getKey(),
            'file' => $file,
        ])
        ->assertForbidden();
});

it('dispatches due email reminders and marks them as sent', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create([
        'start_at' => Carbon::parse('2026-03-02 08:30:00'),
        'end_at' => Carbon::parse('2026-03-02 09:30:00'),
    ]);
    $reminder = AgendaEventReminder::factory()->for($event, 'event')->create([
        'method' => 'email',
        'minutes_before' => 30,
        'sent_at' => null,
    ]);

    $this->artisan('agenda:dispatch-reminders')->assertSuccessful();

    expect($reminder->refresh()->sent_at)->not->toBeNull();
});

it('does not send a reminder that is not due yet', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create([
        'start_at' => Carbon::parse('2026-03-02 18:00:00'),
        'end_at' => Carbon::parse('2026-03-02 19:00:00'),
    ]);
    $reminder = AgendaEventReminder::factory()->for($event, 'event')->create([
        'method' => 'email',
        'minutes_before' => 10,
        'sent_at' => null,
    ]);

    $this->artisan('agenda:dispatch-reminders')->assertSuccessful();

    expect($reminder->refresh()->sent_at)->toBeNull();
});

it('lists pending reminders in dry run mode without sending', function () {
    $user = User::factory()->create();
    $calendar = AgendaCalendar::factory()->for($user)->create();
    $event = AgendaEvent::factory()->forCalendar($calendar)->create([
        'start_at' => Carbon::parse('2026-03-02 08:30:00'),
        'end_at' => Carbon::parse('2026-03-02 09:30:00'),
    ]);
    $reminder = AgendaEventReminder::factory()->for($event, 'event')->create([
        'method' => 'email',
        'minutes_before' => 30,
        'sent_at' => null,
    ]);

    $this->artisan('agenda:dispatch-reminders --dry-run')->assertSuccessful();

    expect($reminder->refresh()->sent_at)->toBeNull();
});
