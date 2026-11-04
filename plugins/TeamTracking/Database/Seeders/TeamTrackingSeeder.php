<?php

namespace Plugins\TeamTracking\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Plugins\TeamTracking\Enums\CheckInStatus;
use Plugins\TeamTracking\Enums\TeamStatus;
use Plugins\TeamTracking\Models\Team;

class TeamTrackingSeeder extends Seeder
{
    public function run(): void
    {
        if (Team::query()->exists()) {
            return;
        }

        $users = User::query()->orderBy('id')->take(4)->get();

        if ($users->count() < 2) {
            $users = $users->merge(User::factory()->count(3 - $users->count())->create());
        }

        $teams = [
            [
                'name' => 'Operações',
                'description' => 'Equipe responsável pela operação e suporte do dia a dia.',
                'area' => 'Operações',
                'status' => TeamStatus::Active,
                'started_at' => now()->subMonths(8)->toDateString(),
                'check_in' => [
                    'title' => 'Check-in semanal',
                    'status' => CheckInStatus::OnTrack,
                    'health_score' => 4,
                    'summary' => 'Entregas em dia e bom nível de colaboração.',
                    'next_steps' => 'Revisar escalas da próxima semana.',
                ],
            ],
            [
                'name' => 'Comercial',
                'description' => 'Equipe de relacionamento e expansão comercial.',
                'area' => 'Comercial',
                'status' => TeamStatus::Active,
                'started_at' => now()->subMonths(5)->toDateString(),
                'check_in' => [
                    'title' => 'Revisão de metas',
                    'status' => CheckInStatus::Attention,
                    'health_score' => 2,
                    'summary' => 'Volume de propostas abaixo do esperado.',
                    'next_steps' => 'Reforçar acompanhamento dos leads.',
                ],
            ],
            [
                'name' => 'Tecnologia',
                'description' => 'Equipe de desenvolvimento e infraestrutura.',
                'area' => 'TI',
                'status' => TeamStatus::Paused,
                'started_at' => now()->subMonths(12)->toDateString(),
                'check_in' => [
                    'title' => 'Check-in mensal',
                    'status' => CheckInStatus::Completed,
                    'health_score' => 5,
                    'summary' => 'Projeto entregue e equipe em transição.',
                    'next_steps' => 'Planejar próximas frentes.',
                ],
            ],
        ];

        foreach ($teams as $index => $data) {
            $leader = $users[$index % $users->count()];

            $team = Team::query()->create([
                'name' => $data['name'],
                'description' => $data['description'],
                'area' => $data['area'],
                'leader_id' => $leader->getKey(),
                'status' => $data['status']->value,
                'started_at' => $data['started_at'],
            ]);

            foreach ($users->take(2) as $member) {
                $team->members()->attach($member->getKey(), [
                    'role' => $member->is($leader) ? 'lider' : 'membro',
                    'joined_at' => $data['started_at'],
                ]);
            }

            $team->checkIns()->create([
                ...$data['check_in'],
                'reference_date' => now()->subDays($index * 7)->toDateString(),
                'status' => $data['check_in']['status']->value,
                'user_id' => $leader->getKey(),
            ]);
        }
    }
}
