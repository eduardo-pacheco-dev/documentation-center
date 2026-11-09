<?php

namespace Plugins\Nokia\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Plugins\Nokia\Models\NokiaSite;

/**
 * Builds the aggregates shown by the Nokia dashboard widget.
 *
 * The site acceptance bucket is derived from the checklist statuses and the
 * OS fields, because the plugin does not store a separate approval column.
 */
final class NokiaDashboardMetrics
{
    public const TARGET_ACCEPTANCE = 80;

    /**
     * The dashboard data.
     *
     * @return array{
     *     totals: array<string, int|float>,
     *     statuses: list<array<string, mixed>>,
     *     regional: list<array<string, mixed>>,
     *     aging: list<array<string, int|string>>,
     *     subcons: list<array<string, mixed>>,
     *     roms: list<array<string, mixed>>,
     *     sites: list<array<string, mixed>>,
     *     insights: list<array{tone: string, text: string}>
     * }
     */
    public function build(): array
    {
        $sites = NokiaSite::query()
            ->with('rom')
            ->get([
                'id',
                'site_id',
                'oc',
                'os_fam_entrega',
                'os_fam_instalacao',
                'regional',
                'subcon',
                'nokia_rom_id',
                'mos_real_at',
                'instalacao_real_at',
                'integracao_real_at',
                'rfa_real_at',
                'checklist_fam_entrega',
                'checklist_fam_entrega_status',
                'checklist_fam_instalacao',
                'checklist_fam_instalacao_status',
                'checklist_fam_panoramica',
                'checklist_fam_panoramica_status',
                'fam_instalacao_approved_at',
                'created_at',
                'updated_at',
            ]);

        $definitions = $this->statusDefinitions();
        $total = $sites->count();

        /** @var Collection<int, string> $statusBySite */
        $statusBySite = $sites->mapWithKeys(
            fn (NokiaSite $site): array => [$site->getKey() => $this->statusFor($site)],
        );

        $counts = $statusBySite->countBy();
        $accepted = (int) $counts->get('accepted', 0);
        $inProgress = (int) $counts->get('analysis', 0) + (int) $counts->get('first_send', 0);

        $statuses = collect($definitions)
            ->map(fn (array $definition, string $key): array => [
                'key' => $key,
                'label' => $definition['label'],
                'color' => $definition['color'],
                'tone' => $definition['tone'],
                'count' => (int) $counts->get($key, 0),
                'percent' => $this->percent((int) $counts->get($key, 0), $total),
            ])
            ->values()
            ->all();

        $regional = $this->regional($sites, $statusBySite, $definitions);
        $subcons = $this->subcons($sites, $statusBySite);

        return [
            'totals' => [
                'sites' => $total,
                'accepted' => $accepted,
                'analysis' => (int) $counts->get('analysis', 0),
                'first_send' => (int) $counts->get('first_send', 0),
                'rejected' => (int) $counts->get('rejected', 0),
                'no_os' => (int) $counts->get('no_os', 0),
                'acceptance' => $this->percent($accepted, $total),
                'projected' => $this->percent($accepted + $inProgress, $total),
                'target' => self::TARGET_ACCEPTANCE,
            ],
            'statuses' => $statuses,
            'regional' => $regional,
            'aging' => $this->aging($sites, $statusBySite),
            'subcons' => $subcons,
            'roms' => $this->roms($sites, $statusBySite),
            'sites' => $this->recentSites($sites, $statusBySite, $definitions),
            'insights' => $this->insights($sites, $statusBySite, $regional, $total),
        ];
    }

    /**
     * The status definitions used by the KPIs, charts and tables.
     *
     * @return array<string, array{label: string, color: string, tone: string}>
     */
    private function statusDefinitions(): array
    {
        return [
            'accepted' => ['label' => 'Aceitos', 'color' => '#10b981', 'tone' => 'emerald'],
            'analysis' => ['label' => 'Análise TIM', 'color' => '#f59e0b', 'tone' => 'amber'],
            'first_send' => ['label' => 'Primeiro Envio', 'color' => '#f97316', 'tone' => 'orange'],
            'rejected' => ['label' => 'Rejeitado', 'color' => '#ef4444', 'tone' => 'red'],
            'no_os' => ['label' => 'Sem OS MOS', 'color' => '#6b7280', 'tone' => 'gray'],
        ];
    }

    /**
     * The mutually exclusive bucket of the site, based on the FAM Instalação.
     *
     * "Primeiro Envio" are the sites whose FAM Instalação was not sent to the
     * TIM analysis yet (status pending) and that already have a FAM Entrega OS.
     * Sites without that OS are grouped as "Sem OS MOS".
     */
    private function statusFor(NokiaSite $site): string
    {
        $status = $site->checklistStatus('checklist_fam_instalacao');

        if ($status === NokiaSite::STATUS_SENT) {
            return 'accepted';
        }

        if ($status === NokiaSite::STATUS_REJECTED) {
            return 'rejected';
        }

        if ($status === NokiaSite::STATUS_ANALYSIS) {
            return 'analysis';
        }

        return blank($site->os_fam_entrega) ? 'no_os' : 'first_send';
    }

    /**
     * The status counts grouped by regional.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @param  array<string, array{label: string, color: string, tone: string}>  $definitions
     * @return list<array<string, mixed>>
     */
    private function regional(Collection $sites, Collection $statusBySite, array $definitions): array
    {
        return $sites
            ->groupBy(fn (NokiaSite $site): string => filled($site->regional) ? $site->regional : 'Sem regional')
            ->sortKeys()
            ->map(function (Collection $group, string $label) use ($statusBySite, $definitions): array {
                $total = $group->count();
                $accepted = $group->filter(
                    fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'accepted',
                )->count();

                return [
                    'label' => $label,
                    'total' => $total,
                    'accepted' => $accepted,
                    'percent' => $this->percent($accepted, $total),
                    'statuses' => collect($definitions)
                        ->map(fn (array $definition, string $key): array => [
                            'key' => $key,
                            'label' => $definition['label'],
                            'color' => $definition['color'],
                            'count' => $group->filter(
                                fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === $key,
                            )->count(),
                        ])
                        ->values()
                        ->all(),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The aging of the sites that are not accepted yet.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @return list<array{label: string, count: int}>
     */
    private function aging(Collection $sites, Collection $statusBySite): array
    {
        $buckets = [
            ['label' => '0 a 10', 'min' => 0, 'max' => 10],
            ['label' => '11 a 20', 'min' => 11, 'max' => 20],
            ['label' => '21 a 60', 'min' => 21, 'max' => 60],
            ['label' => '61 a 90', 'min' => 61, 'max' => 90],
            ['label' => '90+', 'min' => 91, 'max' => null],
        ];

        $counts = array_fill(0, count($buckets), 0);

        foreach ($sites as $site) {
            if ($statusBySite->get($site->getKey()) === 'accepted') {
                continue;
            }

            $days = $this->daysSince($site);

            foreach ($buckets as $index => $bucket) {
                if ($days >= $bucket['min'] && ($bucket['max'] === null || $days <= $bucket['max'])) {
                    $counts[$index]++;

                    break;
                }
            }
        }

        return collect($buckets)
            ->map(fn (array $bucket, int $index): array => [
                'label' => $bucket['label'],
                'count' => $counts[$index],
            ])
            ->all();
    }

    /**
     * The acceptance per subcontractor.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @return list<array<string, mixed>>
     */
    private function subcons(Collection $sites, Collection $statusBySite): array
    {
        return $sites
            ->groupBy(fn (NokiaSite $site): string => filled($site->subcon) ? $site->subcon : 'Sem subcon')
            ->sortKeys()
            ->map(function (Collection $group, string $label) use ($statusBySite): array {
                $total = $group->count();
                $accepted = $group->filter(
                    fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'accepted',
                );

                $aging = $accepted
                    ->filter(fn (NokiaSite $site): bool => $site->mos_real_at !== null && $site->fam_instalacao_approved_at !== null)
                    ->map(fn (NokiaSite $site): int => (int) $site->mos_real_at->diffInDays($site->fam_instalacao_approved_at))
                    ->avg();

                return [
                    'label' => $label,
                    'total' => $total,
                    'accepted' => $accepted->count(),
                    'gap' => $total - $accepted->count(),
                    'aging' => $aging === null ? null : round($aging, 1),
                    'percent' => $this->percent($accepted->count(), $total),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The acceptance per ROM, ordered from the best to the worst rate.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @return list<array<string, mixed>>
     */
    private function roms(Collection $sites, Collection $statusBySite): array
    {
        return $sites
            ->groupBy(fn (NokiaSite $site): string => $site->rom?->fullName() ?: 'Sem ROM')
            ->map(function (Collection $group, string $label) use ($statusBySite): array {
                $total = $group->count();
                $accepted = $group->filter(
                    fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'accepted',
                )->count();

                return [
                    'label' => $label,
                    'total' => $total,
                    'accepted' => $accepted,
                    'percent' => $this->percent($accepted, $total),
                ];
            })
            ->sortBy([
                fn (array $left, array $right): int => $right['percent'] <=> $left['percent'],
                fn (array $left, array $right): int => $left['label'] <=> $right['label'],
            ])
            ->take(8)
            ->values()
            ->all();
    }

    /**
     * The last updated sites rendered in the OS table.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @param  array<string, array{label: string, color: string, tone: string}>  $definitions
     * @return list<array<string, mixed>>
     */
    private function recentSites(Collection $sites, Collection $statusBySite, array $definitions): array
    {
        return $sites
            ->sortByDesc('updated_at')
            ->take(10)
            ->map(function (NokiaSite $site) use ($statusBySite, $definitions): array {
                $status = (string) $statusBySite->get($site->getKey());

                return [
                    'oc' => $site->oc,
                    'os_entrega' => $site->os_fam_entrega,
                    'os_instalacao' => $site->os_fam_instalacao,
                    'site_id' => $site->site_id,
                    'regional' => $site->regional,
                    'subcon' => $site->subcon,
                    'rom' => $site->rom?->fullName(),
                    'mos_real_at' => $site->mos_real_at,
                    'instalacao_real_at' => $site->instalacao_real_at,
                    'integracao_real_at' => $site->integracao_real_at,
                    'rfa_real_at' => $site->rfa_real_at,
                    'status' => $status,
                    'status_label' => $definitions[$status]['label'],
                    'status_color' => $definitions[$status]['color'],
                ];
            })
            ->values()
            ->all();
    }

    /**
     * The governance insights shown next to the charts.
     *
     * @param  Collection<int, NokiaSite>  $sites
     * @param  Collection<int, string>  $statusBySite
     * @param  list<array<string, mixed>>  $regional
     * @return list<array{tone: string, text: string}>
     */
    private function insights(Collection $sites, Collection $statusBySite, array $regional, int $total): array
    {
        if ($total === 0) {
            return [];
        }

        $accepted = $sites->filter(
            fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'accepted',
        )->count();

        $inProgress = $sites->filter(
            fn (NokiaSite $site): bool => in_array($statusBySite->get($site->getKey()), ['analysis', 'first_send'], true),
        )->count();

        $acceptance = $this->percent($accepted, $total);
        $projected = $this->percent($accepted + $inProgress, $total);

        $insights = [[
            'tone' => $acceptance >= self::TARGET_ACCEPTANCE ? 'success' : 'warning',
            'text' => sprintf(
                'Tendência de aceitação está em %s%%, %s da meta de %d%%.',
                $this->number($acceptance),
                $acceptance >= self::TARGET_ACCEPTANCE ? 'acima' : 'abaixo',
                self::TARGET_ACCEPTANCE,
            ),
        ], [
            'tone' => $projected >= self::TARGET_ACCEPTANCE ? 'success' : 'warning',
            'text' => sprintf('Projeção de aceitação com os sites em andamento: %s%%.', $this->number($projected)),
        ]];

        $stale = $sites->filter(
            fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'rejected' && $this->daysSince($site) > 90,
        )->count();

        if ($stale > 0) {
            $insights[] = [
                'tone' => 'danger',
                'text' => sprintf('%d site(s) rejeitado(s) com mais de 90 dias sem aceite.', $stale),
            ];
        }

        $noOs = $sites->filter(
            fn (NokiaSite $site): bool => $statusBySite->get($site->getKey()) === 'no_os',
        )->count();

        if ($noOs > 0) {
            $insights[] = [
                'tone' => 'warning',
                'text' => sprintf('%d site(s) sem OS de FAM de Entrega (MOS) cadastrada.', $noOs),
            ];
        }

        $withoutRom = $sites->whereNull('nokia_rom_id')->count();

        if ($withoutRom > 0) {
            $insights[] = [
                'tone' => 'info',
                'text' => sprintf('%d site(s) sem ROM responsável atribuído.', $withoutRom),
            ];
        }

        $worst = collect($regional)
            ->filter(fn (array $item): bool => $item['total'] > 0)
            ->sortBy('percent')
            ->first();

        if ($worst !== null) {
            $insights[] = [
                'tone' => $worst['percent'] >= self::TARGET_ACCEPTANCE ? 'success' : 'warning',
                'text' => sprintf(
                    'Regional %s concentra a menor aceitação: %s%% (%d de %d sites).',
                    $worst['label'],
                    $this->number($worst['percent']),
                    $worst['accepted'],
                    $worst['total'],
                ),
            ];
        }

        return $insights;
    }

    /**
     * The days elapsed since the MOS completion, falling back to the creation.
     */
    private function daysSince(NokiaSite $site): int
    {
        $base = $site->mos_real_at ?? $site->created_at ?? today();

        return max(0, (int) Carbon::parse($base)->startOfDay()->diffInDays(today()));
    }

    /**
     * The percentage of the part over the total, rounded to one decimal.
     */
    private function percent(int $part, int $total): float
    {
        return $total > 0 ? round(($part / $total) * 100, 1) : 0.0;
    }

    /**
     * Format a percentage with a comma as the decimal separator.
     */
    private function number(float $value): string
    {
        return number_format($value, 1, ',', '.');
    }
}
