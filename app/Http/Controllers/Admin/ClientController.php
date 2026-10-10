<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ClientsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ClientController extends Controller
{
    /**
     * List the clients the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Client::class);

        $search = (string) $request->query('search', '');

        $sortableColumns = ['name', 'document', 'city', 'state', 'status', 'created_at', 'updated_at'];

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

        $clients = Client::query()
            ->ownedBy($request->user())
            ->when($search !== '', fn (Builder $query) => $query->where(fn (Builder $inner) => $inner
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('document', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.clients.index', [
            'clients' => $clients,
            'search' => $search,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create a client.
     */
    public function create(): View
    {
        $this->authorize('create', Client::class);

        return view('admin.clients.create');
    }

    /**
     * Download the clients, honoring the current list filters, as a spreadsheet.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $this->authorize('viewAny', Client::class);

        $search = (string) $request->query('search', '');

        return Excel::download(
            new ClientsExport($request->user(), $search),
            'clientes.xlsx',
        );
    }

    /**
     * Create a client owned by the authenticated user.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $client = $request->user()->clients()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? 'active',
        ]);

        return redirect()
            ->route('admin.clients.show', $client)
            ->with('status', 'Cliente criado com sucesso.');
    }

    /**
     * Show the client details.
     */
    public function show(Client $client): View
    {
        $this->authorize('view', $client);

        $client->load(['contacts', 'documents']);

        return view('admin.clients.show', ['client' => $client]);
    }

    /**
     * Show the form to edit the client details.
     */
    public function edit(Client $client): View
    {
        $this->authorize('update', $client);

        return view('admin.clients.edit', ['client' => $client]);
    }

    /**
     * Update the client details.
     */
    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $client->update($request->validated());

        return back()->with('status', 'Cliente atualizado com sucesso.');
    }

    /**
     * Delete the client.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $this->authorize('delete', $client);

        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('status', 'Cliente excluído com sucesso.');
    }
}
