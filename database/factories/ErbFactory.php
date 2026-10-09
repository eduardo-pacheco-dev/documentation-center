<?php

namespace Database\Factories;

use App\Enums\ErbStatus;
use App\Models\Erb;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Erb>
 */
class ErbFactory extends Factory
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
            'code' => 'ERB-'.fake()->unique()->numerify('#####'),
            'name' => 'ERB '.fake()->city(),
            'operator' => fake()->randomElement(['Vivo', 'Claro', 'TIM', 'Oi', 'Algar', 'Sercomtel']),
            'technology' => fake()->randomElement(['2G', '3G', '4G', '5G', '4G/5G']),
            'status' => ErbStatus::Active,
            'street' => fake()->streetName(),
            'number' => fake()->buildingNumber(),
            'complement' => null,
            'neighborhood' => fake()->randomElement(['Centro', 'Jardim Paulista', 'Vila Nova', 'Bela Vista', 'Savassi', 'Batel', 'Meireles', 'Boa Viagem', 'Asa Norte', 'Ponta Negra']),
            'city' => fake()->city(),
            'state' => fake()->randomElement(['SP', 'RJ', 'MG', 'RS', 'PR', 'SC', 'BA', 'PE']),
            'zip' => fake()->numerify('#####-###'),
            'latitude' => fake()->latitude(-33.75, 5.27),
            'longitude' => fake()->longitude(-73.98, -34.79),
            'notes' => null,
        ];
    }

    /**
     * Mark the ERB as under maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ErbStatus::Maintenance,
        ]);
    }

    /**
     * Mark the ERB as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ErbStatus::Inactive,
        ]);
    }
}
