<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\WorkOrderClientOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderClientOrder>
 */
class WorkOrderClientOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'work_order_id' => WorkOrder::factory(),
            'number' => 'OSC-'.fake()->unique()->numerify('#####'),
            'description' => fake()->optional()->sentence(8),
        ];
    }
}
