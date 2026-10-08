<?php

namespace Tests\Unit\Scheduling;

use App\Enums\CalendarType;
use App\Models\Calendar;
use App\Models\CalendarDay;
use App\Models\CalendarException;
use App\Services\Scheduling\WorkingCalendar;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use Tests\TestCase;

uses(TestCase::class);

/**
 * Build an in-memory standard calendar: Monday to Friday, 08:00-12:00 and 13:00-17:00.
 */
function calendar(array $exceptions = []): Calendar
{
    $calendar = new Calendar([
        'name' => 'Padrão',
        'type' => CalendarType::Standard,
        'is_default' => true,
        'work_start_minute' => 480,
        'work_end_minute' => 1020,
        'lunch_start_minute' => 720,
        'lunch_end_minute' => 780,
        'minutes_per_day' => 480,
        'minutes_per_week' => 2400,
    ]);

    $days = collect(range(1, 7))->map(fn (int $dayOfWeek) => new CalendarDay([
        'day_of_week' => $dayOfWeek,
        'segments' => in_array($dayOfWeek, [1, 2, 3, 4, 5], true) ? [[480, 720], [780, 1020]] : [],
    ]));

    $calendar->setRelation('days', $days);
    $calendar->setRelation('exceptions', collect($exceptions));

    return $calendar;
}

beforeEach(function () {
    $this->calendar = new WorkingCalendar(calendar());
});

it('counts the working minutes of a day', function () {
    expect($this->calendar->minutesOn(CarbonImmutable::parse('2026-01-05 00:00')))->toBe(480)
        ->and($this->calendar->minutesOn(CarbonImmutable::parse('2026-01-10 00:00')))->toBe(0);
});

it('treats weekends as non working days', function () {
    expect($this->calendar->isWorkingDay(CarbonImmutable::parse('2026-01-10')))->toBeFalse()
        ->and($this->calendar->isWorkingDay(CarbonImmutable::parse('2026-01-05')))->toBeTrue();
});

it('moves an instant forward by working minutes only', function () {
    $start = CarbonImmutable::parse('2026-01-05 08:00');

    expect($this->calendar->add($start, 480)->format('Y-m-d H:i'))->toBe('2026-01-05 17:00')
        ->and($this->calendar->add($start, 481)->format('Y-m-d H:i'))->toBe('2026-01-06 08:01');
});

it('skips the lunch break when adding minutes', function () {
    $start = CarbonImmutable::parse('2026-01-05 11:00');

    expect($this->calendar->add($start, 120)->format('Y-m-d H:i'))->toBe('2026-01-05 14:00');
});

it('skips weekends when adding minutes', function () {
    $start = CarbonImmutable::parse('2026-01-09 16:00');

    expect($this->calendar->add($start, 120)->format('Y-m-d H:i'))->toBe('2026-01-12 09:00');
});

it('moves an instant backward by working minutes only', function () {
    expect($this->calendar->subtract(CarbonImmutable::parse('2026-01-06 08:01'), 1)->format('Y-m-d H:i'))
        ->toBe('2026-01-06 08:00')
        ->and($this->calendar->subtract(CarbonImmutable::parse('2026-01-06 08:01'), 481)->format('Y-m-d H:i'))
        ->toBe('2026-01-05 08:00')
        ->and($this->calendar->subtract(CarbonImmutable::parse('2026-01-05 17:00'), 480)->format('Y-m-d H:i'))
        ->toBe('2026-01-05 08:00')
        ->and($this->calendar->subtract(CarbonImmutable::parse('2026-01-06 08:00'), 60)->format('Y-m-d H:i'))
        ->toBe('2026-01-05 16:00');
});

it('counts working minutes between two instants', function () {
    $start = CarbonImmutable::parse('2026-01-05 08:00');
    $nextDay = CarbonImmutable::parse('2026-01-06 08:00');

    expect($this->calendar->minutesBetween($start, $nextDay))->toBe(480)
        ->and($this->calendar->minutesBetween($start, $start))->toBe(0);
});

it('snaps an instant forward to the next working moment', function () {
    expect($this->calendar->nextWorkingInstant(CarbonImmutable::parse('2026-01-10 10:00'))->format('Y-m-d H:i'))
        ->toBe('2026-01-12 08:00');
});

it('honours a holiday configured as a calendar exception', function () {
    $calendar = calendar([new CalendarException([
        'date' => '2026-01-07',
        'name' => 'Feriado',
        'segments' => [],
    ])]);

    $working = new WorkingCalendar($calendar);

    expect($working->isWorkingDay(CarbonImmutable::parse('2026-01-07')))->toBeFalse()
        ->and($working->add(CarbonImmutable::parse('2026-01-06 16:00'), 120)->format('Y-m-d H:i'))
        ->toBe('2026-01-08 09:00');
});

it('opens a weekend day when an exception declares working time', function () {
    $calendar = calendar([new CalendarException([
        'date' => '2026-01-10',
        'name' => 'Sábado útil',
        'segments' => [[480, 720]],
    ])]);

    $working = new WorkingCalendar($calendar);

    expect($working->minutesOn(CarbonImmutable::parse('2026-01-10')))->toBe(240)
        ->and($working->add(CarbonImmutable::parse('2026-01-09 17:00'), 60)->format('Y-m-d H:i'))
        ->toBe('2026-01-10 09:00');
});

it('snaps a day boundary to the working windows', function () {
    expect($this->calendar->startOfDay(CarbonImmutable::parse('2026-01-05'))->format('Y-m-d H:i'))
        ->toBe('2026-01-05 08:00')
        ->and($this->calendar->endOfDay(CarbonImmutable::parse('2026-01-05'))->format('Y-m-d H:i'))
        ->toBe('2026-01-05 17:00');
});

it('lists the working days of a range', function () {
    $days = $this->calendar->workingDaysBetween(
        CarbonImmutable::parse('2026-01-05'),
        CarbonImmutable::parse('2026-01-11'),
    );

    expect($days)->toBe(['2026-01-05', '2026-01-06', '2026-01-07', '2026-01-08', '2026-01-09']);
});

it('fails when the calendar has no working time at all', function () {
    $days = collect(range(1, 7))->map(fn (int $dayOfWeek) => new CalendarDay([
        'day_of_week' => $dayOfWeek,
        'segments' => [],
    ]));

    $calendar = calendar();
    $calendar->setRelation('days', $days);

    expect(fn () => (new WorkingCalendar($calendar))->add(CarbonImmutable::parse('2026-01-05 08:00'), 10))
        ->toThrow(InvalidArgumentException::class);
});
