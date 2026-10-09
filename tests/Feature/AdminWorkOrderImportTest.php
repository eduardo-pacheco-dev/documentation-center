<?php

use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
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
function workOrderWorkbook(array $rows): UploadedFile
{
    $headings = ['Cliente', 'Título', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações'];

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$headings, ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'os-import').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'ordens-de-servico.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

it('imports work orders from an excel spreadsheet', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'Manutenção elétrica', 'Revisão do quadro', 'alta', 'aberta', '10/01/2026', '24/01/2026', 'Portaria liberada'],
        ['Construtora Alfa', 'Troca de luminárias', '', 'urgente', 'em andamento', '12/01/2026', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.work-orders.index'));
    $response->assertSessionHas('status');

    $workOrders = WorkOrder::query()->orderBy('id')->get();

    expect($workOrders)->toHaveCount(2)
        ->and($workOrders[0]->number)->toBe('OS-0001')
        ->and($workOrders[1]->number)->toBe('OS-0002')
        ->and($workOrders[0]->user_id)->toBe($user->getKey())
        ->and($workOrders[0]->title)->toBe('Manutenção elétrica')
        ->and($workOrders[0]->priority->value)->toBe('high')
        ->and($workOrders[0]->status->value)->toBe('open')
        ->and($workOrders[0]->opened_at->toDateString())->toBe('2026-01-10')
        ->and($workOrders[0]->due_at->toDateString())->toBe('2026-01-24')
        ->and($workOrders[1]->priority->value)->toBe('urgent')
        ->and($workOrders[1]->status->value)->toBe('in_progress')
        ->and($workOrders[1]->due_at)->toBeNull()
        ->and((float) $workOrders[0]->total)->toBe(0.0);
});

it('creates no work order when any row is invalid', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'OS válida', '', 'normal', 'aberta', '10/01/2026', '', ''],
        ['Cliente Inexistente', 'OS inválida', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('rejects a file that is not an excel spreadsheet', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post(route('admin.work-orders.import'), [
            'file' => UploadedFile::fake()->create('clientes.pdf', 100, 'application/pdf'),
        ])
        ->assertSessionHasErrors('file');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('downloads the import template', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('admin.work-orders.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});

it('requires authentication to import work orders', function () {
    $this->post(route('admin.work-orders.import'))->assertRedirect('/login');
});
