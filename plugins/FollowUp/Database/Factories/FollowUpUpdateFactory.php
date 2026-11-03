<?php

namespace Plugins\FollowUp\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpUpdate;

/**
 * @extends Factory<FollowUpUpdate>
 */
class FollowUpUpdateFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = FollowUpUpdate::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'follow_up_id' => FollowUp::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
            'status_from' => null,
            'status_to' => null,
        ];
    }
}
