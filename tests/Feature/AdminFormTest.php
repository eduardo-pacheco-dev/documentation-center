<?php

use App\Models\Form;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the forms area', function () {
    $this->get('/admin/forms')->assertRedirect('/login');
});

it('lists only the forms the user owns', function () {
    $owner = User::factory()->create();

    Form::factory()->create(['user_id' => $owner->getKey(), 'code' => 'FRM-0001', 'name' => 'Registro Fotográfico']);
    Form::factory()->create(['code' => 'FRM-9999', 'name' => 'Formulário Alheio']);

    $this->actingAs($owner)
        ->get('/admin/forms')
        ->assertOk()
        ->assertSee('FRM-0001')
        ->assertDontSee('FRM-9999')
        ->assertSee('overflow-x-auto', false)
        ->assertSee('min-w-[1100px]', false);
});

it('filters the forms list by search term', function () {
    $owner = User::factory()->create();

    Form::factory()->create(['user_id' => $owner->getKey(), 'code' => 'FRM-0001', 'name' => 'Registro Fotográfico']);
    Form::factory()->create(['user_id' => $owner->getKey(), 'code' => 'FRM-0002', 'name' => 'Checklist de Segurança']);

    $this->actingAs($owner)
        ->get('/admin/forms?search=Fotográfico')
        ->assertOk()
        ->assertSee('FRM-0001')
        ->assertDontSee('FRM-0002');
});

it('renders the form list in the alternate view modes', function () {
    $owner = User::factory()->create();

    Form::factory()->create(['user_id' => $owner->getKey(), 'code' => 'FRM-0001', 'name' => 'Registro Fotográfico']);

    $this->actingAs($owner)->get('/admin/forms?view=cards')->assertOk()->assertSee('FRM-0001');
    $this->actingAs($owner)->get('/admin/forms?view=compact')->assertOk()->assertSee('FRM-0001');
});

it('opens the create modal from the forms list', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/forms')
        ->assertOk()
        ->assertSee('data-modal-open="form-create-modal"', false)
        ->assertSee('id="form-create-modal"', false)
        ->assertSee('action="'.route('admin.forms.store').'"', false);
});

it('keeps the create modal open when the form validation fails', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->from('/admin/forms')
        ->followingRedirects()
        ->post('/admin/forms', [
            'modal' => 'create',
            'name' => '',
            'type' => 'Checklist',
            'status' => 'Pendente',
        ]);

    $response->assertOk();
    $response->assertSee('id="form-create-modal"', false);
    $response->assertSee('data-modal-error', false);
});

it('creates a form owned by the authenticated user with its steps', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $steps = [
        [
            'id' => 'S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'value' => '', 'status' => 'pendente'],
            ],
        ],
    ];

    $this->actingAs($user)->get('/admin/forms/create')->assertOk();

    $this->actingAs($user)
        ->post('/admin/forms', [
            'project_id' => $project->getKey(),
            'name' => 'Registro Fotográfico Completo - Estrutura',
            'type' => 'Registro Fotográfico',
            'status' => 'Pendente',
            'steps_json' => json_encode($steps),
        ])
        ->assertRedirect();

    $form = Form::query()->firstOrFail();

    expect($form->user_id)->toBe($user->getKey());
    expect($form->project_id)->toBe($project->getKey());
    expect($form->code)->not->toBeEmpty();
    expect($form->steps)->toBe($steps);

    $this->get(route('admin.forms.edit', $form))->assertOk()->assertSee('Registro Fotográfico Completo');
});

it('creates a form without a project and defaults the status to pending', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/forms', [
            'name' => 'Registro sem projeto',
        ])
        ->assertRedirect();

    $form = Form::query()->firstOrFail();

    expect($form->project_id)->toBeNull();
    expect($form->status)->toBe('Pendente');
    expect($form->type)->toBe('Registro Fotográfico');
});

it('selects the pending status by default even when it is not first', function () {
    $user = User::factory()->create([
        'form_settings' => ['statuses' => ['Em análise', 'Pendente', 'Concluído']],
    ]);

    $this->actingAs($user)
        ->get('/admin/forms/create')
        ->assertOk()
        ->assertSee('<option value="Pendente" selected>', false);
});

it('clears the project when updating a form without one', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->put(route('admin.forms.update', $form), [
            'project_id' => '',
            'name' => 'Formulário sem projeto',
            'type' => 'Checklist',
            'status' => 'Pendente',
        ])
        ->assertRedirect();

    expect($form->fresh()->project_id)->toBeNull();
});

it('updates a form and its steps', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();
    $form = Form::factory()->forProject($project)->create(['status' => 'Pendente']);

    $this->actingAs($user)
        ->put(route('admin.forms.update', $form), [
            'project_id' => $project->getKey(),
            'name' => 'Checklist de Segurança',
            'type' => 'Checklist',
            'status' => 'Preenchido',
            'steps_json' => json_encode([]),
        ])
        ->assertRedirect();

    expect($form->fresh()->status)->toBe('Preenchido');
    expect($form->fresh()->name)->toBe('Checklist de Segurança');
    expect($form->fresh()->steps)->toBe([]);
});

it('renders the structure builder on the form detail page', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.forms.edit', $form))
        ->assertOk()
        ->assertSee('Estrutura do formulário')
        ->assertSee('Salvar estrutura')
        ->assertSee('action="'.route('admin.forms.steps.update', $form).'"', false);
});

it('saves the form structure from the builder', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $steps = [
        [
            'id' => 'S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área', 'required' => true],
                ['id' => 'F1-2', 'type' => 'radio', 'label' => 'Conformidade', 'required' => false, 'options' => ['Conforme', 'Não conforme']],
                ['id' => 'F1-3', 'type' => 'scale', 'label' => 'Avaliação', 'required' => false, 'scaleMin' => 1, 'scaleMax' => 5, 'scaleMinLabel' => 'Ruim', 'scaleMaxLabel' => 'Ótimo'],
            ],
        ],
    ];

    $this->actingAs($user)
        ->patch(route('admin.forms.steps.update', $form), ['steps_json' => json_encode($steps)])
        ->assertRedirect(route('admin.forms.edit', $form));

    expect($form->fresh()->steps)->toBe($steps);
});

it('rejects an unsupported field type from the builder', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey(), 'steps' => []]);

    $steps = [
        ['id' => 'S1', 'title' => 'Seção', 'fields' => [
            ['id' => 'F1-1', 'type' => 'signature', 'label' => 'Assinatura'],
        ]],
    ];

    $this->actingAs($user)
        ->patch(route('admin.forms.steps.update', $form), ['steps_json' => json_encode($steps)])
        ->assertSessionHasErrors('steps.0.fields.0.type');

    expect($form->fresh()->steps)->toBe([]);
});

it('keeps field answers when saving the structure', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $steps = [
        ['id' => 'S1', 'title' => 'Seção', 'fields' => [
            ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => false, 'value' => 'Galpão A', 'status' => 'aprovado'],
        ]],
    ];

    $this->actingAs($user)
        ->patch(route('admin.forms.steps.update', $form), ['steps_json' => json_encode($steps)])
        ->assertRedirect();

    $saved = $form->fresh()->steps;

    expect($saved[0]['fields'][0]['value'])->toBe('Galpão A');
    expect($saved[0]['fields'][0]['status'])->toBe('aprovado');
});

it('forbids updating the structure of a form the user does not own', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create();

    $this->actingAs($user)
        ->patch(route('admin.forms.steps.update', $form), ['steps_json' => json_encode([])])
        ->assertForbidden();
});

it('shows the application answers in read only mode', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create([
        'user_id' => $user->getKey(),
        'steps' => [[
            'id' => 'S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'value' => 'Galpão A', 'status' => 'aprovado'],
            ],
        ]],
    ]);

    $this->actingAs($user)
        ->get(route('admin.forms.show', $form))
        ->assertOk()
        ->assertSee('Respostas do aplicativo')
        ->assertSee('Galpão A');
});

it('shows the files uploaded to a form field', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create([
        'user_id' => $user->getKey(),
        'steps' => [[
            'id' => 'S1',
            'title' => 'Anexos',
            'fields' => [
                [
                    'id' => 'F1-1',
                    'type' => 'file',
                    'label' => 'Laudo técnico',
                    'files' => ['https://example.test/storage/forms/1/laudo.pdf'],
                    'fileNames' => ['laudo-tecnico.pdf'],
                    'status' => 'enviado',
                ],
            ],
        ]],
    ]);

    $this->actingAs($user)
        ->get(route('admin.forms.show', $form))
        ->assertOk()
        ->assertSee('Arquivo')
        ->assertSee('laudo-tecnico.pdf')
        ->assertSee('https://example.test/storage/forms/1/laudo.pdf', false);
});

it('forbids viewing a form the user does not own', function () {
    $form = Form::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get(route('admin.forms.show', $form))
        ->assertForbidden();
});

it('rejects creating a form for a project the user does not own', function () {
    $user = User::factory()->create();
    $foreign = Project::factory()->create();

    $this->actingAs($user)
        ->post('/admin/forms', [
            'project_id' => $foreign->getKey(),
            'name' => 'Registro Fotográfico',
            'type' => 'Registro Fotográfico',
            'status' => 'Pendente',
        ])
        ->assertSessionHasErrors('project_id');
});

it('deletes a form owned by the user', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete(route('admin.forms.destroy', $form))
        ->assertRedirect(route('admin.forms.index'));

    $this->assertSoftDeleted('forms', ['id' => $form->getKey()]);
});
