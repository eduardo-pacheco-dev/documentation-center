<?php

namespace Database\Factories;

use App\Models\Form;
use App\Models\FormEvaluation;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormEvaluation>
 */
class FormEvaluationFactory extends Factory
{
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
