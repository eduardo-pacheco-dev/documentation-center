<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ResourceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ResourceRequest;
use App\Models\Project;
use App\Models\Resource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ProjectResourceController extends Controller
{
    /**
     * Create a resource of the project.
     */
    public function store(ResourceRequest $request, Project $project): RedirectResponse
    {
        $project->resources()->create($this->attributes($request));

        return back()->with('status', 'Recurso criado com sucesso.');
    }

    /**
     * Update a resource of the project.
     */
    public function update(ResourceRequest $request, Project $project, Resource $resource): RedirectResponse
    {
        abort_unless($resource->project_id === $project->getKey(), 404);

        $resource->update($this->attributes($request, $resource));

        return back()->with('status', 'Recurso atualizado com sucesso.');
    }

    /**
     * Delete a resource of the project.
     */
    public function destroy(Request $request, Project $project, Resource $resource): RedirectResponse
    {
        $this->authorize('plan', $project);

        abort_unless($resource->project_id === $project->getKey(), 404);

        $resource->delete();

        return back()->with('status', 'Recurso excluído com sucesso.');
    }

    /**
     * The validated resource fields.
     */
    private function attributes(ResourceRequest $request, ?Resource $resource = null): array
    {
        $validated = $request->validated();

        return [
            'name' => $validated['name'],
            'type' => $validated['type'] ?? $resource?->type?->value ?? ResourceType::Work->value,
            'code' => $validated['code'] ?? $resource?->code,
            'max_units' => $validated['max_units'] ?? $resource?->max_units ?? 100,
            'cost_per_hour' => $validated['cost_per_hour'] ?? $resource?->cost_per_hour ?? 0,
            'cost_per_unit' => $validated['cost_per_unit'] ?? $resource?->cost_per_unit ?? 0,
            'is_active' => $validated['is_active'] ?? $resource?->is_active ?? true,
        ];
    }
}
