<?php

namespace Plugins\Files\Database\Factories;

use Plugins\Files\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<Document>
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extension = fake()->randomElement(['pdf', 'docx', 'xlsx', 'txt']);

        return [
            'user_id' => User::factory(),
            'uploaded_via_short_link_id' => null,
            'original_name' => fake()->slug(2).'.'.$extension,
            'path' => 'documents/'.fake()->uuid().'.'.$extension,
            'disk' => 'local',
            'mime_type' => fake()->randomElement(['application/pdf', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'text/plain']),
            'size' => fake()->numberBetween(10_000, 2_000_000),
        ];
    }
}
