<?php

namespace App\Models\Directory;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Business extends BaseModel
{
    protected function casts(): array
    {
        return ['verified_at' => 'datetime', 'halal_certificate_expires_at' => 'date', 'latitude' => 'float', 'longitude' => 'float', 'rating_average' => 'float'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BusinessCategory::class, 'business_category_id');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(BusinessReview::class);
    }

    public function submitter(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'submitted_by');
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
