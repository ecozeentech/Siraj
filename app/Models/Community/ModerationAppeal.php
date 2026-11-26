<?php

namespace App\Models\Community;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationAppeal extends BaseModel
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function restriction(): BelongsTo
    {
        return $this->belongsTo(UserRestriction::class, 'user_restriction_id');
    }

    public function resolver(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'resolved_by');
    }
}
