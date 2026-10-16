<?php

use App\Models\CatalogItem;
use App\Models\Client;
use App\Models\Erb;
use App\Models\RadioLink;
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

/**
 * Create a catalog item owned by the given user.
 */
function ownedCatalogItem(User $user, array $attributes = []): CatalogItem
{
    return CatalogItem::factory()->create(['user_id' => $user->getKey(), ...$attributes]);
}

it('requires an authenticated user to open the work orders area', function () {
    $this->get('/admin/work-orders')->assertRedirect('/login');
});

it('lists only the work orders the user owns', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey(), 'number' => 'OS-9001']);
    WorkOrder::factory()->create(['number' => 'OS-9002']);

    $this->actingAs($owner)
        ->get('/admin/work-orders')
        ->assertOk()
        ->assertSee('OS-9001')
        ->assertDontSee('OS-9002');
});

it('shows work order statistics for the authenticated user', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey()]);
    WorkOrder::factory()->inProgress()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey()]);
    WorkOrder::factory()->completed()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey()]);
    WorkOrder::factory()->cancelled()->create();

    $this->actingAs($owner)
        ->get('/admin/work-orders')
        ->assertOk()
        ->assertViewHas('stats', fn (array $stats): bool => $stats['total'] === 3
            && $stats['open'] === 1
            && $stats['inProgress'] === 1
            && $stats['completed'] === 1);
});

it('filters the work order list by search term and status', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->inProgress()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'number' => 'OS-5001',
    ]);
    WorkOrder::factory()->cancelled()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'number' => 'OS-5002',
    ]);

    $this->actingAs($owner)
        ->get('/admin/work-orders?search=OS-5001')
        ->assertOk()
        ->assertSee('OS-5001')
        ->assertDontSee('OS-5002');

    $this->actingAs($owner)
        ->get('/admin/work-orders?status=in_progress')
        ->assertOk()
        ->assertSee('OS-5001')
        ->assertDontSee('OS-5002');
});

it('renders the work order list in the alternate view modes', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    WorkOrder::factory()->create(['user_id' => $owner->getKey(), 'client_id' => $client->getKey(), 'number' => 'OS-7000']);

    $this->actingAs($owner)->get('/admin/work-orders?view=cards')->assertOk()->assertSee('OS-7000');
    $this->actingAs($owner)->get('/admin/work-orders?view=compact')->assertOk()->assertSee('OS-7000');
});

it('shows the linked erb site id in the list', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);
    $erb = Erb::factory()->create(['user_id' => $owner->getKey(), 'site_id' => 'SITE-ABC-123']);

    WorkOrder::factory()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'erb_id' => $erb->getKey(),
    ]);

    $this->actingAs($owner)
        ->get('/admin/work-orders')
        ->assertOk()
        ->assertSee('SITE-ABC-123');
});

it('shows the radio link ends as site a to site b in the list', function () {
    $owner = User::factory()->create();
    $client = ownedClient($owner);

    $endA = Erb::factory()->create(['user_id' => $owner->getKey(), 'site_id' => 'SITE-AAA']);
    $endB = Erb::factory()->create(['user_id' => $owner->getKey(), 'site_id' => 'SITE-BBB']);

    $radioLink = RadioLink::factory()->create([
        'user_id' => $owner->getKey(),
        'erb_a_id' => $endA->getKey(),
        'erb_b_id' => $endB->getKey(),
    ]);

    WorkOrder::factory()->create([
        'user_id' => $owner->getKey(),
        'client_id' => $client->getKey(),
        'radio_link_id' => $radioLink->getKey(),
    ]);

    $response = $this->actingAs($owner)->get('/admin/work-orders')->assertOk();

    expect($response->getContent())
        ->toContain('SITE-AAA')
        ->toContain('SITE-BBB')
        ->toContain('M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3');
});

it('renders the create form inside a modal on the work orders list', function () {
    $owner = User::factory()->create();
    ownedClient($owner);

    $this->actingAs($owner)
        ->get('/admin/work-orders')
        ->assertOk()
        ->assertSee('data-modal-open="work-order-modal"', false)
        ->assertSee('id="work-order-modal"', false)
        ->assertSee(route('admin.work-orders.store'), false);
});

it('creates a work order with items and computes the totals', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $cimento = ownedCatalogItem($user, ['name' => 'Cimento CP-II', 'unit' => 'un', 'price' => 39.90]);
    $maoDeObra = ownedCatalogItem($user, ['name' => 'Mão de obra', 'unit' => 'h', 'price' => 100]);

    $this->actingAs($user)->get('/admin/work-orders/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'description' => 'Revisão completa do quadro de distribuição.',
        'priority' => 'high',
        'status' => 'open',
        'opened_at' => '2026-01-10',
        'due_at' => '2026-01-24',
        'notes' => 'Acesso liberado pela portaria.',
        'items' => [
            [
                'catalog_item_id' => $cimento->getKey(),
                'quantity' => 2,
            ],
            [
                'catalog_item_id' => $maoDeObra->getKey(),
                'quantity' => 3,
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
        ->and($workOrder->items->first()->description)->toBe('Cimento CP-II')
        ->and($workOrder->completed_at)->toBeNull();

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('OS-0001')
        ->assertSee('work-order-items-editor-data', false);
});

it('marks the completion date when the status is completed', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'priority' => 'normal',
        'status' => 'completed',
        'opened_at' => '2026-01-01',
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
        ],
    ])->assertRedirect();

    expect(WorkOrder::query()->firstOrFail()->completed_at)->not->toBeNull();
});

it('rejects a work order without a client or items', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/work-orders', [])
        ->assertSessionHasErrors(['client_id', 'items']);

    expect(WorkOrder::query()->count())->toBe(0);
});

it('rejects a client that belongs to another user', function () {
    $user = User::factory()->create();
    $stranger = User::factory()->create();
    $foreignClient = ownedClient($stranger);
    $catalogItem = ownedCatalogItem($user);

    $this->actingAs($user)
        ->post('/admin/work-orders', [
            'client_id' => $foreignClient->getKey(),
            'priority' => 'normal',
            'opened_at' => '2026-01-01',
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
            ],
        ])
        ->assertSessionHasErrors('client_id');
});

it('rejects an item without a catalog item or a valid quantity', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);

    $this->actingAs($user)
        ->post('/admin/work-orders', [
            'client_id' => $client->getKey(),
            'priority' => 'normal',
            'opened_at' => '2026-01-01',
            'items' => [
                ['quantity' => 0],
            ],
        ])
        ->assertSessionHasErrors(['items.0.catalog_item_id', 'items.0.quantity']);
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
    $catalogItem = ownedCatalogItem($user, ['name' => 'Item novo', 'price' => 25]);
    $workOrder = WorkOrder::factory()->completed()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);
    $workOrder->items()->create(['description' => 'Item antigo', 'quantity' => 1, 'unit_price' => 50, 'total' => 50]);

    $this->actingAs($user)->get("/admin/work-orders/{$workOrder->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/work-orders/{$workOrder->getKey()}", [
            'client_id' => $client->getKey(),
            'priority' => 'urgent',
            'status' => 'open',
            'opened_at' => '2026-02-01',
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 4],
            ],
        ])
        ->assertSessionHas('status');

    $workOrder->refresh();

    expect($workOrder->priority->value)->toBe('urgent')
        ->and($workOrder->completed_at)->toBeNull()
        ->and((float) $workOrder->total)->toBe(100.0)
        ->and($workOrder->items)->toHaveCount(1)
        ->and($workOrder->items->first()->description)->toBe('Item novo');
});

it('updates the work order items inline from the details page', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user, ['name' => 'Cabo coaxial', 'unit' => 'm', 'price' => 12.50]);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);
    $workOrder->items()->create(['description' => 'Item antigo', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $workOrder->update(['total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.update', $workOrder), [
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 4],
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHas('status');

    $workOrder->refresh();

    expect($workOrder->items)->toHaveCount(1)
        ->and((float) $workOrder->total)->toBe(50.0)
        ->and($workOrder->items->first()->description)->toBe('Cabo coaxial');
});

it('updates the status of the work order items inline from the details page', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user, ['name' => 'Cabo coaxial', 'unit' => 'm', 'price' => 12.50]);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);
    $workOrder->items()->create(['description' => 'Item antigo', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.update', $workOrder), [
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 4, 'status' => 'completed'],
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHasNoErrors();

    $items = $workOrder->refresh()->items->sortBy('id')->values();

    expect($items)->toHaveCount(2)
        ->and($items[0]->status->value)->toBe('completed')
        ->and($items[1]->status->value)->toBe('pending');
});

it('rejects an unknown work order item status', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
    ]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.show', $workOrder))
        ->put(route('admin.work-orders.items.update', $workOrder), [
            'items' => [
                ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1, 'status' => 'finished'],
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHasErrors('items.0.status');
});

it('shows the item progress on the work order details page', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $workOrder->items()->createMany([
        ['description' => 'Item concluído', 'quantity' => 1, 'unit_price' => 10, 'total' => 10, 'status' => 'completed'],
        ['description' => 'Item em andamento', 'quantity' => 1, 'unit_price' => 10, 'total' => 10, 'status' => 'in_progress'],
        ['description' => 'Item pendente', 'quantity' => 1, 'unit_price' => 10, 'total' => 10, 'status' => 'pending'],
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Progresso dos itens')
        ->assertSee('1 de 3 itens concluídos')
        ->assertSee('33%')
        ->assertSee('Em andamento');
});

it('forbids a stranger from updating the work order items inline', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)
        ->put(route('admin.work-orders.items.update', $workOrder), [
            'items' => [
                ['catalog_item_id' => 1, 'quantity' => 1],
            ],
        ])
        ->assertForbidden();
});

it('updates the work order notes inline from the details page', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.notes.update', $workOrder), ['notes' => 'Acesso liberado pela portaria.'])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHas('status');

    expect($workOrder->refresh()->notes)->toBe('Acesso liberado pela portaria.');
});

it('forbids a stranger from updating the work order notes inline', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)
        ->put(route('admin.work-orders.notes.update', $workOrder), ['notes' => 'Tentativa'])
        ->assertForbidden();
});

it('updates the work order description inline from the details page', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.description.update', $workOrder), ['description' => 'Revisão completa do quadro.'])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHas('status');

    expect($workOrder->refresh()->description)->toBe('Revisão completa do quadro.');
});

it('forbids a stranger from updating the work order description inline', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)
        ->put(route('admin.work-orders.description.update', $workOrder), ['description' => 'Tentativa'])
        ->assertForbidden();
});

it('updates the work order dates inline from the details page', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey(), 'opened_at' => '2026-01-01']);

    $this->actingAs($user)
        ->put(route('admin.work-orders.dates.update', $workOrder), [
            'opened_at' => '2026-02-01',
            'due_at' => '2026-02-15',
        ])
        ->assertRedirect(route('admin.work-orders.show', $workOrder))
        ->assertSessionHas('status');

    $workOrder->refresh();

    expect($workOrder->opened_at->toDateString())->toBe('2026-02-01')
        ->and($workOrder->due_at->toDateString())->toBe('2026-02-15');
});

it('rejects a due date before the opening date', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.work-orders.show', $workOrder))
        ->put(route('admin.work-orders.dates.update', $workOrder), [
            'opened_at' => '2026-02-10',
            'due_at' => '2026-02-01',
        ])
        ->assertSessionHasErrors('due_at');
});

it('forbids a stranger from updating the work order dates inline', function () {
    $stranger = User::factory()->create();
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs($stranger)
        ->put(route('admin.work-orders.dates.update', $workOrder), ['opened_at' => '2026-01-01'])
        ->assertForbidden();
});

it('soft deletes the work order', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey(), 'client_id' => $client->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/work-orders/{$workOrder->getKey()}")
        ->assertRedirect(route('admin.work-orders.index'));

    expect($workOrder->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/work-orders')->assertOk()->assertDontSee($workOrder->number);
});

it('links a work order to a radio link', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user);
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'radio_link_id' => $radioLink->getKey(),
        'priority' => 'normal',
        'opened_at' => '2026-01-10',
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
        ],
    ])->assertRedirect();

    $workOrder = WorkOrder::query()->firstOrFail();

    expect($workOrder->radio_link_id)->toBe($radioLink->getKey())
        ->and($workOrder->erb_id)->toBeNull();
});

it('rejects a work order linked to both an erb and a radio link', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user);
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'erb_id' => $erb->getKey(),
        'radio_link_id' => $radioLink->getKey(),
        'priority' => 'normal',
        'opened_at' => '2026-01-10',
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
        ],
    ])->assertSessionHasErrors(['erb_id', 'radio_link_id']);

    expect(WorkOrder::query()->count())->toBe(0);
});

it('clears the erb when the work order is switched to a radio link', function () {
    $user = User::factory()->create();
    $client = ownedClient($user);
    $catalogItem = ownedCatalogItem($user);
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $workOrder = WorkOrder::factory()->create([
        'user_id' => $user->getKey(),
        'client_id' => $client->getKey(),
        'erb_id' => $erb->getKey(),
    ]);

    $this->actingAs($user)->put("/admin/work-orders/{$workOrder->getKey()}", [
        'client_id' => $client->getKey(),
        'radio_link_id' => $radioLink->getKey(),
        'priority' => 'normal',
        'opened_at' => '2026-01-10',
        'items' => [
            ['catalog_item_id' => $catalogItem->getKey(), 'quantity' => 1],
        ],
    ])->assertSessionHas('status');

    $workOrder->refresh();

    expect($workOrder->radio_link_id)->toBe($radioLink->getKey())
        ->and($workOrder->erb_id)->toBeNull();
});
