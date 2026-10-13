<?php

namespace Database\Seeders;

use App\Models\FormTemplate;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormTemplateSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The reusable structures seeded for the organization.
     *
     * @var list<array{name: string, type: string, steps: list<array<string, mixed>>}>
     */
    private const TEMPLATES = [
        [
            'name' => 'Registro fotográfico padrão',
            'type' => 'Registro Fotográfico',
            'steps' => [
                [
                    'id' => 'S1',
                    'title' => 'Identificação',
                    'fields' => [
                        ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true],
                        ['id' => 'F1-2', 'type' => 'date', 'label' => 'Data do registro', 'required' => true],
                    ],
                ],
                [
                    'id' => 'S2',
                    'title' => 'Registro fotográfico',
                    'fields' => [
                        ['id' => 'F2-1', 'type' => 'image', 'label' => 'Foto geral', 'required' => true],
                        ['id' => 'F2-2', 'type' => 'image', 'label' => 'Foto de detalhe', 'required' => false],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Checklist de segurança',
            'type' => 'Checklist',
            'steps' => [
                [
                    'id' => 'S1',
                    'title' => 'Acesso e segurança',
                    'fields' => [
                        ['id' => 'F1-1', 'type' => 'radio', 'label' => 'EPI completo?', 'required' => true, 'options' => ['Sim', 'Não']],
                        ['id' => 'F1-2', 'type' => 'textarea', 'label' => 'Observações', 'required' => false],
                    ],
                ],
            ],
        ],
        [
            'name' => 'Vistoria estrutural',
            'type' => 'Vistoria',
            'steps' => [
                [
                    'id' => 'S1',
                    'title' => 'Avaliação estrutural',
                    'fields' => [
                        ['id' => 'F1-1', 'type' => 'scale', 'label' => 'Grau de conservação', 'required' => true, 'scaleMin' => 1, 'scaleMax' => 5, 'scaleMinLabel' => 'Ruim', 'scaleMaxLabel' => 'Ótimo'],
                        ['id' => 'F1-2', 'type' => 'textarea', 'label' => 'Parecer técnico', 'required' => false],
                    ],
                ],
            ],
        ],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::query()->orderBy('id')->first();

        if ($owner === null) {
            return;
        }

        foreach (self::TEMPLATES as $template) {
            FormTemplate::create([
                'user_id' => $owner->getKey(),
                'name' => $template['name'],
                'type' => $template['type'],
                'steps' => $template['steps'],
            ]);
        }
    }
}
