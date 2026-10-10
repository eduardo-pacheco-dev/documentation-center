<?php

namespace Database\Factories;

use App\Enums\RadioLinkPolarization;
use App\Enums\RadioLinkStatus;
use App\Models\Erb;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RadioLink>
 */
class RadioLinkFactory extends Factory
{
    /**
     * The radio equipment models used to compose the ends.
     *
     * @var list<string>
     */
    private const EQUIPMENT = [
        'Ericsson MINI-LINK 6363',
        'Cambium PTP 820',
        'Ubiquiti AirFiber 60',
        'Huawei RTN 905',
        'Ceragon IP-20C',
        'Nokia Wavence',
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'erb_a_id' => Erb::factory(),
            'erb_b_id' => Erb::factory(),
            'code' => 'RL-'.fake()->unique()->numerify('#####'),
            'equipment_a' => fake()->randomElement(self::EQUIPMENT),
            'equipment_b' => fake()->randomElement(self::EQUIPMENT),
            'frequency' => fake()->randomFloat(3, 5, 70),
            'bandwidth' => fake()->randomElement([10, 20, 40, 60, 80]),
            'capacity' => fake()->randomElement([100, 250, 500, 1000]),
            'polarization' => fake()->randomElement(RadioLinkPolarization::cases()),
            'status' => RadioLinkStatus::Active,
            'notes' => null,
        ];
    }

    /**
     * Mark the radio link as planned.
     */
    public function planned(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RadioLinkStatus::Planned,
        ]);
    }

    /**
     * Mark the radio link as under maintenance.
     */
    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RadioLinkStatus::Maintenance,
        ]);
    }

    /**
     * Mark the radio link as inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => RadioLinkStatus::Inactive,
        ]);
    }
}
