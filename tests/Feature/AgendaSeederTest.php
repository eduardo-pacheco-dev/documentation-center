<?php

use App\Models\AgendaCalendar;
use App\Models\AgendaEvent;
use App\Models\AgendaEventAttendee;
use App\Models\User;
use Database\Seeders\AgendaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('seeds the agenda module for every user', function () {
    User::factory()->count(3)->create();

    $this->seed(AgendaSeeder::class);

    foreach (User::all() as $user) {
        expect($user->agendaCalendars()->count())->toBe(4)
            ->and($user->agendaCalendars()->where('is_default', true)->count())->toBe(1)
            ->and($user->agendaEvents()->count())->toBeGreaterThan(0);
    }

    expect(AgendaEvent::query()->whereNotNull('recurrence')->exists())->toBeTrue()
        ->and(AgendaEvent::query()->where('is_all_day', true)->exists())->toBeTrue()
        ->and(AgendaEvent::query()->whereHas('reminders')->exists())->toBeTrue()
        ->and(AgendaEvent::query()->whereHas('exceptions')->exists())->toBeTrue()
        ->and(AgendaEventAttendee::query()->where('is_organizer', true)->exists())->toBeTrue()
        ->and(AgendaCalendar::query()->whereHas('shares')->exists())->toBeTrue();
});

it('does not duplicate the seeded records when run twice', function () {
    User::factory()->count(3)->create();

    $this->seed(AgendaSeeder::class);

    $counts = [
        'calendars' => AgendaCalendar::query()->count(),
        'events' => AgendaEvent::query()->count(),
        'attendees' => AgendaEventAttendee::query()->count(),
    ];

    $this->seed(AgendaSeeder::class);

    expect(AgendaCalendar::query()->count())->toBe($counts['calendars'])
        ->and(AgendaEvent::query()->count())->toBe($counts['events'])
        ->and(AgendaEventAttendee::query()->count())->toBe($counts['attendees']);
});
