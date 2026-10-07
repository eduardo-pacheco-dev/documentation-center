<?php

use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to the login page when managing files', function () {
    $this->get('/admin/files')->assertRedirect('/login');
});

test('users can upload files to their library', function () {
    Storage::fake('local');

    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files', [
            'documents' => [
                UploadedFile::fake()->create('relatorio.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('planilha.xlsx', 50, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
            ],
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseCount('documents', 2);

    Document::whereBelongsTo($user)->get()->each(function ($document): void {
        Storage::disk('local')->assertExists($document->path);
    });
});

test('file uploads are validated', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/files', [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');
});

test('users only see their own files', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create(['original_name' => 'meu-arquivo.pdf']);
    Document::factory()->create(['original_name' => 'arquivo-do-outro.pdf']);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('meu-arquivo.pdf')
        ->assertDontSee('arquivo-do-outro.pdf');
});

test('users can generate a download link from their selected files', function () {
    $user = User::factory()->create();
    $documents = Document::factory()->count(2)->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/files/generate-link', [
            'title' => 'Entrega de contratos',
            'document_ids' => $documents->modelKeys(),
        ])
        ->assertRedirect();

    $shortLink = ShortLink::whereBelongsTo($user)->where('type', 'download')->firstOrFail();

    expect($shortLink->title)->toBe('Entrega de contratos');
    $this->assertDatabaseHas('link_document', [
        'short_link_id' => $shortLink->getKey(),
        'document_id' => $documents[0]->getKey(),
    ]);
    $this->assertDatabaseHas('link_document', [
        'short_link_id' => $shortLink->getKey(),
        'document_id' => $documents[1]->getKey(),
    ]);
    $this->assertSame(2, $shortLink->documents()->count());
});

test('a generated link defaults the title when none is given', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/files/generate-link', ['document_ids' => [$document->getKey()]])
        ->assertRedirect();

    $this->assertDatabaseHas('short_links', [
        'user_id' => $user->getKey(),
        'type' => 'download',
        'title' => 'Downloads de arquivos',
    ]);
});

test('generating a link ignores files owned by someone else', function () {
    $user = User::factory()->create();
    $foreign = Document::factory()->create();
    $own = Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/generate-link', ['document_ids' => [$foreign->getKey(), $own->getKey()]])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('document_ids.0');
});

test('users can delete their own files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();
    Storage::disk('local')->put($document->path, 'conteudo');

    $this->actingAs($user)
        ->from('/admin/files')
        ->delete('/admin/files/'.$document->getKey())
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertMissing($document->path);
});

test('users can not delete files owned by someone else', function () {
    $document = Document::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete('/admin/files/'.$document->getKey())
        ->assertForbidden();
});

test('a generated link shares the files on the public download page', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'contrato.pdf']);
    Storage::disk('local')->put($document->path, 'conteudo do contrato');

    $this->actingAs($user)
        ->post('/admin/files/generate-link', ['document_ids' => [$document->getKey()]])
        ->assertRedirect();

    $shortLink = ShortLink::whereBelongsTo($user)->where('type', 'download')->firstOrFail();

    $this->get('/s/'.$shortLink->code)
        ->assertOk()
        ->assertSee('contrato.pdf');
});
