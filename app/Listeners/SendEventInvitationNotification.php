<?php

namespace App\Listeners;

use App\Events\InvitationCreatedEvent;
use App\Notifications\EventInvitationNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendEventInvitationNotification implements ShouldQueue
{
    public function handle(InvitationCreatedEvent $event)
    {
        $event->user->notify(new EventInvitationNotification($event->event));
    }
}
