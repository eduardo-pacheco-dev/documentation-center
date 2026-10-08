<?php

use App\Models\Document;
use App\Models\ShortLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to the login page when managing links', function () {
    $this->get('/admin/links')->assertRedirect('/login');
});

test('users can list their own links', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create(['title' => 'Entrega final']);

    $this->actingAs($user)
        ->get('/admin/links')
        ->assertOk()
        ->assertSee('Entrega final')
        ->assertSee($shortLink->code);
});

test('a link belongs to its owner and is visible only to it', function () {
    $owner = User::factory()->create();
    $shortLink = ShortLink::factory()->for($owner)->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin/links')
        ->assertOk()
        ->assertDontSee($shortLink->code);
});

test('links can be searched by title and code', function () {
    $user = User::factory()->create();
    $match = ShortLink::factory()->for($user)->create(['title' => 'Contrato de prestacao']);
    $other = ShortLink::factory()->for($user)->create(['title' => 'Relatorio anual']);

    $this->actingAs($user)
        ->get('/admin/links?search=Contrato')
        ->assertOk()
        ->assertSee('Contrato de prestacao')
        ->assertDontSee($other->title);

    $this->actingAs($user)
        ->get('/admin/links?search='.$match->code)
        ->assertOk()
        ->assertSee($match->code)
        ->assertDontSee($other->code);
});

test('links can be filtered by a specific file', function () {
    $user = User::factory()->create();
    $withFile = ShortLink::factory()->for($user)->download()->withDocuments(1)->create();
    $withoutFile = ShortLink::factory()->for($user)->download()->create();
    $document = $withFile->documents()->firstOrFail();

    $this->actingAs($user)
        ->get('/admin/links?document='.$document->getKey())
        ->assertOk()
        ->assertSee($withFile->code)
        ->assertDontSee($withoutFile->code)
        ->assertSee($document->original_name);
});

test('filtering links by a file owned by someone else returns 404', function () {
    $document = Document::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin/links?document='.$document->getKey())
        ->assertNotFound();
});

test('links are sorted by the requested column', function () {
    $user = User::factory()->create();
    ShortLink::factory()->for($user)->create(['title' => 'Zebra']);
    ShortLink::factory()->for($user)->create(['title' => 'Abacaxi']);

    $this->actingAs($user)
        ->get('/admin/links?sort=title&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['Abacaxi', 'Zebra']);

    $this->actingAs($user)
        ->get('/admin/links?sort=title&direction=desc')
        ->assertOk()
        ->assertSeeInOrder(['Zebra', 'Abacaxi']);
});

test('links can be sorted by the number of documents', function () {
    $user = User::factory()->create();
    ShortLink::factory()->for($user)->download()->withDocuments(2)->create(['title' => 'Com dois']);
    ShortLink::factory()->for($user)->download()->withDocuments(1)->create(['title' => 'Com um']);

    $this->actingAs($user)
        ->get('/admin/links?sort=documents&direction=desc')
        ->assertOk()
        ->assertSeeInOrder(['Com dois', 'Com um']);

    $this->actingAs($user)
        ->get('/admin/links?sort=documents&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['Com um', 'Com dois']);
});

test('the link list view mode can be switched', function () {
    $user = User::factory()->create();
    ShortLink::factory()->for($user)->create(['title' => 'Entrega final']);

    $this->actingAs($user)
        ->get('/admin/links?view=table')
        ->assertSee('sort=code');

    $this->actingAs($user)
        ->get('/admin/links?view=cards')
        ->assertDontSee('sort=code')
        ->assertSee('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3');

    $this->actingAs($user)
        ->get('/admin/links?view=compact')
        ->assertDontSee('sort=code')
        ->assertSee('divide-y divide-gray-100 rounded-xl');
});

test('links fall back to newest first when the sort is invalid', function () {
    $user = User::factory()->create();
    $old = ShortLink::factory()->for($user)->create(['title' => 'Antigo']);
    ShortLink::factory()->for($user)->create(['title' => 'Recente']);

    ShortLink::query()->whereKey($old->getKey())->update(['created_at' => now()->subDay()]);

    $this->actingAs($user)
        ->get('/admin/links?sort=inexistente&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['Recente', 'Antigo']);
});

test('links are paginated ten per page keeping the current filters', function () {
    $user = User::factory()->create();
    $links = ShortLink::factory()->count(11)->for($user)->create(['title' => 'Fatura mensal']);

    $this->actingAs($user)
        ->get('/admin/links?search=Fatura&page=2')
        ->assertOk()
        ->assertSee($links->first()->code)
        ->assertDontSee($links->last()->code);

    $this->actingAs($user)
        ->get('/admin/links?search=Fatura')
        ->assertOk()
        ->assertSee('search=Fatura', false);
});

test('creating a link requires a title and a valid type', function () {
    $this->actingAs(User::factory()->create())
        ->from('/admin/links/create')
        ->post('/admin/links', ['title' => '', 'type' => ''])
        ->assertRedirect('/admin/links/create')
        ->assertSessionHasErrors(['title', 'type']);

    $this->actingAs(User::factory()->create())
        ->from('/admin/links/create')
        ->post('/admin/links', ['title' => 'Sem tipo', 'type' => 'bilhete'])
        ->assertRedirect('/admin/links/create')
        ->assertSessionHasErrors('type');
});

test('users can create an upload link', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post('/admin/links', [
        'title' => 'Envio de contratos',
        'type' => 'upload',
        'description' => 'Envie os documentos aqui.',
        'password' => 'segredo123',
        'max_uses' => 5,
        'expires_at' => now()->addDays(30)->format('Y-m-d H:i'),
        'is_active' => 1,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('status');

    $this->assertDatabaseHas('short_links', [
        'user_id' => $user->getKey(),
        'type' => 'upload',
        'title' => 'Envio de contratos',
        'max_uses' => 5,
        'used_count' => 0,
        'is_active' => true,
    ]);
});

test('users can not edit links owned by someone else', function () {
    $shortLink = ShortLink::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin/links/'.$shortLink->getKey().'/edit')
        ->assertForbidden();

    $this->actingAs(User::factory()->create())
        ->from('/admin/links/'.$shortLink->getKey().'/edit')
        ->put('/admin/links/'.$shortLink->getKey(), [
            'title' => 'Tentar invadir',
            'type' => 'download',
        ])
        ->assertForbidden();
});

test('users can update their own links', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/links/'.$shortLink->getKey().'/edit')
        ->put('/admin/links/'.$shortLink->getKey(), [
            'title' => 'Novo título',
            'type' => 'download',
            'is_active' => 0,
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('short_links', [
        'id' => $shortLink->getKey(),
        'title' => 'Novo título',
        'type' => 'download',
        'is_active' => false,
    ]);
});

test('users can not delete links owned by someone else', function () {
    $shortLink = ShortLink::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete('/admin/links/'.$shortLink->getKey())
        ->assertForbidden();
});

test('users can delete their own links', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();

    $this->actingAs($user)
        ->delete('/admin/links/'.$shortLink->getKey())
        ->assertRedirect('/admin/links');

    $this->assertDatabaseMissing('short_links', ['id' => $shortLink->getKey()]);
});

test('users can attach documents to their links', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/links/'.$shortLink->getKey().'/edit')
        ->post('/admin/links/'.$shortLink->getKey().'/documents', [
            'documents' => [
                UploadedFile::fake()->create('contrato.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('anexo.docx', 50, 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'),
            ],
        ])
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseCount('documents', 2);

    $shortLink->documents()->each(function ($document): void {
        Storage::disk('local')->assertExists($document->path);
    });
});

test('document uploads are validated', function () {
    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->create();

    $this->actingAs($user)
        ->post('/admin/links/'.$shortLink->getKey().'/documents', [
            'documents' => [
                UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload'),
            ],
        ])
        ->assertSessionHasErrors('documents.0');
});

test('deleting a link detaches documents but keeps the files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->download()->withDocuments(1)->create();

    $document = $shortLink->documents()->first();
    Storage::disk('local')->put($document->path, 'conteudo');
    Storage::disk('local')->assertExists($document->path);

    $this->actingAs($user)
        ->delete('/admin/links/'.$shortLink->getKey())
        ->assertRedirect('/admin/links');

    $this->assertDatabaseMissing('short_links', ['id' => $shortLink->getKey()]);
    $this->assertDatabaseMissing('link_document', ['short_link_id' => $shortLink->getKey()]);
    $this->assertDatabaseHas('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertExists($document->path);
});

test('users can detach a document from a download link without deleting it', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->download()->withDocuments(1)->create();

    $document = $shortLink->documents()->first();
    Storage::disk('local')->put($document->path, 'conteudo');

    $this->actingAs($user)
        ->from('/admin/links/'.$shortLink->getKey().'/edit')
        ->delete('/admin/links/'.$shortLink->getKey().'/documents/'.$document->getKey())
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey()]);
    $this->assertDatabaseMissing('link_document', [
        'short_link_id' => $shortLink->getKey(),
        'document_id' => $document->getKey(),
    ]);
    Storage::disk('local')->assertExists($document->path);
});

test('users can delete a received document from an upload link', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $shortLink = ShortLink::factory()->for($user)->withReceivedDocuments(1)->create();

    $document = $shortLink->receivedDocuments()->first();
    Storage::disk('local')->put($document->path, 'conteudo');

    $this->actingAs($user)
        ->from('/admin/links/'.$shortLink->getKey().'/edit')
        ->delete('/admin/links/'.$shortLink->getKey().'/documents/'.$document->getKey())
        ->assertRedirect()
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertExists($document->path);
});
