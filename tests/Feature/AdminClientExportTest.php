<?php

use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

uses(RefreshDatabase::class);

/**
 * Read the rows of a downloaded spreadsheet.
 *
 * @return array<int, array<int|string, mixed>>
 */
function clientExportWorkbookRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'client-export').'.xlsx';
    file_put_contents($path, $content);

    $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray();

    @unlink($path);

    return $rows;
}

it('downloads the clients as an excel file filtered by search', function () {
    $user = User::factory()->create();
    Client::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Alpha Construções',
        'city' => 'São Paulo',
        'state' => 'SP',
        'status' => 'active',
    ]);
    Client::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Beta Comércio',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.clients.export', ['search' => 'Alpha']));

    $response->assertOk();
    $response->assertDownload('clientes.xlsx');

    [$heading, $row] = clientExportWorkbookRows($response->streamedContent());

    expect($heading)->toBe(['Nome', 'CPF/CNPJ', 'E-mail', 'Telefone', 'Site', 'Rua', 'Número', 'Complemento', 'Bairro', 'Cidade', 'UF', 'CEP', 'Status', 'Observações'])
        ->and($row[0])->toBe('Alpha Construções')
        ->and($row[9])->toBe('São Paulo')
        ->and($row[10])->toBe('SP')
        ->and($row[12])->toBe('Ativo')
        ->and(clientExportWorkbookRows($response->streamedContent()))->toHaveCount(2);
});

it('exports clients owned by the authenticated user only', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Alpha Construções']);
    Client::factory()->create(['user_id' => User::factory()->create()->getKey(), 'name' => 'Beta Comércio']);

    $response = $this->actingAs($user)->get(route('admin.clients.export'));

    $rows = clientExportWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('Alpha Construções')
        ->and(collect($rows)->flatten()->doesntContain('Beta Comércio'))->toBeTrue();
});

it('requires authentication to export clients', function () {
    $this->get(route('admin.clients.export'))->assertRedirect('/login');
});
