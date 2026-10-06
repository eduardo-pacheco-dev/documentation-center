<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use OpenApi\Attributes as OA;

#[OA\Info(
    title: 'Documentation Center API',
    version: '1.0.0',
    description: 'API de autenticação utilizada pelo aplicativo mobile.',
)]
#[OA\Server(url: '/')]
#[OA\SecurityScheme(
    securityScheme: 'bearerAuth',
    type: 'http',
    scheme: 'bearer',
    description: 'Token de acesso devolvido nos endpoints de signup e login.',
)]
#[OA\Schema(
    schema: 'SignupRequest',
    title: 'Dados de cadastro',
    required: ['name', 'email', 'password', 'password_confirmation'],
    properties: [
        new OA\Property(property: 'name', type: 'string', maxLength: 255, example: 'Maria Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', maxLength: 255, example: 'maria@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', minLength: 8, example: 'senha-segura'),
        new OA\Property(property: 'password_confirmation', type: 'string', format: 'password', example: 'senha-segura'),
    ],
)]
#[OA\Schema(
    schema: 'LoginRequest',
    title: 'Credenciais de acesso',
    required: ['email', 'password'],
    properties: [
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
        new OA\Property(property: 'password', type: 'string', format: 'password', example: 'senha-segura'),
        new OA\Property(property: 'remember', type: 'boolean', default: false, example: false),
        new OA\Property(property: 'device_name', type: 'string', maxLength: 255, description: 'Identificação do dispositivo que recebe o token.', example: 'Galaxy S24'),
    ],
)]
#[OA\Schema(
    schema: 'User',
    title: 'Usuário autenticado',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'name', type: 'string', example: 'Maria Silva'),
        new OA\Property(property: 'email', type: 'string', format: 'email', example: 'maria@example.com'),
        new OA\Property(property: 'is_admin', type: 'boolean', example: false),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', nullable: true, example: '2026-10-06T12:00:00Z'),
    ],
)]
#[OA\Schema(
    schema: 'AuthResponse',
    title: 'Sessão autenticada',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
                new OA\Property(property: 'token', type: 'string', description: 'Token Bearer usado nos demais endpoints.', example: '1|AbCdEfGhIjKlMnOpQrStUvWxYz'),
            ],
        ),
    ],
)]
#[OA\Schema(
    schema: 'UserResponse',
    title: 'Usuário autenticado',
    properties: [
        new OA\Property(
            property: 'data',
            type: 'object',
            properties: [
                new OA\Property(property: 'user', ref: '#/components/schemas/User'),
            ],
        ),
    ],
)]
#[OA\Schema(
    schema: 'MessageResponse',
    title: 'Mensagem simples',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Sessão encerrada com sucesso.'),
    ],
)]
#[OA\Schema(
    schema: 'ValidationErrorResponse',
    title: 'Dados inválidos',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'Os dados enviados são inválidos.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            example: ['email' => ['O campo email é obrigatório.']],
        ),
    ],
)]
class AuthController extends Controller
{
    /**
     * Create a new account and issue an access token.
     */
    #[OA\Post(
        path: '/api/v1/auth/signup',
        operationId: 'signup',
        summary: 'Cadastra um novo usuário',
        description: 'Cria a conta, autentica o dispositivo e devolve o token de acesso.',
        tags: ['Autenticação'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/SignupRequest'),
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Conta criada',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'),
            ),
            new OA\Response(
                response: 429,
                description: 'Limite de tentativas excedido',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'),
            ),
        ],
    )]
    public function signup(RegisterRequest $request): JsonResponse
    {
        $user = User::create($request->validated());

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ], 201);
    }

    /**
     * Authenticate an existing account and issue an access token.
     */
    #[OA\Post(
        path: '/api/v1/auth/login',
        operationId: 'login',
        summary: 'Autentica um usuário',
        description: 'Valida as credenciais e devolve o token de acesso do dispositivo.',
        tags: ['Autenticação'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/LoginRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Credenciais válidas',
                content: new OA\JsonContent(ref: '#/components/schemas/AuthResponse'),
            ),
            new OA\Response(
                response: 401,
                description: 'Credenciais inválidas',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'),
            ),
            new OA\Response(
                response: 422,
                description: 'Dados inválidos',
                content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse'),
            ),
            new OA\Response(
                response: 429,
                description: 'Limite de tentativas excedido',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'),
            ),
        ],
    )]
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->safe()->only(['email', 'password']);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            abort(401, 'As credenciais informadas não correspondem a um usuário.');
        }

        $token = $user->createToken($request->input('device_name', 'mobile'))->plainTextToken;

        return response()->json([
            'data' => [
                'user' => new UserResource($user),
                'token' => $token,
            ],
        ]);
    }

    /**
     * Revoke the token of the current device.
     */
    #[OA\Post(
        path: '/api/v1/auth/logout',
        operationId: 'logout',
        summary: 'Encerra a sessão do dispositivo',
        description: 'Revoga o token enviado no header Authorization.',
        tags: ['Autenticação'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Sessão encerrada',
                content: new OA\JsonContent(ref: '#/components/schemas/MessageResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()->currentAccessToken();

        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }

        return response()->json([
            'message' => 'Sessão encerrada com sucesso.',
        ]);
    }

    /**
     * Return the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/auth/me',
        operationId: 'me',
        summary: 'Retorna o usuário autenticado',
        tags: ['Autenticação'],
        security: [['bearerAuth' => []]],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Usuário autenticado',
                content: new OA\JsonContent(ref: '#/components/schemas/UserResponse'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'data' => [
                'user' => new UserResource($request->user()),
            ],
        ]);
    }
}
