<?php

use App\Models\Colaborador;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to open the colaboradores area', function () {
    $this->get('/admin/colaboradores')->assertRedirect('/login');
});

it('lists only the colaboradores the user owns', function () {
    $owner = User::factory()->create();

    Colaborador::factory()->create(['user_id' => $owner->getKey(), 'name' => 'João da Silva']);
    Colaborador::factory()->create(['name' => 'Maria Alheia']);

    $this->actingAs($owner)
        ->get('/admin/colaboradores')
        ->assertOk()
        ->assertSee('João da Silva')
        ->assertDontSee('Maria Alheia');
});

it('filters the colaborador list by search term and status', function () {
    $owner = User::factory()->create();

    Colaborador::factory()->create([
        'user_id' => $owner->getKey(),
        'name' => 'João da Silva',
        'role' => 'Técnico N2',
        'status' => 'active',
    ]);
    Colaborador::factory()->inactive()->create([
        'user_id' => $owner->getKey(),
        'name' => 'Maria Souza',
        'role' => 'Coordenadora',
    ]);

    $this->actingAs($owner)
        ->get('/admin/colaboradores?search=Técnico')
        ->assertOk()
        ->assertSee('João da Silva')
        ->assertDontSee('Maria Souza');

    $this->actingAs($owner)
        ->get('/admin/colaboradores?status=inactive')
        ->assertOk()
        ->assertSee('Maria Souza')
        ->assertDontSee('João da Silva');
});

it('filters the colaborador list by regional and uf', function () {
    $owner = User::factory()->create();

    Colaborador::factory()->create([
        'user_id' => $owner->getKey(),
        'name' => 'João da Silva',
        'regional' => 'NO',
        'uf' => 'PA',
    ]);
    Colaborador::factory()->create([
        'user_id' => $owner->getKey(),
        'name' => 'Maria Souza',
        'regional' => 'SP',
        'uf' => 'SP',
    ]);

    $this->actingAs($owner)
        ->get('/admin/colaboradores?regional=NO')
        ->assertOk()
        ->assertSee('João da Silva')
        ->assertDontSee('Maria Souza');

    $this->actingAs($owner)
        ->get('/admin/colaboradores?uf=SP')
        ->assertOk()
        ->assertSee('Maria Souza')
        ->assertDontSee('João da Silva');

    $this->actingAs($owner)
        ->get('/admin/colaboradores?regional=INEXISTENTE&uf=ZZ')
        ->assertOk()
        ->assertSee('João da Silva')
        ->assertSee('Maria Souza');
});

it('renders the colaborador list in the alternate view modes', function () {
    $owner = User::factory()->create();

    Colaborador::factory()->create(['user_id' => $owner->getKey(), 'name' => 'João da Silva']);

    $this->actingAs($owner)->get('/admin/colaboradores?view=cards')->assertOk()->assertSee('João da Silva');
    $this->actingAs($owner)->get('/admin/colaboradores?view=compact')->assertOk()->assertSee('João da Silva');
});

it('creates a colaborador owned by the authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/admin/colaboradores/create')->assertOk();

    $response = $this->actingAs($user)->post('/admin/colaboradores', [
        'name' => 'João da Silva',
        'contract_regime' => 'CLT',
        'regional' => 'NO',
        'uf' => 'PA',
        'pis' => '123.45678.90-1',
        'role' => 'Técnico N2',
        'document' => '123.456.789-00',
        'cnpj' => '12.345.678/0001-95',
        'rg' => '3062601',
        'rg_issuer' => 'SSP/PA',
        'birth_date' => '1978-04-13',
        'mother_name' => 'Maria Lúcia da Costa Ferreira',
        'phone' => '(11) 99999-9999',
        'email' => 'joao@empresa.com.br',
        'status' => 'active',
        'notes' => 'Atua na região central.',
    ]);

    $colaborador = Colaborador::query()->firstOrFail();

    $response->assertRedirect(route('admin.colaboradores.show', $colaborador));

    expect($colaborador->user_id)->toBe($user->getKey())
        ->and($colaborador->name)->toBe('João da Silva')
        ->and($colaborador->contract_regime->value)->toBe('CLT')
        ->and($colaborador->regional)->toBe('NO')
        ->and($colaborador->uf->value)->toBe('PA')
        ->and($colaborador->pis)->toBe('123.45678.90-1')
        ->and($colaborador->role)->toBe('Técnico N2')
        ->and($colaborador->document)->toBe('123.456.789-00')
        ->and($colaborador->cnpj)->toBe('12.345.678/0001-95')
        ->and($colaborador->rg)->toBe('3062601')
        ->and($colaborador->rg_issuer)->toBe('SSP/PA')
        ->and($colaborador->birth_date->format('Y-m-d'))->toBe('1978-04-13')
        ->and($colaborador->mother_name)->toBe('Maria Lúcia da Costa Ferreira')
        ->and($colaborador->email)->toBe('joao@empresa.com.br')
        ->and($colaborador->status->value)->toBe('active');

    $this->actingAs($user)
        ->get(route('admin.colaboradores.show', $colaborador))
        ->assertOk()
        ->assertSee('João da Silva')
        ->assertSee('joao@empresa.com.br')
        ->assertSee('SSP/PA');
});

it('rejects an invalid contract regime', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/colaboradores', [
            'name' => 'João da Silva',
            'contract_regime' => 'CLTZ',
        ])
        ->assertSessionHasErrors('contract_regime');

    expect(Colaborador::query()->count())->toBe(0);
});

it('rejects an invalid UF', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/colaboradores', [
            'name' => 'João da Silva',
            'uf' => 'ZZ',
        ])
        ->assertSessionHasErrors('uf');

    expect(Colaborador::query()->count())->toBe(0);
});

it('defaults the colaborador status to active when it is not sent', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->post('/admin/colaboradores', [
        'name' => 'Sem Status',
    ])->assertRedirect();

    expect(Colaborador::query()->firstOrFail()->status->value)->toBe('active');
});

it('rejects a colaborador without a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/colaboradores', [])
        ->assertSessionHasErrors(['name']);

    expect(Colaborador::query()->count())->toBe(0);
});

it('rejects an invalid email address', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/colaboradores', ['name' => 'João da Silva', 'email' => 'não-é-um-email'])
        ->assertSessionHasErrors(['email']);

    expect(Colaborador::query()->count())->toBe(0);
});

it('rejects a duplicate document owned by the same user', function () {
    $user = User::factory()->create();

    Colaborador::factory()->create(['user_id' => $user->getKey(), 'document' => '123.456.789-00']);

    $this->actingAs($user)
        ->post('/admin/colaboradores', ['name' => 'Duplicado', 'document' => '123.456.789-00'])
        ->assertSessionHasErrors('document');
});

it('forbids a stranger from managing the colaborador', function () {
    $stranger = User::factory()->create();
    $colaborador = Colaborador::factory()->create();

    $this->actingAs($stranger)->get("/admin/colaboradores/{$colaborador->getKey()}")->assertForbidden();
    $this->actingAs($stranger)->get("/admin/colaboradores/{$colaborador->getKey()}/edit")->assertForbidden();
    $this->actingAs($stranger)->put("/admin/colaboradores/{$colaborador->getKey()}", ['name' => 'Tentativa'])->assertForbidden();
    $this->actingAs($stranger)->delete("/admin/colaboradores/{$colaborador->getKey()}")->assertForbidden();
});

it('updates the colaborador details', function () {
    $user = User::factory()->create();
    $colaborador = Colaborador::factory()->create(['user_id' => $user->getKey(), 'name' => 'João da Silva']);

    $this->actingAs($user)->get("/admin/colaboradores/{$colaborador->getKey()}/edit")->assertOk();

    $this->actingAs($user)
        ->put("/admin/colaboradores/{$colaborador->getKey()}", [
            'name' => 'Nome Atualizado',
            'status' => 'inactive',
        ])
        ->assertSessionHas('status');

    $colaborador->refresh();

    expect($colaborador->name)->toBe('Nome Atualizado')
        ->and($colaborador->status->value)->toBe('inactive');
});

it('soft deletes the colaborador', function () {
    $user = User::factory()->create();
    $colaborador = Colaborador::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/colaboradores/{$colaborador->getKey()}")
        ->assertRedirect(route('admin.colaboradores.index'));

    expect($colaborador->refresh()->trashed())->toBeTrue();

    $this->actingAs($user)->get('/admin/colaboradores')->assertOk()->assertDontSee($colaborador->name);
});
