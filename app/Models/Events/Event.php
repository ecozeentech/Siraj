<?php

namespace App\Models\Events;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends BaseModel
{
    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime', 'latitude' => 'float', 'longitude' => 'float'];
    }

    public function scopePublished($q)
    {
        return $q->where('status', 'published');
    }

    public function scopeUpcoming($q)
    {
        return $q->where(fn ($w) => $w->where('ends_at', '>=', now())->orWhere(fn ($x) => $x->whereNull('ends_at')->where('starts_at', '>=', now()->startOfDay())));
    }

    public function isFree(): bool
    {
        return (int) $this->price_kobo === 0;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
