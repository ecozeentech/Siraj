<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Juz extends BaseModel
{
    protected $table = 'juz';

    public $incrementing = false;

    public $timestamps = false;

    public function firstAyah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class, 'first_ayah_id');
    }

    public function lastAyah(): BelongsTo
    {
        return $this->belongsTo(Ayah::class, 'last_ayah_id');
    }
}
