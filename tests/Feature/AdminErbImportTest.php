<?php

use App\Enums\ErbStatus;
use App\Exports\ErbsTemplateExport;
use App\Models\Erb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

/**
 * Build an uploaded Excel workbook from the given data rows.
 *
 * @param  array<int, array<int, mixed>>  $rows  The data rows, excluding the heading.
 */
function erbWorkbook(array $rows): UploadedFile
{
    $headings = ['Site ID', 'End ID', 'Operadora', 'Tecnologia', 'Nokia SMP ID', 'Status', 'Início', 'Término', 'Logradouro', 'Número', 'Complemento', 'Bairro', 'Cidade', 'Estado', 'CEP', 'Latitude', 'Longitude', 'Observações'];

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$headings, ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'erb-import').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'erbs.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

it('imports erbs from an excel spreadsheet', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Vivo', '5G', 'SMP-1024', 'ativa', '10/01/2026', '31/12/2026', 'Av. Paulista', '1000', 'Conjunto 12', 'Bela Vista', 'São Paulo', 'SP', '01310-100', '-23.56143', '-46.65590', 'Acesso pelo estacionamento.'],
        ['SITE-002', 'PABXA_0002', 'Claro', '4G', '', 'em manutenção', '', '', 'Rua A', 45, '', '', 'São Paulo', 'SP', '', '', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.erbs.index'));
    $response->assertSessionHas('status');

    $erbs = Erb::query()->orderBy('id')->get();

    expect($erbs)->toHaveCount(2)
        ->and($erbs[0]->user_id)->toBe($user->getKey())
        ->and($erbs[0]->code)->toBe('ERB-0001')
        ->and($erbs[0]->name)->toBe('SITE-001')
        ->and($erbs[0]->site_id)->toBe('SITE-001')
        ->and($erbs[0]->end_id)->toBe('PABLM_0001')
        ->and($erbs[0]->operator)->toBe('Vivo')
        ->and($erbs[0]->technology)->toBe('5G')
        ->and($erbs[0]->nokia_smp_id)->toBe('SMP-1024')
        ->and($erbs[0]->status->value)->toBe('active')
        ->and($erbs[0]->start_date->toDateString())->toBe('2026-01-10')
        ->and($erbs[0]->end_date->toDateString())->toBe('2026-12-31')
        ->and($erbs[0]->street)->toBe('Av. Paulista')
        ->and($erbs[0]->number)->toBe('1000')
        ->and($erbs[0]->complement)->toBe('Conjunto 12')
        ->and($erbs[0]->neighborhood)->toBe('Bela Vista')
        ->and($erbs[0]->city)->toBe('São Paulo')
        ->and($erbs[0]->state)->toBe('SP')
        ->and($erbs[0]->zip)->toBe('01310-100')
        ->and((float) $erbs[0]->latitude)->toBe(-23.56143)
        ->and((float) $erbs[0]->longitude)->toBe(-46.6559)
        ->and($erbs[0]->notes)->toBe('Acesso pelo estacionamento.')
        ->and($erbs[1]->code)->toBe('ERB-0002')
        ->and($erbs[1]->name)->toBe('SITE-002')
        ->and($erbs[1]->end_id)->toBe('PABXA_0002')
        ->and($erbs[1]->nokia_smp_id)->toBeNull()
        ->and($erbs[1]->start_date)->toBeNull()
        ->and($erbs[1]->end_date)->toBeNull()
        ->and($erbs[1]->number)->toBe('45')
        ->and($erbs[1]->status->value)->toBe('maintenance');
});

it('derives the name from the end id when the site id is missing', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    $erb = Erb::query()->sole();

    expect($erb->site_id)->toBeNull()
        ->and($erb->end_id)->toBe('PABLM_0001')
        ->and($erb->name)->toBe('PABLM_0001');
});

it('accepts operator and technology regardless of case', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'vivo', '4g/5g', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    $erb = Erb::query()->sole();

    expect($erb->operator)->toBe('Vivo')
        ->and($erb->technology)->toBe('4G/5G');
});

it('keeps a single erb when a site id is duplicated within the file', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-DUP', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['site-dup', 'PABLM_0002', 'Claro', '4G', '', 'em manutenção', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    $erb = Erb::query()->sole();

    expect($erb->site_id)->toBe('site-dup')
        ->and($erb->end_id)->toBe('PABLM_0002')
        ->and($erb->operator)->toBe('Claro')
        ->and($erb->technology)->toBe('4G')
        ->and($erb->status->value)->toBe('maintenance');
});

it('updates the existing erb when the site id is already in use', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create([
        'user_id' => $user->getKey(),
        'site_id' => 'SITE-001',
        'end_id' => 'OLD-ID',
        'operator' => 'Oi',
        'technology' => '3G',
        'status' => ErbStatus::Inactive,
    ]);

    $file = erbWorkbook([
        ['site-001', 'PABLM_0009', 'Vivo', '5G', 'SMP-2048', 'ativa', '01/02/2026', '', 'Rua Nova', '10', '', '', 'Campinas', 'sp', '', '', '', 'Atualizada.'],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    $erb->refresh();

    expect(Erb::query()->count())->toBe(1)
        ->and($erb->code)->not->toBeNull()
        ->and($erb->end_id)->toBe('PABLM_0009')
        ->and($erb->operator)->toBe('Vivo')
        ->and($erb->technology)->toBe('5G')
        ->and($erb->nokia_smp_id)->toBe('SMP-2048')
        ->and($erb->status->value)->toBe('active')
        ->and($erb->start_date->toDateString())->toBe('2026-02-01')
        ->and($erb->end_date)->toBeNull()
        ->and($erb->street)->toBe('Rua Nova')
        ->and($erb->city)->toBe('Campinas')
        ->and($erb->state)->toBe('SP')
        ->and($erb->notes)->toBe('Atualizada.');
});

it('creates new erbs and updates existing ones in the same file', function () {
    $user = User::factory()->create();
    Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-001']);

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['SITE-002', 'PABLM_0002', 'Claro', '4G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    expect(Erb::query()->where('user_id', $user->getKey())->count())->toBe(2)
        ->and(Erb::query()->where('site_id', 'SITE-002')->exists())->toBeTrue();
});

it('creates no erb when any row is invalid', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
        ['SITE-002', 'PABLM_0002', 'Vivo', '5G', '', 'desligada', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when the operator or technology is invalid', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Nextel', '6G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when the start or end date is invalid', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '32/13/2026', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when the end date is before the start date', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['SITE-001', 'PABLM_0001', 'Vivo', '5G', '', 'ativa', '10/01/2026', '01/01/2026', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when both the site id and end id are missing', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['', '', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('allows the same site id for different users', function () {
    $user = User::factory()->create();
    User::factory()->create()->erbs()->create([
        'site_id' => 'SITE-001',
        'end_id' => 'PABLM_0001',
    ]);

    $file = erbWorkbook([
        ['SITE-001', 'PABXA_0002', 'Vivo', '5G', '', 'ativa', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertRedirect(route('admin.erbs.index'));

    expect(Erb::query()->where('user_id', $user->getKey())->count())->toBe(1);
});

it('rejects a file that is not an excel spreadsheet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.erbs.import'), [
            'file' => UploadedFile::fake()->create('erbs.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');

    expect(Erb::query()->count())->toBe(0);
});

it('exposes the current column headings in the import template', function () {
    expect((new ErbsTemplateExport)->headings())->toBe([
        'Site ID',
        'End ID',
        'Operadora',
        'Tecnologia',
        'Nokia SMP ID',
        'Status',
        'Início',
        'Término',
        'Logradouro',
        'Número',
        'Complemento',
        'Bairro',
        'Cidade',
        'Estado',
        'CEP',
        'Latitude',
        'Longitude',
        'Observações',
    ]);
});

it('downloads the import template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.erbs.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('requires authentication to import erbs', function () {
    $this->post(route('admin.erbs.import'))->assertRedirect('/login');
});
