<?php

use App\Enums\WorkOrderItemStatus;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to update an item status', function () {
    $workOrder = WorkOrder::factory()->create();
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'completed'])
        ->assertRedirect('/login');
});

it('updates the status of a single item', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'completed'])
        ->assertRedirect(route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()]))
        ->assertSessionHas('status');

    expect($item->refresh()->status)->toBe(WorkOrderItemStatus::Completed);
});

it('accepts the rejected status', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'rejected'])
        ->assertRedirect(route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()]))
        ->assertSessionHas('status');

    expect($item->refresh()->status)->toBe(WorkOrderItemStatus::Rejected);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Rejeitado');
});

it('validates the item status value', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'invalid'])
        ->assertSessionHasErrors('status');

    expect($item->refresh()->status)->toBe(WorkOrderItemStatus::Pending);
});

it('forbids a stranger from updating an item status', function () {
    $workOrder = WorkOrder::factory()->create();
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs(User::factory()->create())
        ->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'completed'])
        ->assertForbidden();

    expect($item->refresh()->status)->toBe(WorkOrderItemStatus::Pending);
});

it('does not update an item status from another work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $other = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $other->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->put(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), ['status' => 'completed'])
        ->assertNotFound();

    expect($item->refresh()->status)->toBe(WorkOrderItemStatus::Pending);
});

it('shows the status form in the item details modal', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee(route('admin.work-orders.items.status.update', ['workOrder' => $workOrder, 'item' => $item]), false)
        ->assertSee('onchange="this.form.submit()"', false)
        ->assertSee('Em andamento');
});
