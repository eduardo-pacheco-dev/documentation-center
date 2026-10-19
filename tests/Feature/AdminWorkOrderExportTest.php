<?php

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\Client;
use App\Models\Erb;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;

uses(RefreshDatabase::class);

/**
 * Read the rows of a downloaded spreadsheet.
 *
 * @return array<int, array<int|string, mixed>>
 */
function workOrderExportWorkbookRows(string $content): array
{
    $path = tempnam(sys_get_temp_dir(), 'work-order-export').'.xlsx';
    file_put_contents($path, $content);

    $rows = (new XlsxReader)->load($path)->getActiveSheet()->toArray();

    @unlink($path);

    return $rows;
}

it('downloads the work orders as an excel file filtered by search and status', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Alpha Telecom',
    ]);
    $erb = Erb::factory()->create([
        'user_id' => $user->getKey(),
        'site_id' => 'SITE-ALPHA',
    ]);

    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
        'erb_id' => $erb->getKey(),
        'number' => 'OS-9001',
        'client_os' => 'CLI-0001',
        'oc' => 'OC-77',
        'description' => 'Manutenção preventiva',
        'priority' => WorkOrderPriority::High,
        'status' => WorkOrderStatus::InProgress,
        'opened_at' => '2026-01-10',
        'due_at' => '2026-01-20',
        'total' => 1500.50,
        'notes' => 'Acesso liberado pela portaria.',
    ]);

    $workOrder->items()->create([
        'description' => 'Troca de módulo',
        'quantity' => 1,
        'unit_price' => 1500.50,
        'total' => 1500.50,
    ]);

    WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'number' => 'OS-9002',
        'description' => 'Instalação de rádio',
        'status' => WorkOrderStatus::Completed,
    ]);

    $response = $this->actingAs($user)->get(route('admin.work-orders.export', [
        'search' => 'Alpha',
        'status' => WorkOrderStatus::InProgress->value,
    ]));

    $response->assertOk();
    $response->assertDownload('ordens-de-servico.xlsx');

    $rows = workOrderExportWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(2)
        ->and($rows[0])->toBe([
            'Número',
            'OS Cliente',
            'OC',
            'Cliente',
            'ERB',
            'Radio link',
            'Descrição',
            'Prioridade',
            'Status',
            'Abertura',
            'Previsão',
            'Concluída em',
            'Itens',
            'Total',
            'Observações',
        ])
        ->and($rows[1][0])->toBe('OS-9001')
        ->and($rows[1][1])->toBe('CLI-0001')
        ->and($rows[1][2])->toBe('OC-77')
        ->and($rows[1][3])->toBe('Alpha Telecom')
        ->and($rows[1][4])->toBe('SITE-ALPHA')
        ->and($rows[1][5])->toBeNull()
        ->and($rows[1][6])->toBe('Manutenção preventiva')
        ->and($rows[1][7])->toBe('Alta')
        ->and($rows[1][8])->toBe('Em andamento')
        ->and($rows[1][9])->toBe('10/01/2026')
        ->and($rows[1][10])->toBe('20/01/2026')
        ->and($rows[1][11])->toBeNull()
        ->and((int) $rows[1][12])->toBe(1)
        ->and((float) $rows[1][13])->toBe(1500.50)
        ->and($rows[1][14])->toBe('Acesso liberado pela portaria.');
});

it('exports work orders owned by the authenticated user only', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();

    WorkOrder::factory()->create(['user_id' => $user->getKey(), 'number' => 'OS-9001']);
    WorkOrder::factory()->create(['user_id' => $other->getKey(), 'number' => 'OS-9002']);

    $response = $this->actingAs($user)->get(route('admin.work-orders.export'));

    $rows = workOrderExportWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(2)
        ->and($rows[1][0])->toBe('OS-9001')
        ->and(collect($rows)->flatten()->doesntContain('OS-9002'))->toBeTrue();
});

it('does not filter the export by an unknown status', function () {
    $user = User::factory()->create();

    WorkOrder::factory()->create(['user_id' => $user->getKey(), 'number' => 'OS-9001']);
    WorkOrder::factory()->completed()->create(['user_id' => $user->getKey(), 'number' => 'OS-9002']);

    $response = $this->actingAs($user)->get(route('admin.work-orders.export', [
        'status' => 'inexistente',
    ]));

    expect(workOrderExportWorkbookRows($response->streamedContent()))->toHaveCount(3);
});

it('requires authentication to export work orders', function () {
    $this->get(route('admin.work-orders.export'))->assertRedirect('/login');
});
