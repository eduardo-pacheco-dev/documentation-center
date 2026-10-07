<?php

namespace Database\Factories;

use App\Enums\ShortLinkType;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends Factory<ShortLink>
 */
class ShortLinkFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => ShortLink::createCode(),
            'type' => ShortLinkType::Upload,
            'title' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'is_active' => true,
            'expires_at' => null,
            'max_uses' => null,
            'used_count' => 0,
            'password' => null,
        ];
    }

    /**
     * Mark the link as a download link.
     */
    public function download(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ShortLinkType::Download,
        ]);
    }

    /**
     * Deactivate the link.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Expire the link immediately.
     */
    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'expires_at' => now()->subMinute(),
        ]);
    }

    /**
     * Restrict the number of allowed accesses.
     */
    public function limited(int $maxUses): static
    {
        return $this->state(fn (array $attributes) => [
            'max_uses' => $maxUses,
        ]);
    }

    /**
     * Protect the link with a password.
     */
    public function passwordProtected(?string $password = 'segredo123'): static
    {
        return $this->state(fn (array $attributes) => [
            'password' => Hash::make($password),
        ]);
    }
}
