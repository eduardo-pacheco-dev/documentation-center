<?php

namespace App\Http\Controllers\Admin;

use App\Enums\RadioLinkStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreRadioLinkRequest;
use App\Http\Requests\Admin\UpdateRadioLinkRequest;
use App\Models\Erb;
use App\Models\RadioLink;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RadioLinkController extends Controller
{
    /**
     * List the radio links the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', RadioLink::class);

        $search = (string) $request->query('search', '');

        $statuses = array_column(RadioLinkStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['code', 'status', 'frequency', 'capacity', 'updated_at'];

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

        $radioLinks = RadioLink::query()
            ->ownedBy($request->user())
            ->with(['erbA:id,code,name', 'erbB:id,code,name'])
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('code', 'like', "%{$search}%")
                ->orWhere('frequency', 'like', "%{$search}%")
                ->orWhere('equipment_a', 'like', "%{$search}%")
                ->orWhere('equipment_b', 'like', "%{$search}%")
                ->orWhereHas('erbA', fn (Builder $endpoint) => $endpoint
                    ->where(fn (Builder $innerEndpoint) => $innerEndpoint
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")))
                ->orWhereHas('erbB', fn (Builder $endpoint) => $endpoint
                    ->where(fn (Builder $innerEndpoint) => $innerEndpoint
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")))))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.radio-links.index', [
            'radioLinks' => $radioLinks,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
            'erbs' => $this->endpoints($request),
        ]);
    }

    /**
     * Create a radio link owned by the authenticated user.
     */
    public function store(StoreRadioLinkRequest $request): RedirectResponse
    {
        $radioLink = $request->user()->radioLinks()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? RadioLinkStatus::Planned->value,
        ]);

        return redirect()
            ->route('admin.radio-links.show', $radioLink)
            ->with('status', 'Radio link criado com sucesso.');
    }

    /**
     * Show the radio link details.
     */
    public function show(RadioLink $radioLink): View
    {
        $this->authorize('view', $radioLink);

        $radioLink->load(['erbA:id,code,name,latitude,longitude', 'erbB:id,code,name,latitude,longitude']);

        return view('admin.radio-links.show', ['radioLink' => $radioLink]);
    }

    /**
     * Show the form to edit the radio link details.
     */
    public function edit(Request $request, RadioLink $radioLink): View
    {
        $this->authorize('update', $radioLink);

        return view('admin.radio-links.edit', [
            'radioLink' => $radioLink,
            'erbs' => $this->endpoints($request),
        ]);
    }

    /**
     * Update the radio link details.
     */
    public function update(UpdateRadioLinkRequest $request, RadioLink $radioLink): RedirectResponse
    {
        $radioLink->update($request->validated());

        return back()->with('status', 'Radio link atualizado com sucesso.');
    }

    /**
     * Delete the radio link.
     */
    public function destroy(RadioLink $radioLink): RedirectResponse
    {
        $this->authorize('delete', $radioLink);

        $radioLink->delete();

        return redirect()
            ->route('admin.radio-links.index')
            ->with('status', 'Radio link excluído com sucesso.');
    }

    /**
     * The ERBs the authenticated user can use as ends.
     *
     * @return Collection<int, Erb>
     */
    private function endpoints(Request $request): Collection
    {
        return $request->user()->erbs()->orderBy('code')->get();
    }
}
