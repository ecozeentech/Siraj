<?php

namespace App\Models\Family;

use App\Models\BaseModel;
use App\Models\Kids\KidsProgress;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ChildProfile extends BaseModel
{
    protected $hidden = ['pin_hash'];

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function controls(): HasOne
    {
        return $this->hasOne(ParentalControl::class)->withDefault(['daily_limit_minutes' => 60, 'allowed_sections' => ['courses', 'stories', 'games']]);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(KidsProgress::class);
    }

    public function age(): int
    {
        return max(1, (int) now()->format('Y') - $this->birth_year);
    }
}
