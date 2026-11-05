<?php

namespace Plugins\Forms\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Forms\Models\Form;
use Plugins\Forms\Models\FormResponse;

/**
 * @extends Factory<FormResponse>
 */
class FormResponseFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<FormResponse>
     */
    protected $model = FormResponse::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'form_id' => Form::factory(),
            'user_id' => User::factory(),
            'status' => 'Preenchido',
            'steps' => [],
            'submitted_at' => now(),
        ];
    }
}
