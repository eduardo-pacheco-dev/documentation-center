<?php

use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('links endpoints require a valid token', function () {
    $this->getJson('/api/v1/links')->assertUnauthorized();
    $this->postJson('/api/v1/links', [
        'title' => 'Não autorizado',
        'type' => 'upload',
    ])->assertUnauthorized();
});

test('users can create a short link through the api', function () {
    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/links', [
        'title' => 'Envio de relatórios',
        'type' => 'upload',
        'max_uses' => 10,
        'expires_at' => '2026-12-31',
        'is_active' => true,
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Envio de relatórios')
        ->assertJsonPath('data.type', 'upload')
        ->assertJsonPath('data.max_uses', 10)
        ->assertJsonPath('data.has_password', false);

    $this->assertDatabaseHas('short_links', [
        'user_id' => $user->getKey(),
        'type' => 'upload',
        'title' => 'Envio de relatórios',
        'max_uses' => 10,
    ]);

    expect($response->json('data.code'))->not->toBeEmpty;
});

test('link creation validates the payload', function () {
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)->postJson('/api/v1/links', [
        'title' => '',
        'type' => 'bilhete',
        'password' => 'curto',
    ])->assertUnprocessable()->assertJsonValidationErrors(['title', 'type', 'password']);
});

test('users can list their own links only', function () {
    $user = User::factory()->create();
    ShortLink::factory()->count(3)->for($user)->create();
    $foreign = ShortLink::factory()->create();

    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/links');

    $response->assertOk()->assertJsonCount(3, 'data');
    expect($response->json('data.*.code'))->not->toContain($foreign->code);
});

test('users can not view a link owned by another user', function () {
    $shortLink = ShortLink::factory()->create();
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/links/'.$shortLink->getKey())
        ->assertForbidden();
});

test('users can view their own link with its documents', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->download()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'relatorio.pdf']);
    $shortLink->documents()->attach($document);

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->getJson('/api/v1/links/'.$shortLink->getKey())
        ->assertOk()
        ->assertJsonPath('data.documents.0.original_name', 'relatorio.pdf');
});

test('users can update their own links', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->putJson('/api/v1/links/'.$shortLink->getKey(), [
        'title' => 'Atualizado',
        'type' => 'download',
        'is_active' => false,
    ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Atualizado')
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('short_links', [
        'id' => $shortLink->getKey(),
        'is_active' => false,
    ]);
});

test('users can not update a link owned by another user', function () {
    $shortLink = ShortLink::factory()->create();
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->putJson('/api/v1/links/'.$shortLink->getKey(), ['title' => 'Invadido'])
        ->assertForbidden();
});

test('users can delete their own links', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/links/'.$shortLink->getKey())
        ->assertNoContent();

    $this->assertDatabaseMissing('short_links', ['id' => $shortLink->getKey()]);
});

test('users can not delete a link owned by another user', function () {
    $shortLink = ShortLink::factory()->create();
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/links/'.$shortLink->getKey())
        ->assertForbidden();
});

test('users can attach documents to a link through the api', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->post('/api/v1/links/'.$shortLink->getKey().'/documents', [
        'documents' => [UploadedFile::fake()->create('anexo.pdf', 100, 'application/pdf')],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.documents.0.original_name', 'anexo.pdf');

    $this->assertDatabaseCount('documents', 1);
});

test('document uploads through the api are validated', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->post('/api/v1/links/'.$shortLink->getKey().'/documents', [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('documents.0');
});
