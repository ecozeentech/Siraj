<?php

namespace App\Models\System;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketReply extends BaseModel
{
    protected function casts(): array
    {
        return ['is_staff' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
