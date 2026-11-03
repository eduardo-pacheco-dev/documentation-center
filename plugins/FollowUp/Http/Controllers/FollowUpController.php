<?php

namespace Plugins\FollowUp\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Plugins\FollowUp\Enums\FollowUpPriority;
use Plugins\FollowUp\Enums\FollowUpStatus;
use Plugins\FollowUp\Http\Requests\StoreFollowUpRequest;
use Plugins\FollowUp\Http\Requests\UpdateFollowUpRequest;
use Plugins\FollowUp\Models\FollowUp;

class FollowUpController extends Controller
{
    /**
     * List the follow-ups.
     */
    public function index(Request $request): View
    {
        $search = $request->string('search')->trim()->toString();
        $status = $request->string('status')->trim()->toString();
        $priority = $request->string('priority')->trim()->toString();
        $assigned = $request->string('assigned')->trim()->toString();

        $query = FollowUp::query()->with(['assignee', 'creator']);

        if ($search !== '') {
            $query->matching($search);
        }

        if (in_array($status, FollowUpStatus::values(), true)) {
            $query->where('status', $status);
        }

        if (in_array($priority, FollowUpPriority::values(), true)) {
            $query->where('priority', $priority);
        }

        if ($assigned === 'me') {
            $query->where('assigned_to', $request->user()->getKey());
        } elseif (ctype_digit($assigned)) {
            $query->where('assigned_to', (int) $assigned);
        }

        $sort = $request->string('sort')->trim()->toString();
        $sort = in_array($sort, ['title', 'status', 'priority', 'assignee', 'due_date'], true)
            ? $sort
            : 'due_date';

        $direction = $request->string('direction')->trim()->toString();
        $direction = in_array($direction, ['asc', 'desc'], true) ? $direction : 'asc';

        $this->applySort($query, $sort, $direction);

        $view = $request->string('view')->trim()->toString();
        $view = in_array($view, ['table', 'cards', 'compact', 'kanban'], true) ? $view : 'table';

        $followUps = null;
        $kanban = collect();

        if ($view === 'kanban') {
            $kanban = collect(FollowUpStatus::cases())
                ->mapWithKeys(fn (FollowUpStatus $case): array => [
                    $case->value => (clone $query)->where('status', $case->value)->get(),
                ]);
        } else {
            $followUps = $query
                ->paginate((int) config('follow-up.per_page', 15))
                ->withQueryString();
        }

        return view('follow-up::index', [
            'followUps' => $followUps,
            'kanban' => $kanban,
            'view' => $view,
            'search' => $search,
            'status' => $status,
            'priority' => $priority,
            'assigned' => $assigned,
            'statuses' => FollowUpStatus::options(),
            'priorities' => FollowUpPriority::options(),
            'users' => $this->users(),
            'stats' => [
                'open' => FollowUp::query()->open()->count(),
                'overdue' => FollowUp::query()->overdue()->count(),
                'completed' => FollowUp::query()->where('status', FollowUpStatus::Completed->value)->count(),
                'mine' => FollowUp::query()->open()->where('assigned_to', $request->user()->getKey())->count(),
            ],
        ]);
    }

    /**
     * Show the form to create a follow-up.
     */
    public function create(): View
    {
        return view('follow-up::create', [
            'users' => $this->users(),
            'statuses' => FollowUpStatus::options(),
            'priorities' => FollowUpPriority::options(),
        ]);
    }

    /**
     * Store a new follow-up.
     */
    public function store(StoreFollowUpRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->getKey();
        $data['completed_at'] = $data['status'] === FollowUpStatus::Completed->value ? now() : null;

        $followUp = FollowUp::query()->create($data);

        $followUp->updates()->create([
            'user_id' => $request->user()->getKey(),
            'body' => 'Follow-up criado.',
            'status_to' => $data['status'],
        ]);

        return redirect()
            ->route('admin.follow-ups.show', $followUp)
            ->with('status', 'Follow-up criado com sucesso.');
    }

    /**
     * Show the given follow-up.
     */
    public function show(FollowUp $followUp): View
    {
        $followUp->load([
            'assignee',
            'creator',
            'updates.user',
        ]);

        return view('follow-up::show', [
            'followUp' => $followUp,
        ]);
    }

    /**
     * Show the form to edit the given follow-up.
     */
    public function edit(FollowUp $followUp): View
    {
        return view('follow-up::edit', [
            'followUp' => $followUp,
            'users' => $this->users(),
            'statuses' => FollowUpStatus::options(),
            'priorities' => FollowUpPriority::options(),
        ]);
    }

    /**
     * Update the given follow-up.
     */
    public function update(UpdateFollowUpRequest $request, FollowUp $followUp): RedirectResponse
    {
        $previousStatus = $followUp->status;
        $data = $request->validated();
        $status = FollowUpStatus::from($data['status']);

        $data['completed_at'] = $status === FollowUpStatus::Completed
            ? ($followUp->completed_at ?? now())
            : null;

        $followUp->update($data);

        if ($previousStatus !== $status) {
            $followUp->updates()->create([
                'user_id' => $request->user()->getKey(),
                'body' => 'Status alterado de '.$previousStatus->label().' para '.$status->label().'.',
                'status_from' => $previousStatus->value,
                'status_to' => $status->value,
            ]);
        }

        return redirect()
            ->route('admin.follow-ups.show', $followUp)
            ->with('status', 'Follow-up atualizado com sucesso.');
    }

    /**
     * Remove the given follow-up.
     */
    public function destroy(FollowUp $followUp): RedirectResponse
    {
        $followUp->delete();

        return redirect()
            ->route('admin.follow-ups.index')
            ->with('status', 'Follow-up excluído com sucesso.');
    }

    /**
     * Apply the requested column ordering, keeping follow-ups without a due date last.
     */
    private function applySort(Builder $query, string $sort, string $direction): void
    {
        match ($sort) {
            'status' => $query->orderByRaw(
                $this->enumOrder('status', FollowUpStatus::values()).' '.strtoupper($direction),
                FollowUpStatus::values(),
            ),
            'priority' => $query->orderByRaw(
                $this->enumOrder('priority', FollowUpPriority::values()).' '.strtoupper($direction),
                FollowUpPriority::values(),
            ),
            'assignee' => $query->orderBy(
                User::query()->select('name')->whereColumn('users.id', 'follow_ups.assigned_to'),
                $direction,
            ),
            'due_date' => $query
                ->orderByRaw('due_date IS NULL')
                ->orderBy('due_date', $direction),
            default => $query->orderBy($sort, $direction),
        };

        $query->orderBy('id', $direction);
    }

    /**
     * Build a CASE expression that orders an enum column by its declared order.
     *
     * @param  list<string>  $values
     */
    private function enumOrder(string $column, array $values): string
    {
        $cases = collect($values)
            ->map(fn (string $value, int $index): string => 'WHEN ? THEN '.$index)
            ->implode(' ');

        return "CASE {$column} {$cases} ELSE ".count($values).' END';
    }

    /**
     * The users available for assignment.
     *
     * @return Collection<int, User>
     */
    private function users(): Collection
    {
        return User::query()
            ->orderBy('name')
            ->orderBy('surname')
            ->get(['id', 'name', 'surname']);
    }
}
