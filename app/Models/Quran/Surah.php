<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Surah extends BaseModel
{
    public $incrementing = false;

    public function ayahs(): HasMany
    {
        return $this->hasMany(Ayah::class)->orderBy('number_in_surah');
    }

    public function getRouteKeyName(): string
    {
        return 'id';
    }
}
