<?php

use App\Models\Client;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage attachments', function () {
    $client = Client::factory()->create();

    $this->post("/admin/clients/{$client->getKey()}/documents", ['documents' => []])
        ->assertRedirect('/login');
});

it('uploads attachments to the client', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.clients.show', $client))
        ->post("/admin/clients/{$client->getKey()}/documents", [
            'documents' => [
                UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('proposta.docx', 50),
            ],
        ])
        ->assertRedirect(route('admin.clients.show', $client))
        ->assertSessionHas('status');

    expect($client->documents()->count())->toBe(2);

    $client->documents->each(function (Document $document) use ($client, $user): void {
        expect($document->client_id)->toBe($client->getKey())
            ->and($document->user_id)->toBe($user->getKey());

        Storage::disk('local')->assertExists($document->path);
    });
});

it('validates the uploaded attachment type', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/clients/{$client->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');

    expect($client->documents()->count())->toBe(0);
});

it('forbids a stranger from uploading attachments', function () {
    $client = Client::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/clients/{$client->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf')],
        ])
        ->assertForbidden();
});

it('removes an attachment from the client', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['client_id' => $client->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.clients.show', $client))
        ->delete("/admin/clients/{$client->getKey()}/documents/{$document->getKey()}")
        ->assertRedirect(route('admin.clients.show', $client))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('does not remove an attachment that belongs to another client', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $other = Client::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['client_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/clients/{$client->getKey()}/documents/{$document->getKey()}")
        ->assertNotFound();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('forbids a stranger from removing an attachment', function () {
    $owner = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $owner->getKey()]);
    $document = Document::factory()->for($owner)->create(['client_id' => $client->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/clients/{$client->getKey()}/documents/{$document->getKey()}")
        ->assertForbidden();
});

it('renders the attachments on the client page', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create([
        'client_id' => $client->getKey(),
        'original_name' => 'contrato-social.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('admin.clients.show', $client))
        ->assertOk()
        ->assertSee('contrato-social.pdf')
        ->assertSee(route('admin.files.preview', $document), false);
});
