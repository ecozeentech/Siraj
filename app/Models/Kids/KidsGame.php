<?php

namespace App\Models\Kids;

use App\Models\BaseModel;

class KidsGame extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean', 'config' => 'array'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
