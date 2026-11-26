<?php

namespace App\Models\System;

use App\Models\BaseModel;

class AppSetting extends BaseModel
{
    protected function casts(): array
    {
        return ['is_encrypted' => 'boolean'];
    }
}
