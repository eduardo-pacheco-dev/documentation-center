<?php

use App\Models\Client;
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
function clientWorkbook(array $rows): UploadedFile
{
    $headings = [
        'Nome',
        'CPF/CNPJ',
        'E-mail',
        'Telefone',
        'Site',
        'Rua',
        'Número',
        'Complemento',
        'Bairro',
        'Cidade',
        'UF',
        'CEP',
        'Status',
        'Observações',
    ];

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$headings, ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'client-import').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'clientes.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

it('imports clients from an excel spreadsheet', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Construtora Alfa Ltda.', '12.345.678/0001-90', 'contato@alfa.test', '(11) 99999-0000', 'https://alfa.test', 'Avenida Paulista', '1000', 'Sala 12', 'Bela Vista', 'São Paulo', 'sp', '01310-100', 'ativo', 'Cliente estratégico.'],
        ['beta comércio', '', '', '', '', '', '', '', '', '', '', '', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.clients.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.clients.index'));
    $response->assertSessionHas('status');

    $clients = Client::query()->orderBy('id')->get();

    expect($clients)->toHaveCount(2)
        ->and($clients[0]->user_id)->toBe($user->getKey())
        ->and($clients[0]->name)->toBe('Construtora Alfa Ltda.')
        ->and($clients[0]->document)->toBe('12.345.678/0001-90')
        ->and($clients[0]->email)->toBe('contato@alfa.test')
        ->and($clients[0]->phone)->toBe('(11) 99999-0000')
        ->and($clients[0]->website)->toBe('https://alfa.test')
        ->and($clients[0]->street)->toBe('Avenida Paulista')
        ->and($clients[0]->number)->toBe('1000')
        ->and($clients[0]->complement)->toBe('Sala 12')
        ->and($clients[0]->neighborhood)->toBe('Bela Vista')
        ->and($clients[0]->city)->toBe('São Paulo')
        ->and($clients[0]->state)->toBe('SP')
        ->and($clients[0]->zip)->toBe('01310-100')
        ->and($clients[0]->status->value)->toBe('active')
        ->and($clients[0]->notes)->toBe('Cliente estratégico.')
        ->and($clients[1]->name)->toBe('beta comércio')
        ->and($clients[1]->status->value)->toBe('active');
});

it('adds a scheme to a website without one', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Gamma Ltda.', '', '', '', 'gamma.test', '', '', '', '', '', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)->post(route('admin.clients.import'), ['file' => $file])
        ->assertRedirect(route('admin.clients.index'));

    expect(Client::query()->firstOrFail()->website)->toBe('https://gamma.test');
});

it('creates no client when any row is invalid', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Alpha Ltda.', '', '', '', '', '', '', '', '', '', 'SP', '', 'ativo', ''],
        ['Beta Ltda.', '', '', '', '', '', '', '', '', '', '', '', 'desconhecido', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.clients.index'))
        ->post(route('admin.clients.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Client::query()->count())->toBe(0);
});

it('creates no client when a required field is missing', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['', '', '', '', '', '', '', '', '', '', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.clients.index'))
        ->post(route('admin.clients.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Client::query()->count())->toBe(0);
});

it('creates no client when the email is invalid', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Alpha Ltda.', '', 'não-é-email', '', '', '', '', '', '', '', '', '', 'ativo', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.clients.index'))
        ->post(route('admin.clients.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(Client::query()->count())->toBe(0);
});

it('rejects a file that is not an excel spreadsheet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.clients.import'), [
            'file' => UploadedFile::fake()->create('clientes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');

    expect(Client::query()->count())->toBe(0);
});

it('downloads the import template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.clients.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('imports clients via ajax and returns a redirect url', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Alpha Ltda.', '', '', '', '', '', '', '', '', '', 'SP', '', 'ativo', ''],
    ]);

    $this->actingAs($user)
        ->postJson(route('admin.clients.import'), ['file' => $file])
        ->assertOk()
        ->assertJsonPath('redirect', route('admin.clients.index'))
        ->assertSessionHas('status');

    expect(Client::query()->count())->toBe(1);
});

it('returns json validation errors when an ajax import is invalid', function () {
    $user = User::factory()->create();

    $file = clientWorkbook([
        ['Beta Ltda.', '', '', '', '', '', '', '', '', '', '', '', 'desconhecido', ''],
    ]);

    $this->actingAs($user)
        ->postJson(route('admin.clients.import'), ['file' => $file])
        ->assertStatus(422)
        ->assertJsonValidationErrors('import');

    expect(Client::query()->count())->toBe(0);
});

it('requires authentication to import clients', function () {
    $this->post(route('admin.clients.import'))->assertRedirect('/login');
});
