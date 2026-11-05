<?php

namespace Plugins\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Projects\Enums\CalendarType;
use Plugins\Projects\Models\Calendar;
use Plugins\Projects\Models\Project;

/**
 * @extends Factory<Calendar>
 */
class CalendarFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<Calendar>
     */
    protected $model = Calendar::class;

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
