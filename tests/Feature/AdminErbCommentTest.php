<?php

use App\Models\Comment;
use App\Models\Erb;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage comments', function () {
    $erb = Erb::factory()->create();

    $this->post("/admin/erbs/{$erb->getKey()}/comments", ['body' => 'Observação'])
        ->assertRedirect('/login');
});

it('adds a comment to the ERB', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.erbs.show', $erb))
        ->post("/admin/erbs/{$erb->getKey()}/comments", [
            'body' => 'A torre precisa de inspeção.',
        ])
        ->assertRedirect(route('admin.erbs.show', $erb))
        ->assertSessionHas('status');

    $comment = Comment::query()->firstOrFail();

    expect($comment->erb_id)->toBe($erb->getKey())
        ->and($comment->user_id)->toBe($user->getKey())
        ->and($comment->body)->toBe('A torre precisa de inspeção.');
});

it('rejects an empty or oversized comment', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/erbs/{$erb->getKey()}/comments", ['body' => ''])
        ->assertSessionHasErrors('body');

    $this->actingAs($user)
        ->post("/admin/erbs/{$erb->getKey()}/comments", ['body' => str_repeat('a', 4001)])
        ->assertSessionHasErrors('body');

    expect(Comment::query()->count())->toBe(0);
});

it('forbids a stranger from adding comments', function () {
    $erb = Erb::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/erbs/{$erb->getKey()}/comments", ['body' => 'Intromissão'])
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

it('removes a comment from the ERB', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create(['erb_id' => $erb->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.erbs.show', $erb))
        ->delete("/admin/erbs/{$erb->getKey()}/comments/{$comment->getKey()}")
        ->assertRedirect(route('admin.erbs.show', $erb))
        ->assertSessionHas('status');

    expect(Comment::query()->count())->toBe(0);
});

it('does not remove a comment that belongs to another ERB', function () {
    $user = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $other = Erb::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create(['erb_id' => $other->getKey()]);

    $this->actingAs($user)
        ->delete("/admin/erbs/{$erb->getKey()}/comments/{$comment->getKey()}")
        ->assertNotFound();

    expect(Comment::query()->count())->toBe(1);
});

it('forbids a stranger from removing a comment', function () {
    $owner = User::factory()->create();
    $erb = Erb::factory()->create(['user_id' => $owner->getKey()]);
    $comment = Comment::factory()->for($owner)->create(['erb_id' => $erb->getKey()]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/erbs/{$erb->getKey()}/comments/{$comment->getKey()}")
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(1);
});

it('renders the comments on the ERB page', function () {
    $user = User::factory()->create(['name' => 'Ana Souza']);
    $erb = Erb::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create([
        'erb_id' => $erb->getKey(),
        'body' => 'Visita agendada para quinta-feira.',
    ]);

    $this->actingAs($user)
        ->get(route('admin.erbs.show', $erb))
        ->assertOk()
        ->assertSee('Ana Souza')
        ->assertSee('Visita agendada para quinta-feira.');
});
