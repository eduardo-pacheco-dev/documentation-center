<?php

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the templates area', function () {
    $this->get('/admin/form-templates')->assertRedirect('/login');
});

it('lists templates shared by every user', function () {
    $owner = User::factory()->create();

    FormTemplate::factory()->create(['name' => 'Registro fotográfico padrão']);
    FormTemplate::factory()->create(['name' => 'Checklist de segurança']);

    $this->actingAs($owner)
        ->get(route('admin.form-templates.index'))
        ->assertOk()
        ->assertSee('Registro fotográfico padrão')
        ->assertSee('Checklist de segurança');
});

it('filters the templates list by search term', function () {
    $user = User::factory()->create();

    FormTemplate::factory()->create(['name' => 'Registro fotográfico padrão', 'type' => 'Registro Fotográfico']);
    FormTemplate::factory()->create(['name' => 'Checklist de segurança', 'type' => 'Checklist']);

    $this->actingAs($user)
        ->get(route('admin.form-templates.index', ['search' => 'Checklist']))
        ->assertOk()
        ->assertSee('Checklist de segurança')
        ->assertDontSee('Registro fotográfico padrão');
});

it('filters the templates list by type', function () {
    $user = User::factory()->create();

    FormTemplate::factory()->create(['name' => 'Registro fotográfico padrão', 'type' => 'Registro Fotográfico']);
    FormTemplate::factory()->create(['name' => 'Checklist de segurança', 'type' => 'Checklist']);

    $this->actingAs($user)
        ->get(route('admin.form-templates.index', ['type' => 'Checklist']))
        ->assertOk()
        ->assertSee('Checklist de segurança')
        ->assertDontSee('Registro fotográfico padrão');
});

it('ignores an unsupported type filter', function () {
    $user = User::factory()->create();

    FormTemplate::factory()->create(['name' => 'Checklist de segurança', 'type' => 'Checklist']);

    $this->actingAs($user)
        ->get(route('admin.form-templates.index', ['type' => 'Inexistente']))
        ->assertOk()
        ->assertSee('Checklist de segurança');
});

it('switches the templates list view mode', function () {
    $user = User::factory()->create();
    FormTemplate::factory()->create(['name' => 'Checklist de segurança', 'type' => 'Checklist']);

    $this->actingAs($user)
        ->get(route('admin.form-templates.index', ['view' => 'cards']))
        ->assertOk()
        ->assertSee('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3');

    $this->actingAs($user)
        ->get(route('admin.form-templates.index', ['view' => 'compact']))
        ->assertOk()
        ->assertSee('divide-y divide-gray-100 overflow-hidden rounded-2xl');
});

it('summarizes the template library statistics', function () {
    $user = User::factory()->create();

    FormTemplate::factory()->ownedBy($user)->create([
        'name' => 'Checklist de segurança',
        'type' => 'Checklist',
        'steps' => [
            ['id' => 'S1', 'title' => 'Seção', 'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Um'],
                ['id' => 'F1-2', 'type' => 'text', 'label' => 'Dois'],
            ]],
        ],
    ]);

    FormTemplate::factory()->ownedBy($user)->create([
        'name' => 'Vistoria elétrica',
        'type' => 'Vistoria',
        'steps' => [
            ['id' => 'S1', 'title' => 'Seção', 'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Um'],
            ]],
        ],
    ]);

    $this->actingAs($user)
        ->get(route('admin.form-templates.index'))
        ->assertOk()
        ->assertViewHas('stats', fn (array $stats) => $stats === [
            'total' => 2,
            'types' => 2,
            'questions' => 3,
            'contributors' => 1,
        ]);
});

it('creates a template from a form the user owns', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $steps = [
        ['id' => 'S1', 'title' => 'Identificação', 'fields' => [
            ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true],
        ]],
    ];

    $form = Form::factory()->forProject($project)->create([
        'user_id' => $user->getKey(),
        'name' => 'Registro Fotográfico - Estrutura',
        'type' => 'Registro Fotográfico',
        'steps' => $steps,
    ]);

    $this->actingAs($user)
        ->post(route('admin.form-templates.store'), [
            'name' => 'Template Estrutura',
            'form_id' => $form->getKey(),
        ])
        ->assertRedirect(route('admin.form-templates.index'));

    $template = FormTemplate::query()->firstOrFail();

    expect($template->user_id)->toBe($user->getKey());
    expect($template->name)->toBe('Template Estrutura');
    expect($template->type)->toBe('Registro Fotográfico');
    expect($template->steps)->toBe($steps);
});

it('rejects creating a template from a form the user does not own', function () {
    $user = User::factory()->create();
    $form = Form::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.form-templates.store'), [
            'name' => 'Template Alheio',
            'form_id' => $form->getKey(),
        ])
        ->assertSessionHasErrors('form_id');

    expect(FormTemplate::query()->count())->toBe(0);
});

it('renders the template edit page with details and structure tabs', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->get(route('admin.form-templates.edit', $template))
        ->assertOk()
        ->assertSee('data-tab="details"', false)
        ->assertSee('data-tab="structure"', false)
        ->assertSee(route('admin.form-templates.update', $template), false)
        ->assertSee(route('admin.form-templates.steps.update', $template), false);
});

it('updates a template the user owns', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->ownedBy($user)->create(['name' => 'Antigo']);

    $this->actingAs($user)
        ->put(route('admin.form-templates.update', $template), [
            'name' => 'Atualizado',
            'type' => 'Checklist',
        ])
        ->assertRedirect();

    $template->refresh();

    expect($template->name)->toBe('Atualizado');
    expect($template->type)->toBe('Checklist');
});

it('updates the template structure from the builder', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->ownedBy($user)->create(['steps' => []]);

    $steps = [
        ['id' => 'S1', 'title' => 'Nova seção', 'fields' => [
            ['id' => 'F1-1', 'type' => 'radio', 'label' => 'Conforme?', 'required' => true, 'options' => ['Sim', 'Não']],
        ]],
    ];

    $this->actingAs($user)
        ->patch(route('admin.form-templates.steps.update', $template), [
            'steps_json' => json_encode($steps),
        ])
        ->assertRedirect(route('admin.form-templates.edit', $template));

    expect($template->fresh()->steps)->toBe($steps);
});

it('rejects an unsupported field type when updating a template', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->ownedBy($user)->create(['steps' => []]);

    $steps = [
        ['id' => 'S1', 'title' => 'Seção', 'fields' => [
            ['id' => 'F1-1', 'type' => 'signature', 'label' => 'Assinatura'],
        ]],
    ];

    $this->actingAs($user)
        ->patch(route('admin.form-templates.steps.update', $template), [
            'steps_json' => json_encode($steps),
        ])
        ->assertSessionHasErrors('steps.0.fields.0.type');

    expect($template->fresh()->steps)->toBe([]);
});

it('forbids updating a template owned by another user', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create();

    $this->actingAs($user)
        ->get(route('admin.form-templates.edit', $template))
        ->assertForbidden();

    $this->actingAs($user)
        ->put(route('admin.form-templates.update', $template), [
            'name' => 'Invasão',
            'steps_json' => json_encode([]),
        ])
        ->assertForbidden();
});

it('lets an admin update a template owned by another user', function () {
    $admin = User::factory()->admin()->create();
    $template = FormTemplate::factory()->create(['name' => 'Antigo']);

    $this->actingAs($admin)
        ->put(route('admin.form-templates.update', $template), [
            'name' => 'Atualizado pelo admin',
            'steps_json' => json_encode([]),
        ])
        ->assertRedirect();

    expect($template->fresh()->name)->toBe('Atualizado pelo admin');
});

it('deletes a template the user owns', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->ownedBy($user)->create();

    $this->actingAs($user)
        ->delete(route('admin.form-templates.destroy', $template))
        ->assertRedirect(route('admin.form-templates.index'));

    $this->assertDatabaseMissing('form_templates', ['id' => $template->getKey()]);
});

it('forbids deleting a template owned by another user', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create();

    $this->actingAs($user)
        ->delete(route('admin.form-templates.destroy', $template))
        ->assertForbidden();

    $this->assertDatabaseHas('form_templates', ['id' => $template->getKey()]);
});

it('shows the template selector when creating a form', function () {
    $user = User::factory()->create();
    FormTemplate::factory()->create(['name' => 'Registro fotográfico padrão', 'type' => 'Registro Fotográfico']);

    $this->actingAs($user)
        ->get(route('admin.forms.create'))
        ->assertOk()
        ->assertSee('name="template_id"', false)
        ->assertSee('Registro fotográfico padrão')
        ->assertSee('data-template-type="Registro Fotográfico"', false);
});

it('applies a template structure when creating a form', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $steps = [
        ['id' => 'S1', 'title' => 'Do template', 'fields' => [
            ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'required' => true],
        ]],
    ];

    $template = FormTemplate::factory()->create([
        'name' => 'Template base',
        'type' => 'Checklist',
        'steps' => $steps,
    ]);

    $this->actingAs($user)
        ->post(route('admin.forms.store'), [
            'project_id' => $project->getKey(),
            'name' => 'Novo a partir do template',
            'type' => 'Checklist',
            'status' => 'Pendente',
            'template_id' => $template->getKey(),
        ])
        ->assertRedirect();

    $form = Form::query()->firstOrFail();

    expect($form->steps)->toBe($steps);
});

it('rejects creating a form from a template that does not exist', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create();

    $this->actingAs($user)
        ->post(route('admin.forms.store'), [
            'project_id' => $project->getKey(),
            'name' => 'Novo',
            'type' => 'Checklist',
            'status' => 'Pendente',
            'template_id' => 99999,
        ])
        ->assertSessionHasErrors('template_id');
});
