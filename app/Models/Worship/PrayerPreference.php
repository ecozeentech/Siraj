<?php

namespace App\Models\Worship;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrayerPreference extends BaseModel
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected function casts(): array
    {
        return ['adjustments' => 'array', 'reminders_enabled' => 'boolean', 'latitude' => 'float', 'longitude' => 'float'];
    }
}
