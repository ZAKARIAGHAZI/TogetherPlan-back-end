<?php

namespace App\Events;

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class InvitationCreatedEvent
{
    use Dispatchable, SerializesModels;

    public $user;
    public $event;

    public function __construct(User $user, Event $event)
    {
        $this->user = $user;
        $this->event = $event;
    }
}
