<?php

use App\Models\Comment;
use App\Models\RadioLink;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('requires an authenticated user to manage comments', function () {
    $radioLink = RadioLink::factory()->create();

    $this->post("/admin/radio-links/{$radioLink->getKey()}/comments", ['body' => 'Observação'])
        ->assertRedirect('/login');
});

it('adds a comment to the radio link', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->from(route('admin.radio-links.show', $radioLink))
        ->post("/admin/radio-links/{$radioLink->getKey()}/comments", [
            'body' => 'O enlace precisa de realinhamento.',
        ])
        ->assertRedirect(route('admin.radio-links.show', $radioLink))
        ->assertSessionHas('status');

    $comment = Comment::query()->firstOrFail();

    expect($comment->radio_link_id)->toBe($radioLink->getKey())
        ->and($comment->erb_id)->toBeNull()
        ->and($comment->user_id)->toBe($user->getKey())
        ->and($comment->body)->toBe('O enlace precisa de realinhamento.');
});

it('rejects an empty or oversized comment', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->post("/admin/radio-links/{$radioLink->getKey()}/comments", ['body' => ''])
        ->assertSessionHasErrors('body');

    $this->actingAs($user)
        ->post("/admin/radio-links/{$radioLink->getKey()}/comments", ['body' => str_repeat('a', 4001)])
        ->assertSessionHasErrors('body');

    expect(Comment::query()->count())->toBe(0);
});

it('forbids a stranger from adding comments', function () {
    $radioLink = RadioLink::factory()->create();

    $this->actingAs(User::factory()->create())
        ->post("/admin/radio-links/{$radioLink->getKey()}/comments", ['body' => 'Intromissão'])
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(0);
});

it('removes a comment from the radio link', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create([
        'radio_link_id' => $radioLink->getKey(),
        'erb_id' => null,
    ]);

    $this->actingAs($user)
        ->from(route('admin.radio-links.show', $radioLink))
        ->delete("/admin/radio-links/{$radioLink->getKey()}/comments/{$comment->getKey()}")
        ->assertRedirect(route('admin.radio-links.show', $radioLink))
        ->assertSessionHas('status');

    expect(Comment::query()->count())->toBe(0);
});

it('does not remove a comment that belongs to another radio link', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $other = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    $comment = Comment::factory()->for($user)->create([
        'radio_link_id' => $other->getKey(),
        'erb_id' => null,
    ]);

    $this->actingAs($user)
        ->delete("/admin/radio-links/{$radioLink->getKey()}/comments/{$comment->getKey()}")
        ->assertNotFound();

    expect(Comment::query()->count())->toBe(1);
});

it('forbids a stranger from removing a comment', function () {
    $owner = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $owner->getKey()]);
    $comment = Comment::factory()->for($owner)->create([
        'radio_link_id' => $radioLink->getKey(),
        'erb_id' => null,
    ]);

    $this->actingAs(User::factory()->create())
        ->delete("/admin/radio-links/{$radioLink->getKey()}/comments/{$comment->getKey()}")
        ->assertForbidden();

    expect(Comment::query()->count())->toBe(1);
});

it('renders the comments on the radio link page', function () {
    $user = User::factory()->create(['name' => 'Ana Souza']);
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);
    Comment::factory()->for($user)->create([
        'radio_link_id' => $radioLink->getKey(),
        'erb_id' => null,
        'body' => 'Potência medida dentro do esperado.',
    ]);

    $this->actingAs($user)
        ->get(route('admin.radio-links.show', $radioLink))
        ->assertOk()
        ->assertSee('Comentários')
        ->assertSee('Ana Souza')
        ->assertSee('Potência medida dentro do esperado.');

    $this->actingAs($user)
        ->get(route('admin.radio-links.edit', $radioLink))
        ->assertOk()
        ->assertSee('Comentários')
        ->assertSee('Potência medida dentro do esperado.');
});

it('shows the comment form on the radio link page', function () {
    $user = User::factory()->create();
    $radioLink = RadioLink::factory()->create(['user_id' => $user->getKey()]);

    $this->actingAs($user)
        ->get(route('admin.radio-links.show', $radioLink))
        ->assertOk()
        ->assertSee(route('admin.radio-links.comments.store', $radioLink), false)
        ->assertSee('Nenhum comentário registrado.');
});
