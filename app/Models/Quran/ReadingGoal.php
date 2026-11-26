<?php

namespace App\Models\Quran;

use App\Models\BaseModel;

class ReadingGoal extends BaseModel
{
    protected function casts(): array
    {
        return ['starts_on' => 'date', 'ends_on' => 'date', 'is_active' => 'boolean'];
    }
}
