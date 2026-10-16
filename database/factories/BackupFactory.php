<?php

namespace Database\Factories;

use App\Enums\BackupStatus;
use App\Models\Backup;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Backup>
 */
class BackupFactory extends Factory
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
            'disk' => 'backups',
            'path' => null,
            'size' => null,
            'status' => BackupStatus::Pending,
            'error' => null,
            'started_at' => null,
            'completed_at' => null,
        ];
    }

    /**
     * Indicate that the backup finished successfully.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BackupStatus::Completed,
            'path' => 'backup-'.fake()->uuid().'.zip',
            'size' => fake()->numberBetween(1024, 10485760),
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }

    /**
     * Indicate that the backup failed.
     */
    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => BackupStatus::Failed,
            'error' => 'Falha ao criar o arquivo de backup.',
            'started_at' => now()->subMinutes(2),
            'completed_at' => now(),
        ]);
    }
}
