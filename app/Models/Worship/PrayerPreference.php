<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class PrayerPreference extends BaseModel
{
    protected function casts(): array
    {
        return ['adjustments' => 'array', 'reminders_enabled' => 'boolean', 'latitude' => 'float', 'longitude' => 'float'];
    }
}
