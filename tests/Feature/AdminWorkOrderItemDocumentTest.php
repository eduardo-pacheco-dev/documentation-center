<?php

use App\Models\Document;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\WorkOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage item attachments', function () {
    $workOrder = WorkOrder::factory()->create();
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->post(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), ['documents' => []])
        ->assertRedirect('/login');
});

it('uploads attachments to an item', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), [
            'documents' => [
                UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('fotografia.jpg', 50),
            ],
        ])
        ->assertRedirect(route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()]))
        ->assertSessionHas('status');

    expect($item->documents()->count())->toBe(2);

    $item->documents->each(function (Document $document) use ($item, $user): void {
        expect($document->work_order_item_id)->toBe($item->getKey())
            ->and($document->radio_link_id)->toBeNull()
            ->and($document->erb_id)->toBeNull()
            ->and($document->user_id)->toBe($user->getKey());

        Storage::disk('local')->assertExists($document->path);
    });
});

it('validates the uploaded item attachment type', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');

    expect($item->documents()->count())->toBe(0);
});

it('forbids a stranger from uploading item attachments', function () {
    $workOrder = WorkOrder::factory()->create();
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs(User::factory()->create())
        ->post(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), [
            'documents' => [UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf')],
        ])
        ->assertForbidden();
});

it('does not upload an attachment to an item from another work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $other = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $other->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), [
            'documents' => [UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf')],
        ])
        ->assertNotFound();

    expect($item->documents()->count())->toBe(0);
});

it('removes an attachment from an item', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $document = Document::factory()->for($user)->create(['work_order_item_id' => $item->getKey()]);

    $this->actingAs($user)
        ->delete(route('admin.work-orders.items.documents.destroy', ['workOrder' => $workOrder, 'item' => $item, 'document' => $document]))
        ->assertRedirect(route('admin.work-orders.show', ['workOrder' => $workOrder, 'item' => $item->getKey()]))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('does not remove an attachment that belongs to another item', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $other = $workOrder->items()->create(['description' => 'Outro', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $document = Document::factory()->for($user)->create(['work_order_item_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete(route('admin.work-orders.items.documents.destroy', ['workOrder' => $workOrder, 'item' => $item, 'document' => $document]))
        ->assertNotFound();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('forbids a stranger from removing an item attachment', function () {
    $owner = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $owner->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $document = Document::factory()->for($owner)->create(['work_order_item_id' => $item->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.work-orders.items.documents.destroy', ['workOrder' => $workOrder, 'item' => $item, 'document' => $document]))
        ->assertForbidden();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('renders the item details modal with its attachments', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Item com anexo', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    $document = Document::factory()->for($user)->create([
        'work_order_item_id' => $item->getKey(),
        'original_name' => 'laudo-tecnico.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('data-modal-open="work-order-item-modal-'.$item->getKey().'"', false)
        ->assertSee(route('admin.work-orders.items.documents.store', ['workOrder' => $workOrder, 'item' => $item]), false)
        ->assertSee('laudo-tecnico.pdf')
        ->assertSee(route('admin.files.preview', $document), false);
});

it('lists the item attachments in the work order attachments section', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $item = $workOrder->items()->create(['description' => 'Instalação de torre', 'quantity' => 1, 'unit_price' => 10, 'total' => 10]);
    Document::factory()->for($user)->create([
        'work_order_item_id' => $item->getKey(),
        'original_name' => 'foto-torre.jpg',
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('foto-torre.jpg')
        ->assertSee('Instalação de torre');
});

it('shows the empty attachment state in the item details modal', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    WorkOrderItem::factory()->for($workOrder)->create(['description' => 'Item sem anexo']);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Nenhum anexo cadastrado.');
});
