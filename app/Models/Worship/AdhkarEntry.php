<?php

namespace App\Models\Worship;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdhkarEntry extends BaseModel
{
    protected function casts(): array
    {
        return ['is_verified' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AdhkarCategory::class, 'adhkar_category_id');
    }
}
