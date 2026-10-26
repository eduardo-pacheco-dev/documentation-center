<?php

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * @return array<string, mixed>
 */
function formSettings(array $overrides = []): array
{
    return array_merge([
        'types' => ['Registro Fotográfico', 'Checklist'],
        'statuses' => ['Pendente', 'Preenchido'],
        'default_template_id' => null,
        'default_view' => 'table',
        'per_page' => 12,
    ], $overrides);
}

it('requires an authenticated user to open the forms module settings', function () {
    $this->get('/admin/forms/settings')->assertRedirect('/login');
});

it('shows the forms module settings page with the defaults', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/forms/settings')
        ->assertOk()
        ->assertSee('Configurações de Formulários')
        ->assertSee('Tipos de formulário')
        ->assertSee('Status de formulário')
        ->assertSee('Visualização padrão')
        ->assertSee('Itens por página')
        ->assertSee('Registro Fotográfico')
        ->assertSee('Em preenchimento')
        ->assertSee('Adicionar tipo')
        ->assertSee('Adicionar status')
        ->assertSee('name="types[0]"', false)
        ->assertSee('name="statuses[0]"', false);
});

it('saves the forms module settings', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create();

    $this->actingAs($user)
        ->put('/admin/forms/settings', [
            'types' => ['Vistoria', 'Checklist'],
            'statuses' => ['Pendente', 'Em análise'],
            'default_template_id' => $template->getKey(),
            'default_view' => 'cards',
            'per_page' => 24,
        ])
        ->assertRedirect(route('admin.forms.settings.edit'))
        ->assertSessionHas('status', 'Configurações do módulo atualizadas com sucesso.');

    expect($user->fresh()->form_settings)->toBe([
        'types' => ['Vistoria', 'Checklist'],
        'statuses' => ['Pendente', 'Em análise'],
        'default_template_id' => $template->getKey(),
        'default_view' => 'cards',
        'per_page' => 24,
    ]);
});

it('validates the forms module settings', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put('/admin/forms/settings', [
            'types' => [],
            'statuses' => ['Pendente', 'pendente'],
            'default_template_id' => 999,
            'default_view' => 'grid',
            'per_page' => 10,
        ])
        ->assertSessionHasErrors([
            'types',
            'statuses.1',
            'default_template_id',
            'default_view',
            'per_page',
        ]);

    expect($user->fresh()->form_settings)->toBeNull();
});

it('uses the configured view and page size as list defaults', function () {
    $user = User::factory()->create([
        'form_settings' => formSettings(['default_view' => 'cards', 'per_page' => 24]),
    ]);

    $project = Project::factory()->for($user)->create();

    Form::factory()->count(30)->forProject($project)->create();

    $response = $this->actingAs($user)->get('/admin/forms');

    $response->assertOk();

    expect($response->viewData('view'))->toBe('cards');
    expect($response->viewData('perPage'))->toBe(24);
    expect($response->viewData('forms')->count())->toBe(24);
    expect($response->viewData('forms')->total())->toBe(30);
});

it('lets query parameters override the configured list defaults', function () {
    $user = User::factory()->create([
        'form_settings' => formSettings(['default_view' => 'cards', 'per_page' => 24]),
    ]);

    $response = $this->actingAs($user)->get('/admin/forms?view=compact&per_page=36');

    $response->assertOk();

    expect($response->viewData('view'))->toBe('compact');
    expect($response->viewData('perPage'))->toBe(36);
});

it('uses the configured types and statuses when creating a form', function () {
    $user = User::factory()->create([
        'form_settings' => formSettings(['types' => ['Auditoria'], 'statuses' => ['Aberto']]),
    ]);

    $this->actingAs($user)
        ->get('/admin/forms/create')
        ->assertOk()
        ->assertSee('Auditoria')
        ->assertSee('Aberto')
        ->assertDontSee('Checklist')
        ->assertDontSee('Reprovado');
});

it('keeps legacy types and statuses selectable when they are not configured', function () {
    $user = User::factory()->create([
        'form_settings' => formSettings(['types' => ['Auditoria'], 'statuses' => ['Aberto']]),
    ]);

    $project = Project::factory()->for($user)->create();

    $form = Form::factory()->forProject($project)->create([
        'type' => 'Legado',
        'status' => 'Arquivado',
    ]);

    $this->actingAs($user)
        ->get(route('admin.forms.edit', $form))
        ->assertOk()
        ->assertSee('Auditoria')
        ->assertSee('Legado')
        ->assertSee('Aberto')
        ->assertSee('Arquivado');
});

it('preselects the default template on the create page and in the create modal', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create(['name' => 'Template Padrão']);

    $user->update([
        'form_settings' => formSettings(['default_template_id' => $template->getKey()]),
    ]);

    $createResponse = $this->actingAs($user)->get('/admin/forms/create');

    $createResponse->assertOk()->assertSee('Template Padrão');
    expect($createResponse->viewData('defaultTemplateId'))->toBe($template->getKey());

    expect($createResponse->getContent())->toMatch(
        '/value="'.$template->getKey().'"\s+data-template-type="[^"]*"\s+selected/',
    );

    $indexResponse = $this->actingAs($user)->get('/admin/forms');

    $indexResponse->assertOk();

    expect($indexResponse->getContent())->toMatch(
        '/value="'.$template->getKey().'"\s+data-template-type="[^"]*"\s+selected/',
    );
});
