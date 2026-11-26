<?php

namespace App\Policies;

use App\Models\Messaging\Conversation;
use App\Models\User;

class ConversationPolicy
{
    /** Only members of a conversation may read or write in it (prevents IDOR on conversation ids). */
    public function view(User $user, Conversation $conversation): bool
    {
        return $conversation->members()->where('user_id', $user->id)->exists();
    }
}
