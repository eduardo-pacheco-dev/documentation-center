<?php

use App\Models\Document;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('guests are redirected to the login page when opening the trash', function () {
    $this->get('/admin/files?view=trash')->assertRedirect('/login');
});

test('the files page offers the trash as a view mode', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('view=trash', false)
        ->assertSee('title="Lixeira"', false);
});

test('users see only their own trashed items', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Minha pasta lixeira']);
    $document = Document::factory()->for($user)->create(['original_name' => 'meu-lixeira.pdf']);
    $folder->delete();
    $document->delete();

    $otherFolder = Folder::factory()->create(['name' => 'Pasta alheia lixeira']);
    $otherDocument = Document::factory()->create(['original_name' => 'alheio-lixeira.pdf']);
    $otherFolder->delete();
    $otherDocument->delete();

    $this->actingAs($user)
        ->get('/admin/files?view=trash')
        ->assertOk()
        ->assertSee('Minha pasta lixeira')
        ->assertSee('meu-lixeira.pdf')
        ->assertDontSee('Pasta alheia lixeira')
        ->assertDontSee('alheio-lixeira.pdf');
});

test('the trash shows an empty state when there is nothing to restore', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/files?view=trash')
        ->assertOk()
        ->assertSee('A lixeira está vazia');
});

test('a trashed file can be restored back to the library', function () {
    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create(['original_name' => 'restaurado.pdf']);

    $this->actingAs($user)
        ->from('/admin/files')
        ->delete('/admin/files/'.$document->getKey())
        ->assertRedirect('/admin/files');

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertDontSee('restaurado.pdf');

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->patch('/admin/trash/documents/'.$document->getKey().'/restore')
        ->assertRedirect('/admin/files?view=trash')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey(), 'deleted_at' => null]);

    $this->actingAs($user)
        ->get('/admin/files')
        ->assertOk()
        ->assertSee('restaurado.pdf');
});

test('restoring a file also restores its ancestor folders without leaving siblings behind', function () {
    $user = User::factory()->create();
    $parent = Folder::factory()->for($user)->create(['name' => 'Pai']);
    $child = Folder::factory()->for($user)->create(['name' => 'Filho', 'parent_id' => $parent->getKey()]);
    $sibling = Folder::factory()->for($user)->create(['name' => 'Irma', 'parent_id' => $parent->getKey()]);
    $document = Document::factory()->for($user)->create(['folder_id' => $child->getKey()]);

    $parent->delete();

    $this->assertSoftDeleted('folders', ['id' => $parent->getKey()]);
    $this->assertSoftDeleted('folders', ['id' => $child->getKey()]);
    $this->assertSoftDeleted('folders', ['id' => $sibling->getKey()]);
    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->patch('/admin/trash/documents/'.$document->getKey().'/restore')
        ->assertRedirect('/admin/files?view=trash');

    $this->assertDatabaseHas('documents', ['id' => $document->getKey(), 'deleted_at' => null]);
    $this->assertDatabaseHas('folders', ['id' => $child->getKey(), 'deleted_at' => null]);
    $this->assertDatabaseHas('folders', ['id' => $parent->getKey(), 'deleted_at' => null]);
    $this->assertSoftDeleted('folders', ['id' => $sibling->getKey()]);
});

test('restoring a folder restores its subfolders and files', function () {
    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create(['name' => 'Projeto']);
    $subfolder = Folder::factory()->for($user)->create(['parent_id' => $folder->getKey()]);
    $inside = Document::factory()->for($user)->create(['folder_id' => $folder->getKey()]);
    $insideSubfolder = Document::factory()->for($user)->create(['folder_id' => $subfolder->getKey()]);

    $folder->delete();

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->patch('/admin/trash/folders/'.$folder->getKey().'/restore')
        ->assertRedirect('/admin/files?view=trash')
        ->assertSessionHas('status');

    $this->assertDatabaseHas('folders', ['id' => $folder->getKey(), 'deleted_at' => null]);
    $this->assertDatabaseHas('folders', ['id' => $subfolder->getKey(), 'deleted_at' => null]);
    $this->assertDatabaseHas('documents', ['id' => $inside->getKey(), 'deleted_at' => null]);
    $this->assertDatabaseHas('documents', ['id' => $insideSubfolder->getKey(), 'deleted_at' => null]);
});

test('force deleting a trashed file removes the row and the storage file', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $document = Document::factory()->for($user)->create();
    Storage::disk('local')->put($document->path, 'conteudo');

    $document->delete();

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->delete('/admin/trash/documents/'.$document->getKey())
        ->assertRedirect('/admin/files?view=trash')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertMissing($document->path);
});

test('force deleting a trashed folder purges its contents and storage files', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $document = Document::factory()->for($user)->create(['folder_id' => $folder->getKey()]);
    Storage::disk('local')->put($document->path, 'conteudo');

    $folder->delete();

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->delete('/admin/trash/folders/'.$folder->getKey())
        ->assertRedirect('/admin/files?view=trash')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('folders', ['id' => $folder->getKey()]);
    $this->assertDatabaseMissing('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertMissing($document->path);
});

test('emptying the trash purges only the items of the authenticated user', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $folder = Folder::factory()->for($user)->create();
    $document = Document::factory()->for($user)->create();
    Storage::disk('local')->put($document->path, 'conteudo');
    $folder->delete();
    $document->delete();

    $otherDocument = Document::factory()->create();
    $otherDocument->delete();

    $this->actingAs($user)
        ->from('/admin/files?view=trash')
        ->delete('/admin/trash')
        ->assertRedirect('/admin/files?view=trash')
        ->assertSessionHas('status');

    $this->assertDatabaseMissing('folders', ['id' => $folder->getKey()]);
    $this->assertDatabaseMissing('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertMissing($document->path);
    $this->assertSoftDeleted('documents', ['id' => $otherDocument->getKey()]);
});

test('users can not restore items owned by someone else', function () {
    $document = Document::factory()->create();
    $document->delete();

    $this->actingAs(User::factory()->create())
        ->patch('/admin/trash/documents/'.$document->getKey().'/restore')
        ->assertForbidden();

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

test('users can not force delete items owned by someone else', function () {
    Storage::fake('local');

    $document = Document::factory()->create();
    Storage::disk('local')->put($document->path, 'conteudo');
    $document->delete();

    $this->actingAs(User::factory()->create())
        ->delete('/admin/trash/documents/'.$document->getKey())
        ->assertForbidden();

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
    Storage::disk('local')->assertExists($document->path);
});
