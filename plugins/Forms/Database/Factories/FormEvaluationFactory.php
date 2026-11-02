<?php

namespace Plugins\Forms\Database\Factories;

use Plugins\Forms\Models\Form;
use Plugins\Forms\Models\FormEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormEvaluation>
 */
class FormEvaluationFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<FormEvaluation>
     */
    protected $model = FormEvaluation::class;

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
            'status' => fake()->randomElement(['Aprovado', 'Reprovado']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
