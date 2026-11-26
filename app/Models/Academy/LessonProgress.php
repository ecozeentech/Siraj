<?php

namespace App\Models\Academy;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends BaseModel
{
    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CourseLesson::class, 'course_lesson_id');
    }

    protected $table = 'lesson_progress';
}
