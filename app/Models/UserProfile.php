<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends BaseModel
{
    protected function casts(): array
    {
        return ['date_of_birth' => 'date', 'show_location' => 'boolean', 'share_prayer_activity' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
