<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EquipmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreEquipmentRequest;
use App\Http\Requests\Admin\UpdateEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EquipmentController extends Controller
{
    /**
     * List the equipments the authenticated user owns.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Equipment::class);

        $search = $request->string('search')->trim()->toString();

        $statuses = array_column(EquipmentStatus::cases(), 'value');

        $status = in_array($request->query('status'), $statuses, true)
            ? $request->query('status')
            : null;

        $sortableColumns = ['name', 'code', 'manufacturer', 'model', 'category', 'status', 'updated_at'];

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

        $equipments = Equipment::query()
            ->ownedBy($request->user())
            ->withCount('documents')
            ->when($search !== '', fn ($query) => $query->matching($search))
            ->when($status !== null, fn ($query) => $query->where('status', $status))
            ->orderBy($sort, $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        $view = in_array($request->query('view'), ['table', 'cards', 'compact'], true)
            ? $request->query('view')
            : 'table';

        return view('admin.equipments.index', [
            'equipments' => $equipments,
            'search' => $search,
            'status' => $status,
            'view' => $view,
            'perPage' => $perPage,
        ]);
    }

    /**
     * Show the form to create an equipment.
     */
    public function create(): View
    {
        $this->authorize('create', Equipment::class);

        return view('admin.equipments.create', [
            'statuses' => EquipmentStatus::cases(),
        ]);
    }

    /**
     * Create an equipment owned by the authenticated user.
     */
    public function store(StoreEquipmentRequest $request): RedirectResponse
    {
        $equipment = $request->user()->equipments()->create([
            ...$request->validated(),
            'status' => $request->validated('status') ?? EquipmentStatus::Active->value,
        ]);

        return redirect()
            ->route('admin.equipments.show', $equipment)
            ->with('status', 'Equipamento cadastrado com sucesso.');
    }

    /**
     * Show the equipment details.
     */
    public function show(Equipment $equipment): View
    {
        $this->authorize('view', $equipment);

        return $this->detail($equipment);
    }

    /**
     * Show the equipment details with the edit modal open.
     */
    public function edit(Equipment $equipment): View
    {
        $this->authorize('update', $equipment);

        return $this->detail($equipment, openEditModal: true);
    }

    /**
     * Render the equipment detail page.
     */
    private function detail(Equipment $equipment, bool $openEditModal = false): View
    {
        $equipment->load('documents');

        return view('admin.equipments.show', [
            'equipment' => $equipment,
            'statuses' => EquipmentStatus::cases(),
            'openEditModal' => $openEditModal,
        ]);
    }

    /**
     * Update the equipment details.
     */
    public function update(UpdateEquipmentRequest $request, Equipment $equipment): RedirectResponse
    {
        $equipment->update($request->validated());

        return back()->with('status', 'Equipamento atualizado com sucesso.');
    }

    /**
     * Delete the equipment.
     */
    public function destroy(Equipment $equipment): RedirectResponse
    {
        $this->authorize('delete', $equipment);

        $equipment->delete();

        return redirect()
            ->route('admin.equipments.index')
            ->with('status', 'Equipamento excluído com sucesso.');
    }
}
