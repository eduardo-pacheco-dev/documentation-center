<?php

namespace Plugins\TeamTracking\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\TeamTracking\Enums\TeamStatus;
use Plugins\TeamTracking\Models\Team;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Team',
            'description' => fake()->sentence(12),
            'area' => fake()->randomElement(['Operações', 'Comercial', 'Tecnologia', 'Financeiro']),
            'leader_id' => User::factory(),
            'status' => TeamStatus::Active->value,
            'started_at' => now()->subMonths(fake()->numberBetween(1, 24))->toDateString(),
        ];
    }

    public function paused(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TeamStatus::Paused->value,
        ]);
    }

    public function closed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => TeamStatus::Closed->value,
        ]);
    }
}
