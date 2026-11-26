<?php

namespace App\Models\Academy;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseCategory extends BaseModel
{
    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
