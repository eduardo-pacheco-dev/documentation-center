<?php

namespace Plugins\Nokia\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Plugins\Nokia\Models\NokiaSite;

/**
 * @extends Factory<NokiaSite>
 */
class NokiaSiteFactory extends Factory
{
    /**
     * The model the factory creates.
     */
    protected $model = NokiaSite::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => 'SP'.fake()->bothify('???###?'),
            'end_id' => 'END-'.fake()->numerify('#####'),
            'oc' => 'OC-'.fake()->numerify('####'),
            'os_fam_entrega' => 'OS-E-'.fake()->numerify('#####'),
            'os_fam_instalacao' => 'OS-I-'.fake()->numerify('#####'),
            'checklist_tarefas_enviadas' => fake()->boolean(),
            'checklist_fam_entrega' => fake()->boolean(),
            'checklist_fam_instalacao' => fake()->boolean(),
            'checklist_fam_panoramica' => fake()->boolean(),
        ];
    }

    /**
     * Indicate that every checklist item is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes): array => [
            'checklist_tarefas_enviadas' => true,
            'checklist_fam_entrega' => true,
            'checklist_fam_instalacao' => true,
            'checklist_fam_panoramica' => true,
        ]);
    }
}
