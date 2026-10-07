<?php

use App\Models\ShortLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

test('an active upload link opens the public upload page', function () {
    $shortLink = ShortLink::factory()->create(['title' => 'Envio de documentos']);

    $this->get('/s/'.$shortLink->code)
        ->assertOk()
        ->assertSee('Envio de documentos')
        ->assertSee('Envios de documentos');
});

test('a download link opens the public download page', function () {
    $shortLink = ShortLink::factory()->download()->create(['title' => 'Relatórios finais']);

    $this->get('/s/'.$shortLink->code)
        ->assertOk()
        ->assertSee('Relatórios finais')
        ->assertSee('Download de documentos');
});

test('an unknown code returns not found', function () {
    $this->get('/s/nao-existe')->assertNotFound();
});

test('an inactive link is gone', function () {
    $shortLink = ShortLink::factory()->inactive()->create();

    $this->get('/s/'.$shortLink->code)->assertStatus(410);
});

test('an expired link is gone', function () {
    $shortLink = ShortLink::factory()->expired()->create();

    $this->get('/s/'.$shortLink->code)->assertStatus(410);
});

test('a link that reached its access limit is gone', function () {
    $shortLink = ShortLink::factory()->limited(2)->create(['used_count' => 2]);

    $this->get('/s/'.$shortLink->code)->assertStatus(410);
});

test('opening an available link records the access', function () {
    $shortLink = ShortLink::factory()->create(['used_count' => 0]);

    $this->get('/s/'.$shortLink->code)->assertOk();

    $this->assertDatabaseHas('short_links', ['id' => $shortLink->getKey(), 'used_count' => 1]);
});

test('the access limit stops further openings', function () {
    $shortLink = ShortLink::factory()->limited(2)->create(['used_count' => 0]);

    $this->get('/s/'.$shortLink->code)->assertOk();
    $this->get('/s/'.$shortLink->code)->assertOk();
    $this->get('/s/'.$shortLink->code)->assertStatus(410);

    $this->assertDatabaseHas('short_links', ['id' => $shortLink->getKey(), 'used_count' => 2]);
});

test('a password protected link asks for the password and rejects a wrong password', function () {
    $shortLink = ShortLink::factory()->passwordProtected('segredo123')->create();

    $this->get('/s/'.$shortLink->code)
        ->assertOk()
        ->assertSee('protegido por senha');

    $this->post('/s/'.$shortLink->code.'/unlock', ['password' => 'errada'])
        ->assertRedirect()
        ->assertSessionHasErrors('password');
});

test('a password protected link unlocks on success and counts a single access', function () {
    $shortLink = ShortLink::factory()->passwordProtected('segredo123')->create(['used_count' => 0]);

    $this->post('/s/'.$shortLink->code.'/unlock', ['password' => 'segredo123'])
        ->assertRedirect('/s/'.$shortLink->code);

    $this->get('/s/'.$shortLink->code)
        ->assertOk()
        ->assertSee($shortLink->title);

    $this->assertDatabaseHas('short_links', ['id' => $shortLink->getKey(), 'used_count' => 1]);
});

test('documents can be uploaded through an upload link', function () {
    Storage::fake('local');

    $shortLink = ShortLink::factory()->create(['title' => 'Cliente envia anexos']);

    $this->from('/s/'.$shortLink->code)
        ->post('/s/'.$shortLink->code.'/documents', [
            'documents' => [UploadedFile::fake()->create('anexo.pdf', 100, 'application/pdf')],
        ])
        ->assertRedirect('/s/'.$shortLink->code)
        ->assertSessionHas('status');

    $this->assertDatabaseHas('documents', [
        'short_link_id' => $shortLink->getKey(),
        'original_name' => 'anexo.pdf',
    ]);
});

test('uploading through a password protected link requires the unlocked session', function () {
    Storage::fake('local');

    $shortLink = ShortLink::factory()->passwordProtected('segredo123')->create();

    $this->post('/s/'.$shortLink->code.'/documents', [
        'documents' => [UploadedFile::fake()->create('anexo.pdf', 100, 'application/pdf')],
    ])->assertForbidden();

    $this->post('/s/'.$shortLink->code.'/unlock', ['password' => 'segredo123'])->assertRedirect();

    $this->post('/s/'.$shortLink->code.'/documents', [
        'documents' => [UploadedFile::fake()->create('anexo.pdf', 100, 'application/pdf')],
    ])->assertRedirect();

    $this->assertDatabaseCount('documents', 1);
});

test('uploading to a download link is not allowed', function () {
    $shortLink = ShortLink::factory()->download()->create();

    $this->post('/s/'.$shortLink->code.'/documents', [
        'documents' => [UploadedFile::fake()->create('anexo.pdf', 100, 'application/pdf')],
    ])->assertNotFound();
});

test('documents can be downloaded from an accessible download link', function () {
    Storage::fake('local');

    $shortLink = ShortLink::factory()->download()->create();
    $document = $shortLink->documents()->create([
        'original_name' => 'relatorio.pdf',
        'path' => 'documents/relatorio.pdf',
        'disk' => 'local',
        'mime_type' => 'application/pdf',
        'size' => 100,
    ]);
    Storage::disk('local')->put($document->path, 'conteudo do pdf');

    $this->get(route('public.short-links.documents.download', $document))
        ->assertOk()
        ->assertDownload('relatorio.pdf');
});

test('documents can not be downloaded from an inactive link', function () {
    Storage::fake('local');

    $shortLink = ShortLink::factory()->download()->inactive()->create();
    $document = $shortLink->documents()->create([
        'original_name' => 'relatorio.pdf',
        'path' => 'documents/relatorio.pdf',
        'disk' => 'local',
        'mime_type' => 'application/pdf',
        'size' => 100,
    ]);
    Storage::disk('local')->put($document->path, 'conteudo do pdf');

    $this->get(route('public.short-links.documents.download', $document))->assertNotFound();
});

test('documents from a password protected link require the unlocked session', function () {
    Storage::fake('local');

    $shortLink = ShortLink::factory()->download()->passwordProtected('segredo123')->create();
    $document = $shortLink->documents()->create([
        'original_name' => 'relatorio.pdf',
        'path' => 'documents/relatorio.pdf',
        'disk' => 'local',
        'mime_type' => 'application/pdf',
        'size' => 100,
    ]);
    Storage::disk('local')->put($document->path, 'conteudo do pdf');

    $this->get(route('public.short-links.documents.download', $document))->assertNotFound();

    $this->post('/s/'.$shortLink->code.'/unlock', ['password' => 'segredo123'])->assertRedirect();

    $this->get(route('public.short-links.documents.download', $document))->assertOk();
});
