<?php

use App\Models\Document;
use App\Models\Erb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage attachments', function () {
    $erb = Erb::factory()->create();

    $this->post("/admin/erbs/{$erb->getKey()}/documents", ['documents' => []])
        ->assertRedirect('/login');
});

it('uploads attachments to the ERB', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.erbs.show', $erb))
        ->post("/admin/erbs/{$erb->getKey()}/documents", [
            'documents' => [
                UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('foto-torre.jpg', 50),
            ],
        ])
        ->assertRedirect(route('admin.erbs.show', $erb))
        ->assertSessionHas('status');

    expect($erb->documents()->count())->toBe(2);

    $erb->documents->each(function (Document $document) use ($erb, $user): void {
        expect($document->erb_id)->toBe($erb->getKey())
            ->and($document->user_id)->toBe($user->getKey());

        Storage::disk('local')->assertExists($document->path);
    });
});

it('validates the uploaded attachment type', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/erbs/{$erb->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');

    expect($erb->documents()->count())->toBe(0);
});

it('forbids a stranger from uploading attachments', function () {
    $erb = Erb::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/erbs/{$erb->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf')],
        ])
        ->assertForbidden();
});

it('removes an attachment from the ERB', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['erb_id' => $erb->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.erbs.show', $erb))
        ->delete("/admin/erbs/{$erb->getKey()}/documents/{$document->getKey()}")
        ->assertRedirect(route('admin.erbs.show', $erb))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('does not remove an attachment that belongs to another ERB', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $other = Erb::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['erb_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/erbs/{$erb->getKey()}/documents/{$document->getKey()}")
        ->assertNotFound();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('forbids a stranger from removing an attachment', function () {
    $owner = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $owner->getKey()]);
    $document = Document::factory()->for($owner)->create(['erb_id' => $erb->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/erbs/{$erb->getKey()}/documents/{$document->getKey()}")
        ->assertForbidden();
});

it('renders the attachments on the ERB page', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create([
        'erb_id' => $erb->getKey(),
        'original_name' => 'laudo-tecnico.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('admin.erbs.show', $erb))
        ->assertOk()
        ->assertSee('laudo-tecnico.pdf')
        ->assertSee(route('admin.files.preview', $document), false);
});
