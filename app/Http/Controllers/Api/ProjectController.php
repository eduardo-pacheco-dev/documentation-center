<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Project',
    title: 'Projeto',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'PRJ-0001'),
        new OA\Property(property: 'siteId', type: 'string', nullable: true, example: 'PAMBA45'),
        new OA\Property(property: 'name', type: 'string', example: 'PAMBA45 - Estrutura'),
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'Concluído'),
    ],
)]
class ProjectController extends Controller
{
    /**
     * List the projects the authenticated user can open.
     */
    #[OA\Get(
        path: '/api/v1/projects',
        operationId: 'listProjects',
        summary: 'Lista os projetos do usuário',
        description: 'Filtra pelos projetos de um site quando `siteId` é informado.',
        tags: ['Projetos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'siteId', in: 'query', required: false, description: 'Código do site.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de projetos',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Project')),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->accessibleBy($request->user())
            ->with('erb:id,code')
            ->when($request->filled('siteId'), fn (Builder $builder) => $builder
                ->whereHas('erb', fn (Builder $erb) => $erb->where('code', $request->query('siteId'))))
            ->orderBy('id')
            ->get();

        return response()->json(ProjectResource::collection($projects)->toArray($request));
    }

    /**
     * Show a single project.
     */
    #[OA\Get(
        path: '/api/v1/projects/{id}',
        operationId: 'showProject',
        summary: 'Retorna um projeto',
        tags: ['Projetos'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Código do projeto.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Projeto do usuário',
                content: new OA\JsonContent(ref: '#/components/schemas/Project'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 404, description: 'Projeto não encontrado'),
        ],
    )]
    public function show(Request $request, string $project): JsonResponse
    {
        $model = Project::query()
            ->accessibleBy($request->user())
            ->with('erb:id,code')
            ->where(fn (Builder $builder) => $builder
                ->where('code', $project)
                ->orWhere('id', ctype_digit($project) ? (int) $project : 0))
            ->firstOrFail();

        return response()->json((new ProjectResource($model))->toArray($request));
    }
}
