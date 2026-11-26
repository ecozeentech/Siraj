<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemorisationProgress extends BaseModel
{
    protected $table = 'memorisation_progress';

    protected function casts(): array
    {
        return ['last_reviewed_at' => 'datetime'];
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }
}
