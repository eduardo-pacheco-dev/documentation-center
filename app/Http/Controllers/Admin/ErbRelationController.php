<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Erb;
use App\Models\Project;
use App\Models\RadioLink;
use App\Models\WorkOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ErbRelationController extends Controller
{
    /**
     * Attach the selected projects to the ERB and delete the unselected ones.
     */
    public function projects(Request $request, Erb $erb): RedirectResponse
    {
        $this->authorize('update', $erb);

        $validated = $request->validate([
            'projects' => ['nullable', 'array'],
            'projects.*' => ['integer'],
        ]);

        $user = $request->user();

        /** @var Collection<int, int> $selected */
        $selected = Collection::make($validated['projects'] ?? [])
            ->map(fn (int|string $id): int => (int) $id)
            ->unique();

        $linked = $user->projects()->where('erb_id', $erb->getKey())->get();

        DB::transaction(function () use ($user, $erb, $selected, $linked): void {
            foreach ($linked as $project) {
                if (! $selected->contains($project->getKey())) {
                    $this->authorize('delete', $project);
                    $project->delete();
                }
            }

            $user->projects()
                ->whereIn('id', $selected->all())
                ->where(fn ($query) => $query->whereNull('erb_id')->orWhere('erb_id', '!=', $erb->getKey()))
                ->get()
                ->each(function (Project $project) use ($erb): void {
                    $this->authorize('update', $project);
                    $project->update(['erb_id' => $erb->getKey()]);
                });
        });

        return back()->with('status', 'Projetos vinculados à ERB atualizados.');
    }

    /**
     * Attach the selected work orders to the ERB and delete the unselected ones.
     */
    public function workOrders(Request $request, Erb $erb): RedirectResponse
    {
        $this->authorize('update', $erb);

        $validated = $request->validate([
            'work_orders' => ['nullable', 'array'],
            'work_orders.*' => ['integer'],
        ]);

        $user = $request->user();

        /** @var Collection<int, int> $selected */
        $selected = Collection::make($validated['work_orders'] ?? [])
            ->map(fn (int|string $id): int => (int) $id)
            ->unique();

        $linked = $user->workOrders()->where('erb_id', $erb->getKey())->get();

        DB::transaction(function () use ($user, $erb, $selected, $linked): void {
            foreach ($linked as $workOrder) {
                if (! $selected->contains($workOrder->getKey())) {
                    $this->authorize('delete', $workOrder);
                    $workOrder->delete();
                }
            }

            $user->workOrders()
                ->whereIn('id', $selected->all())
                ->where(fn ($query) => $query->whereNull('erb_id')->orWhere('erb_id', '!=', $erb->getKey()))
                ->get()
                ->each(function (WorkOrder $workOrder) use ($erb): void {
                    $this->authorize('update', $workOrder);
                    $workOrder->update(['erb_id' => $erb->getKey()]);
                });
        });

        return back()->with('status', 'Ordens de serviço vinculadas à ERB atualizadas.');
    }

    /**
     * Attach the selected radio links to the ERB and delete the unselected ones.
     */
    public function radioLinks(Request $request, Erb $erb): RedirectResponse
    {
        $this->authorize('update', $erb);

        $validated = $request->validate([
            'radio_links' => ['nullable', 'array'],
            'radio_links.*' => ['array:end'],
            'radio_links.*.end' => ['nullable', Rule::in(['a', 'b'])],
        ]);

        $user = $request->user();

        /** @var Collection<int, string|null> $submitted */
        $submitted = Collection::make($validated['radio_links'] ?? [])
            ->mapWithKeys(fn (array $row, int|string $id): array => [(int) $id => $row['end'] ?? null]);

        $linked = $user->radioLinks()
            ->where(fn ($query) => $query->where('erb_a_id', $erb->getKey())->orWhere('erb_b_id', $erb->getKey()))
            ->get();

        $selected = $user->radioLinks()
            ->whereIn('id', $submitted->keys())
            ->get()
            ->keyBy(fn ($radioLink): int => $radioLink->getKey());

        /** @var list<array{0: RadioLink, 1: string}> $updates */
        $updates = [];

        foreach ($submitted as $id => $end) {
            if (! in_array($end, ['a', 'b'], true) || ! $selected->has($id)) {
                continue;
            }

            $radioLink = $selected->get($id);
            $column = $end === 'a' ? 'erb_a_id' : 'erb_b_id';
            $otherColumn = $end === 'a' ? 'erb_b_id' : 'erb_a_id';

            if ((int) $radioLink->{$column} === $erb->getKey()) {
                continue;
            }

            if ((int) $radioLink->{$otherColumn} === $erb->getKey()) {
                return back()->withErrors([
                    'radio_links' => 'O radio link '.$radioLink->code.' já usa esta ERB na outra ponta. Edite o link para trocar a ponta.',
                ]);
            }

            $updates[] = [$radioLink, $column];
        }

        DB::transaction(function () use ($erb, $submitted, $linked, $updates): void {
            foreach ($linked as $radioLink) {
                if (! in_array($submitted->get($radioLink->getKey()), ['a', 'b'], true)) {
                    $this->authorize('delete', $radioLink);
                    $radioLink->delete();
                }
            }

            foreach ($updates as [$radioLink, $column]) {
                $this->authorize('update', $radioLink);
                $radioLink->update([$column => $erb->getKey()]);
            }
        });

        return back()->with('status', 'Radio links vinculados à ERB atualizados.');
    }
}
