<?php

namespace App\Models\Family;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ParentalControl extends BaseModel
{
    protected function casts(): array
    {
        return ['allowed_sections' => 'array', 'require_parent_approval' => 'boolean'];
    }

    public function child(): BelongsTo
    {
        return $this->belongsTo(ChildProfile::class, 'child_profile_id');
    }
}
