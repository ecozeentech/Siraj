<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class TajweedResource extends BaseModel
{
    protected function casts(): array
    {
        return ['is_published' => 'boolean'];
    }
}
