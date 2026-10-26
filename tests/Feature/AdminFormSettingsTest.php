<?php

use App\Models\Form;
use App\Models\FormTemplate;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

/**
 * Build an uploaded configuration workbook from the given rows.
 *
 * @param  array<int, array<int, mixed>>  $rows  The data rows, excluding the heading.
 */
function formSettingsWorkbook(array $rows): UploadedFile
{
    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([['Configuração', 'Valor'], ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'form-settings-import').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'configuracoes-formularios.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

/**
 * Read the rows of a downloaded configuration workbook.
 *
 * @return array<int, array<int|string, mixed>>
 */
function formSettingsWorkbookRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'form-settings-export').'.xlsx';
    file_put_contents($path, $content);

    $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray();

    @unlink($path);

    return $rows;
}

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
        ->assertSee('Exportar template')
        ->assertSee('Importar template')
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

it('exports the forms module settings as an excel template', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create(['name' => 'Template de Vistoria']);

    $user->update([
        'form_settings' => formSettings([
            'types' => ['Auditoria', 'Checklist'],
            'statuses' => ['Aberto', 'Concluído'],
            'default_template_id' => $template->getKey(),
            'default_view' => 'cards',
            'per_page' => 24,
        ]),
    ]);

    $response = $this->actingAs($user)->get(route('admin.forms.settings.export'));

    $response->assertOk();
    $response->assertDownload('configuracoes-formularios.xlsx');

    $rows = formSettingsWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(8)
        ->and($rows[0])->toBe(['Configuração', 'Valor'])
        ->and($rows[1])->toBe(['Tipo de formulário', 'Auditoria'])
        ->and($rows[2])->toBe(['Tipo de formulário', 'Checklist'])
        ->and($rows[3])->toBe(['Status de formulário', 'Aberto'])
        ->and($rows[4])->toBe(['Status de formulário', 'Concluído'])
        ->and($rows[5])->toBe(['Visualização padrão', 'Cards'])
        ->and((int) $rows[6][1])->toBe(24)
        ->and($rows[7])->toBe(['Template padrão', 'Template de Vistoria']);
});

it('imports the forms module settings from an excel template', function () {
    $user = User::factory()->create();
    $template = FormTemplate::factory()->create(['name' => 'Template de Vistoria']);

    $file = formSettingsWorkbook([
        ['Tipo de formulário', 'Auditoria'],
        ['Tipo de formulário', 'Checklist'],
        ['Status de formulário', 'Aberto'],
        ['Status de formulário', 'Concluído'],
        ['Visualização padrão', 'Cards'],
        ['Itens por página', 24],
        ['Template padrão', 'Template de Vistoria'],
    ]);

    $this->actingAs($user)
        ->post(route('admin.forms.settings.import'), ['file' => $file])
        ->assertRedirect(route('admin.forms.settings.edit'))
        ->assertSessionHas('status', 'Template de configurações importado com sucesso.');

    expect($user->fresh()->form_settings)->toBe([
        'types' => ['Auditoria', 'Checklist'],
        'statuses' => ['Aberto', 'Concluído'],
        'default_template_id' => $template->getKey(),
        'default_view' => 'cards',
        'per_page' => 24,
    ]);
});

it('imports a configuration template without a default template', function () {
    $user = User::factory()->create();

    $file = formSettingsWorkbook([
        ['Tipo de formulário', 'Vistoria'],
        ['Status de formulário', 'Pendente'],
        ['Visualização padrão', 'Lista'],
        ['Itens por página', '36 itens'],
        ['Template padrão', ''],
    ]);

    $this->actingAs($user)
        ->post(route('admin.forms.settings.import'), ['file' => $file])
        ->assertRedirect(route('admin.forms.settings.edit'));

    expect($user->fresh()->form_settings)->toBe([
        'types' => ['Vistoria'],
        'statuses' => ['Pendente'],
        'default_template_id' => null,
        'default_view' => 'compact',
        'per_page' => 36,
    ]);
});

it('validates the imported configuration template', function () {
    $user = User::factory()->create();

    $file = formSettingsWorkbook([
        ['Tipo de formulário', 'Checklist'],
        ['Tipo de formulário', 'checklist'],
        ['Status de formulário', 'Aberto'],
        ['Status de formulário', 'Aberto'],
        ['Visualização padrão', 'Grade'],
        ['Itens por página', 10],
        ['Template padrão', 'Template inexistente'],
    ]);

    $response = $this->actingAs($user)
        ->post(route('admin.forms.settings.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.forms.settings.edit'));
    $response->assertSessionHasErrors('import');

    $errors = collect(session('errors')->get('import'))->implode("\n");

    expect($errors)->toContain('o tipo de formulário "checklist" está repetido')
        ->and($errors)->toContain('o status de formulário "Aberto" está repetido')
        ->and($errors)->toContain('A visualização "Grade" é inválida')
        ->and($errors)->toContain('A quantidade de itens por página deve ser 12, 24 ou 36')
        ->and($errors)->toContain('O template padrão "Template inexistente" não foi encontrado')
        ->and($user->fresh()->form_settings)->toBeNull();
});

it('requires an excel file to import the configuration template', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.forms.settings.import'), [
            'file' => UploadedFile::fake()->create('configuracoes.txt', 10, 'text/plain'),
        ])
        ->assertSessionHasErrors('file');

    expect($user->fresh()->form_settings)->toBeNull();
});

it('requires authentication to export and import the configuration template', function () {
    $this->get(route('admin.forms.settings.export'))->assertRedirect('/login');
    $this->post(route('admin.forms.settings.import'))->assertRedirect('/login');
});
