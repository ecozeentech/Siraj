<?php

namespace App\Models\Messaging;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MessageAttachment extends BaseModel
{
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
