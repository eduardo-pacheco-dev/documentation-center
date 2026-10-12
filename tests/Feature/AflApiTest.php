<?php

use App\Models\Erb;
use App\Models\Form;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('afl endpoints require a valid token', function () {
    $this->getJson('/api/v1/sites')->assertUnauthorized();
    $this->getJson('/api/v1/projects')->assertUnauthorized();
    $this->getJson('/api/v1/forms')->assertUnauthorized();
    $this->patchJson('/api/v1/forms/FRM-0001')->assertUnauthorized();
    $this->postJson('/api/v1/uploads')->assertUnauthorized();
});

test('users can list their own sites with json-server pagination', function () {
    $user = User::factory()->create();

    $codes = ['PAMBA45', 'PAMBA41', 'PAMBA10'];
    foreach ($codes as $code) {
        Erb::factory()->for($user)->create(['code' => $code]);
    }
    Erb::factory()->create(['code' => 'ALHEIA01']);

    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/sites?_page=1&_limit=2');

    $response->assertOk()
        ->assertHeader('X-Total-Count', '3')
        ->assertJsonCount(2)
        ->assertDontSee('ALHEIA01');

    expect($response->json('0.id'))->toBe('PAMBA10');

    $this->withToken($token)->getJson('/api/v1/sites?_page=2&_limit=2')
        ->assertOk()
        ->assertJsonCount(1);
});

test('users can filter sites by the assigned person', function () {
    $user = User::factory()->create();

    Erb::factory()->for($user)->create([
        'code' => 'PAMBA45',
        'assigned_to' => 'dev@afl.com.br',
        'nokia_smp_id' => 'SMP1000',
        'service_order_id' => 'OS-10000',
        'start_date' => '2025-03-01',
        'end_date' => '2025-11-01',
    ]);
    Erb::factory()->for($user)->create(['code' => 'PAMBA41', 'assigned_to' => 'outro@afl.com.br']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/sites?assignedTo=dev@afl.com.br')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'PAMBA45')
        ->assertJsonPath('0.nokiaSmpId', 'SMP1000')
        ->assertJsonPath('0.serviceOrderId', 'OS-10000')
        ->assertJsonPath('0.startDate', '2025-03-01')
        ->assertJsonPath('0.endDate', '2025-11-01');
});

test('users can filter their sites by the site and end identifiers', function () {
    $user = User::factory()->create();

    Erb::factory()->for($user)->create([
        'code' => 'PAMBA45',
        'site_id' => 'PABLM',
        'end_id' => 'PABLM_0001',
    ]);
    Erb::factory()->for($user)->create([
        'code' => 'PAMBA41',
        'site_id' => 'PABXA',
        'end_id' => 'PABXA_0002',
    ]);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/sites?siteId=PABLM')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'PAMBA45')
        ->assertJsonPath('0.siteId', 'PABLM')
        ->assertJsonPath('0.endId', 'PABLM_0001');

    $this->withToken($token)
        ->getJson('/api/v1/sites?endId=PABXA_0002')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'PAMBA41');
});

test('users can fetch a single site by code', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->for($user)->create(['code' => 'PAMBA45', 'city' => 'Salvador', 'state' => 'BA']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/sites/'.$erb->code)
        ->assertOk()
        ->assertJsonPath('id', 'PAMBA45')
        ->assertJsonPath('city', 'Salvador');

    $this->withToken($token)->getJson('/api/v1/sites/ALHEIA01')->assertNotFound();
});

test('users can list their own projects and filter by site', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->for($user)->create(['code' => 'PAMBA45']);

    $project = Project::factory()->for($user)->create(['code' => 'PRJ-0001', 'erb_id' => $erb->getKey()]);
    Project::factory()->for($user)->create(['code' => 'PRJ-0002', 'erb_id' => null]);

    $foreign = Project::factory()->create(['code' => 'PRJ-ALHEIO']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/projects')
        ->assertOk()
        ->assertJsonCount(2)
        ->assertDontSee($foreign->code);

    $this->withToken($token)
        ->getJson('/api/v1/projects?siteId=PAMBA45')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'PRJ-0001')
        ->assertJsonPath('0.siteId', 'PAMBA45');
});

test('users can fetch a single project by code', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['code' => 'PRJ-0001', 'name' => 'PAMBA45 - Estrutura']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/projects/'.$project->code)
        ->assertOk()
        ->assertJsonPath('id', 'PRJ-0001')
        ->assertJsonPath('name', 'PAMBA45 - Estrutura');

    $this->withToken($token)->getJson('/api/v1/projects/PRJ-ALHEIO')->assertNotFound();
});

test('users can list their own forms including the steps', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['code' => 'PRJ-0001']);
    $form = Form::factory()->forProject($project)->create(['code' => 'FRM-0001']);

    Form::factory()->create(['code' => 'FRM-ALHEIO']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/forms')
        ->assertOk()
        ->assertJsonCount(1)
        ->assertJsonPath('0.id', 'FRM-0001')
        ->assertJsonPath('0.projectId', 'PRJ-0001')
        ->assertJsonStructure(['0' => ['id', 'projectId', 'name', 'type', 'steps']]);

    $this->withToken($token)
        ->getJson('/api/v1/forms?projectId=PRJ-0001')
        ->assertOk()
        ->assertJsonCount(1);

    expect($form->fresh()->steps[0]['id'])->toBe('S1');
});

test('users can fetch a single form by code', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['code' => 'PRJ-0001']);
    $form = Form::factory()->forProject($project)->create(['code' => 'FRM-0001']);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/forms/'.$form->code)
        ->assertOk()
        ->assertJsonPath('id', 'FRM-0001');

    $this->withToken($token)->getJson('/api/v1/forms/FRM-ALHEIO')->assertNotFound();
});

test('users can update their forms with answers and photos', function () {
    $user = User::factory()->create();
    $project = Project::factory()->for($user)->create(['code' => 'PRJ-0001']);
    $form = Form::factory()->forProject($project)->create(['code' => 'FRM-0001', 'status' => 'Pendente']);

    $token = $user->createToken('mobile')->plainTextToken;

    $steps = [
        [
            'id' => 'FRM-0001-S1',
            'title' => 'Identificação',
            'fields' => [
                ['id' => 'F1-1', 'type' => 'text', 'label' => 'Área registrada', 'value' => 'Galpão A', 'status' => 'aprovado'],
                ['id' => 'F1-2', 'type' => 'image', 'label' => 'Foto', 'images' => ['https://example.test/storage/uploads/foto.jpg'], 'status' => 'enviado'],
            ],
        ],
    ];

    $this->withToken($token)
        ->patchJson('/api/v1/forms/'.$form->code, [
            'status' => 'Preenchido',
            'steps' => $steps,
        ])
        ->assertOk()
        ->assertJsonPath('status', 'Preenchido')
        ->assertJsonPath('steps.0.fields.1.value', null)
        ->assertJsonPath('steps.0.fields.1.images.0', 'https://example.test/storage/uploads/foto.jpg');

    expect($form->fresh()->status)->toBe('Preenchido');
    expect($form->fresh()->steps)->toBe($steps);
});

test('users can not update a form owned by another user', function () {
    $form = Form::factory()->create(['status' => 'Pendente']);
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->patchJson('/api/v1/forms/'.$form->code, ['status' => 'Preenchido'])
        ->assertForbidden();
});

test('users can upload a photo and get a public url', function () {
    Storage::fake('public');

    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $upload = $this->withToken($token)->post('/api/v1/uploads', [
        'file' => UploadedFile::fake()->image('foto.jpg', 2, 2),
    ]);

    $upload->assertCreated()->assertJsonStructure(['url', 'path']);

    Storage::disk('public')->assertExists('uploads/'.basename(parse_url($upload->json('url'), PHP_URL_PATH)));

    $this->withToken($token)
        ->post('/api/v1/uploads', [
            'file' => UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf'),
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('file');
});
