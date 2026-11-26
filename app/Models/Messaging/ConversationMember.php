<?php

namespace App\Models\Messaging;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ConversationMember extends BaseModel
{
    protected function casts(): array
    {
        return ['last_read_at' => 'datetime', 'muted_at' => 'datetime'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
