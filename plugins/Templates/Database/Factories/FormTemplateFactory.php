<?php

namespace Plugins\Templates\Database\Factories;

use Plugins\Templates\Models\FormTemplate;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FormTemplate>
 */
class FormTemplateFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<FormTemplate>
     */
    protected $model = FormTemplate::class;

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
