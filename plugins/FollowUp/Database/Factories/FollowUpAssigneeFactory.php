<?php

namespace Plugins\FollowUp\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\FollowUp\Models\FollowUpAssignee;

/**
 * @extends Factory<FollowUpAssignee>
 */
class FollowUpAssigneeFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = FollowUpAssignee::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
        ];
    }
}
