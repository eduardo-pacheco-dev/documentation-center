<?php

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to the login page when creating a folder', function () {
    $this->post('/admin/folders', ['name' => 'Contratos'])->assertRedirect('/login');
});

test('users can create a folder in their library', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/folders', ['name' => 'Contratos'])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('folders', [
        'user_id' => $user->getKey(),
        'parent_id' => null,
        'name' => 'Contratos',
    ]);
});

test('users can create a folder inside another folder', function () {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create(['name' => 'Jurídico']);

    $this->actingAs($user)
        ->from('/admin/files?folder='.$parent->getKey())
        ->post('/admin/folders', ['name' => 'Contratos', 'parent_id' => $parent->getKey()])
        ->assertRedirect('/admin/files?folder='.$parent->getKey())
        ->assertSessionHas('status');

    $this->assertDatabaseHas('folders', [
        'user_id' => $user->getKey(),
        'parent_id' => $parent->getKey(),
        'name' => 'Contratos',
    ]);
});

test('creating a folder requires a name', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/folders', ['name' => ''])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('name');

    $this->assertDatabaseCount('folders', 0);
});

test('creating a folder rejects a name already used by a sibling folder', function () {
    $user = User::factory()->create();
    Folder::factory()->for($user)->create(['name' => 'Contratos']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/folders', ['name' => 'Contratos'])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('name');

    $this->assertDatabaseCount('folders', 1);
});

test('the same folder name is allowed in another folder', function () {
    $user = User::factory()->create();
    Folder::factory()->for($user)->create(['name' => 'Contratos']);
    $parent = Folder::factory()->for($user)->create(['name' => 'Jurídico']);

    $this->actingAs($user)
        ->post('/admin/folders', ['name' => 'Contratos', 'parent_id' => $parent->getKey()])
        ->assertRedirect();

    $this->assertDatabaseCount('folders', 3);
});

test('the same folder name is allowed for another user', function () {
    Folder::factory()->create(['name' => 'Contratos']);
    $user = User::factory()->create();

    $this->actingAs($user)
        ->post('/admin/folders', ['name' => 'Contratos'])
        ->assertRedirect();

    $this->assertDatabaseCount('folders', 2);
});

test('users can not create a folder inside a folder owned by someone else', function () {
    $foreign = Folder::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/folders', ['name' => 'Invasão', 'parent_id' => $foreign->getKey()])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('parent_id');

    $this->assertDatabaseCount('folders', 1);
});

test('the files page lists folders owned by the user', function () {
    $user = User::factory()->create();
    Folder::factory()->for($user)->create(['name' => 'Contratos']);
    Folder::factory()->create(['name' => 'pasta-alheia']);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('Nova pasta')
        ->assertSee('Contratos')
        ->assertDontSee('pasta-alheia');
});

test('opening a folder shows only the files inside it', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Jurídico']);
    Document::factory()->for($user)->create(['original_name' => 'dentro-da-pasta.pdf', 'folder_id' => $folder->getKey()]);
    Document::factory()->for($user)->create(['original_name' => 'na-raiz.pdf']);

    $this->actingAs($user)
        ->get('/admin/files?folder='.$folder->getKey())
        ->assertOk()
        ->assertSee('dentro-da-pasta.pdf')
        ->assertDontSee('na-raiz.pdf')
        ->assertSee('Meus arquivos')
        ->assertSee('Jurídico');
});

test('a folder only lists its own subfolders', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Pasta Pai']);
    Folder::factory()->for($user)->create(['name' => 'Pasta Filha', 'parent_id' => $folder->getKey()]);
    Folder::factory()->for($user)->create(['name' => 'Pasta Irma']);

    $this->actingAs($user)
        ->get('/admin/files?folder='.$folder->getKey())
        ->assertOk()
        ->assertSee('title="Pasta Filha"', false)
        ->assertDontSee('title="Pasta Irma"', false);
});

test('users can not open a folder owned by someone else', function () {
    $foreign = Folder::factory()->create();

    $this->actingAs(User::factory()->create())
        ->get('/admin/files?folder='.$foreign->getKey())
        ->assertNotFound();
});

test('users can rename their own folder', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Antigo Nome']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->put('/admin/folders/'.$folder->getKey(), ['name' => 'Novo Nome'])
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('folders', [
        'id' => $folder->getKey(),
        'name' => 'Novo Nome',
    ]);
});

test('renaming a folder requires a name', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Original']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->put('/admin/folders/'.$folder->getKey(), ['name' => ''])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('name');

    $this->assertDatabaseHas('folders', [
        'id' => $folder->getKey(),
        'name' => 'Original',
    ]);
});

test('users can not rename a folder owned by someone else', function () {
    $folder = Folder::factory()->create(['name' => 'Original']);

    $this->actingAs(User::factory()->create())
        ->put('/admin/folders/'.$folder->getKey(), ['name' => 'Roubado'])
        ->assertForbidden();

    $this->assertDatabaseHas('folders', [
        'id' => $folder->getKey(),
        'name' => 'Original',
    ]);
});

test('deleting a folder moves it, its subfolders and its files to the trash', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $subfolder = Folder::factory()->for($user)->create(['parent_id' => $folder->getKey()]);
    $inside = Document::factory()->for($user)->create(['folder_id' => $folder->getKey()]);
    $insideSubfolder = Document::factory()->for($user)->create(['folder_id' => $subfolder->getKey()]);
    $rootFile = Document::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->delete('/admin/folders/'.$folder->getKey())
        ->assertRedirect('/admin/files')
        ->assertSessionHas('status');

    $this->assertSoftDeleted('folders', ['id' => $folder->getKey()]);
    $this->assertSoftDeleted('folders', ['id' => $subfolder->getKey()]);
    $this->assertSoftDeleted('documents', ['id' => $inside->getKey()]);
    $this->assertSoftDeleted('documents', ['id' => $insideSubfolder->getKey()]);
    $this->assertDatabaseHas('documents', ['id' => $rootFile->getKey(), 'folder_id' => null, 'deleted_at' => null]);
});

test('users can not delete a folder owned by someone else', function () {
    $folder = Folder::factory()->create();

    $this->actingAs(User::factory()->create())
        ->delete('/admin/folders/'.$folder->getKey())
        ->assertForbidden();

    $this->assertDatabaseHas('folders', ['id' => $folder->getKey()]);
});

test('uploads are stored in the opened folder', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();

    $this->actingAs($user)
        ->from('/admin/files?folder='.$folder->getKey())
        ->post('/admin/files', [
            'documents' => [UploadedFile::fake()->create('contrato.pdf', 50, 'application/pdf')],
            'folder' => $folder->getKey(),
        ])
        ->assertRedirect('/admin/files?folder='.$folder->getKey())
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', [
        'user_id' => $user->getKey(),
        'original_name' => 'contrato.pdf',
        'folder_id' => $folder->getKey(),
    ]);
});

test('uploads can not target a folder owned by someone else', function () {
    $foreign = Folder::factory()->create();
    $user = User::factory()->create();

    $this->actingAs($user)
        ->from('/admin/files')
        ->post('/admin/files', [
            'documents' => [UploadedFile::fake()->create('contrato.pdf', 50, 'application/pdf')],
            'folder' => $foreign->getKey(),
        ])
        ->assertRedirect('/admin/files')
        ->assertSessionHasErrors('folder');

    $this->assertDatabaseCount('documents', 0);
});

test('folder navigation keeps the current view mode', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();

    $this->actingAs($user)
        ->get('/admin/files?view=cards')
        ->assertOk()
        ->assertSee('view=cards&folder='.$folder->getKey());
});
