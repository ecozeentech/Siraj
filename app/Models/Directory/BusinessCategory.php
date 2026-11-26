<?php

namespace App\Models\Directory;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BusinessCategory extends BaseModel
{
    public function businesses(): HasMany
    {
        return $this->hasMany(Business::class);
    }
}
