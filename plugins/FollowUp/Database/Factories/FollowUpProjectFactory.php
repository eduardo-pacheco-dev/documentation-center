<?php

namespace Plugins\FollowUp\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\FollowUp\Models\FollowUpClient;
use Plugins\FollowUp\Models\FollowUpProject;

/**
 * @extends Factory<FollowUpProject>
 */
class FollowUpProjectFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<FollowUpProject>
     */
    protected $model = FollowUpProject::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => FollowUpClient::factory(),
            'name' => fake()->unique()->words(3, true),
        ];
    }
}
