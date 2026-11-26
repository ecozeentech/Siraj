<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class PrayerLog extends BaseModel
{
    protected function casts(): array
    {
        return ['day' => 'date', 'in_congregation' => 'boolean', 'prayed_at' => 'datetime'];
    }
}
