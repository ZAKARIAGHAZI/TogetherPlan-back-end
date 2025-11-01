<?php

namespace App\Mail;

use App\Models\Event;
use Illuminate\Mail\Mailable;

class BestDateMail extends Mailable
{
    public $event;

    public function __construct(Event $event)
    {
        $this->event = $event;
    }

    public function build()
    {
        return $this->subject('Meilleure date pour votre événement')
            ->view('emails.best_date')
            ->with([
                'eventTitle' => $this->event->title,
                'bestDate' => $this->event->bestDate->date ?? null,
            ]);
    }
}
