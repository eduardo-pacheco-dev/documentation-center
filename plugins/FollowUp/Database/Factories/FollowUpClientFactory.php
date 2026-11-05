<?php

namespace Plugins\FollowUp\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\FollowUp\Models\FollowUpClient;

/**
 * @extends Factory<FollowUpClient>
 */
class FollowUpClientFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<FollowUpClient>
     */
    protected $model = FollowUpClient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company(),
        ];
    }
}
