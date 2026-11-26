<?php

namespace App\Models\Kids;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KidsCourse extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(KidsLesson::class)->orderBy('sort');
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
