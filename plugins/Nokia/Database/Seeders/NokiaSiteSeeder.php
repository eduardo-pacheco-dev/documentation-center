<?php

namespace Plugins\Nokia\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Plugins\Nokia\Models\NokiaRom;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
use Plugins\Nokia\Models\NokiaTask;

class NokiaSiteSeeder extends Seeder
{
    /**
     * Sample locations used to build the generated sites.
     *
     * @var list<array{0: string, 1: string}>
     */
    private const LOCATIONS = [
        ['SP', 'JAB'], ['SP', 'PIN'], ['SP', 'STO'], ['SP', 'CAM'],
        ['SP', 'SAN'], ['SP', 'SJC'], ['SP', 'OSA'], ['SP', 'CPS'],
        ['RJ', 'RIO'], ['RJ', 'NIT'], ['MG', 'BEL'], ['MG', 'UBE'],
        ['BA', 'SSA'], ['BA', 'FSA'], ['PR', 'CWB'], ['PR', 'LON'],
        ['SC', 'FLN'], ['PE', 'REC'], ['CE', 'FOR'], ['DF', 'BSB'],
        ['GO', 'GYN'], ['AM', 'MAO'], ['PA', 'BEL'], ['MA', 'SLZ'],
        ['RN', 'NAT'], ['AL', 'MCZ'], ['MT', 'CUI'], ['ES', 'VIX'],
    ];

    /**
     * Seed the nokia sites with a sample dataset.
     *
     * The seeder is idempotent: running it again updates the sample sites and
     * recreates their checklist, tasks, rejections and attachments without
     * duplicating the rows.
     */
    public function run(): void
    {
        $tasks = $this->seedTasks();
        $romIds = NokiaRom::query()->orderBy('id')->pluck('id')->all();

        foreach ($this->sites() as $index => $row) {
            $createdAt = $row['created_at'];
            $tasksDone = $row['tasks_done'];
            $rejections = $row['rejections'] ?? [];
            $attachments = $row['attachments'] ?? [];

            unset($row['created_at'], $row['tasks_done'], $row['rejections'], $row['attachments']);

            if ($romIds !== []) {
                $row['nokia_rom_id'] = $romIds[$index % count($romIds)];
            }

            $site = NokiaSite::query()->updateOrCreate(
                ['site_id' => $row['site_id']],
                $row,
            );

            $site->forceFill([
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ])->save();

            $site->tasks()->sync(
                $tasks
                    ->only($tasksDone)
                    ->mapWithKeys(fn (NokiaTask $task): array => [
                        $task->getKey() => ['completed_at' => $createdAt],
                    ])
                    ->all(),
            );

            $site->rejections()->delete();

            foreach ($rejections as $rejection) {
                $site->rejections()->create($rejection);
            }

            $this->seedAttachments($site, $attachments);
        }
    }

    /**
     * Create the configurable tasks tracked by every site.
     *
     * @return Collection<int, NokiaTask>
     */
    private function seedTasks(): Collection
    {
        return collect([
            'Enviar documentação',
            'Validar projeto',
            'Agendar instalação',
            'Aprovar FAM Panorâmica',
        ])->map(fn (string $name, int $index): NokiaTask => NokiaTask::query()->updateOrCreate(
            ['name' => $name],
            ['position' => $index + 1],
        ));
    }

    /**
     * Build the sample sites, starting with the hand-written anchors.
     *
     * @return list<array<string, mixed>>
     */
    private function sites(): array
    {
        $sites = [
            $this->row('SPJAB051M', 'END-10001', 'OC-5010', 'OS-E-90010', 'OS-I-90010', 5, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3], [
                'attachments' => $this->attachments(['tssr.pdf', 'doc-d.docx', 'planta.dwg']),
            ]),
            $this->row('SPJAB052N', 'END-10002', 'OC-5011', 'OS-E-90011', 'OS-I-90011', 4, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
            ], [0, 1, 2], [
                'attachments' => $this->attachments(['tssr.pdf', 'doc-d.docx']),
            ]),
            $this->row('SPPIN101A', 'END-10003', 'OC-5012', 'OS-E-90012', null, 4, [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], []),
            $this->row('SPSTO201B', 'END-10004', 'OC-5013', 'OS-E-90013', 'OS-I-90013', 3, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ], [0, 1, 2, 3], [
                'attachments' => $this->attachments(['tssr.pdf', 'planta.dwg']),
            ]),
            $this->row('SPCAM301C', 'END-10005', 'OC-5014', null, null, 3, [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0]),
            $this->row('SPSAN401D', 'END-10006', 'OC-5015', 'OS-E-90015', 'OS-I-90015', 2, [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ], [0, 1], [
                'attachments' => $this->attachments(['tssr.pdf']),
            ]),
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
            ], [0, 1, 2], [
                'attachments' => $this->attachments(['tssr.pdf', 'doc-d.docx']),
            ]),
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
                'rejections' => [
                    [
                        'checklist_field' => 'checklist_fam_entrega',
                        'reason' => 'Documentação incompleta',
                        'return_days' => 7,
                        'requires_field_return' => true,
                        'started_at' => now()->subDays(2),
                    ],
                    [
                        'checklist_field' => 'checklist_fam_entrega',
                        'reason' => 'TSSR desatualizado',
                        'return_days' => 14,
                        'requires_field_return' => false,
                        'started_at' => now()->subDays(20),
                        'resolved_at' => now()->subDays(10),
                    ],
                ],
                'attachments' => $this->attachments(['tssr.pdf', 'planta.dwg']),
            ]),
        ];

        foreach (self::LOCATIONS as $index => [$state, $city]) {
            $statuses = $this->statusesFor($index);
            $monthsAgo = $index % 12;

            $sites[] = $this->row(
                sprintf('%s%s%03d', $state, $city, 100 + $index),
                sprintf('END-%05d', 20000 + $index),
                sprintf('OC-%04d', 6000 + $index),
                $index % 6 === 5 ? null : sprintf('OS-E-%05d', 91000 + $index),
                $index % 4 === 0 ? null : sprintf('OS-I-%05d', 91000 + $index),
                $monthsAgo,
                $statuses,
                $this->tasksDoneFor($statuses),
                $this->extrasFor($index, $statuses),
            );
        }

        return $sites;
    }

    /**
     * The checklist statuses used by a generated site.
     *
     * @return list<string>
     */
    private function statusesFor(int $index): array
    {
        return match (true) {
            $index % 7 === 0 => [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
            ],
            $index % 5 === 0 => [
                NokiaSite::STATUS_REJECTED,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ],
            $index % 3 === 0 => [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_ANALYSIS,
                NokiaSite::STATUS_PENDING,
            ],
            $index % 2 === 0 => [
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_SENT,
                NokiaSite::STATUS_PENDING,
            ],
            default => [
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
                NokiaSite::STATUS_PENDING,
            ],
        };
    }

    /**
     * The tasks completed for the given checklist statuses.
     *
     * @param  list<string>  $statuses
     * @return list<int>
     */
    private function tasksDoneFor(array $statuses): array
    {
        $sent = count(array_filter($statuses, fn (string $status): bool => $status === NokiaSite::STATUS_SENT));

        return match (true) {
            $sent >= 3 => [0, 1, 2, 3],
            $sent === 2 => [0, 1],
            $sent === 1 => [0],
            in_array(NokiaSite::STATUS_REJECTED, $statuses, true) => [0],
            default => [],
        };
    }

    /**
     * Interruptions, rejections and attachments for a generated site.
     *
     * @param  list<string>  $statuses
     * @return array<string, mixed>
     */
    private function extrasFor(int $index, array $statuses): array
    {
        $extra = [];

        if ($index % 8 === 3) {
            $extra['interruption_reason'] = 'Aguardando liberação de acesso ao local';
            $extra['interruption_return_days'] = [7, 14, 21, 28][$index % 4];
            $extra['interruption_started_at'] = now()->subDays(($index % 5) + 1);
        }

        if (in_array(NokiaSite::STATUS_REJECTED, $statuses, true)) {
            $extra['rejections'] = [[
                'checklist_field' => 'checklist_fam_entrega',
                'reason' => 'Documentação incompleta',
                'return_days' => 7,
                'requires_field_return' => $index % 2 === 0,
                'started_at' => now()->subDays(($index % 4) + 1),
            ]];

            if ($index % 2 === 0) {
                $extra['rejections'][] = [
                    'checklist_field' => 'checklist_fam_entrega',
                    'reason' => 'TSSR desatualizado',
                    'return_days' => 14,
                    'requires_field_return' => false,
                    'started_at' => now()->subDays(30),
                    'resolved_at' => now()->subDays(15),
                ];
            }
        }

        if ($index % 4 === 0) {
            $extra['attachments'] = $this->attachments(
                $index % 8 === 0
                    ? ['tssr.pdf', 'doc-d.docx', 'planta.dwg']
                    : ['tssr.pdf'],
            );
        }

        return $extra;
    }

    /**
     * Build the sample attachment list for a site.
     *
     * @param  list<string>  $names
     * @return list<array{category: string, name: string, mime: string}>
     */
    private function attachments(array $names): array
    {
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dwg' => 'application/acad',
        ];

        return collect($names)->map(function (string $name) use ($mimeTypes): array {
            $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
            $category = match ($extension) {
                'dwg' => NokiaSiteAttachment::CATEGORY_DWG,
                'docx' => NokiaSiteAttachment::CATEGORY_DOC_D,
                default => NokiaSiteAttachment::CATEGORY_TSSR,
            };

            return [
                'category' => $category,
                'name' => $name,
                'mime' => $mimeTypes[$extension] ?? 'application/pdf',
            ];
        })->all();
    }

    /**
     * Write the sample attachments for the given site.
     *
     * @param  list<array{category: string, name: string, mime: string}>  $files
     */
    private function seedAttachments(NokiaSite $site, array $files): void
    {
        if ($files === []) {
            return;
        }

        $storage = plugin_storage('nokia');

        foreach ($files as $file) {
            $path = 'sites/'.$site->getKey().'/'.$file['category'].'/'.$file['name'];
            $contents = "Documento de exemplo do site {$site->site_id} ({$file['category']}).\n";

            $storage->put($path, $contents);

            $site->attachments()->updateOrCreate(
                ['path' => $path],
                [
                    'category' => $file['category'],
                    'original_name' => $file['name'],
                    'size' => strlen($contents),
                    'mime_type' => $file['mime'],
                ],
            );
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
