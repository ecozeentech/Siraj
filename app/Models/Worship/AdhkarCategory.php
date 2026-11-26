<?php

namespace App\Models\Worship;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdhkarCategory extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(AdhkarEntry::class)->orderBy('sort');
    }

    public function title(): string
    {
        return $this->title_en ?: $this->title_ar;
    }
}
