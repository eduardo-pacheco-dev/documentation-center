<?php

namespace Plugins\Stations\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Stations\Models\Station;
use Plugins\Stations\Models\StationAttachment;

/**
 * @extends Factory<StationAttachment>
 */
class StationAttachmentFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = StationAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->words(3, true).'.pdf';

        return [
            'station_id' => Station::factory(),
            'uploaded_by' => User::factory(),
            'category' => StationAttachment::CATEGORY_ATTACHMENT,
            'original_name' => $name,
            'path' => 'stations/attachments/'.fake()->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'size' => fake()->numberBetween(1024, 5 * 1024 * 1024),
        ];
    }

    /**
     * Indicate that the attachment belongs to the PPI/TSSR section.
     */
    public function ppiTssr(): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => StationAttachment::CATEGORY_PPI_TSSR,
        ]);
    }
}
