<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class DhikrSession extends BaseModel
{
    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }
}
