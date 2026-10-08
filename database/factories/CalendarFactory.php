<?php

namespace Database\Factories;

use App\Enums\CalendarType;
use App\Models\Calendar;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Calendar>
 */
class CalendarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name' => 'Calendário padrão',
            'is_default' => true,
            'type' => CalendarType::Standard,
            'work_start_minute' => 480,
            'work_end_minute' => 1020,
            'lunch_start_minute' => 720,
            'lunch_end_minute' => 780,
            'minutes_per_day' => 480,
            'minutes_per_week' => 2400,
        ];
    }

    /**
     * Build the calendar with its seven weekdays configured.
     */
    public function withDays(): static
    {
        return $this->afterCreating(function (Calendar $calendar): void {
            if ($calendar->days()->exists()) {
                return;
            }

            $type = $calendar->type;
            $segments = $type->segments();
            $workingDays = $type->workingDays();

            foreach (range(1, 7) as $dayOfWeek) {
                $calendar->days()->create([
                    'day_of_week' => $dayOfWeek,
                    'segments' => in_array($dayOfWeek, $workingDays, true) ? $segments : [],
                ]);
            }
        });
    }
}
