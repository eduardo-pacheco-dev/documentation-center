<?php

namespace Database\Factories;

use App\Enums\ColaboradorStatus;
use App\Enums\ContractRegime;
use App\Enums\Uf;
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
            'contract_regime' => fake()->randomElement(ContractRegime::cases())->value,
            'regional' => fake()->randomElement(['NO', 'NE', 'CO', 'SE', 'S']),
            'uf' => fake()->randomElement(Uf::cases())->value,
            'pis' => fake()->numerify('###.#####.##-#'),
            'role' => fake()->randomElement(['Técnico N1', 'Técnico N2', 'Técnico de campo', 'Instalador', 'Supervisor', 'Engenheiro de projetos']),
            'document' => fake()->numerify('###.###.###-##'),
            'cnpj' => null,
            'rg' => fake()->numerify('#######'),
            'rg_issuer' => 'SSP',
            'birth_date' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'mother_name' => fake()->name('female'),
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
