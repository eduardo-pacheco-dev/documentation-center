<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class FormSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * The form statuses seeded per user, one rotation per user so everyone has filled forms.
     *
     * @var list<list<string>>
     */
    private const STATUS_ROTATIONS = [
        ['Preenchido', 'Aprovado', 'Pendente'],
        ['Aprovado', 'Em preenchimento', 'Preenchido'],
        ['Preenchido', 'Pendente', 'Aprovado'],
        ['Aprovado', 'Pendente', 'Em preenchimento'],
    ];

    /**
     * The form types distributed across the seeded forms.
     *
     * @var list<string>
     */
    private const TYPES = ['Registro Fotográfico', 'Checklist', 'Vistoria', 'Inspeção', 'Laudo Técnico'];

    /**
     * The names used to compose a form based on its type.
     *
     * @var array<string, list<string>>
     */
    private const NAME_SUFFIXES = [
        'Registro Fotográfico' => ['Estrutura', 'Abrigo', 'Torre', 'Antenas', 'Energia'],
        'Checklist' => ['Segurança', 'Acesso', 'Equipamentos', 'Cabos', 'Aterramento'],
        'Vistoria' => ['Preliminar', 'Periódica', 'Pós-instalação', 'Entrega'],
        'Inspeção' => ['Estrutural', 'Civil', 'Elétrica', 'Painel solar'],
        'Laudo Técnico' => ['Estrutural', 'Rádio frequência', 'Proteção', 'Ambiental'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::query()->orderBy('id')->get();

        if ($users->isEmpty()) {
            return;
        }

        $index = 0;

        foreach ($users->values() as $userIndex => $owner) {
            $statuses = self::STATUS_ROTATIONS[$userIndex % count(self::STATUS_ROTATIONS)];

            foreach ($statuses as $status) {
                $this->seedForm($owner, ++$index, $status);
            }
        }
    }

    /**
     * Seed one form attached to a project owned by the given user.
     */
    private function seedForm(User $owner, int $index, string $status): void
    {
        $projects = $owner->projects()->orderBy('id')->get();

        if ($projects->isEmpty()) {
            return;
        }

        $project = $projects->get(($index - 1) % $projects->count());
        $type = self::TYPES[($index - 1) % count(self::TYPES)];
        $suffixes = self::NAME_SUFFIXES[$type];
        $suffix = $suffixes[($index - 1) % count($suffixes)];

        Form::create([
            'user_id' => $owner->getKey(),
            'project_id' => $project->getKey(),
            'code' => Form::generateCode($owner),
            'name' => $project->name ? $type.' - '.$suffix.' ('.$project->name.')' : $type.' - '.$suffix,
            'type' => $type,
            'status' => $status,
            'steps' => $this->structure(),
            'fields' => null,
        ]);
    }

    /**
     * Build the empty structure (sections and questions) shared by the seeded forms.
     *
     * @return list<array<string, mixed>>
     */
    private function structure(): array
    {
        return [
            [
                'id' => 'S1',
                'title' => 'Identificação',
                'fields' => [
                    ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true, 'value' => '', 'status' => 'pendente'],
                    ['id' => 'F1-2', 'type' => 'number', 'label' => 'Código SMP', 'required' => false, 'value' => '', 'status' => 'pendente'],
                    ['id' => 'F1-3', 'type' => 'date', 'label' => 'Data da vistoria', 'required' => true, 'value' => '', 'status' => 'pendente'],
                ],
            ],
            [
                'id' => 'S2',
                'title' => 'Registro fotográfico',
                'fields' => [
                    ['id' => 'F2-1', 'type' => 'image', 'label' => 'Foto do painel', 'required' => true, 'images' => [], 'status' => 'pendente'],
                    ['id' => 'F2-2', 'type' => 'image', 'label' => 'Foto do abrigo', 'required' => false, 'images' => [], 'status' => 'pendente'],
                ],
            ],
            [
                'id' => 'S3',
                'title' => 'Conformidade',
                'fields' => [
                    ['id' => 'F3-1', 'type' => 'textarea', 'label' => 'Observações', 'required' => false, 'value' => '', 'status' => 'pendente'],
                    ['id' => 'F3-2', 'type' => 'radio', 'label' => 'Parecer', 'required' => true, 'options' => ['Conforme', 'Não conforme'], 'value' => '', 'status' => 'pendente'],
                    ['id' => 'F3-3', 'type' => 'scale', 'label' => 'Grau de conservação', 'required' => false, 'scaleMin' => 1, 'scaleMax' => 5, 'scaleMinLabel' => 'Ruim', 'scaleMaxLabel' => 'Ótimo', 'value' => '', 'status' => 'pendente'],
                ],
            ],
        ];
    }
}
