<?php

namespace App\Models\Ai;

use App\Models\BaseModel;

class AiSource extends BaseModel
{
    protected function casts(): array
    {
        return ['is_verified' => 'boolean', 'is_active' => 'boolean'];
    }
}
