<?php

use App\Models\Document;
use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage datasheets', function () {
    $equipment = Equipment::factory()->create();

    $this->post("/admin/equipments/{$equipment->getKey()}/documents", ['documents' => []])
        ->assertRedirect('/login');
});

it('uploads datasheets to the equipment', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.equipments.show', $equipment))
        ->post("/admin/equipments/{$equipment->getKey()}/documents", [
            'documents' => [
                UploadedFile::fake()->create('datasheet-radio.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('manual.pdf', 50, 'application/pdf'),
            ],
        ])
        ->assertRedirect(route('admin.equipments.show', $equipment))
        ->assertSessionHas('status');

    expect($equipment->documents()->count())->toBe(2);

    $equipment->documents->each(function (Document $document) use ($equipment, $user): void {
        expect($document->equipment_id)->toBe($equipment->getKey())
            ->and($document->user_id)->toBe($user->getKey());

        Storage::disk('local')->assertExists($document->path);
    });
});

it('validates the uploaded datasheet type', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/equipments/{$equipment->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');

    expect($equipment->documents()->count())->toBe(0);
});

it('forbids a stranger from uploading datasheets', function () {
    $equipment = Equipment::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/equipments/{$equipment->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('datasheet.pdf', 100, 'application/pdf')],
        ])
        ->assertForbidden();
});

it('removes a datasheet from the equipment', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['equipment_id' => $equipment->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.equipments.show', $equipment))
        ->delete("/admin/equipments/{$equipment->getKey()}/documents/{$document->getKey()}")
        ->assertRedirect(route('admin.equipments.show', $equipment))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('does not remove a datasheet that belongs to another equipment', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);
    $other = Equipment::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['equipment_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/equipments/{$equipment->getKey()}/documents/{$document->getKey()}")
        ->assertNotFound();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('forbids a stranger from removing a datasheet', function () {
    $owner = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $owner->getKey()]);
    $document = Document::factory()->for($owner)->create(['equipment_id' => $equipment->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/equipments/{$equipment->getKey()}/documents/{$document->getKey()}")
        ->assertForbidden();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('renders the datasheets on the equipment page', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create([
        'equipment_id' => $equipment->getKey(),
        'original_name' => 'datasheet-tecnico.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('admin.equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Datasheets')
        ->assertSee('datasheet-tecnico.pdf')
        ->assertSee(route('admin.files.preview', $document), false)
        ->assertSee(route('admin.files.download', $document), false);
});

it('shows the datasheet upload form on the equipment page', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.equipments.show', $equipment))
        ->assertOk()
        ->assertSee(route('admin.equipments.documents.store', $equipment), false)
        ->assertSee('Nenhum datasheet anexado.');
});
