<?php

use App\Models\Document;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage attachments', function () {
    $radioLink = RadioLink::factory()->create();

    $this->post("/admin/radio-links/{$radioLink->getKey()}/documents", ['documents' => []])
        ->assertRedirect('/login');
});

it('uploads attachments to the radio link', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.radio-links.show', $radioLink))
        ->post("/admin/radio-links/{$radioLink->getKey()}/documents", [
            'documents' => [
                UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('fotografia-torre.jpg', 50),
            ],
        ])
        ->assertRedirect(route('admin.radio-links.show', $radioLink))
        ->assertSessionHas('status');

    expect($radioLink->documents()->count())->toBe(2);

    $radioLink->documents->each(function (Document $document) use ($radioLink, $user): void {
        expect($document->radio_link_id)->toBe($radioLink->getKey())
            ->and($document->erb_id)->toBeNull()
            ->and($document->user_id)->toBe($user->getKey());

        Storage::disk('local')->assertExists($document->path);
    });
});

it('validates the uploaded attachment type', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/radio-links/{$radioLink->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('virus.exe', 100, 'application/x-msdownload')],
        ])
        ->assertSessionHasErrors('documents.0');

    expect($radioLink->documents()->count())->toBe(0);
});

it('forbids a stranger from uploading attachments', function () {
    $radioLink = RadioLink::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/radio-links/{$radioLink->getKey()}/documents", [
            'documents' => [UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf')],
        ])
        ->assertForbidden();
});

it('removes an attachment from the radio link', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['radio_link_id' => $radioLink->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.radio-links.show', $radioLink))
        ->delete("/admin/radio-links/{$radioLink->getKey()}/documents/{$document->getKey()}")
        ->assertRedirect(route('admin.radio-links.show', $radioLink))
        ->assertSessionHas('status');

    $this->assertSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('does not remove an attachment that belongs to another radio link', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $other = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create(['radio_link_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/radio-links/{$radioLink->getKey()}/documents/{$document->getKey()}")
        ->assertNotFound();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('forbids a stranger from removing an attachment', function () {
    $owner = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $owner->getKey()]);
    $document = Document::factory()->for($owner)->create(['radio_link_id' => $radioLink->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/radio-links/{$radioLink->getKey()}/documents/{$document->getKey()}")
        ->assertForbidden();

    $this->assertNotSoftDeleted('documents', ['id' => $document->getKey()]);
});

it('renders the attachments on the radio link page', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $document = Document::factory()->for($user)->create([
        'radio_link_id' => $radioLink->getKey(),
        'original_name' => 'laudo-tecnico.pdf',
    ]);

    $this->actingAs($user)
        ->get(route('admin.radio-links.show', $radioLink))
        ->assertOk()
        ->assertSee('Anexos')
        ->assertSee('laudo-tecnico.pdf')
        ->assertSee(route('admin.files.preview', $document), false);

    $this->actingAs($user)
        ->get(route('admin.radio-links.edit', $radioLink))
        ->assertOk()
        ->assertSee('Anexos')
        ->assertSee('laudo-tecnico.pdf');
});

it('shows the attachment form on the radio link page', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.radio-links.show', $radioLink))
        ->assertOk()
        ->assertSee(route('admin.radio-links.documents.store', $radioLink), false)
        ->assertSee('Nenhum anexo cadastrado.');
});
