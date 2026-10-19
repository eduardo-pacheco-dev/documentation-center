<?php

use App\Models\Comment;
use App\Models\Document;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage work order comments', function () {
    $workOrder = WorkOrder::factory()->create();

    $this->post(route('admin.work-orders.comments.store', $workOrder), ['body' => 'Olá'])
        ->assertRedirect('/login');
});

it('adds a comment to a work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), ['body' => 'Precisa de revisão'])
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($workOrder->comments()->count())->toBe(1);

    $comment = $workOrder->comments->first();

    expect($comment->body)->toBe('Precisa de revisão')
        ->and($comment->user_id)->toBe($user->getKey())
        ->and($comment->work_order_id)->toBe($workOrder->getKey());
});

it('validates the comment body', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect($workOrder->comments()->count())->toBe(0);
});

it('forbids a stranger from commenting on a work order', function () {
    $workOrder = WorkOrder::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post(route('admin.work-orders.comments.store', $workOrder), ['body' => 'Olá'])
        ->assertForbidden();

    expect($workOrder->comments()->count())->toBe(0);
});

it('removes a comment from a work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create(['work_order_id' => $workOrder->getKey(), 'erb_id' => null]);

    $this->actingAs($user)
        ->delete(route('admin.work-orders.comments.destroy', [$workOrder, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($workOrder->comments()->count())->toBe(0);
});

it('does not remove a comment that belongs to another work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $other = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create(['work_order_id' => $other->getKey(), 'erb_id' => null]);

    $this->actingAs($user)
        ->delete(route('admin.work-orders.comments.destroy', [$workOrder, $comment]))
        ->assertNotFound();

    expect($other->comments()->count())->toBe(1);
});

it('forbids a stranger from removing a work order comment', function () {
    $owner = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $owner->getKey()]);
    $comment = Comment::factory()->for($owner)->create(['work_order_id' => $workOrder->getKey(), 'erb_id' => null]);

    $this->actingAs(User::factory()->create())
        ->delete(route('admin.work-orders.comments.destroy', [$workOrder, $comment]))
        ->assertForbidden();

    expect($workOrder->comments()->count())->toBe(1);
});

it('renders the work order comments', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    Comment::factory()->for($user)->create([
        'work_order_id' => $workOrder->getKey(),
        'erb_id' => null,
        'body' => 'Comentário de teste',
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Comentários')
        ->assertSee('Comentário de teste')
        ->assertSee(route('admin.work-orders.comments.store', $workOrder), false);
});

it('shows the empty comment state on the work order', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Nenhum comentário registrado.');
});

it('stores the images attached to a work order comment', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), [
            'body' => 'Seguem as fotos do local',
            'images' => [
                UploadedFile::fake()->image('local.jpg', 800, 600),
                UploadedFile::fake()->image('placa.png', 640, 480),
            ],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors()
        ->assertSessionHas('status');

    $comment = $workOrder->comments()->firstOrFail();

    expect($comment->body)->toBe('Seguem as fotos do local')
        ->and($comment->documents()->count())->toBe(2);

    $comment->documents->each(function (Document $document) use ($user): void {
        expect($document->user_id)->toBe($user->getKey())
            ->and($document->work_order_id)->toBeNull()
            ->and($document->mime_type)->toStartWith('image/');

        Storage::disk('local')->assertExists($document->path);
    });
});

it('accepts a work order comment with images and no body', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), [
            'images' => [UploadedFile::fake()->image('print.png', 320, 200)],
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($workOrder->comments()->firstOrFail()->body)->toBe('');
});

it('requires a body or an image on a work order comment', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), ['body' => ''])
        ->assertSessionHasErrors('body');

    expect($workOrder->comments()->count())->toBe(0);
});

it('rejects files that are not images on a work order comment', function () {
    Storage::fake('local');

    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post(route('admin.work-orders.comments.store', $workOrder), [
            'body' => 'Segue o laudo',
            'images' => [UploadedFile::fake()->create('laudo.pdf', 100, 'application/pdf')],
        ])
        ->assertSessionHasErrors('images.0');

    expect($workOrder->comments()->count())->toBe(0)
        ->and(Document::count())->toBe(0);
});

it('removes the images of a deleted work order comment', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create(['work_order_id' => $workOrder->getKey(), 'erb_id' => null]);
    $document = Document::factory()->create(['user_id' => $user->getKey(), 'comment_id' => $comment->getKey()]);

    $this->actingAs($user)
        ->delete(route('admin.work-orders.comments.destroy', [$workOrder, $comment]))
        ->assertRedirect()
        ->assertSessionHas('status');

    expect($workOrder->comments()->count())->toBe(0)
        ->and(Document::withTrashed()->find($document->getKey())->trashed())->toBeTrue();
});

it('renders the images of a work order comment', function () {
    $user = User::factory()->create();
    $workOrder = WorkOrder::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create([
        'work_order_id' => $workOrder->getKey(),
        'erb_id' => null,
        'body' => 'Foto do local',
    ]);
    $document = Document::factory()->create([
        'user_id' => $user->getKey(),
        'comment_id' => $comment->getKey(),
        'original_name' => 'local.jpg',
    ]);

    $this->actingAs($user)
        ->get(route('admin.work-orders.show', $workOrder))
        ->assertOk()
        ->assertSee('Foto do local')
        ->assertSee(route('admin.files.preview', $document), false)
        ->assertSee('Arraste imagens, clique para buscar ou cole com Ctrl + V');
});
