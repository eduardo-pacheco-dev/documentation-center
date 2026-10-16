<?php

use App\Models\Comment;
use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
