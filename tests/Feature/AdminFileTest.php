<?php

use App\Models\Document;
use App\Models\Folder;
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

test('files can be searched by name', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create(['original_name' => 'contrato-prestacao.pdf']);
    $other = Document::factory()->for($user)->create(['original_name' => 'relatorio-anual.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?search=contrato')
        ->assertOk()
        ->assertSee('contrato-prestacao.pdf')
        ->assertDontSee($other->original_name);
});

test('files are sorted by the requested column', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create(['original_name' => 'zebra.pdf']);
    Document::factory()->for($user)->create(['original_name' => 'abacaxi.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?sort=original_name&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['abacaxi.pdf', 'zebra.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?sort=original_name&direction=desc')
        ->assertOk()
        ->assertSeeInOrder(['zebra.pdf', 'abacaxi.pdf']);
});

test('the file list view mode can be switched', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create(['original_name' => 'arquivo.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?view=table')
        ->assertSee('sort=original_name');

    $this->actingAs($user)
        ->get('/admin/files?view=cards')
        ->assertDontSee('sort=original_name')
        ->assertSee('grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3');

    $this->actingAs($user)
        ->get('/admin/files?view=compact')
        ->assertSee('sort=original_name');
});

test('the files page shows a folder tree aside', function () {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create(['name' => 'Árvore Pai']);
    Folder::factory()->for($user)->create(['name' => 'Árvore Filho', 'parent_id' => $parent->getKey()]);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Árvore de pastas')
        ->assertSee('Meus arquivos')
        ->assertSee('Árvore Pai')
        ->assertSee('Árvore Filho');
});

test('the folder tree keeps the branch of the open folder expanded and highlighted', function () {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create(['name' => 'Pasta Ancestral']);
    $child = Folder::factory()->for($user)->create(['name' => 'Pasta Atual', 'parent_id' => $parent->getKey()]);

    $this->actingAs($user)
        ->get('/admin/files?folder='.$child->getKey())
        ->assertOk()
        ->assertSee('aria-expanded="true"', false)
        ->assertSee('title="Pasta Atual"', false)
        ->assertSee('bg-indigo-50 font-medium text-indigo-700', false);
});

test('files fall back to newest first when the sort is invalid', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create(['original_name' => 'antigo.pdf']);
    Document::factory()->for($user)->create(['original_name' => 'recente.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?sort=inexistente&direction=asc')
        ->assertOk()
        ->assertSeeInOrder(['recente.pdf', 'antigo.pdf']);
});

test('files are paginated ten per page keeping the current filters', function () {
    $user = User::factory()->create();

    $documents = collect(range(1, 11))->map(fn (int $number) => Document::factory()->for($user)->create([
        'original_name' => 'fatura-mensal-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT).'.pdf',
    ]));

    $this->actingAs($user)
        ->get('/admin/files?search=fatura&page=2')
        ->assertOk()
        ->assertSee($documents->first()->original_name)
        ->assertDontSee($documents->last()->original_name);

    $this->actingAs($user)
        ->get('/admin/files?search=fatura')
        ->assertOk()
        ->assertSee('search=fatura', false);
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

test('users can rename their own files', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'antigo-nome.pdf']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->put('/admin/files/'.$document->getKey(), ['original_name' => 'novo-nome.pdf'])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', [
        'id' => $document->getKey(),
        'original_name' => 'novo-nome.pdf',
    ]);
});

test('renaming a file requires a name', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'original.pdf']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->put('/admin/files/'.$document->getKey(), ['original_name' => ''])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('original_name');

    $this->assertDatabaseHas('documents', [
        'id' => $document->getKey(),
        'original_name' => 'original.pdf',
    ]);
});

test('users can not rename files owned by someone else', function () {
    $document = Document::factory()->create(['original_name' => 'original.pdf']);

    $this->actingAs(User::factory()->create())
        ->put('/admin/files/'.$document->getKey(), ['original_name' => 'roubado.pdf'])
        ->assertForbidden();

    $this->assertDatabaseHas('documents', [
        'id' => $document->getKey(),
        'original_name' => 'original.pdf',
    ]);
});

test('each file offers renaming and managing its links', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'contrato.pdf']);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Renomear')
        ->assertSee('Gerenciar links')
        ->assertSee('links-modal-'.$document->getKey(), false);
});

test('the links modal lists the links that contain the file', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();
    $shortLink = ShortLink::factory()->for($user)->download()->create(['title' => 'Entrega ao cliente']);
    $shortLink->documents()->attach($document);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Entrega ao cliente')
        ->assertSee($shortLink->code)
        ->assertSee('Remover');
});

test('the links modal reports when a file is in no link', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Este arquivo ainda não está em nenhum link.');
});

test('users can move their own files to the trash', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();
    Storage::disk('local')->put($document->path, 'conteudo');

    $this->actingAs($user)
        ->from('/admin/files')
        ->delete('/admin/files/'.$document->getKey())
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertExists($document->path);
    $this->get('/admin/files')->assertDontSee($document->original_name);
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

test('the files page offers moving files into folders', function () {
    $user = User::factory()->create();
    Document::factory()->for($user)->create();
    Folder::factory()->for($user)->create(['name' => 'Destino']);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Mover para')
        ->assertSee('move-modal', false)
        ->assertSee('Destino');
});

test('users can move their own files into a folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Destino']);
    $document = Document::factory()->for($user)->create();
    $other = Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/move', [
            'document_ids' => [$document->getKey()],
            'folder' => $folder->getKey(),
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey(), 'folder_id' => $folder->getKey()]);
    $this->assertDatabaseHas('documents', ['id' => $other->getKey(), 'folder_id' => null]);
});

test('files can be moved back to the library root', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $document = Document::factory()->for($user)->create(['folder_id' => $folder->getKey()]);

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/move', ['document_ids' => [$document->getKey()]])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey(), 'folder_id' => null]);
});

test('moving files to a folder owned by someone else is rejected', function () {
    $foreign = Folder::factory()->create();
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/move', [
            'document_ids' => [$document->getKey()],
            'folder' => $foreign->getKey(),
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('folder');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey(), 'folder_id' => null]);
});

test('moving files owned by someone else is rejected', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $foreign = Document::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/move', [
            'document_ids' => [$foreign->getKey()],
            'folder' => $folder->getKey(),
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('document_ids.0');

    $this->assertDatabaseHas('documents', ['id' => $foreign->getKey(), 'folder_id' => null]);
});

test('trashed files can not be moved', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $document = Document::factory()->for($user)->create();
    $document->delete();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files/move', [
            'document_ids' => [$document->getKey()],
            'folder' => $folder->getKey(),
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('document_ids.0');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});
