<?php

namespace App\Models\Worship;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guide extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function steps(): HasMany
    {
        return $this->hasMany(GuideStep::class)->orderBy('sort');
    }

    public function checklistItems(): HasMany
    {
        return $this->hasMany(GuideChecklistItem::class)->orderBy('sort');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
