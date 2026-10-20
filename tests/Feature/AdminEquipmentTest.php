<?php

use App\Models\Equipment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the equipment area', function () {
    $this->get('/admin/equipments')->assertRedirect('/login');
});

it('lists only the equipments the user owns', function () {
    $owner = User::factory()->create();

    Equipment::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Rádio do Titular']);
    Equipment::factory()->create(['name' => 'Rádio Alheio']);

    $this->actingAs($owner)
        ->get('/admin/equipments')
        ->assertOk()
        ->assertSee('Rádio do Titular')
        ->assertDontSee('Rádio Alheio');
});

it('filters the equipment list by search term', function () {
    $owner = User::factory()->create();

    Equipment::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Antena Alpha', 'serial_number' => 'SN-ALPHA']);
    Equipment::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Fonte Beta', 'serial_number' => 'SN-BETA']);

    $this->actingAs($owner)
        ->get('/admin/equipments?search=Alpha')
        ->assertOk()
        ->assertSee('Antena Alpha')
        ->assertDontSee('Fonte Beta');
});

it('filters the equipment list by status', function () {
    $owner = User::factory()->create();

    Equipment::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Equipamento Ativo']);
    Equipment::factory()->maintenance()->create(['user_id' => $owner->getKey(), 'name' => 'Equipamento em Manutenção']);

    $this->actingAs($owner)
        ->get('/admin/equipments?status=maintenance')
        ->assertOk()
        ->assertSee('Equipamento em Manutenção')
        ->assertDontSee('Equipamento Ativo');
});

it('renders the equipment list in the alternate view modes', function () {
    $owner = User::factory()->create();

    Equipment::factory()->create(['user_id' => $owner->getKey(), 'name' => 'Antena Alpha']);

    $this->actingAs($owner)->get('/admin/equipments?view=cards')->assertOk()->assertSee('Antena Alpha');
    $this->actingAs($owner)->get('/admin/equipments?view=compact')->assertOk()->assertSee('Antena Alpha');
});

it('renders the create form on the full page and inside the list modal', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/admin/equipments/create')
        ->assertOk()
        ->assertSee('Cadastrar equipamento')
        ->assertSee(route('admin.equipments.store'), false);

    $this->actingAs($user)
        ->get('/admin/equipments')
        ->assertOk()
        ->assertSee('data-modal-open="equipment-modal"', false)
        ->assertSee('id="equipment-modal"', false)
        ->assertSee(route('admin.equipments.store'), false);
});

it('renders edit modals on the equipment list', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get('/admin/equipments')
        ->assertOk()
        ->assertSee('data-modal-open="equipment-edit-modal-'.$equipment->getKey().'"', false)
        ->assertSee('id="equipment-edit-modal-'.$equipment->getKey().'"', false);
});

it('creates an equipment owned by the authenticated user', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/equipments', [
        'name' => 'Rádio Base Alpha',
        'category' => 'Rádio',
        'manufacturer' => 'Fabricante X',
        'model' => 'RX-1000',
        'serial_number' => 'SN-123456',
        'description' => 'Rádio para consulta de campo.',
        'notes' => 'Datasheet disponível.',
    ]);

    $equipment = Equipment::query()->firstOrFail();

    $response->assertRedirect(route('admin.equipments.show', $equipment));

    expect($equipment->user_id)->toBe($user->getKey())
        ->and($equipment->name)->toBe('Rádio Base Alpha')
        ->and($equipment->code)->toBe('EQP-0001')
        ->and($equipment->serial_number)->toBe('SN-123456')
        ->and($equipment->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.equipments.show', $equipment))
        ->assertOk()
        ->assertSee('Rádio Base Alpha')
        ->assertSee('RX-1000')
        ->assertSee('SN-123456');
});

it('generates a new sequential code for each equipment', function () {
    $user = User::factory()->create();

    Equipment::factory()->create(['user_id' => $user->getKey(), 'code' => 'EQP-0009']);

    $this->actingAs($user)->post('/admin/equipments', [
        'name' => 'Segundo equipamento',
    ])->assertRedirect();

    expect(Equipment::query()->latest('id')->firstOrFail()->code)->toBe('EQP-0002');
});

it('defaults the equipment status to active when it is not sent', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/equipments', [
        'name' => 'Equipamento sem status',
    ])->assertRedirect();

    expect(Equipment::query()->firstOrFail()->status->value)->toBe('active');
});

it('rejects an equipment without a name or with invalid fields', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/equipments', [])
        ->assertSessionHasErrors('name');

    $this->actingAs($user)
        ->post('/admin/equipments', [
            'name' => 'Equipamento Inválido',
            'status' => 'unknown',
        ])
        ->assertSessionHasErrors('status');

    expect(Equipment::query()->count())->toBe(0);
});

it('forbids a stranger from managing the equipment', function () {
    $stranger = User::factory()->create();
    $equipment = Equipment::factory()->create();

    $this->actingAs($stranger)->get("/admin/equipments/{$equipment->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/equipments/{$equipment->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/equipments/{$equipment->getKey()}", ['name' => 'Tentativa'])->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/equipments/{$equipment->getKey()}")->assertForbidden();
});

it('updates the equipment details', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->get("/admin/equipments/{$equipment->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/equipments/{$equipment->getKey()}", [
            'name' => 'Nome Atualizado',
            'manufacturer' => 'Novo Fabricante',
            'status' => 'maintenance',
        ])
        ->assertSessionHas('status');

    $equipment->refresh();

    expect($equipment->name)->toBe('Nome Atualizado')
        ->and($equipment->manufacturer)->toBe('Novo Fabricante')
        ->and($equipment->status->value)->toBe('maintenance');
});

it('soft deletes the equipment', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/equipments/{$equipment->getKey()}")
        ->assertRedirect(route('admin.equipments.index'));

    expect($equipment->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/equipments')->assertOk()->assertDontSee($equipment->name);
});

it('opens the edit modal from the equipment page and edit route', function () {
    $user = User::factory()->create();
    $equipment = Equipment::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.equipments.show', $equipment))
        ->assertOk()
        ->assertSee('data-modal-open="equipment-edit-modal-'.$equipment->getKey().'"', false)
        ->assertSee(route('admin.equipments.update', $equipment), false);

    $this->actingAs($user)
        ->get(route('admin.equipments.edit', $equipment))
        ->assertOk()
        ->assertSee('data-open', false)
        ->assertSee(route('admin.equipments.update', $equipment), false);
});
