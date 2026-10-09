<?php

use App\Models\Client;
use App\Models\Erb;
use App\Models\Project;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the ERBs area', function () {
    $this->get('/admin/erbs')->assertRedirect('/login');
});

it('lists only the ERBs the user owns', function () {
    $owner = User::factory()->create();

    Erb::factory()->create(['user_id' => $owner->getKey(), 'code' => 'ERB-00001', 'name' => 'ERB do Titular']);
    Erb::factory()->create(['code' => 'ERB-09999', 'name' => 'ERB Alheia']);

    $this->actingAs($owner)
        ->get('/admin/erbs')
        ->assertOk()
        ->assertSee('ERB-00001')
        ->assertSee('ERB do Titular')
        ->assertDontSee('ERB Alheia');
});

it('filters the ERB list by search term and status', function () {
    $owner = User::factory()->create();

    Erb::factory()->create([
        'user_id' => $owner->getKey(),
        'code' => 'ERB-00001',
        'name' => 'Alpha Norte',
        'status' => 'active',
    ]);
    Erb::factory()->maintenance()->create([
        'user_id' => $owner->getKey(),
        'code' => 'ERB-00002',
        'name' => 'Beta Sul',
    ]);

    $this->actingAs($owner)
        ->get('/admin/erbs?search=Alpha')
        ->assertOk()
        ->assertSee('Alpha Norte')
        ->assertDontSee('Beta Sul');

    $this->actingAs($owner)
        ->get('/admin/erbs?status=maintenance')
        ->assertOk()
        ->assertSee('Beta Sul')
        ->assertDontSee('Alpha Norte');
});

it('renders the ERB list in the alternate view modes', function () {
    $owner = User::factory()->create();

    Erb::factory()->create(['user_id' => $owner->getKey(), 'code' => 'ERB-00001', 'name' => 'Alpha Norte']);

    $this->actingAs($owner)->get('/admin/erbs?view=cards')->assertOk()->assertSee('ERB-00001');
    $this->actingAs($owner)->get('/admin/erbs?view=compact')->assertOk()->assertSee('ERB-00001');
});

it('creates an ERB owned by the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/erbs/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/erbs', [
        'code' => 'ERB-00042',
        'name' => 'ERB Centro',
        'operator' => 'Vivo',
        'technology' => '4G/5G',
        'status' => 'active',
        'street' => 'Avenida Paulista',
        'number' => '1000',
        'complement' => 'Torre A',
        'neighborhood' => 'Bela Vista',
        'city' => 'São Paulo',
        'state' => 'SP',
        'zip' => '01310-100',
        'latitude' => '-23.5614140',
        'longitude' => '-46.6558810',
        'notes' => 'Cobertura da região central.',
    ]);

    $erb = Erb::query()->firstOrFail();

    $response->assertRedirect(route('admin.erbs.show', $erb));

    expect($erb->user_id)->toBe($user->getKey())
        ->and($erb->code)->toBe('ERB-00042')
        ->and($erb->name)->toBe('ERB Centro')
        ->and($erb->operator)->toBe('Vivo')
        ->and($erb->city)->toBe('São Paulo')
        ->and($erb->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.erbs.show', $erb))
        ->assertOk()
        ->assertSee('ERB-00042')
        ->assertSee('Vivo');
});

it('defaults the ERB status to active when it is not sent', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/erbs', [
        'code' => 'ERB-00007',
        'name' => 'Sem Status',
    ])->assertRedirect();

    expect(Erb::query()->firstOrFail()->status->value)->toBe('active');
});

it('rejects an ERB without a code or name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/erbs', [])
        ->assertSessionHasErrors(['code', 'name']);

    expect(Erb::query()->count())->toBe(0);
});

it('rejects a duplicate code owned by the same user', function () {
    $user = User::factory()->create();

    Erb::factory()->create(['user_id' => $user->getKey(), 'code' => 'ERB-00042']);

    $this->actingAs($user)
        ->post('/admin/erbs', ['code' => 'ERB-00042', 'name' => 'Duplicada'])
        ->assertSessionHasErrors('code');
});

it('rejects invalid coordinates', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/erbs', [
            'code' => 'ERB-00010',
            'name' => 'Coordenadas Inválidas',
            'latitude' => '-120',
            'longitude' => '200',
        ])
        ->assertSessionHasErrors(['latitude', 'longitude']);
});

it('forbids a stranger from managing the ERB', function () {
    $stranger = User::factory()->create();
    $erb = Erb::factory()->create();

    $this->actingAs($stranger)->get("/admin/erbs/{$erb->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/erbs/{$erb->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/erbs/{$erb->getKey()}", ['code' => 'ERB-1', 'name' => 'Tentativa'])->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/erbs/{$erb->getKey()}")->assertForbidden();
});

it('updates the ERB details', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey(), 'code' => 'ERB-00042']);

    $this->actingAs($user)->get("/admin/erbs/{$erb->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/erbs/{$erb->getKey()}", [
            'code' => 'ERB-00042',
            'name' => 'Nome Atualizado',
            'status' => 'inactive',
        ])
        ->assertSessionHas('status');

    $erb->refresh();

    expect($erb->name)->toBe('Nome Atualizado')
        ->and($erb->status->value)->toBe('inactive');
});

it('soft deletes the ERB', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/erbs/{$erb->getKey()}")
        ->assertRedirect(route('admin.erbs.index'));

    expect($erb->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/erbs')->assertOk()->assertDontSee($erb->code);
});

it('links an ERB to the work orders the user owns', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $owner->getKey()]);
    $erb = Erb::factory()->create(['user_id' => $owner->getKey()]);

    $this->actingAs($owner)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'erb_id' => $erb->getKey(),
        'title' => 'Manutenção na torre',
        'priority' => 'normal',
        'status' => 'open',
        'opened_at' => '2026-01-10',
        'items' => [
            ['description' => 'Mão de obra', 'unit' => 'h', 'quantity' => 1, 'unit_price' => 100],
        ],
    ])->assertRedirect();

    expect(WorkOrder::query()->firstOrFail()->erb_id)->toBe($erb->getKey());
});

it('rejects an ERB that belongs to another user on a work order', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $owner->getKey()]);
    $foreignErb = Erb::factory()->create();

    $this->actingAs($owner)->post('/admin/work-orders', [
        'client_id' => $client->getKey(),
        'erb_id' => $foreignErb->getKey(),
        'title' => 'Manutenção na torre',
        'priority' => 'normal',
        'opened_at' => '2026-01-10',
        'items' => [
            ['description' => 'Mão de obra', 'unit' => 'h', 'quantity' => 1, 'unit_price' => 100],
        ],
    ])->assertSessionHasErrors('erb_id');
});

it('links an ERB to a project', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)->post('/admin/projects', [
        'name' => 'Projeto da torre',
        'start_date' => '2026-01-10',
        'erb_id' => $erb->getKey(),
    ])->assertRedirect();

    expect(Project::query()->firstOrFail()->erb_id)->toBe($erb->getKey());
});
