<?php

namespace Plugins\Nokia\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaTask;

class NokiaSiteSeeder extends Seeder
{
    /**
     * Seed the nokia sites with a sample dataset.
     */
    public function run(): void
    {
        if (NokiaSite::query()->exists()) {
            return;
        }

        $tasks = collect([
            'Enviar documentação',
            'Validar projeto',
            'Agendar instalação',
            'Aprovar FAM Panorâmica',
        ])->map(fn (string $name, int $index): NokiaTask => NokiaTask::query()->create([
            'name' => $name,
            'position' => $index + 1,
        ]));

        $rows = [
            $this->row('SPJAB051M', 'END-10001', 'OC-5010', 'OS-E-90010', 'OS-I-90010', 5, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3]),
            $this->row('SPJAB052N', 'END-10002', 'OC-5011', 'OS-E-90011', 'OS-I-90011', 4, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
            ], [0, 1, 2]),
            $this->row('SPPIN101A', 'END-10003', 'OC-5012', 'OS-E-90012', null, 4, [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], []),
            $this->row('SPSTO201B', 'END-10004', 'OC-5013', 'OS-E-90013', 'OS-I-90013', 3, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3]),
            $this->row('SPCAM301C', 'END-10005', 'OC-5014', null, null, 3, [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0]),
            $this->row('SPSAN401D', 'END-10006', 'OC-5015', 'OS-E-90015', 'OS-I-90015', 2, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0, 1]),
            $this->row('SPSJC501E', 'END-10007', 'OC-5016', 'OS-E-90016', 'OS-I-90016', 2, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3]),
            $this->row('SPOSA601F', 'END-10008', 'OC-5017', 'OS-E-90017', null, 1, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0, 1], [
                'interruption_reason' => 'Aguardando liberação da prefeitura',
                'interruption_return_days' => 14,
                'interruption_started_at' => now()->subDays(3),
            ]),
            $this->row('SPCPS701G', 'END-10009', 'OC-5018', 'OS-E-90018', 'OS-I-90018', 0, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_ANALYSIS,
            ], [0, 1, 2]),
            $this->row('SPRIO801H', 'END-10010', 'OC-5019', 'OS-E-90019', 'OS-I-90019', 0, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3]),
            $this->row('SPBSB901I', 'END-10011', 'OC-5020', 'OS-E-90020', null, 0, [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], []),
            $this->row('SPFORA01J', 'END-10012', 'OC-5021', 'OS-E-90021', 'OS-I-90021', 0, [
                NokiaSite::STATUS_REJECTED,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0], [
                'rejection_reason' => 'Documentação incompleta',
                'rejection_return_days' => 7,
                'rejection_started_at' => now()->subDays(2),
            ]),
        ];

        foreach ($rows as $row) {
            $createdAt = $row['created_at'];
            $tasksDone = $row['tasks_done'];

            unset($row['created_at'], $row['tasks_done']);

            $site = NokiaSite::query()->create($row);

            $site->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();

            foreach ($tasksDone as $index) {
                if ($tasks->has($index)) {
                    $site->tasks()->attach($tasks[$index]->getKey(), [
                        'completed_at' => $createdAt,
                    ]);
                }
            }
        }
    }

    /**
     * Build a site row with its checklist status, milestones and extras.
     *
     * @param  list<string>  $statuses
     * @param  list<int>  $tasksDone
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function row(
        string $siteId,
        string $endId,
        string $oc,
        ?string $osFamEntrega,
        ?string $osFamInstalacao,
        int $monthsAgo,
        array $statuses,
        array $tasksDone,
        array $extra = [],
    ): array {
        $createdAt = now()->subMonths($monthsAgo)->subDays(5);
        $checklists = ['fam_entrega', 'fam_instalacao', 'fam_panoramica'];

        $row = [
            'site_id' => $siteId,
            'end_id' => $endId,
            'oc' => $oc,
            'os_fam_entrega' => $osFamEntrega,
            'os_fam_instalacao' => $osFamInstalacao,
            'created_at' => $createdAt,
            'tasks_done' => $tasksDone,
        ];

        foreach ($checklists as $index => $item) {
            $field = 'checklist_'.$item;
            $status = $statuses[$index] ?? NokiaSite::STATUS_PENDING;

            $row[$field] = $status === NokiaSite::STATUS_SENT;
            $row[$field.'_status'] = $status;
        }

        return array_merge([
            'mos_plan_at' => $createdAt->copy()->addDays(7)->toDateString(),
            'mos_real_at' => $createdAt->copy()->addDays(9)->toDateString(),
            'instalacao_plan_at' => $createdAt->copy()->addDays(20)->toDateString(),
            'instalacao_real_at' => $createdAt->copy()->addDays(23)->toDateString(),
            'integracao_plan_at' => $createdAt->copy()->addDays(35)->toDateString(),
            'integracao_real_at' => $createdAt->copy()->addDays(37)->toDateString(),
            'rfa_plan_at' => $createdAt->copy()->addDays(50)->toDateString(),
            'rfa_real_at' => $createdAt->copy()->addDays(52)->toDateString(),
            'fam_entrega_approved_at' => $statuses[0] === NokiaSite::STATUS_SENT
                ? $createdAt->copy()->addDays(25)->toDateString()
                : null,
            'fam_instalacao_approved_at' => ($statuses[1] ?? null) === NokiaSite::STATUS_SENT
                ? $createdAt->copy()->addDays(40)->toDateString()
                : null,
            'fam_panoramica_approved_at' => ($statuses[2] ?? null) === NokiaSite::STATUS_SENT
                ? $createdAt->copy()->addDays(55)->toDateString()
                : null,
        ], $extra, $row);
    }
}
