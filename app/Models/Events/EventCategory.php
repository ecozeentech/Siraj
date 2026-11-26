<?php

namespace App\Models\Events;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventCategory extends BaseModel
{
    public function events(): HasMany
    {
        return $this->hasMany(Event::class);
    }
}
