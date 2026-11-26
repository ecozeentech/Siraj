<?php

namespace App\Models\System;

use App\Models\BaseModel;

class Location extends BaseModel
{
    protected function casts(): array
    {
        return ['latitude' => 'float', 'longitude' => 'float'];
    }

    public function label(): string
    {
        return $this->name.', '.$this->country;
    }
}
