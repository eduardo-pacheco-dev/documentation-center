<?php

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a client owned by the given user.
 */
function ownedClient(User $user): Client
{
    return Client::factory()->create(['user_id' => $user->getKey()]);
}

it('requires an authenticated user to open the work orders area', function () {
    $this->get('/admin/work-orders')->assertRedirect('/login');
});

it('lists only the work orders the user owns', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey(), 'title' => 'OS do Titular']);
    WorkOrder::factory()->create(['title' => 'OS Alheia']);

    $this->actingAs($owner)
        ->get('/admin/work-orders')
        ->assertOk()
        ->assertSee('OS do Titular')
        ->assertDontSee('OS Alheia');
});

it('filters the work order list by search term and status', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->inProgress()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'title' => 'Alpha Manutenção',
    ]);
    WorkOrder::factory()->cancelled()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'title' => 'Beta Reforma',
    ]);

    $this->actingAs($owner)
        ->get('/admin/work-orders?search=Alpha')
        ->assertOk()
        ->assertSee('Alpha Manutenção')
        ->assertDontSee('Beta Reforma');

    $this->actingAs($owner)
        ->get('/admin/work-orders?status=in_progress')
        ->assertOk()
        ->assertSee('Alpha Manutenção')
        ->assertDontSee('Beta Reforma');
});

it('renders the work order list in the alternate view modes', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey(), 'title' => 'Alpha Manutenção']);

    $this->actingAs($owner)->get('/admin/work-orders?view=cards')->assertOk()->assertSee('Alpha Manutenção');
    $this->actingAs($owner)->get('/admin/work-orders?view=compact')->assertOk()->assertSee('Alpha Manutenção');
});

it('creates a work order with items and computes the totals', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = CatalogItem::factory()->create(['user_id' => $user->getKey(), 'name' => 'Cimento CP-II']);

    $this->actingAs($user)->get('/admin/work-orders/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'title' => 'Manutenção elétrica do galpão',
        'description' => 'Revisão completa do quadro de distribuição.',
        'priority' => 'high',
        'status' => 'open',
        'opened_at' => '2026-01-10',
        'due_at' => '2026-01-24',
        'notes' => 'Acesso liberado pela portaria.',
        'items' => [
            [
                'catalog_item_id' => $catalogItem->getKey(),
                'description' => 'Cimento CP-II',
                'unit' => 'un',
                'quantity' => 2,
                'unit_price' => 39.90,
            ],
            [
                'catalog_item_id' => null,
                'description' => 'Mão de obra',
                'unit' => 'h',
                'quantity' => 3,
                'unit_price' => 100,
            ],
        ],
    ]);

    $workOrder = WorkOrder::query()->firstOrFail();

    $response->assertRedirect(route('admin.work-orders.show', $workOrder));

    expect($workOrder->user_id)->toBe($user->getKey())
        ->and($workOrder->client_id)->toBe($client->getKey())
        ->and($workOrder->number)->toBe('OS-0001')
        ->and($workOrder->priority->value)->toBe('high')
        ->and($workOrder->items)->toHaveCount(2)
        ->and((float) $workOrder->total)->toBe(379.8)
        ->and((float) $workOrder->items->first()->total)->toBe(79.8)
        ->and($workOrder->completed_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('OS-0001')
        ->assertSee('Mão de obra');
});

it('marks the completion date when the status is completed', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'title' => 'OS concluída',
        'priority' => 'normal',
        'status' => 'completed',
        'opened_at' => '2026-01-01',
        'items' => [
            ['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 200],
        ],
    ])->assertRedirect();

    expect(WorkOrder::query()->firstOrFail()->completed_at)->not->toBeNull();
});

it('rejects a work order without a client, title or items', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/work-orders', [])
        ->assertSessionHasErrors(['client_id', 'title', 'items']);

    expect(WorkOrder::query()->count())->toBe(0);
});

it('rejects a client that belongs to another user', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $foreignClient = ownedClient($stranger);

    $this->actingAs($user)
        ->post('/admin/work-orders', [
            'client_id' => $foreignClient->getKey(),
            'title' => 'OS inválida',
            'priority' => 'normal',
            'opened_at' => '2026-01-01',
            'items' => [
                ['description' => 'Serviço', 'quantity' => 1, 'unit_price' => 100],
            ],
        ])
        ->assertSessionHasErrors('client_id');
});

it('rejects an item without a description or a valid quantity', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);

    $this->actingAs($user)
        ->post('/admin/work-orders', [
            'client_id' => $client->getKey(),
            'title' => 'OS com item inválido',
            'priority' => 'normal',
            'opened_at' => '2026-01-01',
            'items' => [
                ['description' => '', 'quantity' => 0, 'unit_price' => -5],
            ],
        ])
        ->assertSessionHasErrors(['items.0.description', 'items.0.quantity', 'items.0.unit_price']);
});

it('forbids a stranger from managing the work order', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)->get("/admin/work-orders/{$workOrder->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/work-orders/{$workOrder->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/work-orders/{$workOrder->getKey()}")->assertForbidden();
});

it('updates the work order, replaces the items and clears the completion date', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $workOrder = WorkOrder::factory()->completed()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);
    $workOrder->items()->create(['description' => 'Item antigo', 'quantity' => 1, 'unit_price' => 50, 'total' => 50]);

    $this->actingAs($user)->get("/admin/work-orders/{$workOrder->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/work-orders/{$workOrder->getKey()}", [
            'client_id' => $client->getKey(),
            'title' => 'Título atualizado',
            'priority' => 'urgent',
            'status' => 'open',
            'opened_at' => '2026-02-01',
            'items' => [
                ['description' => 'Item novo', 'quantity' => 4, 'unit_price' => 25],
            ],
        ])
        ->assertSessionHas('status');

    $workOrder->refresh();

    expect($workOrder->title)->toBe('Título atualizado')
        ->and($workOrder->priority->value)->toBe('urgent')
        ->and($workOrder->completed_at)->toBeNull()
        ->and((float) $workOrder->total)->toBe(100.0)
        ->and($workOrder->items)->toHaveCount(1)
        ->and($workOrder->items->first()->description)->toBe('Item novo');
});

it('soft deletes the work order', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey(), 'client_id' => $client->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/work-orders/{$workOrder->getKey()}")
        ->assertRedirect(route('admin.work-orders.index'));

    expect($workOrder->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/work-orders')->assertOk()->assertDontSee($workOrder->title);
});
