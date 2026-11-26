<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reciter extends BaseModel
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function audioFiles(): HasMany
    {
        return $this->hasMany(AudioFile::class);
    }

    public function urlForAyah(Ayah|int $ayah): ?string
    {
        $id = $ayah instanceof Ayah ? $ayah->id : $ayah;
        $override = $this->audioFiles()->where('ayah_id', $id)->first();
        if ($override) {
            return $override->resolvedUrl();
        }

        return $this->audio_url_pattern ? str_replace('{ayah}', (string) $id, $this->audio_url_pattern) : null;
    }
}
