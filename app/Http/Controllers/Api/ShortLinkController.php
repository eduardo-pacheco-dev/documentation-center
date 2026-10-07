<?php

namespace App\Http\Controllers\Api;

use App\Enums\ShortLinkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentsRequest;
use App\Http\Requests\StoreShortLinkRequest;
use App\Http\Requests\UpdateShortLinkRequest;
use App\Http\Resources\ShortLinkResource;
use App\Models\ShortLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ShortLink',
    title: 'Link encurtado',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'code', type: 'string', example: 'aB3xK9qZ'),
        new OA\Property(property: 'url', type: 'string', format: 'uri', example: 'https://documentation-center.test/s/aB3xK9qZ'),
        new OA\Property(property: 'type', type: 'string', enum: ['upload', 'download'], example: 'upload'),
        new OA\Property(property: 'title', type: 'string', example: 'Entrega de contratos'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Envie os documentos assinados.'),
        new OA\Property(property: 'is_active', type: 'boolean', example: true),
        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true, example: '2026-12-31T23:59:59'),
        new OA\Property(property: 'max_uses', type: 'integer', nullable: true, example: 100),
        new OA\Property(property: 'used_count', type: 'integer', example: 3),
        new OA\Property(property: 'has_password', type: 'boolean', example: false),
        new OA\Property(property: 'documents', type: 'array', items: new OA\Items(ref: '#/components/schemas/Document'), nullable: true),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'ShortLinkRequest',
    title: 'Dados do link',
    required: ['title', 'type'],
    properties: [
        new OA\Property(property: 'title', type: 'string', maxLength: 255, example: 'Entrega de contratos'),
        new OA\Property(property: 'type', type: 'string', enum: ['upload', 'download'], example: 'upload'),
        new OA\Property(property: 'description', type: 'string', nullable: true, example: 'Envie os documentos assinados.'),
        new OA\Property(property: 'expires_at', type: 'string', format: 'date-time', nullable: true, example: '2026-12-31'),
        new OA\Property(property: 'max_uses', type: 'integer', minimum: 1, nullable: true, example: 100),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 6, nullable: true),
        new OA\Property(property: 'is_active', type: 'boolean', default: true, example: true),
    ],
)]
#[OA\Schema(
    schema: 'Document',
    title: 'Documento anexado',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 12),
        new OA\Property(property: 'original_name', type: 'string', example: 'contrato-assinado.pdf'),
        new OA\Property(property: 'mime_type', type: 'string', nullable: true, example: 'application/pdf'),
        new OA\Property(property: 'size', type: 'integer', nullable: true, example: 245760),
        new OA\Property(property: 'url', type: 'string', format: 'uri', example: 'https://documentation-center.test/s/aB3xK9qZ/documents/12/download'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true),
    ],
)]
#[OA\Schema(
    schema: 'DocumentsRequest',
    title: 'Documentos a enviar',
    required: ['documents'],
    properties: [
        new OA\Property(
            property: 'documents',
            type: 'array',
            description: 'Arquivos de documento (máx. 20 MB cada, até 10 por envio).',
            minItems: 1,
            maxItems: 10,
            items: new OA\Items(type: 'string', format: 'binary'),
        ),
    ],
)]
#[OA\Schema(
    schema: 'ShortLinkResponse',
    title: 'Link criado ou atualizado',
    properties: [
        new OA\Property(property: 'data', ref: '#/components/schemas/ShortLink'),
    ],
)]
#[OA\Schema(
    schema: 'ShortLinkListResponse',
    title: 'Lista de links',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/ShortLink'),
        ),
    ],
)]
class ShortLinkController extends Controller
{
    /**
     * List every short link owned by the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/links',
        operationId: 'listShortLinks',
        summary: 'Lista os links encurtados do usuário',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Links do usuário',
                content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkListResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return ShortLinkResource::collection(
            ShortLink::query()
                ->whereBelongsTo($request->user())
                ->withCount('documents')
                ->latest('created_at')
                ->paginate(15),
        );
    }

    /**
     * Create a new short link.
     */
    #[OA\Post(
        path: '/api/v1/links',
        operationId: 'storeShortLink',
        summary: 'Cria um link encurtado',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Link criado',
                content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ],
    )]
    public function store(StoreShortLinkRequest $request): JsonResponse
    {
        $attributes = $request->safe()->except(['password', 'document_ids']);

        $attributes['user_id'] = $request->user()->getKey();
        $attributes['code'] = ShortLink::createCode();

        if ($request->filled('password')) {
            $attributes['password'] = Hash::make($request->string('password'));
        }

        $shortLink = ShortLink::create($attributes);

        if ($shortLink->type === ShortLinkType::Download && $request->filled('document_ids')) {
            $shortLink->documents()->attach($request->input('document_ids'));
        }

        return (new ShortLinkResource($shortLink->load('documents')))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Show a single short link.
     */
    #[OA\Get(
        path: '/api/v1/links/{id}',
        operationId: 'showShortLink',
        summary: 'Retorna um link encurtado',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Link do usuário',
                content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao link de outro usuário'),
        ],
    )]
    public function show(ShortLink $shortLink): ShortLinkResource
    {
        $this->authorize('view', $shortLink);

        return new ShortLinkResource($shortLink->load('documents'));
    }

    /**
     * Update an existing short link.
     */
    #[OA\Put(
        path: '/api/v1/links/{id}',
        operationId: 'updateShortLink',
        summary: 'Atualiza um link encurtado',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Link atualizado',
                content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao link de outro usuário'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ],
    )]
    public function update(UpdateShortLinkRequest $request, ShortLink $shortLink): ShortLinkResource
    {
        $attributes = $request->safe()->except(['password', 'is_active']);

        if ($request->has('is_active')) {
            $attributes['is_active'] = $request->boolean('is_active');
        }

        if ($request->has('password')) {
            $attributes['password'] = $request->filled('password')
                ? Hash::make($request->string('password'))
                : null;
        }

        $shortLink->update($attributes);

        return new ShortLinkResource($shortLink->load('documents'));
    }

    /**
     * Delete a short link.
     */
    #[OA\Delete(
        path: '/api/v1/links/{id}',
        operationId: 'destroyShortLink',
        summary: 'Exclui um link encurtado',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Link excluído'),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao link de outro usuário'),
        ],
    )]
    public function destroy(ShortLink $shortLink): Response
    {
        $this->authorize('delete', $shortLink);

        $shortLink->delete();

        return response()->noContent();
    }

    /**
     * Attach uploaded documents to a link.
     */
    #[OA\Post(
        path: '/api/v1/links/{id}/documents',
        operationId: 'storeShortLinkDocuments',
        summary: 'Anexa documentos a um link',
        tags: ['Links'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(ref: '#/components/schemas/DocumentsRequest'),
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Documentos anexados',
                content: new OA\JsonContent(ref: '#/components/schemas/ShortLinkResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao link de outro usuário'),
            new OA\Response(response: 422, description: 'Arquivos inválidos'),
        ],
    )]
    public function storeDocuments(StoreDocumentsRequest $request, ShortLink $shortLink): JsonResponse
    {
        $this->authorize('update', $shortLink);

        foreach ($request->file('documents') as $file) {
            $document = $request->user()->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
                'uploaded_via_short_link_id' => $shortLink->type === ShortLinkType::Upload ? $shortLink->getKey() : null,
            ]);

            $shortLink->documents()->attach($document);
        }

        return (new ShortLinkResource($shortLink->load('documents')))
            ->response()
            ->setStatusCode(201);
    }
}
