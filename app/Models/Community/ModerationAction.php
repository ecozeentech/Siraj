<?php

namespace App\Models\Community;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModerationAction extends BaseModel
{
    public function moderator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'moderator_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'target_user_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
