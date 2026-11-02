<?php

namespace Plugins\Stations\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Stations\Enums\StationOperator;
use Plugins\Stations\Enums\StationStatus;
use Plugins\Stations\Enums\StationTechnology;
use Plugins\Stations\Models\Station;

/**
 * @extends Factory<Station>
 */
class StationFactory extends Factory
{
    protected $model = Station::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'site_id' => 'SITE-'.$this->faker->unique()->numerify('#####'),
            'end_id' => 'END-'.$this->faker->unique()->numerify('####'),
            'operator' => $this->faker->randomElement(StationOperator::cases())->value,
            'technology' => $this->faker->randomElement(StationTechnology::cases())->value,
            'status' => StationStatus::Active,
            'street' => $this->faker->streetName(),
            'number' => (string) $this->faker->buildingNumber(),
            'neighborhood' => $this->faker->word(),
            'city' => $this->faker->city(),
            'state' => 'SP',
            'zip' => $this->faker->numerify('#####-###'),
            'latitude' => $this->faker->latitude(-33, 5),
            'longitude' => $this->faker->longitude(-73, -35),
        ];
    }

    public function maintenance(): static
    {
        return $this->state(fn (): array => ['status' => StationStatus::Maintenance]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['status' => StationStatus::Inactive]);
    }
}
