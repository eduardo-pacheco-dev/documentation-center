<?php

namespace Plugins\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;
use Plugins\Projects\Http\Resources\ProjectResource;
use Plugins\Projects\Models\Project;

#[OA\Schema(
    schema: 'Project',
    title: 'Projeto',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'PRJ-0001'),
        new OA\Property(property: 'name', type: 'string', example: 'Implantação da rede'),
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'Concluído'),
    ],
)]
class ProjectApiController extends Controller
{
    /**
     * List the projects the authenticated user can open.
     */
    #[OA\Get(
        path: '/api/v1/projects',
        operationId: 'listProjects',
        summary: 'Lista os projetos do usuário',
        tags: ['Projetos'],
        security: [['bearerAuth' => []]],
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
            ->where(fn (Builder $builder) => $builder
                ->where('code', $project)
                ->orWhere('id', ctype_digit($project) ? (int) $project : 0))
            ->firstOrFail();

        return response()->json((new ProjectResource($model))->toArray($request));
    }
}
