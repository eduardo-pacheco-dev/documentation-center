<?php

namespace Plugins\FollowUp\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Models\FollowUp;
use Plugins\FollowUp\Models\FollowUpAssignee;

class FollowUpSeeder extends Seeder
{
    /**
     * Seed sample follow-ups for the available users.
     */
    public function run(): void
    {
        if (FollowUp::query()->exists()) {
            return;
        }

        $users = User::query()->orderBy('id')->take(3)->get();

        if ($users->isEmpty()) {
            return;
        }

        $assignees = $users->mapWithKeys(fn (User $user): array => [
            $user->getKey() => FollowUpAssignee::query()->firstOrCreate(
                ['email' => $user->email],
                ['name' => $user->full_name],
            ),
        ]);

        $followUps = [
            [
                'title' => 'Retornar contato com o fornecedor',
                'description' => 'Confirmar prazo e valores da proposta enviada.',
                'status' => FollowUpStatus::InProgress,
                'priority' => FollowUpPriority::High,
                'due_date' => now()->addDays(2)->toDateString(),
                'notes' => ['Contato inicial realizado; aguardando retorno.'],
            ],
            [
                'title' => 'Validar proposta comercial',
                'description' => 'Revisar condições comerciais e aprovar com a diretoria.',
                'status' => FollowUpStatus::Pending,
                'priority' => FollowUpPriority::Urgent,
                'due_date' => now()->subDay()->toDateString(),
                'notes' => ['Aguardando validação jurídica.'],
            ],
            [
                'title' => 'Revisar documentação técnica',
                'description' => 'Validar os documentos pendentes antes da aprovação.',
                'status' => FollowUpStatus::Pending,
                'priority' => FollowUpPriority::Medium,
                'due_date' => now()->addDays(7)->toDateString(),
            ],
            [
                'title' => 'Agendar visita técnica',
                'description' => 'Confirmar agenda com a equipe de campo.',
                'status' => FollowUpStatus::InProgress,
                'priority' => FollowUpPriority::Medium,
                'due_date' => now()->addDays(10)->toDateString(),
            ],
            [
                'title' => 'Atualizar cadastro do cliente',
                'description' => 'Revisar contatos, endereço e dados de faturamento.',
                'status' => FollowUpStatus::Pending,
                'priority' => FollowUpPriority::High,
                'due_date' => now()->addDay()->toDateString(),
            ],
            [
                'title' => 'Enviar relatório mensal',
                'description' => 'Consolidar os indicadores e encaminhar para a diretoria.',
                'status' => FollowUpStatus::Completed,
                'priority' => FollowUpPriority::Low,
                'due_date' => now()->subDays(2)->toDateString(),
                'completed_at' => now()->subDay(),
                'notes' => ['Relatório enviado por e-mail.'],
            ],
            [
                'title' => 'Preparar apresentação',
                'description' => 'Montar os slides da reunião de acompanhamento.',
                'status' => FollowUpStatus::Completed,
                'priority' => FollowUpPriority::Medium,
                'due_date' => now()->subDays(3)->toDateString(),
                'completed_at' => now()->subDays(3),
            ],
            [
                'title' => 'Confirmar pagamento',
                'description' => 'Follow-up cancelado após confirmação do financeiro.',
                'status' => FollowUpStatus::Cancelled,
                'priority' => FollowUpPriority::Low,
                'due_date' => now()->subDays(5)->toDateString(),
            ],
        ];

        $clients = ['AFL Engenharia', 'Nokia', 'Vivo', 'TIM'];
        $projects = ['Expansão 5G', 'Manutenção preventiva', 'Modernização de rede', 'Implantação de site'];

        foreach ($followUps as $index => $data) {
            $user = $users[$index % $users->count()];

            $followUp = FollowUp::query()->create([
                'title' => $data['title'],
                'description' => $data['description'],
                'client' => $data['client'] ?? $clients[$index % count($clients)],
                'project' => $data['project'] ?? $projects[$index % count($projects)],
                'status' => $data['status']->value,
                'priority' => $data['priority']->value,
                'due_date' => $data['due_date'],
                'assignee_id' => $assignees->get($user->getKey())?->getKey(),
                'created_by' => $users->first()->getKey(),
                'completed_at' => $data['completed_at'] ?? null,
            ]);

            $followUp->updates()->create([
                'user_id' => $user->getKey(),
                'body' => 'Follow-up criado.',
                'status_to' => $data['status']->value,
            ]);

            foreach ($data['notes'] ?? [] as $note) {
                $followUp->updates()->create([
                    'user_id' => $user->getKey(),
                    'body' => $note,
                ]);
            }
        }
    }
}
