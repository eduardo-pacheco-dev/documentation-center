<?php

namespace App\Models;

use App\Enums\CalendarType;
use Database\Factories\CalendarFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'project_id',
    'name',
    'is_default',
    'type',
    'work_start_minute',
    'work_end_minute',
    'lunch_start_minute',
    'lunch_end_minute',
    'minutes_per_day',
    'minutes_per_week',
])]
class Calendar extends Model
{
    /** @use HasFactory<CalendarFactory> */
    use HasFactory;

    /**
     * The project the calendar belongs to.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * The working windows configured for each weekday.
     *
     * @return HasMany<CalendarDay, $this>
     */
    public function days(): HasMany
    {
        return $this->hasMany(CalendarDay::class);
    }

    /**
     * The holidays and exceptions of the calendar.
     *
     * @return HasMany<CalendarException, $this>
     */
    public function exceptions(): HasMany
    {
        return $this->hasMany(CalendarException::class);
    }

    /**
     * Create a calendar with its default working week.
     */
    public static function createDefault(Project $project, CalendarType $type, string $name, bool $isDefault = true): self
    {
        $window = $type->window();
        $segments = $type->segments();
        $workingDays = $type->workingDays();

        $calendar = $project->calendars()->create([
            'name' => $name,
            'type' => $type,
            'is_default' => $isDefault,
            'work_start_minute' => $window['start'],
            'work_end_minute' => $window['end'],
            'lunch_start_minute' => $window['lunch_start'],
            'lunch_end_minute' => $window['lunch_end'],
            'minutes_per_day' => self::totalMinutes($segments),
            'minutes_per_week' => self::totalMinutes($segments) * count($workingDays),
        ]);

        foreach (range(1, 7) as $dayOfWeek) {
            $calendar->days()->create([
                'day_of_week' => $dayOfWeek,
                'segments' => in_array($dayOfWeek, $workingDays, true) ? $segments : [],
            ]);
        }

        return $calendar;
    }

    /**
     * The working windows configured for a weekday.
     *
     * @return list<array{0: int, 1: int}>
     */
    public function segmentsFor(int $dayOfWeek): array
    {
        $segments = $this->days->firstWhere('day_of_week', $dayOfWeek)?->segments ?? [];

        return array_values(array_map(
            fn (array $segment) => [(int) $segment[0], (int) $segment[1]],
            $segments,
        ));
    }

    /**
     * Replace the working windows of a weekday and refresh the totals.
     *
     * @param  list<array{0: int, 1: int}>  $segments
     */
    public function setSegmentsFor(int $dayOfWeek, array $segments): void
    {
        $this->days()->updateOrCreate(['day_of_week' => $dayOfWeek], ['segments' => $segments]);

        $this->refreshWeekTotals();
    }

    /**
     * Recalculate the day and week totals from the configured weekdays.
     */
    public function refreshWeekTotals(): void
    {
        $perDay = 0;
        $perWeek = 0;

        foreach ($this->days()->get() as $day) {
            $minutes = self::totalMinutes($day->segments ?? []);
            $perDay = max($perDay, $minutes);

            if ($minutes > 0) {
                $perWeek += $minutes;
            }
        }

        $this->forceFill([
            'minutes_per_day' => $perDay,
            'minutes_per_week' => $perWeek,
        ])->save();
    }

    /**
     * Sum the minutes covered by a list of working windows.
     *
     * @param  list<array{0: int, 1: int}>  $segments
     */
    public static function totalMinutes(array $segments): int
    {
        return array_sum(array_map(
            fn (array $segment) => max(0, (int) $segment[1] - (int) $segment[0]),
            $segments,
        ));
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CalendarType::class,
            'is_default' => 'boolean',
            'work_start_minute' => 'integer',
            'work_end_minute' => 'integer',
            'lunch_start_minute' => 'integer',
            'lunch_end_minute' => 'integer',
            'minutes_per_day' => 'integer',
            'minutes_per_week' => 'integer',
        ];
    }
}
