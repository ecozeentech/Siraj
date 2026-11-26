<?php

namespace App\Models\Kids;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KidsProgress extends BaseModel
{
    protected function casts(): array
    {
        return ['completed_at' => 'datetime'];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Family\ChildProfile::class, 'child_profile_id');
    }

    protected $table = 'kids_progress';
}
