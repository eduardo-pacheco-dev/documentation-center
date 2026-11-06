<?php

namespace Plugins\Nokia\Database\Seeders;

use Illuminate\Database\Seeder;
use Plugins\Nokia\Models\NokiaSite;

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

        $rows = [
            [
                'site_id' => 'SPJAB051M',
                'end_id' => 'END-10001',
                'oc' => 'OC-5010',
                'os_fam_entrega' => 'OS-E-90010',
                'os_fam_instalacao' => 'OS-I-90010',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => true,
                'created_at' => now()->subMonths(5),
            ],
            [
                'site_id' => 'SPJAB052N',
                'end_id' => 'END-10002',
                'oc' => 'OC-5011',
                'os_fam_entrega' => 'OS-E-90011',
                'os_fam_instalacao' => 'OS-I-90011',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subMonths(4),
            ],
            [
                'site_id' => 'SPPIN101A',
                'end_id' => 'END-10003',
                'oc' => 'OC-5012',
                'os_fam_entrega' => 'OS-E-90012',
                'os_fam_instalacao' => null,
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => false,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subMonths(4),
            ],
            [
                'site_id' => 'SPSTO201B',
                'end_id' => 'END-10004',
                'oc' => 'OC-5013',
                'os_fam_entrega' => 'OS-E-90013',
                'os_fam_instalacao' => 'OS-I-90013',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => true,
                'created_at' => now()->subMonths(3),
            ],
            [
                'site_id' => 'SPCAM301C',
                'end_id' => 'END-10005',
                'oc' => 'OC-5014',
                'os_fam_entrega' => null,
                'os_fam_instalacao' => null,
                'checklist_tarefas_enviadas' => false,
                'checklist_fam_entrega' => false,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subMonths(3),
            ],
            [
                'site_id' => 'SPSAN401D',
                'end_id' => 'END-10006',
                'oc' => 'OC-5015',
                'os_fam_entrega' => 'OS-E-90015',
                'os_fam_instalacao' => 'OS-I-90015',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subMonths(2),
            ],
            [
                'site_id' => 'SPSJC501E',
                'end_id' => 'END-10007',
                'oc' => 'OC-5016',
                'os_fam_entrega' => 'OS-E-90016',
                'os_fam_instalacao' => 'OS-I-90016',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => true,
                'created_at' => now()->subMonths(2),
            ],
            [
                'site_id' => 'SPOSA601F',
                'end_id' => 'END-10008',
                'oc' => 'OC-5017',
                'os_fam_entrega' => 'OS-E-90017',
                'os_fam_instalacao' => null,
                'checklist_tarefas_enviadas' => false,
                'checklist_fam_entrega' => false,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subMonth(),
            ],
            [
                'site_id' => 'SPCPS701G',
                'end_id' => 'END-10009',
                'oc' => 'OC-5018',
                'os_fam_entrega' => 'OS-E-90018',
                'os_fam_instalacao' => 'OS-I-90018',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subWeeks(3),
            ],
            [
                'site_id' => 'SPRIO801H',
                'end_id' => 'END-10010',
                'oc' => 'OC-5019',
                'os_fam_entrega' => 'OS-E-90019',
                'os_fam_instalacao' => 'OS-I-90019',
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => true,
                'checklist_fam_instalacao' => true,
                'checklist_fam_panoramica' => true,
                'created_at' => now()->subWeeks(2),
            ],
            [
                'site_id' => 'SPBSB901I',
                'end_id' => 'END-10011',
                'oc' => 'OC-5020',
                'os_fam_entrega' => 'OS-E-90020',
                'os_fam_instalacao' => null,
                'checklist_tarefas_enviadas' => true,
                'checklist_fam_entrega' => false,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subWeeks(1),
            ],
            [
                'site_id' => 'SPFORA01J',
                'end_id' => 'END-10012',
                'oc' => 'OC-5021',
                'os_fam_entrega' => 'OS-E-90021',
                'os_fam_instalacao' => 'OS-I-90021',
                'checklist_tarefas_enviadas' => false,
                'checklist_fam_entrega' => false,
                'checklist_fam_instalacao' => false,
                'checklist_fam_panoramica' => false,
                'created_at' => now()->subDays(5),
            ],
        ];

        foreach ($rows as $row) {
            $createdAt = $row['created_at'];

            unset($row['created_at']);

            $site = NokiaSite::query()->create($row);

            $site->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();
        }
    }
}
