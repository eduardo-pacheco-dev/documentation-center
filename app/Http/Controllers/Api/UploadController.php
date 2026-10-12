<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'UploadResponse',
    title: 'Upload de arquivo',
    properties: [
        new OA\Property(property: 'url', type: 'string', format: 'uri', example: 'http://127.0.0.1:3333/storage/uploads/foto.jpg'),
        new OA\Property(property: 'path', type: 'string', format: 'uri', example: 'http://127.0.0.1:3333/storage/uploads/foto.jpg'),
    ],
)]
class UploadController extends Controller
{
    /**
     * Store a photo and return its public URL.
     */
    #[OA\Post(
        path: '/api/v1/uploads',
        operationId: 'storeUpload',
        summary: 'Envia uma foto e devolve a URL pública',
        tags: ['Uploads'],
        security: [['bearerAuth' => []]],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\MediaType(
                mediaType: 'multipart/form-data',
                schema: new OA\Schema(
                    required: ['file'],
                    properties: [
                        new OA\Property(property: 'file', type: 'string', format: 'binary'),
                    ],
                ),
            ),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Arquivo armazenado',
                content: new OA\JsonContent(ref: '#/components/schemas/UploadResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 422, description: 'Arquivo inválido'),
        ],
    )]
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'max:20480'],
        ]);

        $path = $request->file('file')->store('uploads', 'public');

        $url = Storage::disk('public')->url($path);

        return response()->json([
            'url' => $url,
            'path' => $url,
        ], 201);
    }
}
