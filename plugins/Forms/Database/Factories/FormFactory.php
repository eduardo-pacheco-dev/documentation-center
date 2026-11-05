<?php

namespace Plugins\Forms\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Forms\Models\Form;

/**
 * @extends Factory<Form>
 */
class FormFactory extends Factory
{
    /**
     * The model the factory creates.
     *
     * @var class-string<Form>
     */
    protected $model = Form::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'code' => 'FRM-'.fake()->unique()->numerify('####'),
            'name' => fake()->sentence(3),
            'type' => fake()->randomElement(['Registro Fotográfico', 'Checklist', 'Vistoria']),
            'status' => fake()->randomElement(['Pendente', 'Preenchido', 'Aprovado', 'Reprovado']),
            'steps' => [
                [
                    'id' => 'S1',
                    'title' => 'Identificação',
                    'fields' => [
                        ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'value' => '', 'status' => 'pendente'],
                    ],
                ],
            ],
            'fields' => null,
        ];
    }

    /**
     * Attach the form to the given user.
     */
    public function ownedBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->getKey(),
        ]);
    }
}
