<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Event;

class EventInvitationNotification extends Notification
{
    use Queueable;

    protected $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    // Database notification
    public function toDatabase($notifiable)
    {
        return [
            'event_id' => $this->event->id,
            'title' => $this->event->title,
            'message' => "Vous avez été invité à l'événement « {$this->event->title} ».",
        ];
    }

    // Email notification
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Invitation à un événement')
            ->line("Vous avez été invité à l'événement : {$this->event->title}")
            ->action('Voir l’événement', url("/events/{$this->event->id}"))
            ->line('Merci d’utiliser TogetherPlan !');
    }
}
