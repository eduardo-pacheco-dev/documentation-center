<?php

namespace Database\Factories;

use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormTemplate>
 */
class FormTemplateFactory extends Factory
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
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(['Registro Fotográfico', 'Checklist', 'Vistoria']),
            'steps' => [
                [
                    'id' => 'S1',
                    'title' => 'Identificação',
                    'fields' => [
                        ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => false],
                    ],
                ],
            ],
        ];
    }

    /**
     * Attach the template to the given user.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->getKey(),
        ]);
    }
}
