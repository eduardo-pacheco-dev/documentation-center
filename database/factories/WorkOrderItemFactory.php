<?php

namespace Database\Factories;

use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkOrderItem>
 */
class WorkOrderItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 20);
        $unitPrice = fake()->randomFloat(2, 10, 500);

        return [
            'work_order_id' => WorkOrder::factory(),
            'catalog_item_id' => null,
            'description' => ucfirst(fake()->words(3, true)),
            'unit' => fake()->randomElement(['un', 'kg', 'm', 'm²', 'h']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2),
        ];
    }
}
