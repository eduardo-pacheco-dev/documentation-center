<?php

namespace Plugins\Stations\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationComment;

/**
 * @extends Factory<StationComment>
 */
class StationCommentFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = StationComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'station_id' => Station::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
