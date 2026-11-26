<?php

namespace App\Models\Academy;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseEnrolment extends BaseModel
{
    protected function casts(): array
    {
        return ['enrolled_at' => 'datetime', 'completed_at' => 'datetime'];
    }

    public function getProgressPercentAttribute(): int
    {
        $total = $this->course->publishedLessons()->count();
        if ($total === 0) {
            return 0;
        }
        $done = LessonProgress::query()->where('user_id', $this->user_id)->whereNotNull('completed_at')
            ->whereIn('course_lesson_id', $this->course->publishedLessons()->pluck('id'))->count();

        return (int) round($done / $total * 100);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
