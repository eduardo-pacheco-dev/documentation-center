<?php

namespace Plugins\Nokia\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Plugins\Nokia\Events\NokiaChecklistStatusChanged;
use Plugins\Nokia\Events\NokiaSiteRejected;
use Plugins\Nokia\Http\Requests\StoreNokiaRejectionRequest;
use Plugins\Nokia\Http\Requests\StoreNokiaSiteRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaDatesRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaInterruptionRequest;
use Plugins\Nokia\Http\Requests\UpdateNokiaSiteRequest;
use Plugins\Nokia\Models\NokiaRom;
use Plugins\Nokia\Models\NokiaSite;
use Plugins\Nokia\Models\NokiaSiteAttachment;
use Plugins\Nokia\Models\NokiaSiteRejection;
use Plugins\Nokia\Models\NokiaTask;

class NokiaSiteController extends Controller
{
    /**
     * List the registered sites.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();

        $query = NokiaSite::query()
            ->with('rom')
            ->withCount([
                'tasks as completed_tasks_count' => fn ($query) => $query->whereNotNull('nokia_site_task.completed_at'),
            ]);

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
            'tasksTotal' => NokiaTask::query()->count(),
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
        $site->load(['attachments', 'rom', 'rejections', 'comments.user']);

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
            'comments' => $site->comments,
        ]);
    }

    /**
     * Store a newly created site.
     */
    public function store(StoreNokiaSiteRequest $request): RedirectResponse
    {
        $site = NokiaSite::query()->create($request->validated());

        $this->syncChecklistStatuses($site);

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
        $original = $site->only(array_keys(config('nokia.checklists', [])));

        $site->update($request->validated());

        $this->syncChecklistStatuses($site, $original);

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
     * Update the status of a checklist item for the given site.
     */
    public function updateChecklist(Request $request, NokiaSite $site, string $field): RedirectResponse
    {
        $checklists = config('nokia.checklists', []);

        abort_unless(array_key_exists($field, $checklists), 404);

        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(array_keys(NokiaSite::CHECKLIST_STATUSES))],
        ]);

        $status = $validated['status'];
        $previousStatus = (string) $site->{$field.'_status'};

        $site->update([
            $field => $status === NokiaSite::STATUS_SENT,
            $field.'_status' => $status,
        ]);

        NokiaChecklistStatusChanged::dispatch($site, $field, $previousStatus, $status);

        return back()->with(
            'status',
            sprintf('%s atualizado para %s.', $checklists[$field], NokiaSite::CHECKLIST_STATUSES[$status]),
        );
    }

    /**
     * Keep the checklist status columns aligned with the legacy booleans.
     *
     * @param  array<string, mixed>  $original
     */
    private function syncChecklistStatuses(NokiaSite $site, array $original = []): void
    {
        $attributes = [];

        foreach (array_keys(config('nokia.checklists', [])) as $field) {
            $changed = ! array_key_exists($field, $original)
                || (bool) $original[$field] !== (bool) $site->{$field};

            if ($changed) {
                $attributes[$field.'_status'] = $site->{$field}
                    ? NokiaSite::STATUS_SENT
                    : NokiaSite::STATUS_PENDING;
            }
        }

        if ($attributes !== []) {
            $site->update($attributes);
        }
    }

    /**
     * Register one rejection for each selected FAM.
     */
    public function storeRejection(StoreNokiaRejectionRequest $request, NokiaSite $site): RedirectResponse
    {
        foreach ($request->validated('fam') as $field) {
            $rejection = $site->rejections()->create([
                'checklist_field' => $field,
                'reason' => $request->validated('rejection_reason'),
                'return_days' => (int) $request->validated('rejection_return_days'),
                'requires_field_return' => $request->boolean('rejection_requires_field_return'),
                'started_at' => now(),
            ]);

            NokiaSiteRejected::dispatch($site, $rejection);
        }

        return back()->with('status', 'Rejeição registrada.');
    }

    /**
     * Resolve the given rejection, keeping it in the history.
     */
    public function resolveRejection(NokiaSite $site, NokiaSiteRejection $rejection): RedirectResponse
    {
        abort_unless($rejection->nokia_site_id === $site->getKey(), 404);

        $rejection->update(['resolved_at' => now()]);

        return back()->with('status', 'Rejeição encerrada.');
    }

    /**
     * Update the milestone dates of the given site.
     */
    public function updateDates(UpdateNokiaDatesRequest $request, NokiaSite $site): RedirectResponse
    {
        $site->update($request->validated());

        return back()->with('status', 'Datas atualizadas com sucesso.');
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
