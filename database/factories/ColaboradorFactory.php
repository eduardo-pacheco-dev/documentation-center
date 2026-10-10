<?php

namespace Database\Factories;

use App\Enums\ColaboradorStatus;
use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Colaborador>
 */
class ColaboradorFactory extends Factory
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
            'name' => fake()->name(),
            'role' => fake()->randomElement(['Técnico N1', 'Técnico N2', 'Técnico de campo', 'Instalador', 'Supervisor', 'Engenheiro de projetos']),
            'document' => fake()->numerify('###.###.###-##'),
            'phone' => fake()->numerify('(##) #####-####'),
            'email' => fake()->unique()->safeEmail(),
            'status' => ColaboradorStatus::Active,
            'notes' => null,
        ];
    }

    /**
     * Mark the colaborador as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ColaboradorStatus::Inactive,
        ]);
    }
}
