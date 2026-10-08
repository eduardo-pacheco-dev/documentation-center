<?php

namespace Database\Factories;

use App\Enums\ResourceType;
use App\Models\Project;
use App\Models\Resource;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<resource>
 */
class ResourceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'calendar_id' => null,
            'name' => fake()->unique()->name(),
            'type' => ResourceType::Work,
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'max_units' => 100,
            'cost_per_hour' => 0,
            'cost_per_unit' => 0,
            'is_active' => true,
        ];
    }

    /**
     * Allow the resource to work on several tasks at once.
     */
    public function withCapacity(float $maxUnits): static
    {
        return $this->state(fn (array $attributes) => [
            'max_units' => $maxUnits,
        ]);
    }

    /**
     * Charge an hourly rate to the resource.
     */
    public function costing(float $costPerHour): static
    {
        return $this->state(fn (array $attributes) => [
            'cost_per_hour' => $costPerHour,
        ]);
    }

    /**
     * Register the resource as a material.
     */
    public function material(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ResourceType::Material,
        ]);
    }

    /**
     * Deactivate the resource.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
