<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\SiteResource;
use App\Models\Erb;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Site',
    title: 'Site',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'PAMBA45'),
        new OA\Property(property: 'assignedTo', type: 'string', nullable: true, example: 'dev@afl.com.br'),
        new OA\Property(property: 'siteId', type: 'string', nullable: true, example: 'SITE-00001'),
        new OA\Property(property: 'endId', type: 'string', nullable: true, example: 'PABLM_0001'),
        new OA\Property(property: 'name', type: 'string', nullable: true, example: 'Sede Administrativa'),
        new OA\Property(property: 'city', type: 'string', nullable: true, example: 'Salvador'),
        new OA\Property(property: 'state', type: 'string', nullable: true, example: 'BA'),
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'Em operação'),
        new OA\Property(property: 'latitude', type: 'number', format: 'float', nullable: true, example: -12.9927),
        new OA\Property(property: 'longitude', type: 'number', format: 'float', nullable: true, example: -38.4866),
        new OA\Property(property: 'nokiaSmpId', type: 'string', nullable: true, example: 'SMP1000'),
        new OA\Property(property: 'startDate', type: 'string', format: 'date', nullable: true, example: '2025-03-01'),
        new OA\Property(property: 'endDate', type: 'string', format: 'date', nullable: true, example: '2025-11-01'),
        new OA\Property(property: 'serviceOrderId', type: 'string', nullable: true, example: 'OS-10000'),
    ],
)]
class SiteController extends Controller
{
    /**
     * List the sites assigned to the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/sites',
        operationId: 'listSites',
        summary: 'Lista os sites do usuário',
        description: 'Paginação no formato json-server (`_page` e `_limit`). O total de registros vem no header `X-Total-Count`.',
        tags: ['Sites'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: '_page', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 1)),
            new OA\Parameter(name: '_limit', in: 'query', required: false, schema: new OA\Schema(type: 'integer', default: 15)),
            new OA\Parameter(name: 'assignedTo', in: 'query', required: false, description: 'Filtra pelos sites atribuídos ao e-mail.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'siteId', in: 'query', required: false, description: 'Filtra pelo identificador do site.', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'endId', in: 'query', required: false, description: 'Filtra pelo código de identificação da ERB.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de sites',
                headers: [
                    new OA\Header(header: 'X-Total-Count', description: 'Total de registros', schema: new OA\Schema(type: 'integer')),
                ],
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Site')),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $limit = max(1, (int) $request->integer('_limit', 15));
        $page = max(1, (int) $request->integer('_page', 1));

        $query = Erb::query()
            ->ownedBy($request->user())
            ->when($request->filled('assignedTo'), fn ($builder) => $builder->where('assigned_to', $request->query('assignedTo')))
            ->when($request->filled('siteId'), fn ($builder) => $builder->where('site_id', $request->query('siteId')))
            ->when($request->filled('endId'), fn ($builder) => $builder->where('end_id', $request->query('endId')));

        $total = (clone $query)->count();

        $erbs = $query->orderBy('code')->forPage($page, $limit)->get();

        return response()
            ->json(SiteResource::collection($erbs)->toArray($request))
            ->header('X-Total-Count', (string) $total);
    }

    /**
     * Show a single site.
     */
    #[OA\Get(
        path: '/api/v1/sites/{id}',
        operationId: 'showSite',
        summary: 'Retorna um site',
        tags: ['Sites'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Código do site.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Site do usuário',
                content: new OA\JsonContent(ref: '#/components/schemas/Site'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 404, description: 'Site não encontrado'),
        ],
    )]
    public function show(Request $request, string $site): JsonResponse
    {
        $erb = Erb::query()
            ->ownedBy($request->user())
            ->where('code', $site)
            ->firstOrFail();

        return response()->json((new SiteResource($erb))->toArray($request));
    }
}
