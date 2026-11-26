<?php

namespace App\Models\System;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BroadcastNotification extends BaseModel
{
    protected function casts(): array
    {
        return ['audience_filter' => 'array', 'channels' => 'array', 'scheduled_at' => 'datetime', 'sent_at' => 'datetime'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(BroadcastDelivery::class);
    }
}
