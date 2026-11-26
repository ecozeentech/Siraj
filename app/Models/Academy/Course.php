<?php

namespace App\Models\Academy;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends BaseModel
{
    protected function casts(): array
    {
        return ['is_premium' => 'boolean', 'is_published' => 'boolean', 'published_at' => 'datetime'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(Instructor::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('sort');
    }

    public function publishedLessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->where('is_published', true)->orderBy('sort');
    }

    public function quizzes(): HasMany
    {
        return $this->hasMany(Quiz::class);
    }

    public function enrolments(): HasMany
    {
        return $this->hasMany(CourseEnrolment::class);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
