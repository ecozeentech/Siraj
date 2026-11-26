<?php

namespace App\Models;

use App\Models\Quran\Reciter;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends BaseModel
{
    protected function casts(): array
    {
        return ['show_translation' => 'boolean', 'show_tafsir' => 'boolean', 'quran_font_size' => 'integer'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function translationSource(): BelongsTo
    {
        return $this->belongsTo(TranslationSource::class);
    }

    public function tafsirSource(): BelongsTo
    {
        return $this->belongsTo(TafsirSource::class);
    }

    public function reciter(): BelongsTo
    {
        return $this->belongsTo(Reciter::class);
    }
}
