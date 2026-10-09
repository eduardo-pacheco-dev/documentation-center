<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ErbStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreErbRequest;
use App\Http\Requests\Admin\UpdateErbRequest;
use App\Models\Erb;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ErbController extends Controller
{
    /**
     * List the ERBs the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Erb::class);

        $search = (string) $request->query('search', '');

        $statuses = array_column(ErbStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['code', 'name', 'operator', 'technology', 'city', 'state', 'status', 'updated_at'];

        if (in_array($request->query('sort'), $sortableColumns, true)) {
            $sort = $request->query('sort');
            $direction = in_array($request->query('direction'), ['asc', 'desc'], true)
                ? $request->query('direction')
                : ($sort === 'updated_at' ? 'desc' : 'asc');
        } else {
            $sort = 'updated_at';
            $direction = 'desc';
        }

        $perPage = in_array($request->integer('per_page'), [12, 24, 36], true)
            ? $request->integer('per_page')
            : 12;

        $erbs = Erb::query()
            ->ownedBy($request->user())
            ->withCount(['workOrders', 'projects'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('operator', 'like', "%{$search}%")
                ->orWhere('technology', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.erbs.index', [
            'erbs' => $erbs,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create an ERB.
     */
    public function create(): View
    {
        $this->authorize('create', Erb::class);

        return view('admin.erbs.create');
    }

    /**
     * Create an ERB owned by the authenticated user.
     */
    public function store(StoreErbRequest $request): RedirectResponse
    {
        $erb = $request->user()->erbs()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? ErbStatus::Active->value,
        ]);

        return redirect()
            ->route('admin.erbs.show', $erb)
            ->with('status', 'ERB criada com sucesso.');
    }

    /**
     * Show the ERB details.
     */
    public function show(Erb $erb): View
    {
        $this->authorize('view', $erb);

        $erb->load([
            'workOrders' => fn ($query) => $query->with('client:id,name')->orderByDesc('opened_at')->orderByDesc('id'),
            'projects' => fn ($query) => $query->orderByDesc('updated_at'),
            'documents',
        ]);

        return view('admin.erbs.show', ['erb' => $erb]);
    }

    /**
     * Show the form to edit the ERB details.
     */
    public function edit(Erb $erb): View
    {
        $this->authorize('update', $erb);

        return view('admin.erbs.edit', ['erb' => $erb]);
    }

    /**
     * Update the ERB details.
     */
    public function update(UpdateErbRequest $request, Erb $erb): RedirectResponse
    {
        $erb->update($request->validated());

        return back()->with('status', 'ERB atualizada com sucesso.');
    }

    /**
     * Delete the ERB.
     */
    public function destroy(Erb $erb): RedirectResponse
    {
        $this->authorize('delete', $erb);

        $erb->delete();

        return redirect()
            ->route('admin.erbs.index')
            ->with('status', 'ERB excluída com sucesso.');
    }
}
