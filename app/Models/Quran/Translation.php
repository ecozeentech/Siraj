<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Translation extends BaseModel
{
    public $timestamps = false;

    public function source(): BelongsTo
    {
        return $this->belongsTo(TranslationSource::class, 'translation_source_id');
    }

    public function ayah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class);
    }
}
