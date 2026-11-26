<?php

namespace App\Models\Worship;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GuideChecklistItem extends BaseModel
{
    public function guide(): BelongsTo
    {
        return $this->belongsTo(Guide::class);
    }
}
