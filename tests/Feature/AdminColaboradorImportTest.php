<?php

use App\Models\Colaborador;
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
function colaboradorWorkbook(array $rows): UploadedFile
{
    $headings = ['Nome', 'Cargo', 'CPF', 'Telefone', 'E-mail', 'Status', 'Observações'];

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$headings, ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'colaborador-import').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'colaboradores.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

it('imports colaboradores from an excel spreadsheet', function () {
    $user = User::factory()->create();

    $file = colaboradorWorkbook([
        ['João da Silva', 'Técnico N2', '123.456.789-00', '(11) 99999-9999', 'joao@empresa.com.br', 'ativo', 'Atua na região central.'],
        ['maria souza', 'Coordenadora', '98765432100', '', '', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.colaboradores.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.colaboradores.index'));
    $response->assertSessionHas('status');

    $colaboradores = Colaborador::query()->orderBy('id')->get();

    expect($colaboradores)->toHaveCount(2)
        ->and($colaboradores[0]->user_id)->toBe($user->getKey())
        ->and($colaboradores[0]->name)->toBe('João da Silva')
        ->and($colaboradores[0]->role)->toBe('Técnico N2')
        ->and($colaboradores[0]->document)->toBe('123.456.789-00')
        ->and($colaboradores[0]->phone)->toBe('(11) 99999-9999')
        ->and($colaboradores[0]->email)->toBe('joao@empresa.com.br')
        ->and($colaboradores[0]->status->value)->toBe('active')
        ->and($colaboradores[0]->notes)->toBe('Atua na região central.')
        ->and($colaboradores[1]->name)->toBe('maria souza')
        ->and($colaboradores[1]->document)->toBe('987.654.321-00')
        ->and($colaboradores[1]->status->value)->toBe('active');
});

it('creates no colaborador when a document is duplicated within the file', function () {
    $user = User::factory()->create();

    $file = colaboradorWorkbook([
        ['Ana Souza', 'Supervisora', '123.456.789-00', '', '', 'ativo', ''],
        ['Bruno Lima', 'Técnico N2', '123.456.789-00', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.colaboradores.index'))
        ->post(route('admin.colaboradores.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Colaborador::query()->count())->toBe(0);
});

it('creates no colaborador when the document already exists for the user', function () {
    $user = User::factory()->create();
    Colaborador::factory()->create(['user_id' => $user->getKey(), 'document' => '123.456.789-00']);

    $file = colaboradorWorkbook([
        ['Bruno Lima', 'Técnico N2', '123.456.789-00', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)->post(route('admin.colaboradores.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Colaborador::query()->count())->toBe(1);
});

it('creates no colaborador when any row is invalid', function () {
    $user = User::factory()->create();

    $file = colaboradorWorkbook([
        ['Ana Souza', 'Supervisora', '123.456.789-00', '', '', 'ativo', ''],
        ['Bruno Lima', 'Técnico N2', '', '', '', 'demitido', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.colaboradores.index'))
        ->post(route('admin.colaboradores.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Colaborador::query()->count())->toBe(0);
});

it('creates no colaborador when a required field is missing', function () {
    $user = User::factory()->create();

    $file = colaboradorWorkbook([
        ['', 'Técnico N2', '', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.colaboradores.index'))
        ->post(route('admin.colaboradores.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Colaborador::query()->count())->toBe(0);
});

it('allows the same document for different users', function () {
    $user = User::factory()->create();
    User::factory()->create()->colaboradores()->create([
        'name' => 'Colaborador de outro usuário',
        'document' => '123.456.789-00',
    ]);

    $file = colaboradorWorkbook([
        ['Ana Souza', 'Supervisora', '123.456.789-00', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)->post(route('admin.colaboradores.import'), ['file' => $file])
        ->assertRedirect(route('admin.colaboradores.index'));

    expect(Colaborador::query()->where('user_id', $user->getKey())->count())->toBe(1);
});

it('rejects a file that is not an excel spreadsheet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.colaboradores.import'), [
            'file' => UploadedFile::fake()->create('colaboradores.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');

    expect(Colaborador::query()->count())->toBe(0);
});

it('downloads the import template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.colaboradores.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('requires authentication to import colaboradores', function () {
    $this->post(route('admin.colaboradores.import'))->assertRedirect('/login');
});
