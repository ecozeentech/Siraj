<?php

namespace App\Models\Kids;

use App\Models\BaseModel;

class KidsStory extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
