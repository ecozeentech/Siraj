<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class QiblaPreference extends BaseModel
{
    protected function casts(): array
    {
        return ['use_prayer_location' => 'boolean', 'latitude' => 'float', 'longitude' => 'float'];
    }
}
