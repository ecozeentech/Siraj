<?php

namespace App\Models\Directory;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mosque extends BaseModel
{
    protected function casts(): array
    {
        return ['facilities' => 'array', 'verified_at' => 'datetime', 'latitude' => 'float', 'longitude' => 'float'];
    }

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'verified_by');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
