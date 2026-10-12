<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'FormField',
    title: 'Campo do formulário',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'F1-1'),
        new OA\Property(property: 'type', type: 'string', enum: ['text', 'textarea', 'number', 'date', 'image'], example: 'text'),
        new OA\Property(property: 'label', type: 'string', example: 'Área registrada'),
        new OA\Property(property: 'value', type: 'string', nullable: true, example: 'Galpão A'),
        new OA\Property(property: 'images', type: 'array', nullable: true, items: new OA\Items(type: 'string', format: 'uri')),
        new OA\Property(property: 'status', type: 'string', nullable: true, enum: ['aprovado', 'reprovado', 'pendente', 'enviado', 'nao_aplicavel']),
    ],
)]
#[OA\Schema(
    schema: 'FormStep',
    title: 'Etapa do formulário',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'FRM-0001-S1'),
        new OA\Property(property: 'title', type: 'string', nullable: true, example: 'Identificação'),
        new OA\Property(property: 'fields', type: 'array', items: new OA\Items(ref: '#/components/schemas/FormField')),
    ],
)]
#[OA\Schema(
    schema: 'Form',
    title: 'Formulário',
    properties: [
        new OA\Property(property: 'id', type: 'string', example: 'FRM-0001'),
        new OA\Property(property: 'projectId', type: 'string', nullable: true, example: 'PRJ-0001'),
        new OA\Property(property: 'name', type: 'string', example: 'Registro Fotográfico Completo - Estrutura'),
        new OA\Property(property: 'type', type: 'string', example: 'Registro Fotográfico'),
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'Preenchido'),
        new OA\Property(property: 'createdAt', type: 'string', format: 'date', nullable: true, example: '2026-02-02'),
        new OA\Property(property: 'steps', type: 'array', items: new OA\Items(ref: '#/components/schemas/FormStep')),
    ],
)]
#[OA\Schema(
    schema: 'FormUpdateRequest',
    title: 'Atualização do formulário',
    properties: [
        new OA\Property(property: 'status', type: 'string', nullable: true, example: 'Preenchido'),
        new OA\Property(property: 'steps', type: 'array', items: new OA\Items(ref: '#/components/schemas/FormStep')),
    ],
)]
class FormController extends Controller
{
    /**
     * List the forms of the authenticated user.
     */
    #[OA\Get(
        path: '/api/v1/forms',
        operationId: 'listForms',
        summary: 'Lista os formulários do usuário',
        description: 'O retorno já inclui os `steps`/`fields` completos, usados para cache offline.',
        tags: ['Formulários'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'projectId', in: 'query', required: false, description: 'Código do projeto.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Lista de formulários',
                content: new OA\JsonContent(type: 'array', items: new OA\Items(ref: '#/components/schemas/Form')),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
        ],
    )]
    public function index(Request $request): JsonResponse
    {
        $forms = Form::query()
            ->ownedBy($request->user())
            ->with('project:id,code')
            ->when($request->filled('projectId'), fn (Builder $builder) => $builder
                ->whereHas('project', fn (Builder $project) => $project->where('code', $request->query('projectId'))))
            ->orderBy('id')
            ->get();

        return response()->json(FormResource::collection($forms)->toArray($request));
    }

    /**
     * Show a single form.
     */
    #[OA\Get(
        path: '/api/v1/forms/{id}',
        operationId: 'showForm',
        summary: 'Retorna um formulário',
        tags: ['Formulários'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Código do formulário.', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: 200,
                description: 'Formulário do usuário',
                content: new OA\JsonContent(ref: '#/components/schemas/Form'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 404, description: 'Formulário não encontrado'),
        ],
    )]
    public function show(Request $request, string $form): JsonResponse
    {
        $model = $this->resolveForm($request, $form);

        return response()->json((new FormResource($model))->toArray($request));
    }

    /**
     * Update a form with the answers, photos and synchronization state.
     */
    #[OA\Patch(
        path: '/api/v1/forms/{id}',
        operationId: 'updateForm',
        summary: 'Atualiza um formulário',
        description: 'Usado ao preencher/salvar respostas, capturar fotos e sincronizar. O recurso atualizado é devolvido.',
        tags: ['Formulários'],
        security: [['bearerAuth' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, description: 'Código do formulário.', schema: new OA\Schema(type: 'string')),
        ],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/FormUpdateRequest'),
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Formulário atualizado',
                content: new OA\JsonContent(ref: '#/components/schemas/Form'),
            ),
            new OA\Response(response: 401, description: 'Token ausente ou inválido'),
            new OA\Response(response: 403, description: 'Acesso negado ao formulário de outro usuário'),
            new OA\Response(response: 404, description: 'Formulário não encontrado'),
            new OA\Response(response: 422, description: 'Dados inválidos'),
        ],
    )]
    public function update(UpdateFormRequest $request, string $form): JsonResponse
    {
        $model = $this->resolveFormForUpdate($request, $form);

        $this->authorize('update', $model);

        $model->update($request->validated());

        return response()->json((new FormResource($model->load('project:id,code')))->toArray($request));
    }

    /**
     * Resolve a form by code (or numeric id) without user scope so the policy can decide access.
     */
    private function resolveFormForUpdate(Request $request, string $form): Form
    {
        return Form::query()
            ->where(fn (Builder $builder) => $builder
                ->where('code', $form)
                ->orWhere('id', ctype_digit($form) ? (int) $form : 0))
            ->firstOrFail();
    }

    /**
     * Resolve a form by code (or numeric id) for the authenticated user.
     */
    private function resolveForm(Request $request, string $form): Form
    {
        return Form::query()
            ->ownedBy($request->user())
            ->with('project:id,code')
            ->where(fn (Builder $builder) => $builder
                ->where('code', $form)
                ->orWhere('id', ctype_digit($form) ? (int) $form : 0))
            ->firstOrFail();
    }
}
