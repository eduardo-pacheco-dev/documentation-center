<?php

use App\Models\Client;
use App\Models\Erb;
use App\Models\RadioLink;
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
    $headings = ['Cliente', 'ERB', 'Radio link', 'Descrição', 'Prioridade', 'Status', 'Abertura', 'Previsão', 'Observações'];

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

it('rejects a row that references an unknown erb or radio link', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'SITE-999', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
        ['Construtora Alfa', '', 'RL-99999', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('does not use an erb that belongs to another user', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);
    Erb::factory()->create(['site_id' => 'SITE-010']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', 'SITE-010', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
});

it('creates no work order when any row is invalid', function () {
    $user = User::factory()->create();
    Client::factory()->create(['user_id' => $user->getKey(), 'name' => 'Construtora Alfa']);

    $file = workOrderWorkbook([
        ['Construtora Alfa', '', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
        ['Cliente Inexistente', '', '', '', 'normal', 'aberta', '10/01/2026', '', ''],
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.index'))
        ->post(route('admin.work-orders.import'), ['file' => $file])
        ->assertSessionHasErrors('import');

    expect(WorkOrder::query()->count())->toBe(0);
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
