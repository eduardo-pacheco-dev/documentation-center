<?php

namespace App\Notifications;

use App\Models\AgendaEvent;
use App\Models\AgendaEventAttendee;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AgendaEventInvitationNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public AgendaEvent $event,
        public AgendaEventAttendee $attendee,
        public string $change = 'invited',
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
        $event = $this->event;
        $subject = match ($this->change) {
            'cancelled' => 'Cancelado: '.$event->title,
            'updated' => 'Atualizado: '.$event->title,
            default => 'Convite: '.$event->title,
        };

        $message = (new MailMessage)
            ->subject($subject)
            ->greeting('Olá '.($notifiable->name ?? ''))
            ->line(match ($this->change) {
                'cancelled' => 'Este evento foi cancelado.',
                'updated' => 'Este evento foi atualizado.',
                default => 'Você foi convidado para um evento.',
            })
            ->line('**'.$event->title.'**')
            ->line($this->when());

        if ($event->location) {
            $message->line('Local: '.$event->location);
        }

        if ($event->meeting_url) {
            $message->action('Entrar na reunião', $event->meeting_url);
        }

        return $message->action('Abrir agenda', route('admin.agenda.index'));
    }

    /**
     * Format the event period for display.
     */
    private function when(): string
    {
        if ($this->event->is_all_day) {
            return $this->event->start_at->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y').' (dia inteiro)';
        }

        return $this->event->start_at->locale('pt_BR')->translatedFormat('d \d\e F \d\e Y H:i')
            .' — '.$this->event->end_at->format('H:i');
    }
}
