<?php

namespace App\Models\Kids;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsLesson extends BaseModel
{
    public function course(): BelongsTo
    {
        return $this->belongsTo(KidsCourse::class, 'kids_course_id');
    }
}
