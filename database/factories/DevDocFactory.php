<?php

namespace Database\Factories;

use App\Enums\DevDocCategory;
use App\Models\DevDoc;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<DevDoc>
 */
class DevDocFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = fake()->sentence(4);

        return [
            'user_id' => User::factory(),
            'title' => $title,
            'slug' => Str::slug($title).'-'.fake()->unique()->numberBetween(1, 999999),
            'category' => fake()->randomElement(DevDocCategory::cases()),
            'summary' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'is_published' => true,
        ];
    }

    /**
     * Indicate that the document is a draft.
     */
    public function draft(): static
    {
        return $this->state(fn (): array => [
            'is_published' => false,
        ]);
    }
}
