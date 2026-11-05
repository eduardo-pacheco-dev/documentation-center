<?php

namespace Plugins\Nokia\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;

/**
 * @extends Factory<NokiaSiteAttachment>
 */
class NokiaSiteAttachmentFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = NokiaSiteAttachment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->word().'.pdf';

        return [
            'nokia_site_id' => NokiaSite::factory(),
            'category' => fake()->randomElement(array_keys(NokiaSiteAttachment::categories())),
            'original_name' => $name,
            'path' => 'sites/'.fake()->numberBetween(1, 999).'/'.$name,
            'size' => fake()->numberBetween(1024, 2048000),
            'mime_type' => 'application/pdf',
        ];
    }

    /**
     * Indicate the attachment category.
     */
    public function category(string $category): static
    {
        return $this->state(fn (array $attributes): array => [
            'category' => $category,
        ]);
    }
}
