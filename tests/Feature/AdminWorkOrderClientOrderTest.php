<?php

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderClientOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a client and a catalog item owned by the given user.
 *
 * @return array{0: Client, 1: CatalogItem}
 */
function workOrderClientOrderFixtures(User $user): array
{
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $catalogItem = CatalogItem::factory()->create([
        'user_id' => $user->getKey(),
        'name' => 'Item de teste',
        'unit' => 'un',
        'price' => 100,
    ]);

    return [$client, $catalogItem];
}

it('creates a work order with optional client OS data and an individual OS per item', function () {
    $user = User::factory()->create();
    [$client, $catalogItem] = workOrderClientOrderFixtures($user);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'client_os' => 'CLI-0001',
        'oc' => 'OC-77',
        'priority' => 'normal',
        'status' => 'open',
        'opened_at' => '2026-01-10',
        'client_orders' => [
            'client-order-0' => ['number' => 'OS-CLI-1', 'description' => 'Primeira OS do cliente'],
            'client-order-1' => ['number' => 'OS-CLI-2', 'description' => 'Segunda OS do cliente'],
        ],
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1, 'client_os' => 'OS-ITEM-1'],
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 2, 'client_os' => 'OS-ITEM-2'],
        ],
    ])->assertRedirect();

    $workOrder = WorkOrder::query()->firstOrFail();
    $clientOrders = $workOrder->clientOrders()->orderBy('id')->get();
    $items = $workOrder->items()->orderBy('id')->get();

    expect($workOrder->client_os)->toBe('CLI-0001')
        ->and($workOrder->oc)->toBe('OC-77')
        ->and($clientOrders)->toHaveCount(2)
        ->and($clientOrders[0]->number)->toBe('OS-CLI-1')
        ->and($clientOrders[1]->number)->toBe('OS-CLI-2')
        ->and($items[0]->client_os)->toBe('OS-ITEM-1')
        ->and($items[1]->client_os)->toBe('OS-ITEM-2');

    $response = $this->actingAs($user)->get(route('admin.work-orders.show', $workOrder));

    $response->assertOk()
        ->assertSee('OS Cliente Principal')
        ->assertSee('CLI-0001')
        ->assertSee('OC (ordem complexa)')
        ->assertSee('OC-77')
        ->assertSee('OS-CLI-1')
        ->assertSee('Primeira OS do cliente')
        ->assertSee('OS cliente: OS-ITEM-1')
        ->assertSee('OS cliente: OS-ITEM-2');

    expect($response->getContent())->toContain('"client_os":"OS-ITEM-1"');
});

it('replaces the client OS list and the individual item OS when updating the work order', function () {
    $user = User::factory()->create();
    [$client, $catalogItem] = workOrderClientOrderFixtures($user);

    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);

    $workOrder->clientOrders()->create(['number' => 'OS-ANTIGA']);
    $workOrder->items()->create([
        'client_os' => 'OS-ITEM-ANTIGA',
        'description' => 'Item antigo',
        'quantity' => 1,
        'unit_price' => 10,
        'total' => 10,
    ]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.update', $workOrder), [
            'client_id' => $client->getKey(),
            'client_os' => 'CLI-0002',
            'oc' => 'OC-88',
            'priority' => 'high',
            'status' => 'open',
            'opened_at' => '2026-02-01',
            'client_orders' => [
                'client-order-1' => ['number' => 'OS-NOVA', 'description' => 'OS atualizada do cliente'],
            ],
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 3, 'client_os' => 'OS-ITEM-NOVA'],
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder));

    $workOrder->refresh();
    $newClientOrder = $workOrder->clientOrders()->firstOrFail();
    $item = $workOrder->items()->firstOrFail();

    expect($workOrder->client_os)->toBe('CLI-0002')
        ->and($workOrder->oc)->toBe('OC-88')
        ->and(WorkOrderClientOrder::query()->count())->toBe(1)
        ->and($newClientOrder->number)->toBe('OS-NOVA')
        ->and($item->client_os)->toBe('OS-ITEM-NOVA');
});

it('updates the individual client OS of the items from the details page', function () {
    $user = User::factory()->create();
    [$client, $catalogItem] = workOrderClientOrderFixtures($user);

    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.update', $workOrder), [
            'items' => [
                [
                    'catalog_item_id' => $catalogItem->getKey(),
                    'quantity' => 2,
                    'client_os' => 'OS-INLINE-9',
                ],
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHasNoErrors();

    expect($workOrder->items()->firstOrFail()->client_os)->toBe('OS-INLINE-9');
});

it('accepts items without an individual client OS', function () {
    $user = User::factory()->create();
    [$client, $catalogItem] = workOrderClientOrderFixtures($user);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'priority' => 'normal',
        'status' => 'open',
        'opened_at' => '2026-01-10',
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
        ],
    ])->assertRedirect();

    expect(WorkOrder::query()->firstOrFail()->items()->firstOrFail()->client_os)->toBeNull();
});
