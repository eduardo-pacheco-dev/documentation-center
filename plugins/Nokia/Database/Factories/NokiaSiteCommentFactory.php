<?php

namespace Plugins\Nokia\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteComment;

/**
 * @extends Factory<NokiaSiteComment>
 */
class NokiaSiteCommentFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = NokiaSiteComment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nokia_site_id' => NokiaSite::factory(),
            'user_id' => User::factory(),
            'body' => fake()->paragraph(),
        ];
    }
}
