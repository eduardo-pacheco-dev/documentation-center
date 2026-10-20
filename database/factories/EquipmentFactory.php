<?php

namespace Database\Factories;

use App\Enums\EquipmentStatus;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Equipment>
 */
class EquipmentFactory extends Factory
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
            'code' => 'EQP-'.fake()->unique()->numerify('####'),
            'name' => ucfirst(fake()->words(3, true)),
            'manufacturer' => fake()->company(),
            'model' => strtoupper(fake()->bothify('??-####')),
            'category' => fake()->randomElement(['Rádio', 'Antena', 'Fonte', 'Switch', 'Roteador', 'Bateria']),
            'description' => fake()->sentence(12),
            'notes' => fake()->optional()->sentence(8),
            'status' => EquipmentStatus::Active,
        ];
    }

    /**
     * Indicate that the equipment is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EquipmentStatus::Inactive,
        ]);
    }

    /**
     * Indicate that the equipment is under maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EquipmentStatus::Maintenance,
        ]);
    }
}
