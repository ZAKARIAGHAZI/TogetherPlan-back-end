<?php

namespace App\Mail;

use App\Models\Group;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GroupInvitation extends Mailable
{
    use Queueable, SerializesModels;

    public $group;
    public $inviter;

    /**
     * Créer une nouvelle instance du Mailable
     *
     * @param Group $group
     * @param User $inviter
     */
    public function __construct(Group $group, User $inviter)
    {
        $this->group = $group;
        $this->inviter = $inviter;
    }

    /**
     * Construire le message
     */
    public function build()
    {
        return $this->subject("Vous avez été ajouté au groupe {$this->group->name}")
            ->view('emails.group_invitation');
    }
}
