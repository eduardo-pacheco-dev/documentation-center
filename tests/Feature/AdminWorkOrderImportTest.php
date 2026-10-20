<?php

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Erb;
use App\Models\RadioLink;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

uses(RefreshDatabase::class);

/**
 * Build an uploaded Excel workbook from the given data rows.
 *
 * @param  array<int, array<int, mixed>>  $rows  The data rows, excluding the heading.
 */
function workOrderWorkbook(array $rows): UploadedFile
{
    $headings = ['Cliente', 'ERB', 'Radio link', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações', 'OC', 'CHAVE'];

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

/**
 * Build an uploaded complete Excel workbook from the given data rows.
 *
 * @param  array<int, array<int, mixed>>  $rows  The data rows, excluding the heading.
 */
function completeWorkOrderWorkbook(array $rows): UploadedFile
{
    $headings = [
        'Cliente',
        'ERB',
        'Radio link',
        'Descrição',
        'Prioridade',
        'Status',
        'Abertura',
        'Previsão',
        'Observações',
        'OC',
        'CHAVE',
        'Item',
        'Quantidade',
        'Status do item',
        'OS cliente 1',
        'OS cliente 2',
    ];

    $spreadsheet = new Spreadsheet;
    $spreadsheet->getActiveSheet()->fromArray([$headings, ...$rows]);

    $path = tempnam(sys_get_temp_dir(), 'os-import-complete').'.xlsx';

    (new Xlsx($spreadsheet))->save($path);

    return new UploadedFile(
        $path,
        'ordens-de-servico-completo.xlsx',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        null,
        true,
    );
}

it('imports work orders from an excel spreadsheet', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', 'Revisão do quadro', 'alta', 'aberta', '10/01/2026', '24/01/2026', 'Portaria liberada'],
        ['Construtora Alfa', '', '', '', 'urgente', 'em andamento', '12/01/2026', '', ''],
    ]);

    $response = $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file]);

    $response->assertRedirect(route('admin.work-orders.index'));
    $response->assertSessionHas('status');

    $workOrders = WorkOrder::query()->orderBy('id')->get();

    expect($workOrders)->toHaveCount(2)
        ->and($workOrders[0]->number)->toBe('OS-0001')
        ->and($workOrders[1]->number)->toBe('OS-0002')
        ->and($workOrders[0]->user_id)->toBe($user->getKey())
        ->and($workOrders[0]->erb_id)->toBeNull()
        ->and($workOrders[0]->radio_link_id)->toBeNull()
        ->and($workOrders[0]->description)->toBe('Revisão do quadro')
        ->and($workOrders[0]->priority->value)->toBe('high')
        ->and($workOrders[0]->status->value)->toBe('open')
        ->and($workOrders[0]->opened_at->toDateString())->toBe('2026-01-10')
        ->and($workOrders[0]->due_at->toDateString())->toBe('2026-01-24')
        ->and($workOrders[1]->priority->value)->toBe('urgent')
        ->and($workOrders[1]->status->value)->toBe('in_progress')
        ->and($workOrders[1]->due_at)->toBeNull()
        ->and((float) $workOrders[0]->total)->toBe(0.0);
});

it('links the work order to an erb by site id or code', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $erb = Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-010']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'site-010', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
        ['Construtora Alfa', $erb->code, '', '', 'normal', 'aberta', '11/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrders = WorkOrder::query()->orderBy('id')->get();

    expect($workOrders)->toHaveCount(2)
        ->and($workOrders[0]->erb_id)->toBe($erb->getKey())
        ->and($workOrders[1]->erb_id)->toBe($erb->getKey())
        ->and($workOrders[0]->radio_link_id)->toBeNull();
});

it('links the work order to a radio link by code', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey(), 'code' => 'RL-00042']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', 'rl-00042', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder = WorkOrder::query()->sole();

    expect($workOrder->radio_link_id)->toBe($radioLink->getKey())
        ->and($workOrder->erb_id)->toBeNull();
});

it('rejects a row that fills both the erb and the radio link', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-010']);
    RadioLink::factory()->create(['user_id' => $user->getKey(), 'code' => 'RL-00042']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'SITE-010', 'RL-00042', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('creates a missing erb and links it to the work order', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'SITE-999', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $erb = Erb::query()->where('user_id', $user->getKey())->where('site_id', 'SITE-999')->sole();

    expect($erb->name)->toBe('SITE-999')
        ->and(WorkOrder::query()->sole()->erb_id)->toBe($erb->getKey());
});

it('rejects an unknown radio link code without endpoints', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', 'RL-99999', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('creates a new erb instead of using one that belongs to another user', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $foreignErb = Erb::factory()->create(['site_id' => 'SITE-010']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'SITE-010', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $erb = Erb::query()->where('user_id', $user->getKey())->where('site_id', 'SITE-010')->sole();

    expect($erb->getKey())->not->toBe($foreignErb->getKey())
        ->and(WorkOrder::query()->sole()->erb_id)->toBe($erb->getKey());
});

it('creates the radio link and its endpoints from the dir separator', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', 'SITE-A dir SITE-B', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder = WorkOrder::query()->sole();
    $radioLink = $workOrder->radioLink;

    expect(Erb::query()->where('user_id', $user->getKey())->count())->toBe(2)
        ->and($radioLink)->not->toBeNull()
        ->and($radioLink->erbA->site_id)->toBe('SITE-A')
        ->and($radioLink->erbB->site_id)->toBe('SITE-B')
        ->and($workOrder->erb_id)->toBeNull();
});

it('creates the radio link and its endpoints from the dash separator', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', 'SITE-C - SITE-D', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $radioLink = WorkOrder::query()->sole()->radioLink;

    expect($radioLink)->not->toBeNull()
        ->and($radioLink->erbA->site_id)->toBe('SITE-C')
        ->and($radioLink->erbB->site_id)->toBe('SITE-D');
});

it('reuses an existing radio link with the endpoints reversed', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $erbA = Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-A']);
    $erbB = Erb::factory()->create(['user_id' => $user->getKey(), 'site_id' => 'SITE-B']);
    $radioLink = RadioLink::factory()->create([
        'user_id' => $user->getKey(),
        'erb_a_id' => $erbA->getKey(),
        'erb_b_id' => $erbB->getKey(),
    ]);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', 'SITE-B dir SITE-A', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    expect(RadioLink::query()->count())->toBe(1)
        ->and(WorkOrder::query()->sole()->radio_link_id)->toBe($radioLink->getKey());
});

it('creates a missing client and links it to the work order', function () {
    $user = User::factory()->create();

    $file = workOrderWorkbook([
        ['Novo Cliente Importado', '', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $client = Client::query()->where('user_id', $user->getKey())->where('name', 'Novo Cliente Importado')->sole();

    expect($client->status->value)->toBe('active')
        ->and(WorkOrder::query()->sole()->client_id)->toBe($client->getKey());
});

it('creates no work order when any row is invalid', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
        ['Construtora Alfa', '', '', '', 'inexistente', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0)
        ->and(Client::query()->count())->toBe(1);
});

it('accepts the on hold status label in the spreadsheet', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'em espera', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder = WorkOrder::query()->sole();

    expect($workOrder->status->value)->toBe('on_hold');
});

it('imports the oc and chave columns', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', 'Revisão', 'normal', 'aberta', '10/01/2026', '', '', '123', 'CHV-456'],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder = WorkOrder::query()->sole();

    expect($workOrder->oc)->toBe('123')
        ->and($workOrder->chave)->toBe('CHV-456');
});

it('updates an existing work order when the oc matches', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'oc' => '1001',
        'chave' => 'CHV-EXISTENTE',
        'description' => 'Descrição antiga',
        'priority' => 'low',
        'status' => 'open',
    ]);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', 'Descrição atualizada', 'alta', 'em andamento', '10/01/2026', '20/01/2026', '', '1001', 'CHV-EXISTENTE'],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder->refresh();

    expect(WorkOrder::query()->count())->toBe(1)
        ->and($workOrder->description)->toBe('Descrição atualizada')
        ->and($workOrder->priority->value)->toBe('high')
        ->and($workOrder->status->value)->toBe('in_progress')
        ->and($workOrder->opened_at->toDateString())->toBe('2026-01-10')
        ->and($workOrder->due_at?->toDateString())->toBe('2026-01-20');
});

it('updates an existing work order when the chave matches', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'oc' => null,
        'chave' => 'CHV-EXISTENTE',
        'description' => 'Descrição antiga',
    ]);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', 'Descrição pela chave', 'normal', 'aberta', '10/01/2026', '', '', '2002', 'CHV-EXISTENTE'],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertRedirect(route('admin.work-orders.index'));

    $workOrder->refresh();

    expect(WorkOrder::query()->count())->toBe(1)
        ->and($workOrder->oc)->toBe('2002')
        ->and($workOrder->description)->toBe('Descrição pela chave');
});

it('rejects a non numeric oc in the spreadsheet', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', '', 'OC-123', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('does not update a work order that belongs to another user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    WorkOrder::factory()->create([
        'user_id' => $other->getKey(),
        'oc' => '1001',
        'chave' => 'CHV-EXISTENTE',
    ]);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', '', '1001', 'CHV-EXISTENTE'],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(1);
});

it('rejects duplicated oc or chave inside the spreadsheet', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', '', '3003', 'CHV-REPETIDA'],
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '11/01/2026', '', '', '3003', 'CHV-REPETIDA'],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('imports a complete spreadsheet with a work order and its items', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = completeWorkOrderWorkbook([
        ['Construtora Alfa', '', '', 'OS completa', 'alta', 'em andamento', '10/01/2026', '20/01/2026', '', '555', '', 'Item A', 2, 'pendente', 'CLI-A1', 'CLI-A2'],
        ['Construtora Alfa', '', '', 'OS completa', 'alta', 'em andamento', '10/01/2026', '20/01/2026', '', '555', '', 'Item B', 1, 'em andamento', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), [
        'file' => $file,
        'mode' => 'completo',
    ])->assertRedirect(route('admin.work-orders.index'));

    $workOrder = WorkOrder::query()->sole();
    $items = $workOrder->items()->orderBy('id')->get();

    expect($workOrder->oc)->toBe('555')
        ->and($workOrder->priority->value)->toBe('high')
        ->and($workOrder->status->value)->toBe('in_progress')
        ->and($items)->toHaveCount(2)
        ->and($items[0]->description)->toBe('Item A')
        ->and($items[0]->client_os)->toBe('CLI-A1')
        ->and($items[0]->client_os_2)->toBe('CLI-A2')
        ->and((float) $items[0]->total)->toBe(0.0)
        ->and($items[1]->description)->toBe('Item B')
        ->and($items[1]->status->value)->toBe('in_progress')
        ->and((float) $workOrder->total)->toBe(0.0);
});

it('updates an existing work order and replaces its items on a complete import', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
        'oc' => '777',
        'description' => 'Descrição antiga',
    ]);
    $workOrder->items()->create(['description' => 'Item antigo', 'quantity' => 1, 'unit_price' => 5, 'total' => 5]);

    $file = completeWorkOrderWorkbook([
        ['Construtora Alfa', '', '', 'Descrição nova', 'normal', 'aberta', '11/01/2026', '', '', '777', '', 'Item novo A', 1, 'pendente', '', ''],
        ['Construtora Alfa', '', '', 'Descrição nova', 'normal', 'aberta', '11/01/2026', '', '', '777', '', 'Item novo B', 2, 'rejeitado', '', ''],
    ]);

    $this->actingAs($user)->post(route('admin.work-orders.import'), [
        'file' => $file,
        'mode' => 'completo',
    ])->assertRedirect(route('admin.work-orders.index'));

    $workOrder->refresh();
    $items = $workOrder->items()->orderBy('id')->get();

    expect(WorkOrder::query()->count())->toBe(1)
        ->and($workOrder->description)->toBe('Descrição nova')
        ->and($items)->toHaveCount(2)
        ->and($items[0]->description)->toBe('Item novo A')
        ->and($items[1]->status->value)->toBe('rejected')
        ->and((float) $workOrder->total)->toBe(0.0);
});

it('requires an oc or chave to group the rows of a complete import', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = completeWorkOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', '', '', '', 'Item sem grupo', 1, 'pendente', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), [
            'file' => $file,
            'mode' => 'completo',
        ])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('downloads the complete import template with the item columns', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Alpha Telecom']);
    CatalogItem::factory()->create(['user_id' => $user->getKey(), 'name' => 'Cabo coaxial']);

    $response = $this->actingAs($user)->get(route('admin.work-orders.import.template', ['mode' => 'completo']));

    $response->assertOk();
    $response->assertDownload('modelo-ordens-de-servico-completo.xlsx');

    $path = tempnam(sys_get_temp_dir(), 'os-template-complete').'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = (new XlsxReader)->load($path);
    $headings = $spreadsheet->getActiveSheet()->rangeToArray('A1:P1')[0];

    expect($headings)->toBe([
        'Cliente',
        'ERB',
        'Radio link',
        'Descrição',
        'Prioridade',
        'Status',
        'Abertura',
        'Previsão',
        'Observações',
        'OC',
        'CHAVE',
        'Item',
        'Quantidade',
        'Status do item',
        'OS cliente 1',
        'OS cliente 2',
    ])
        ->and($spreadsheet->getActiveSheet()->getDataValidation('N2')->getType())->toBe(DataValidation::TYPE_LIST)
        ->and($spreadsheet->getActiveSheet()->getDataValidation('N2')->getFormula1())->toBe('Listas!$D$2:$D$6')
        ->and($spreadsheet->getActiveSheet()->getDataValidation('L2')->getType())->toBe(DataValidation::TYPE_LIST)
        ->and($spreadsheet->getActiveSheet()->getDataValidation('L2')->getFormula1())->toBe('Listas!$E$2:$E$2')
        ->and($spreadsheet->getSheetByName('Listas')->getCell('E2')->getValue())->toBe('Cabo coaxial');

    @unlink($path);
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

it('downloads the import template with dropdown lists for client, priority and status', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Alpha Telecom']);
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Beta Networks']);
    Client::factory()->create(['name' => 'Cliente de outro usuário']);

    $response = $this->actingAs($user)->get(route('admin.work-orders.import.template'));

    $response->assertOk();
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');

    $path = tempnam(sys_get_temp_dir(), 'os-template').'.xlsx';
    file_put_contents($path, $response->streamedContent());

    $spreadsheet = (new XlsxReader)->load($path);
    $sheet = $spreadsheet->getActiveSheet();
    $lists = $spreadsheet->getSheetByName('Listas');

    expect($sheet->getDataValidation('A2')->getType())->toBe(DataValidation::TYPE_LIST)
        ->and($sheet->getDataValidation('A2')->getErrorStyle())->toBe(DataValidation::STYLE_WARNING)
        ->and($sheet->getDataValidation('A2')->getShowDropDown())->toBeTrue()
        ->and($sheet->getDataValidation('A2')->getFormula1())->toBe('Listas!$A$2:$A$3')
        ->and($sheet->getDataValidation('E2')->getType())->toBe(DataValidation::TYPE_LIST)
        ->and($sheet->getDataValidation('E2')->getShowDropDown())->toBeTrue()
        ->and($sheet->getDataValidation('E2')->getFormula1())->toBe('Listas!$B$2:$B$5')
        ->and($sheet->getDataValidation('F2')->getType())->toBe(DataValidation::TYPE_LIST)
        ->and($sheet->getDataValidation('F2')->getShowDropDown())->toBeTrue()
        ->and($sheet->getDataValidation('F2')->getFormula1())->toBe('Listas!$C$2:$C$6')
        ->and($lists)->not->toBeNull()
        ->and($lists->getSheetState())->toBe(Worksheet::SHEETSTATE_HIDDEN)
        ->and($lists->getCell('A2')->getValue())->toBe('Alpha Telecom')
        ->and($lists->getCell('A3')->getValue())->toBe('Beta Networks')
        ->and($lists->getCell('B2')->getValue())->toBe('baixa')
        ->and($lists->getCell('C2')->getValue())->toBe('aberta');

    @unlink($path);
});

it('requires authentication to import work orders', function () {
    $this->post(route('admin.work-orders.import'))->assertRedirect('/login');
});
