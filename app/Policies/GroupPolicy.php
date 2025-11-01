<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class GroupPolicy
{
    public function delete(User $user, Group $group)
    {
        // Seul le créateur peut supprimer le groupe
        return $user->id === $group->created_by;
    }
}
