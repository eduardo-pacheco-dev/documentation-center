<?php

namespace App\Notifications;

use App\Models\AgendaEvent;
use Carbon\CarbonInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgendaEventReminderNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AgendaEvent $event,
        public CarbonInterface $startsAt,
        public int $minutesBefore,
    ) {}

    /**
     * Get the notification's delivery channels.
     *
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Lembrete: '.$this->event->title)
            ->greeting('Olá '.($notifiable->name ?? ''))
            ->line($this->headline())
            ->line('**'.$this->event->title.'**')
            ->line($this->startsAt->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y \à\s H:i'));

        if ($this->event->location) {
            $message->line('Local: '.$this->event->location);
        }

        if ($this->event->meeting_url) {
            $message->action('Entrar na reunião', $this->event->meeting_url);
        }

        return $message->action('Abrir agenda', route('admin.agenda.index'));
    }

    /**
     * Describe how long is left until the event starts.
     */
    private function headline(): string
    {
        if ($this->minutesBefore < 60) {
            return "O evento começa em {$this->minutesBefore} minuto(s).";
        }

        if ($this->minutesBefore < 1440) {
            $hours = intdiv($this->minutesBefore, 60);

            return "O evento começa em {$hours} hora(s).";
        }

        $days = intdiv($this->minutesBefore, 1440);

        return "O evento começa em {$days} dia(s).";
    }
}
