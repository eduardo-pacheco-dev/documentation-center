<?php

namespace Database\Factories;

use App\Enums\CatalogItemStatus;
use App\Enums\CatalogItemType;
use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CatalogItem>
 */
class CatalogItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement([CatalogItemType::Product, CatalogItemType::Service]);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'name' => ucfirst(fake()->words(3, true)),
            'code' => strtoupper(fake()->bothify('??-####')),
            'unit' => fake()->randomElement(['un', 'kg', 'm', 'm²', 'h', 'cx', 'l']),
            'price' => fake()->randomFloat(2, 10, 5000),
            'cost' => fake()->randomFloat(2, 5, 4000),
            'stock_quantity' => $type === CatalogItemType::Product ? fake()->numberBetween(0, 500) : null,
            'description' => fake()->sentence(12),
            'notes' => fake()->optional()->sentence(8),
            'status' => CatalogItemStatus::Active,
        ];
    }

    /**
     * Indicate that the catalog item is a product.
     */
    public function product(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CatalogItemType::Product,
            'stock_quantity' => fake()->numberBetween(0, 500),
        ]);
    }

    /**
     * Indicate that the catalog item is a service.
     */
    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => CatalogItemType::Service,
            'stock_quantity' => null,
        ]);
    }

    /**
     * Indicate that the catalog item is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => CatalogItemStatus::Inactive,
        ]);
    }
}
