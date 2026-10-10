<?php

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
    $headings = ['Código', 'Nome', 'Operadora', 'Tecnologia', 'Status', 'Logradouro', 'Número', 'Complemento', 'Bairro', 'Cidade', 'Estado', 'CEP', 'Latitude', 'Longitude', 'Observações'];

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
        ['TORRE-001', 'ERB Centro', 'Vivo', '5G', 'ativa', 'Av. Paulista', '1000', 'Conjunto 12', 'Bela Vista', 'São Paulo', 'SP', '01310-100', '-23.56143', '-46.65590', 'Acesso pelo estacionamento.'],
        ['torre 002', 'ERB Bela Vista', 'Claro', '4G', 'em manutenção', 'Rua A', 45, '', '', 'São Paulo', 'SP', '', '', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.erbs.index'));
    $response->assertSessionHas('status');

    $erbs = Erb::query()->orderBy('id')->get();

    expect($erbs)->toHaveCount(2)
        ->and($erbs[0]->user_id)->toBe($user->getKey())
        ->and($erbs[0]->code)->toBe('TORRE-001')
        ->and($erbs[0]->name)->toBe('ERB Centro')
        ->and($erbs[0]->operator)->toBe('Vivo')
        ->and($erbs[0]->technology)->toBe('5G')
        ->and($erbs[0]->status->value)->toBe('active')
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
        ->and($erbs[1]->code)->toBe('TORRE 002')
        ->and($erbs[1]->number)->toBe('45')
        ->and($erbs[1]->status->value)->toBe('maintenance');
});

it('creates no erb when a code is duplicated within the file', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['TORRE-001', 'ERB A', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
        ['torre-001', 'ERB B', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when the code already exists for the user', function () {
    $user = User::factory()->create();
    Erb::factory()->create(['user_id' => $user->getKey(), 'code' => 'TORRE-001']);

    $file = erbWorkbook([
        ['torre-001', 'ERB Duplicada', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(1);
});

it('creates no erb when any row is invalid', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['TORRE-001', 'ERB Valida', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
        ['TORRE-002', 'ERB Sem status', 'Vivo', '5G', 'desligada', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('creates no erb when a required field is missing', function () {
    $user = User::factory()->create();

    $file = erbWorkbook([
        ['', 'ERB sem código', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.erbs.index'))
        ->post(route('admin.erbs.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Erb::query()->count())->toBe(0);
});

it('allows the same code for different users', function () {
    $user = User::factory()->create();
    User::factory()->create()->erbs()->create([
        'code' => 'TORRE-001',
        'name' => 'ERB de outro usuário',
    ]);

    $file = erbWorkbook([
        ['TORRE-001', 'ERB Centro', 'Vivo', '5G', 'ativa', '', '', '', '', '', '', '', '', '', ''],
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

it('downloads the import template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.erbs.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('requires authentication to import erbs', function () {
    $this->post(route('admin.erbs.import'))->assertRedirect('/login');
});
