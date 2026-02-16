<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Event $event
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New event created',
            'message' => "Event \"{$this->event->name}\" was created.",
            'url' => route('events.show', $this->event),
        ];
    }
}
