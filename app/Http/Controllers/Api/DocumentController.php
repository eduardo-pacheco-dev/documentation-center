<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDocumentsRequest;
use App\Http\Resources\DocumentResource;
use App\Models\Document;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Attributes as OA;

class DocumentController extends Controller
{
    /**
     * List every file owned by the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/files',
        operationId: 'listFiles',
        summary: 'Lista os arquivos do usuário',
        tags: ['Files'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de arquivos',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Document')),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function index(Request $request): AnonymousResourceCollection
    {
        return DocumentResource::collection(
            Document::query()
                ->whereBelongsTo($request->user())
                ->withCount('shortLinks')
                ->latest('created_at')
                ->paginate(15),
        );
    }

    /**
     * Store files uploaded by the authenticated user.
     */
    #[OA\Post(
        path: '/api/v1/files',
        operationId: 'storeFiles',
        summary: 'Envia arquivos para a biblioteca do usuário',
        tags: ['Files'],
        security: [['bearerAuth' => []]],
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
                description: 'Arquivos enviados',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Document')),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 422, description: 'Arquivos inválidos'),
        ],
    )]
    public function store(StoreDocumentsRequest $request): JsonResponse
    {
        $documents = [];

        foreach ($request->file('documents') as $file) {
            $documents[] = $request->user()->documents()->create([
                'original_name' => $file->getClientOriginalName(),
                'path' => $file->store('documents'),
                'disk' => 'local',
                'mime_type' => $file->getMimeType(),
                'size' => $file->getSize(),
            ]);
        }

        return DocumentResource::collection($documents)
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Remove a file and the underlying storage file.
     */
    #[OA\Delete(
        path: '/api/v1/files/{id}',
        operationId: 'deleteFile',
        summary: 'Exclui um arquivo da biblioteca do usuário',
        tags: ['Files'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Arquivo excluído'),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao arquivo de outro usuário'),
        ],
    )]
    public function destroy(Document $document): Response
    {
        $this->authorize('delete', $document);

        $document->delete();

        return response()->noContent();
    }
}
