<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ColaboradorStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreColaboradorRequest;
use App\Http\Requests\Admin\UpdateColaboradorRequest;
use App\Models\Colaborador;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ColaboradorController extends Controller
{
    /**
     * List the colaboradores the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Colaborador::class);

        $search = (string) $request->query('search', '');

        $statuses = array_column(ColaboradorStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['name', 'role', 'email', 'status', 'updated_at'];

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

        $colaboradores = Colaborador::query()
            ->ownedBy($request->user())
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('role', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('document', 'like', "%{$search}%")))
            ->when($status !== null, fn (Builder $query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.colaboradores.index', [
            'colaboradores' => $colaboradores,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create a colaborador.
     */
    public function create(): View
    {
        $this->authorize('create', Colaborador::class);

        return view('admin.colaboradores.create');
    }

    /**
     * Create a colaborador owned by the authenticated user.
     */
    public function store(StoreColaboradorRequest $request): RedirectResponse
    {
        $colaborador = $request->user()->colaboradores()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? ColaboradorStatus::Active->value,
        ]);

        return redirect()
            ->route('admin.colaboradores.show', $colaborador)
            ->with('status', 'Colaborador criado com sucesso.');
    }

    /**
     * Show the colaborador details.
     */
    public function show(Colaborador $colaborador): View
    {
        $this->authorize('view', $colaborador);

        return view('admin.colaboradores.show', ['colaborador' => $colaborador]);
    }

    /**
     * Show the form to edit the colaborador details.
     */
    public function edit(Colaborador $colaborador): View
    {
        $this->authorize('update', $colaborador);

        return view('admin.colaboradores.edit', ['colaborador' => $colaborador]);
    }

    /**
     * Update the colaborador details.
     */
    public function update(UpdateColaboradorRequest $request, Colaborador $colaborador): RedirectResponse
    {
        $colaborador->update($request->validated());

        return back()->with('status', 'Colaborador atualizado com sucesso.');
    }

    /**
     * Delete the colaborador.
     */
    public function destroy(Colaborador $colaborador): RedirectResponse
    {
        $this->authorize('delete', $colaborador);

        $colaborador->delete();

        return redirect()
            ->route('admin.colaboradores.index')
            ->with('status', 'Colaborador excluído com sucesso.');
    }
}
