<?php

use App\Enums\DevDocCategory;
use App\Models\DevDoc;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests are redirected from the development documentation', function () {
    $this->get('/admin/dev-docs')->assertRedirect('/login');
});

test('non administrators can not access the development documentation', function () {
    $this->actingAs(User::factory()->create())
        ->get('/admin/dev-docs')
        ->assertForbidden();
});

test('the development documentation module appears in the navigation for administrators', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->get('/admin')
        ->assertOk()
        ->assertSee('data-module="documentacao-dev"', false);
});

test('administrators can list the development documentation', function () {
    $admin = User::factory()->admin()->create();
    $document = DevDoc::factory()->create([
        'user_id' => $admin->getKey(),
        'title' => 'Guia de arquitetura',
    ]);

    $this->actingAs($admin)
        ->get('/admin/dev-docs')
        ->assertOk()
        ->assertSee('Documentação Dev')
        ->assertSee($document->title);
});

test('administrators can create a document', function () {
    $admin = User::factory()->admin()->create();

    $response = $this->actingAs($admin)->post('/admin/dev-docs', [
        'title' => 'Guia de API',
        'category' => DevDocCategory::Api->value,
        'summary' => 'Como consumir a API.',
        'content' => "# API\n\nUse o token Bearer.",
        'is_published' => '1',
    ]);

    $document = DevDoc::query()->firstOrFail();

    $response->assertRedirect(route('admin.dev-docs.show', $document));

    expect($document->title)->toBe('Guia de API')
        ->and($document->slug)->toBe('guia-de-api')
        ->and($document->category)->toBe(DevDocCategory::Api)
        ->and($document->is_published)->toBeTrue()
        ->and($document->author->is($admin))->toBeTrue();
});

test('creating a document requires valid data', function () {
    $this->actingAs(User::factory()->admin()->create())
        ->post('/admin/dev-docs', [])
        ->assertSessionHasErrors(['title', 'category', 'content']);
});

test('administrators can update a document without changing its slug', function () {
    $admin = User::factory()->admin()->create();
    $document = DevDoc::factory()->create([
        'user_id' => $admin->getKey(),
        'title' => 'Título antigo',
        'slug' => 'titulo-antigo',
    ]);

    $this->actingAs($admin)
        ->put(route('admin.dev-docs.update', $document), [
            'title' => 'Título novo',
            'category' => DevDocCategory::Frontend->value,
            'summary' => 'Resumo atualizado.',
            'content' => 'Conteúdo atualizado.',
            'is_published' => '1',
        ])
        ->assertRedirect(route('admin.dev-docs.show', $document));

    $document->refresh();

    expect($document->title)->toBe('Título novo')
        ->and($document->slug)->toBe('titulo-antigo')
        ->and($document->category)->toBe(DevDocCategory::Frontend)
        ->and($document->summary)->toBe('Resumo atualizado.');
});

test('administrators can delete a document', function () {
    $admin = User::factory()->admin()->create();
    $document = DevDoc::factory()->create(['user_id' => $admin->getKey()]);

    $this->actingAs($admin)
        ->delete(route('admin.dev-docs.destroy', $document))
        ->assertRedirect(route('admin.dev-docs.index'))
        ->assertSessionHas('status', 'Documento excluído com sucesso.');

    $this->assertDatabaseMissing('dev_docs', ['id' => $document->getKey()]);
});

test('administrators can search and filter documents', function () {
    $admin = User::factory()->admin()->create();

    DevDoc::factory()->create([
        'user_id' => $admin->getKey(),
        'title' => 'Deploy da API',
        'category' => DevDocCategory::Deployment,
    ]);

    DevDoc::factory()->create([
        'user_id' => $admin->getKey(),
        'title' => 'Componentes de frontend',
        'category' => DevDocCategory::Frontend,
    ]);

    $this->actingAs($admin)
        ->get('/admin/dev-docs?search=Deploy')
        ->assertOk()
        ->assertSee('Deploy da API')
        ->assertDontSee('Componentes de frontend');

    $this->actingAs($admin)
        ->get('/admin/dev-docs?category='.DevDocCategory::Frontend->value)
        ->assertOk()
        ->assertSee('Componentes de frontend')
        ->assertDontSee('Deploy da API');
});

test('the document content is rendered as escaped markdown', function () {
    $admin = User::factory()->admin()->create();
    $document = DevDoc::factory()->create([
        'user_id' => $admin->getKey(),
        'content' => "# Título\n\n**Conteúdo forte**\n\n<script>alert('xss')</script>",
    ]);

    $this->actingAs($admin)
        ->get(route('admin.dev-docs.show', $document))
        ->assertOk()
        ->assertSee('<h1>Título</h1>', false)
        ->assertSee('<strong>Conteúdo forte</strong>', false)
        ->assertDontSee("<script>alert('xss')</script>", false);
});
