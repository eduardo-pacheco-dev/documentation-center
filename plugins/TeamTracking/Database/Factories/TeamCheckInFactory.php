<?php

namespace Plugins\TeamTracking\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Models\Team;
use Plugins\TeamTracking\Models\TeamCheckIn;

/**
 * @extends Factory<TeamCheckIn>
 */
class TeamCheckInFactory extends Factory
{
    protected $model = TeamCheckIn::class;

    public function definition(): array
    {
        return [
            'team_id' => Team::factory(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(5),
            'reference_date' => fake()->dateTimeBetween('-2 months', 'now')->format('Y-m-d'),
            'status' => fake()->randomElement(CheckInStatus::values()),
            'health_score' => fake()->numberBetween(1, 5),
            'summary' => fake()->paragraph(),
            'next_steps' => fake()->sentence(10),
        ];
    }

    public function attention(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => CheckInStatus::Attention->value,
            'health_score' => 2,
        ]);
    }
}
