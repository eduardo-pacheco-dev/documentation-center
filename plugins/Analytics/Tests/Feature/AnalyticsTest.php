<?php

declare(strict_types=1);

use App\Dashboard\WidgetRegistry;
use App\Models\User;
use App\Plugins\PluginActivator;
use App\Plugins\PluginMigrator;
use App\Plugins\PluginRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Plugins\Analytics\Database\Seeders\AnalyticsSeeder;
use Plugins\Analytics\Models\AnalyticsRecord;
use Plugins\Analytics\Providers\AnalyticsServiceProvider;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->statusesPath = storage_path('framework/testing/plugin_statuses_'.Str::random(8).'.json');

    config(['plugins.statuses' => $this->statusesPath]);

    app()->forgetInstance(PluginActivator::class);
    app()->forgetInstance(PluginRepository::class);

    $plugin = app(PluginRepository::class)->findOrFail('analytics');

    $plugin->enable();

    app()->register(AnalyticsServiceProvider::class);
    Route::getRoutes()->refreshNameLookups();

    app(PluginMigrator::class)->migrate($plugin);
});

afterEach(function (): void {
    File::delete($this->statusesPath);
});

function analyticsRecord(array $overrides = []): AnalyticsRecord
{
    return AnalyticsRecord::factory()->create($overrides);
}

test('guests are redirected from the analytics panel', function (): void {
    $this->get('/admin/analytics')->assertRedirect('/login');
});

test('regular users can not access the analytics panel', function (): void {
    $this->actingAs(User::factory()->create())
        ->get('/admin/analytics')
        ->assertForbidden();
});

test('administrators can see the analytics panel with records and KPIs', function (): void {
    analyticsRecord([
        'oc' => 'OC-2026-0001',
        'os' => 'OS-2026-0001',
        'fornecedor' => 'NOKIA',
        'node_name' => 'SN-BLMOK3',
        'status' => 'Aceito pela TIM',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/analytics')
        ->assertOk()
        ->assertSee('Analytics &amp; Field Operations', false)
        ->assertSee('Total de OS')
        ->assertSee('Aceitas pela TIM')
        ->assertSee('Pendentes / Impeditivas')
        ->assertSee('OS-2026-0001')
        ->assertSee('SN-BLMOK3')
        ->assertSee('Aceito pela TIM');
});

test('the analytics panel filters records by supplier, status, node and period', function (): void {
    analyticsRecord([
        'os' => 'OS-FILTER-NOKIA',
        'fornecedor' => 'NOKIA',
        'node_name' => 'SN-NOKIA1',
        'status' => 'Aceito pela TIM',
        'data_criacao' => '2026-10-20',
    ]);

    analyticsRecord([
        'os' => 'OS-FILTER-HUAWEI',
        'fornecedor' => 'HUAWEI',
        'node_name' => 'SN-HUAWEI1',
        'status' => 'Aguardando correção de pendência impeditiva via WFM',
        'data_criacao' => '2026-09-01',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin/analytics?fornecedor=NOKIA&status=Aceito+pela+TIM&node_name=SN-NOKIA1&start_date=2026-10-01&end_date=2026-10-31')
        ->assertOk()
        ->assertSee('OS-FILTER-NOKIA')
        ->assertDontSee('OS-FILTER-HUAWEI');
});

test('administrators can import records from a CSV file', function (): void {
    $csv = implode("\n", [
        'OC,OS,Data Criação,Fornecedor,Fornecedor Fiscalização,Tipo Projeto,Etapa Processo,Tipo Elemento,Endereço Atendimento,Cidade,Estado,Node Name,Localização,Operador,Status',
        'OC-900,OS-900,2026-10-20,NOKIA,VANTAGE,Acesso,Instalação,MISTO,"Rua A, 100",São Paulo,SP,SN-BLMOK3,PABLM_0037,TIM,Aceito pela TIM',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.analytics.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('analytics.csv', $csv),
        ])
        ->assertRedirect(route('admin.analytics.index'))
        ->assertSessionHas('status', 'Importação concluída: 1 criado(s) e 0 atualizado(s).');

    $record = AnalyticsRecord::query()->where('os', 'OS-900')->firstOrFail();

    expect($record->oc)->toBe('OC-900')
        ->and($record->node_name)->toBe('SN-BLMOK3')
        ->and($record->localizacao)->toBe('PABLM_0037')
        ->and($record->status)->toBe('Aceito pela TIM')
        ->and($record->data_criacao->toDateString())->toBe('2026-10-20');
});

test('administrators can import records from an XLSX file', function (): void {
    $spreadsheet = new Spreadsheet;
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->fromArray([
        ['OC', 'OS', 'Data Criação', 'Fornecedor', 'Fornecedor Fiscalização', 'Tipo Projeto', 'Etapa Processo', 'Tipo Elemento', 'Endereço Atendimento', 'Cidade', 'Estado', 'Node Name', 'Localização', 'Operador', 'Status'],
        ['OC-XLSX', 'OS-XLSX', '20/10/2026', 'NOKIA', 'VANTAGE', 'Acesso', 'Instalação', 'MISTO', 'Rua C, 300', 'Campinas', 'SP', 'SN-BLMOK3', 'PABLM_0037', 'TIM', 'Aceito pela TIM'],
    ]);

    $path = tempnam(sys_get_temp_dir(), 'analytics').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    $contents = (string) file_get_contents($path);

    @unlink($path);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.analytics.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('analytics.xlsx', $contents),
        ])
        ->assertRedirect(route('admin.analytics.index'))
        ->assertSessionHas('status', 'Importação concluída: 1 criado(s) e 0 atualizado(s).');

    $record = AnalyticsRecord::query()->where('os', 'OS-XLSX')->firstOrFail();

    expect($record->node_name)->toBe('SN-BLMOK3')
        ->and($record->data_criacao->toDateString())->toBe('2026-10-20');
});

test('importing an existing OS updates the record instead of duplicating it', function (): void {
    analyticsRecord([
        'os' => 'OS-900',
        'fornecedor' => 'NOKIA',
        'node_name' => 'SN-BLMOK3',
        'status' => 'Aguardando correção de pendência impeditiva via WFM',
    ]);

    $csv = implode("\n", [
        'OC,OS,Data Criação,Fornecedor,Fornecedor Fiscalização,Tipo Projeto,Etapa Processo,Tipo Elemento,Endereço Atendimento,Cidade,Estado,Node Name,Localização,Operador,Status',
        'OC-900,OS-900,2026-10-21,NOKIA,,Acesso,Entrega,MISTO,Rua B,São Paulo,SP,SN-BLMOK3,PABLM_0037,TIM,Aceito pela TIM',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->post(route('admin.analytics.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('analytics.csv', $csv),
        ])
        ->assertRedirect(route('admin.analytics.index'))
        ->assertSessionHas('status', 'Importação concluída: 0 criado(s) e 1 atualizado(s).');

    expect(AnalyticsRecord::query()->count())->toBe(1)
        ->and(AnalyticsRecord::query()->firstOrFail()->status)->toBe('Aceito pela TIM');
});

test('invalid import rows are reported without persisting anything', function (): void {
    $csv = implode("\n", [
        'OC,OS,Data Criação,Fornecedor,Node Name,Status',
        'OC-901,OS-901,2026-10-20,NOKIA,,Aceito pela TIM',
    ]);

    $this->actingAs(User::factory()->admin()->create())
        ->from(route('admin.analytics.import'))
        ->post(route('admin.analytics.import.store'), [
            'file' => UploadedFile::fake()->createWithContent('analytics.csv', $csv),
        ])
        ->assertRedirect(route('admin.analytics.import'))
        ->assertSessionHasErrors('file');

    expect(AnalyticsRecord::query()->count())->toBe(0);
});

test('the seeder creates the reference OS', function (): void {
    $this->seed(AnalyticsSeeder::class);

    $this->assertDatabaseHas('analytics_records', [
        'os' => 'OS-2026-0001',
        'node_name' => 'SN-BLMOK3',
        'status' => 'Aceito pela TIM',
    ]);
});

test('the plugin registers its dashboard widgets', function (): void {
    $registry = app(WidgetRegistry::class);

    expect($registry->has('analytics.overview'))->toBeTrue()
        ->and($registry->has('analytics.supplier'))->toBeTrue();

    analyticsRecord();

    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('Status das OS')
        ->assertSee('Fornecedores e etapas');
});

test('the plugin appears in the navigation after being enabled', function (): void {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee(route('admin.analytics.index'));
});
