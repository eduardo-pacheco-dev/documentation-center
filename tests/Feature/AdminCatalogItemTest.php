<?php

use App\Models\CatalogItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the catalog area', function () {
    $this->get('/admin/catalog')->assertRedirect('/login');
});

it('lists only the catalog items the user owns', function () {
    $owner = User::factory()->create();

    CatalogItem::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Item do Titular']);
    CatalogItem::factory()->create(['name' => 'Item Alheio']);

    $this->actingAs($owner)
        ->get('/admin/catalog')
        ->assertOk()
        ->assertSee('Item do Titular')
        ->assertDontSee('Item Alheio');
});

it('filters the catalog list by search term', function () {
    $owner = User::factory()->create();

    CatalogItem::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Cimento Alpha']);
    CatalogItem::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Telha Beta']);

    $this->actingAs($owner)
        ->get('/admin/catalog?search=Alpha')
        ->assertOk()
        ->assertSee('Cimento Alpha')
        ->assertDontSee('Telha Beta');
});

it('filters the catalog list by type', function () {
    $owner = User::factory()->create();

    CatalogItem::factory()->product()->create(['user_id' => $owner->getKey(), 'name' => 'Consulta de Produto']);
    CatalogItem::factory()->service()->create(['user_id' => $owner->getKey(), 'name' => 'Consultoria de Serviço']);

    $this->actingAs($owner)
        ->get('/admin/catalog?type=service')
        ->assertOk()
        ->assertSee('Consultoria de Serviço')
        ->assertDontSee('Consulta de Produto');
});

it('renders the catalog list in the alternate view modes', function () {
    $owner = User::factory()->create();

    CatalogItem::factory()->product()->create(['user_id' => $owner->getKey(), 'name' => 'Cimento Alpha']);

    $this->actingAs($owner)->get('/admin/catalog?view=cards')->assertOk()->assertSee('Cimento Alpha');
    $this->actingAs($owner)->get('/admin/catalog?view=compact')->assertOk()->assertSee('Cimento Alpha');
});

it('creates a product owned by the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/catalog/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/catalog', [
        'type' => 'product',
        'name' => 'Cimento CP-II 50kg',
        'code' => 'SC-1001',
        'unit' => 'un',
        'price' => '39.90',
        'cost' => '27.50',
        'stock_quantity' => '120',
        'description' => 'Cimento estrutural de alta resistência.',
        'notes' => 'Fornecedor principal.',
        'status' => 'active',
    ]);

    $item = CatalogItem::query()->firstOrFail();

    $response->assertRedirect(route('admin.catalog.show', $item));

    expect($item->user_id)->toBe($user->getKey())
        ->and($item->name)->toBe('Cimento CP-II 50kg')
        ->and($item->type->value)->toBe('product')
        ->and($item->stock_quantity)->toBe('120.00')
        ->and($item->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.catalog.show', $item))
        ->assertOk()
        ->assertSee('Cimento CP-II 50kg')
        ->assertSee('R$ 39,90');
});

it('clears the stock when the catalog item is a service', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/catalog', [
        'type' => 'service',
        'name' => 'Consultoria Estrutural',
        'price' => '500.00',
        'stock_quantity' => '10',
    ])->assertRedirect();

    expect(CatalogItem::query()->firstOrFail()->stock_quantity)->toBeNull();
});

it('defaults the catalog item status to active when it is not sent', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/catalog', [
        'type' => 'product',
        'name' => 'Item Sem Status',
    ])->assertRedirect();

    expect(CatalogItem::query()->firstOrFail()->status->value)->toBe('active');
});

it('rejects a catalog item without a name or a type', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/catalog', [])
        ->assertSessionHasErrors(['name', 'type']);

    expect(CatalogItem::query()->count())->toBe(0);
});

it('rejects invalid catalog item fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/catalog', [
            'type' => 'unknown',
            'name' => 'Campos Inválidos',
            'price' => 'abc',
            'cost' => '-1',
            'status' => 'unknown',
        ])
        ->assertSessionHasErrors(['type', 'price', 'cost', 'status']);
});

it('forbids a stranger from managing the catalog item', function () {
    $stranger = User::factory()->create();
    $item = CatalogItem::factory()->create();

    $this->actingAs($stranger)->get("/admin/catalog/{$item->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/catalog/{$item->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/catalog/{$item->getKey()}", ['type' => 'product', 'name' => 'Tentativa'])->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/catalog/{$item->getKey()}")->assertForbidden();
});

it('updates the catalog item details', function () {
    $user = User::factory()->create();
    $item = CatalogItem::factory()->product()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->get("/admin/catalog/{$item->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/catalog/{$item->getKey()}", [
            'type' => 'service',
            'name' => 'Nome Atualizado',
            'status' => 'inactive',
        ])
        ->assertSessionHas('status');

    $item->refresh();

    expect($item->name)->toBe('Nome Atualizado')
        ->and($item->type->value)->toBe('service')
        ->and($item->stock_quantity)->toBeNull()
        ->and($item->status->value)->toBe('inactive');
});

it('soft deletes the catalog item', function () {
    $user = User::factory()->create();
    $item = CatalogItem::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/catalog/{$item->getKey()}")
        ->assertRedirect(route('admin.catalog.index'));

    expect($item->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/catalog')->assertOk()->assertDontSee($item->name);
});
