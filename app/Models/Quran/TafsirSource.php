<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TafsirSource extends BaseModel
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(TafsirEntry::class);
    }
}
