<?php

namespace Plugins\WorkOrders\Database\Factories;

use Plugins\WorkOrders\Models\WorkOrder;
use Plugins\WorkOrders\Models\WorkOrderClientOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderClientOrder>
 */
class WorkOrderClientOrderFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<WorkOrderClientOrder>
     */
    protected $model = WorkOrderClientOrder::class;

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
