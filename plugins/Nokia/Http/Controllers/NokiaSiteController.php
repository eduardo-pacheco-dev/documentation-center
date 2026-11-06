<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaInterruptionRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaSiteRequest;
use Plugins\Nokia\Models\NokiaRom;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
use Plugins\Nokia\Models\NokiaTask;

class NokiaSiteController extends Controller
{
    /**
     * List the registered sites.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $query = NokiaSite::query()->with('rom');

        if ($search !== '') {
            $query->matching($search);
        }

        $sites = $query
            ->latest('updated_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        $total = NokiaSite::query()->count();
        $completed = NokiaSite::query()->completed()->count();

        $editSite = old('modal') === 'edit' && old('editing_id')
            ? NokiaSite::query()->find(old('editing_id'))
            : null;

        return view('nokia::index', [
            'sites' => $sites,
            'search' => $search,
            'editSite' => $editSite,
            'roms' => $this->roms(),
            'createHasErrors' => old('modal') === 'create' && session('errors')?->any(),
            'editHasErrors' => old('modal') === 'edit' && session('errors')?->any(),
            'stats' => [
                'total' => $total,
                'completed' => $completed,
                'pending' => $total - $completed,
            ],
        ]);
    }

    /**
     * Show the form to create a site.
     */
    public function create(): View
    {
        return view('nokia::create', [
            'roms' => $this->roms(),
        ]);
    }

    /**
     * Show the given site with its checklist and attachments.
     */
    public function show(NokiaSite $site): View
    {
        $site->load(['attachments', 'rom']);

        $tasks = NokiaTask::query()
            ->orderBy('position')
            ->orderBy('name')
            ->get();

        $completedTaskIds = $site->tasks()
            ->wherePivotNotNull('completed_at')
            ->pluck('nokia_tasks.id')
            ->all();

        $attachments = collect(NokiaSiteAttachment::categories())
            ->map(fn (string $label, string $category): array => [
                'category' => $category,
                'label' => $label,
                'files' => $site->attachments
                    ->where('category', $category)
                    ->values(),
            ])
            ->values();

        return view('nokia::show', [
            'site' => $site,
            'attachments' => $attachments,
            'roms' => $this->roms(),
            'tasks' => $tasks,
            'completedTaskIds' => $completedTaskIds,
        ]);
    }

    /**
     * Store a newly created site.
     */
    public function store(StoreNokiaSiteRequest $request): RedirectResponse
    {
        NokiaSite::query()->create($request->validated());

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site cadastrado com sucesso.');
    }

    /**
     * Show the form to edit the given site.
     */
    public function edit(NokiaSite $site): View
    {
        return view('nokia::edit', [
            'site' => $site,
            'roms' => $this->roms(),
        ]);
    }

    /**
     * Update the given site.
     */
    public function update(UpdateNokiaSiteRequest $request, NokiaSite $site): RedirectResponse
    {
        $site->update($request->validated());

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site atualizado com sucesso.');
    }

    /**
     * Toggle the completion of a task for the given site.
     */
    public function toggleTask(NokiaSite $site, NokiaTask $task): RedirectResponse
    {
        $completed = $site->tasks()
            ->whereKey($task->getKey())
            ->wherePivotNotNull('completed_at')
            ->exists();

        $site->tasks()->syncWithoutDetaching([
            $task->getKey() => ['completed_at' => $completed ? null : now()],
        ]);

        return back()->with(
            'status',
            sprintf(
                'Tarefa %s %s.',
                $task->name,
                $completed ? 'reaberta' : 'concluída',
            ),
        );
    }

    /**
     * Register a work interruption for the given site.
     */
    public function updateInterruption(UpdateNokiaInterruptionRequest $request, NokiaSite $site): RedirectResponse
    {
        $site->update([
            'interruption_reason' => $request->validated('reason'),
            'interruption_return_days' => (int) $request->validated('return_days'),
            'interruption_started_at' => now(),
        ]);

        return back()->with('status', 'Interrupção de obra registrada.');
    }

    /**
     * Clear the work interruption of the given site.
     */
    public function clearInterruption(NokiaSite $site): RedirectResponse
    {
        $site->update([
            'interruption_reason' => null,
            'interruption_return_days' => null,
            'interruption_started_at' => null,
        ]);

        return back()->with('status', 'Interrupção de obra encerrada.');
    }

    /**
     * Remove the given site.
     */
    public function destroy(NokiaSite $site): RedirectResponse
    {
        $site->delete();

        return redirect()
            ->route('admin.nokia.sites.index')
            ->with('status', 'Site excluído com sucesso.');
    }

    /**
     * The ROMs available for selection.
     */
    private function roms(): Collection
    {
        return NokiaRom::query()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }
}
