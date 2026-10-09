<?php

namespace Database\Factories;

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrder>
 */
class WorkOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'client_id' => Client::factory(),
            'number' => 'OS-'.fake()->unique()->numerify('#####'),
            'title' => ucfirst(fake()->words(4, true)),
            'description' => fake()->sentence(12),
            'priority' => fake()->randomElement(WorkOrderPriority::cases()),
            'status' => WorkOrderStatus::Open,
            'opened_at' => fake()->dateTimeBetween('-2 months')->format('Y-m-d'),
            'due_at' => fake()->dateTimeBetween('now', '+1 month')->format('Y-m-d'),
            'completed_at' => null,
            'total' => 0,
            'notes' => fake()->optional()->sentence(8),
        ];
    }

    /**
     * Indicate that the work order is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::InProgress,
        ]);
    }

    /**
     * Indicate that the work order is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::Completed,
            'completed_at' => now()->toDateString(),
        ]);
    }

    /**
     * Indicate that the work order is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => WorkOrderStatus::Cancelled,
        ]);
    }
}
