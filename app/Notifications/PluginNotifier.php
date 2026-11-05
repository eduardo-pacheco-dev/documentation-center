<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Support\Facades\Notification;
use InvalidArgumentException;

/**
 * Sends system notifications on behalf of a plugin.
 */
final class PluginNotifier
{
    public function __construct(private readonly string $plugin) {}

    /**
     * Resolve the notifier for a plugin alias.
     */
    public static function for(string $plugin): self
    {
        $resolved = plugin($plugin);

        if ($resolved === null) {
            throw new InvalidArgumentException("Plugin [{$plugin}] was not found.");
        }

        return new self($resolved->getAlias());
    }

    /**
     * The plugin alias that owns the notifications.
     */
    public function plugin(): string
    {
        return $this->plugin;
    }

    /**
     * Notify the given users.
     *
     * @param  User|iterable<int, User>  $recipients
     */
    public function send(
        User|iterable $recipients,
        string $title,
        string $body,
        ?string $url = null,
        string $level = SystemNotification::LEVEL_INFO,
    ): void {
        Notification::send($recipients, $this->notification($title, $body, $url, $level));
    }

    /**
     * Notify every active administrator.
     */
    public function toAdmins(
        string $title,
        string $body,
        ?string $url = null,
        string $level = SystemNotification::LEVEL_INFO,
    ): void {
        $this->send(
            User::query()->where('is_admin', true)->where('is_active', true)->get(),
            $title,
            $body,
            $url,
            $level,
        );
    }

    /**
     * Build the notification without sending it.
     */
    public function notification(
        string $title,
        string $body,
        ?string $url = null,
        string $level = SystemNotification::LEVEL_INFO,
    ): SystemNotification {
        return new SystemNotification($title, $body, $url, $level, $this->plugin);
    }
}
