<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class ZakatCalculation extends BaseModel
{
    protected function casts(): array
    {
        return ['inputs' => 'array', 'hawl_completed' => 'boolean'];
    }
}
