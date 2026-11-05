<?php

use App\Models\User;
use App\Notifications\SystemNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

function notifyUser(User $user, string $title = 'Aviso do sistema', string $body = 'Conteúdo da notificação', ?string $url = null): void
{
    $user->notify(new SystemNotification($title, $body, $url));
}

it('redirects guests away from the notifications page', function () {
    $this->get('/notifications')->assertRedirect('/login');
});

it('lists only the notifications of the authenticated user', function () {
    $user = User::factory()->create(['email_notifications' => false]);
    $other = User::factory()->create(['email_notifications' => false]);

    notifyUser($user, 'Aviso do usuário');
    notifyUser($other, 'Aviso de outra pessoa');

    $this->actingAs($user)
        ->get('/notifications')
        ->assertOk()
        ->assertSee('Aviso do usuário')
        ->assertDontSee('Aviso de outra pessoa');
});

it('shows the unread count and latest notifications in the sidebar', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    notifyUser($user, 'Primeira notificação');

    $this->actingAs($user)
        ->get('/admin')
        ->assertOk()
        ->assertSee('Primeira notificação')
        ->assertSee('Notificações');
});

it('marks a notification as read', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    notifyUser($user, 'Ler esta notificação');

    $notification = $user->notifications()->firstOrFail();

    $this->actingAs($user)
        ->from('/notifications')
        ->patch('/notifications/'.$notification->id.'/read')
        ->assertRedirect('/notifications')
        ->assertSessionHas('status', 'Notificação marcada como lida.');

    expect($user->notifications()->firstOrFail()->read_at)->not->toBeNull()
        ->and($user->unreadNotifications()->count())->toBe(0);
});

it('cannot read or delete notifications of another user', function () {
    $owner = User::factory()->create(['email_notifications' => false]);
    $intruder = User::factory()->create(['email_notifications' => false]);

    notifyUser($owner, 'Notificação privada');

    $notification = $owner->notifications()->firstOrFail();

    $this->actingAs($intruder)
        ->patch('/notifications/'.$notification->id.'/read')
        ->assertNotFound();

    $this->actingAs($intruder)
        ->delete('/notifications/'.$notification->id)
        ->assertRedirect();

    expect($owner->notifications()->count())->toBe(1)
        ->and($owner->notifications()->firstOrFail()->read_at)->toBeNull();
});

it('marks every notification as read', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    notifyUser($user, 'Primeira');
    notifyUser($user, 'Segunda');

    $this->actingAs($user)
        ->from('/notifications')
        ->patch('/notifications/read-all')
        ->assertRedirect('/notifications')
        ->assertSessionHas('status', 'Todas as notificações foram marcadas como lidas.');

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($user->notifications()->count())->toBe(2);
});

it('deletes a single notification and clears all notifications', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    notifyUser($user, 'Primeira');
    notifyUser($user, 'Segunda');

    $first = $user->notifications()->firstOrFail();

    $this->actingAs($user)
        ->from('/notifications')
        ->delete('/notifications/'.$first->id)
        ->assertSessionHas('status', 'Notificação removida.');

    expect($user->notifications()->count())->toBe(1);

    $this->actingAs($user)
        ->from('/notifications')
        ->delete('/notifications')
        ->assertSessionHas('status', 'Todas as notificações foram removidas.');

    expect($user->notifications()->count())->toBe(0);
});

it('sends notifications on behalf of a plugin', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    plugin_notifier('example')->send(
        $user,
        'Atualização do plugin',
        'O plugin foi atualizado.',
        '/admin',
        SystemNotification::LEVEL_SUCCESS,
    );

    $data = $user->notifications()->firstOrFail()->data;

    expect($data['title'])->toBe('Atualização do plugin')
        ->and($data['body'])->toBe('O plugin foi atualizado.')
        ->and($data['url'])->toBe('/admin')
        ->and($data['level'])->toBe(SystemNotification::LEVEL_SUCCESS)
        ->and($data['plugin'])->toBe('example');
});

it('throws when resolving a notifier for an unknown plugin', function () {
    plugin_notifier('missing-plugin');
})->throws(InvalidArgumentException::class);

it('notifies only active administrators', function () {
    $admin = User::factory()->admin()->create(['email_notifications' => false]);
    $inactiveAdmin = User::factory()->admin()->create(['is_active' => false, 'email_notifications' => false]);
    $regular = User::factory()->create(['email_notifications' => false]);

    plugin_notifier('example')->toAdmins('Aviso administrativo', 'Conteúdo');

    expect($admin->notifications()->count())->toBe(1)
        ->and($inactiveAdmin->notifications()->count())->toBe(0)
        ->and($regular->notifications()->count())->toBe(0);
});

it('sends email notifications according to the user preference', function () {
    Notification::fake();

    $subscriber = User::factory()->create(['email_notifications' => true]);
    $optOut = User::factory()->create(['email_notifications' => false]);

    $subscriber->notify(new SystemNotification('Assunto', 'Corpo'));
    $optOut->notify(new SystemNotification('Assunto', 'Corpo'));

    Notification::assertSentTo(
        $subscriber,
        SystemNotification::class,
        fn (SystemNotification $notification, array $channels): bool => in_array('database', $channels, true)
            && in_array('mail', $channels, true),
    );

    Notification::assertSentTo(
        $optOut,
        SystemNotification::class,
        fn (SystemNotification $notification, array $channels): bool => in_array('database', $channels, true)
            && ! in_array('mail', $channels, true),
    );
});

it('saves the email notification preference', function () {
    $user = User::factory()->create(['email_notifications' => true]);

    $this->actingAs($user)
        ->from('/settings')
        ->put('/settings/notifications', ['email_notifications' => '0'])
        ->assertRedirect('/settings')
        ->assertSessionHas('status', 'Preferências de notificação atualizadas com sucesso.');

    expect($user->fresh()->email_notifications)->toBeFalse();

    $this->actingAs($user)
        ->put('/settings/notifications', ['email_notifications' => '1'])
        ->assertRedirect('/settings');

    expect($user->fresh()->email_notifications)->toBeTrue();
});

it('does not render unsafe notification urls', function () {
    $user = User::factory()->create(['email_notifications' => false]);

    $user->notify(new SystemNotification('Perigo', 'Conteúdo', 'javascript:alert(1)'));

    expect($user->notifications()->firstOrFail()->data['url'])->toBeNull();
});
