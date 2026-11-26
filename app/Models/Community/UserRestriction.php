<?php

namespace App\Models\Community;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserRestriction extends BaseModel
{
    protected function casts(): array
    {
        return ['expires_at' => 'datetime', 'lifted_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function isActive(): bool
    {
        return $this->lifted_at === null && ($this->expires_at === null || $this->expires_at->isFuture());
    }
}
