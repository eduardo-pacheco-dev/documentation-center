<?php

use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

uses(RefreshDatabase::class);

/**
 * Read the rows of a downloaded spreadsheet.
 *
 * @return array<int, array<int|string, mixed>>
 */
function colaboradorWorkbookRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'colaborador-export').'.xlsx';
    file_put_contents($path, $content);

    $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray();

    @unlink($path);

    return $rows;
}

it('downloads the colaboradores as an excel file filtered by search and status', function () {
    $user = User::factory()->create();
    Colaborador::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'João da Silva',
        'role' => 'Técnico N2',
        'status' => 'active',
    ]);
    Colaborador::factory()->inactive()->create([
        'user_id' => $user->getKey(),
        'name' => 'Maria Souza',
        'role' => 'Coordenadora',
    ]);

    $response = $this->actingAs($user)
        ->get(route('admin.colaboradores.export', ['search' => 'Silva']));

    $response->assertOk();
    $response->assertDownload('colaboradores.xlsx');

    [$heading, $row] = colaboradorWorkbookRows($response->streamedContent());

    expect($heading)->toBe(['Nome', 'Cargo', 'CPF', 'Telefone', 'E-mail', 'Status', 'Observações'])
        ->and($row[0])->toBe('João da Silva')
        ->and($row[1])->toBe('Técnico N2')
        ->and($row[5])->toBe('Ativo')
        ->and(colaboradorWorkbookRows($response->streamedContent()))->toHaveCount(2);
});

it('exports colaboradores owned by the authenticated user only', function () {
    $user = User::factory()->create();
    Colaborador::factory()->create(['user_id' => $user->getKey(), 'name' => 'João da Silva']);
    Colaborador::factory()->create(['user_id' => User::factory()->create()->getKey(), 'name' => 'Maria Souza']);

    $response = $this->actingAs($user)->get(route('admin.colaboradores.export'));

    $rows = colaboradorWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('João da Silva')
        ->and(collect($rows)->flatten()->doesntContain('Maria Souza'))->toBeTrue();
});

it('does not filter the export by an unknown status', function () {
    $user = User::factory()->create();
    Colaborador::factory()->create(['user_id' => $user->getKey(), 'name' => 'João da Silva', 'status' => 'active']);
    Colaborador::factory()->inactive()->create(['user_id' => $user->getKey(), 'name' => 'Maria Souza']);

    $response = $this->actingAs($user)
        ->get(route('admin.colaboradores.export', ['status' => 'inexistente']));

    $rows = colaboradorWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(3);
});

it('requires authentication to export colaboradores', function () {
    $this->get(route('admin.colaboradores.export'))->assertRedirect('/login');
});
