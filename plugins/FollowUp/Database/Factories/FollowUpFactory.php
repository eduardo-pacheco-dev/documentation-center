<?php

namespace Plugins\FollowUp\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpAssignee;

/**
 * @extends Factory<FollowUp>
 */
class FollowUpFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = FollowUp::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'client' => fake()->company(),
            'project' => fake()->words(3, true),
            'status' => FollowUpStatus::Pending->value,
            'priority' => fake()->randomElement(FollowUpPriority::values()),
            'due_date' => fake()->dateTimeBetween('-5 days', '+20 days')->format('Y-m-d'),
            'assignee_id' => FollowUpAssignee::factory(),
            'created_by' => User::factory(),
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the follow-up is overdue and open.
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FollowUpStatus::InProgress->value,
            'due_date' => now()->subDays(3)->toDateString(),
            'completed_at' => null,
        ]);
    }

    /**
     * Indicate that the follow-up is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FollowUpStatus::Completed->value,
            'completed_at' => now(),
        ]);
    }
}
