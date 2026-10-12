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
        ->assertDontSee('FRM-9999');
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

    $this->get(route('admin.forms.show', $form))->assertOk()->assertSee('Registro Fotográfico Completo');
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
