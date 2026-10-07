<?php

use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('files endpoints require a valid token', function () {
    $this->getJson('/api/v1/files')->assertUnauthorized();
    $this->postJson('/api/v1/files', [
        'documents' => [UploadedFile::fake()->create('x.pdf', 100, 'application/pdf')],
    ])->assertUnauthorized();
});

test('users can upload files through the api', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->post('/api/v1/files', [
        'documents' => [UploadedFile::fake()->create('relatorio.pdf', 100, 'application/pdf')],
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.0.original_name', 'relatorio.pdf');

    $this->assertDatabaseCount('documents', 1);

    $document = Document::whereBelongsTo($user)->first();
    Storage::disk('local')->assertExists($document?->path);
});

test('file uploads through the api are validated', function () {
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->post('/api/v1/files', [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('documents.0');
});

test('users can list their own files only', function () {
    $user = User::factory()->create();
    Document::factory()->count(2)->for($user)->create(['original_name' => 'meu.pdf']);
    $foreign = Document::factory()->create(['original_name' => 'outro.pdf']);

    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->getJson('/api/v1/files');

    $response->assertOk()->assertJsonCount(2, 'data');
    expect($response->json('data.*.original_name'))->not->toContain($foreign->original_name);
});

test('users can delete their own files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();
    Storage::disk('local')->put($document->path, 'conteudo');

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/files/'.$document->getKey())
        ->assertNoContent();

    $this->assertDatabaseMissing('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertMissing($document->path);
});

test('users can not delete a file owned by another user', function () {
    $document = Document::factory()->create();
    $token = User::factory()->create()->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->deleteJson('/api/v1/files/'.$document->getKey())
        ->assertForbidden();
});

test('users can create a download link from their files through the api', function () {
    $user = User::factory()->create();
    $documents = Document::factory()->count(2)->for($user)->create();

    $token = $user->createToken('mobile')->plainTextToken;

    $response = $this->withToken($token)->postJson('/api/v1/links', [
        'title' => 'Contratos',
        'type' => 'download',
        'document_ids' => $documents->modelKeys(),
    ]);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'Contratos')
        ->assertJsonCount(2, 'data.documents');

    $shortLink = ShortLink::whereBelongsTo($user)->where('type', 'download')->firstOrFail();

    $this->assertSame(2, $shortLink->documents()->count());
});

test('creating a download link rejects files owned by someone else', function () {
    $user = User::factory()->create();
    $foreign = Document::factory()->create();

    $token = $user->createToken('mobile')->plainTextToken;

    $this->withToken($token)
        ->postJson('/api/v1/links', [
            'title' => 'Contratos',
            'type' => 'download',
            'document_ids' => [$foreign->getKey()],
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('document_ids.0');
});
