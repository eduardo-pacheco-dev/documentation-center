<?php

use App\Enums\WorkOrderPriority;
use App\Enums\WorkOrderStatus;
use App\Models\Client;
use App\Models\Erb;
use App\Models\RadioLink;
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
        'oc' => '77077',
        'chave' => 'CHV-001',
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
            'OS Cliente Principal',
            'OC',
            'CHAVE',
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
        ->and($rows[1][2])->toBe('77077')
        ->and($rows[1][3])->toBe('CHV-001')
        ->and($rows[1][4])->toBe('Alpha Telecom')
        ->and($rows[1][5])->toBe('SITE-ALPHA')
        ->and($rows[1][6])->toBeNull()
        ->and($rows[1][7])->toBe('Manutenção preventiva')
        ->and($rows[1][8])->toBe('Alta')
        ->and($rows[1][9])->toBe('Em andamento')
        ->and($rows[1][10])->toBe('10/01/2026')
        ->and($rows[1][11])->toBe('20/01/2026')
        ->and($rows[1][12])->toBeNull()
        ->and((int) $rows[1][13])->toBe(1)
        ->and((float) $rows[1][14])->toBe(1500.50)
        ->and($rows[1][15])->toBe('Acesso liberado pela portaria.');
});

it('exports a complete workbook with one row per work order item', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Alpha Telecom']);

    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
        'number' => 'OS-9001',
        'client_os' => 'CLI-0001',
        'oc' => '12345',
        'chave' => 'CHV-001',
    ]);

    $workOrder->clientOrders()->create([
        'number' => '9100',
        'description' => 'OS cliente A',
    ]);

    $workOrder->items()->createMany([
        [
            'description' => 'Item A',
            'client_os' => 'OS-A1',
            'client_os_2' => 'OS-A2',
            'unit' => 'un',
            'quantity' => 2,
            'unit_price' => 50,
            'total' => 100,
            'status' => 'completed',
        ],
        [
            'description' => 'Item B',
            'unit' => 'h',
            'quantity' => 1,
            'unit_price' => 30,
            'total' => 30,
            'status' => 'pending',
        ],
    ]);

    $response = $this->actingAs($user)->get(route('admin.work-orders.export', ['mode' => 'completo']));

    $response->assertOk();
    $response->assertDownload('ordens-de-servico-completo.xlsx');

    $rows = workOrderExportWorkbookRows($response->streamedContent());

    expect($rows)->toHaveCount(3)
        ->and($rows[0][16])->toBe('OS do cliente (lista)')
        ->and($rows[0][17])->toBe('Item')
        ->and($rows[0][23])->toBe('Subtotal do item')
        ->and($rows[1][0])->toBe('OS-9001')
        ->and($rows[1][16])->toBe('9100 - OS cliente A')
        ->and($rows[1][17])->toBe('Item A')
        ->and($rows[1][18])->toBe('OS-A1')
        ->and($rows[1][19])->toBe('OS-A2')
        ->and($rows[1][20])->toBe('un')
        ->and((float) $rows[1][21])->toBe(2.0)
        ->and((float) $rows[1][22])->toBe(50.0)
        ->and((float) $rows[1][23])->toBe(100.0)
        ->and($rows[1][24])->toBe('Concluído')
        ->and($rows[2][0])->toBe('OS-9001')
        ->and($rows[2][17])->toBe('Item B')
        ->and($rows[2][18])->toBeNull()
        ->and($rows[2][19])->toBeNull()
        ->and($rows[2][24])->toBe('Pendente');
});

it('exports the radio link as its A and B ends in both modes', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Alpha Telecom']);
    $endA = Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-END-A']);
    $endB = Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-END-B']);
    $radioLink = RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $endA->getKey(),
        'erb_b_id' => $endB->getKey(),
        'code' => 'RL-00099',
    ]);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
        'radio_link_id' => $radioLink->getKey(),
    ]);
    $workOrder->items()->create([
        'description' => 'Item único',
        'quantity' => 1,
        'unit_price' => 10,
        'total' => 10,
    ]);

    $simplified = workOrderExportWorkbookRows(
        $this->actingAs($user)->get(route('admin.work-orders.export'))->streamedContent(),
    );
    $complete = workOrderExportWorkbookRows(
        $this->actingAs($user)->get(route('admin.work-orders.export', ['mode' => 'completo']))->streamedContent(),
    );

    expect($simplified[1][6])->toBe('SITE-END-A - SITE-END-B')
        ->and($complete[1][6])->toBe('SITE-END-A - SITE-END-B');
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
