<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use InvalidArgumentException;

class SystemNotification extends Notification
{
    public const LEVEL_INFO = 'info';

    public const LEVEL_SUCCESS = 'success';

    public const LEVEL_WARNING = 'warning';

    public const LEVEL_DANGER = 'danger';

    /**
     * The levels supported by the notification UI.
     */
    public const LEVELS = [
        self::LEVEL_INFO,
        self::LEVEL_SUCCESS,
        self::LEVEL_WARNING,
        self::LEVEL_DANGER,
    ];

    public readonly ?string $url;

    public function __construct(
        public readonly string $title,
        public readonly string $body,
        ?string $url = null,
        public readonly string $level = self::LEVEL_INFO,
        public readonly ?string $plugin = null,
    ) {
        if (! in_array($level, self::LEVELS, true)) {
            throw new InvalidArgumentException("Invalid notification level [{$level}].");
        }

        $this->url = self::sanitizeUrl($url);
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        $channels = ['database'];

        if (data_get($notifiable, 'email_notifications', true) !== false) {
            $channels[] = 'mail';
        }

        return $channels;
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->title)
            ->line($this->body);

        if ($this->url !== null && $this->url !== '') {
            $message->action('Abrir notificação', $this->url);
        }

        return $message;
    }

    /**
     * Allow only absolute http(s) URLs or application relative paths.
     */
    private static function sanitizeUrl(?string $url): ?string
    {
        if ($url === null || $url === '') {
            return null;
        }

        if (str_starts_with($url, '/') && ! str_starts_with($url, '//')) {
            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);

        return filter_var($url, FILTER_VALIDATE_URL) !== false && in_array($scheme, ['http', 'https'], true)
            ? $url
            : null;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'url' => $this->url,
            'level' => $this->level,
            'plugin' => $this->plugin,
        ];
    }
}
