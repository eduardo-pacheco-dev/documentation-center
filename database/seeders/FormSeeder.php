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
     * The number of forms to seed.
     */
    private const FORM_COUNT = 18;

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

        foreach (range(1, self::FORM_COUNT) as $index) {
            $this->seedForm($users->get($index % $users->count()), $index);
        }
    }

    /**
     * Seed one form attached to a project owned by the given user.
     */
    private function seedForm(User $owner, int $index): void
    {
        $projects = $owner->projects()->orderBy('id')->get();

        if ($projects->isEmpty()) {
            return;
        }

        $project = $projects->get(($index - 1) % $projects->count());
        $type = self::TYPES[($index - 1) % count(self::TYPES)];
        $status = match (true) {
            $index % 7 === 0 => 'Aprovado',
            $index % 5 === 0 => 'Em preenchimento',
            $index % 3 === 0 => 'Preenchido',
            default => 'Pendente',
        };

        $sequence = $owner->forms()->count() + 1;
        $suffixes = self::NAME_SUFFIXES[$type];
        $suffix = $suffixes[($index - 1) % count($suffixes)];

        Form::create([
            'user_id' => $owner->getKey(),
            'project_id' => $project->getKey(),
            'code' => 'FRM-'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
            'name' => $project->name ? $type.' - '.$suffix.' ('.$project->name.')' : $type.' - '.$suffix,
            'type' => $type,
            'status' => $status,
            'steps' => $this->stepsFor($status),
            'fields' => null,
        ]);
    }

    /**
     * Build the steps of a form. Filled answers appear once the form is done.
     *
     * @return list<array<string, mixed>>
     */
    private function stepsFor(string $status): array
    {
        $filled = in_array($status, ['Preenchido', 'Aprovado'], true);

        return [
            [
                'id' => 'S1',
                'title' => 'Identificação',
                'fields' => [
                    ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'value' => $filled ? 'Galpão Estrutura' : '', 'status' => $filled ? 'aprovado' : 'pendente'],
                    ['id' => 'F1-2', 'type' => 'number', 'label' => 'Código SMP', 'value' => $filled ? 'SMP-1000' : '', 'status' => $filled ? 'aprovado' : 'pendente'],
                    ['id' => 'F1-3', 'type' => 'date', 'label' => 'Data da vistoria', 'value' => $filled ? now()->toDateString() : '', 'status' => $filled ? 'aprovado' : 'pendente'],
                ],
            ],
            [
                'id' => 'S2',
                'title' => 'Registro fotográfico',
                'fields' => [
                    ['id' => 'F2-1', 'type' => 'image', 'label' => 'Foto do painel', 'images' => $filled ? [fake()->imageUrl(640, 480, 'telecom')] : [], 'status' => $filled ? 'enviado' : 'pendente'],
                    ['id' => 'F2-2', 'type' => 'image', 'label' => 'Foto do abrigo', 'images' => $filled && $status === 'Aprovado' ? [fake()->imageUrl(640, 480, 'building')] : [], 'status' => $filled ? 'enviado' : 'pendente'],
                ],
            ],
            [
                'id' => 'S3',
                'title' => 'Conformidade',
                'fields' => [
                    ['id' => 'F3-1', 'type' => 'textarea', 'label' => 'Observações', 'value' => $filled ? fake('pt_BR')->sentence() : '', 'status' => $filled ? 'aprovado' : 'pendente'],
                    ['id' => 'F3-2', 'type' => 'text', 'label' => 'Parecer', 'value' => $status === 'Aprovado' ? 'Conforme' : '', 'status' => $status === 'Aprovado' ? 'aprovado' : 'nao_aplicavel'],
                ],
            ],
        ];
    }
}
