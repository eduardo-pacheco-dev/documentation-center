<?php

namespace Plugins\Projects\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Projects\Enums\CalendarType;
use Plugins\Projects\Enums\ProjectStatus;
use Plugins\Projects\Models\Calendar;
use Plugins\Projects\Models\Project;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<Project>
     */
    protected $model = Project::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->sentence(),
            'status' => ProjectStatus::Active,
            'start_date' => now()->startOfMonth()->toDateString(),
            'finish_date' => null,
            'status_date' => null,
            'priority' => 500,
            'currency' => 'BRL',
            'budget' => 0,
            'percent_complete' => 0,
            'scheduled_at' => null,
        ];
    }

    /**
     * Ensure the project has a default working calendar.
     */
    public function withCalendar(CalendarType $type = CalendarType::Standard): static
    {
        return $this->afterCreating(function (Project $project) use ($type): void {
            if ($project->calendars()->exists()) {
                return;
            }

            Calendar::createDefault($project, $type, 'Calendário padrão');
        });
    }

    /**
     * Mark the project as completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Completed,
        ]);
    }

    /**
     * Mark the project as archived.
     */
    public function archived(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ProjectStatus::Archived,
        ]);
    }
}
