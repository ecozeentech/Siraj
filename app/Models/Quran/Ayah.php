<?php

namespace App\Models\Quran;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use LogicException;

/**
 * Sacred text. Rows are written exclusively by the importer (QuranImporter) which
 * unlocks the model for the duration of the import. Any other update/delete throws.
 */
class Ayah extends BaseModel
{
    public $incrementing = false;

    public $timestamps = false;

    private static bool $unlocked = false;

    protected function casts(): array
    {
        return ['sajda' => 'boolean'];
    }

    public static function unlockedFor(callable $callback): mixed
    {
        self::$unlocked = true;
        try {
            return $callback();
        } finally {
            self::$unlocked = false;
        }
    }

    protected static function booted(): void
    {
        $guard = function () {
            if (! self::$unlocked) {
                throw new LogicException('Quran text is protected and can only be modified by the verified import workflow.');
            }
        };
        static::updating($guard);
        static::deleting($guard);
        static::creating($guard);
    }

    public function surah(): BelongsTo
    {
        return $this->belongsTo(Surah::class);
    }

    public function translations(): HasMany
    {
        return $this->hasMany(Translation::class);
    }

    /**
     * The imported text of ayah 1 of every surah except Al-Fatihah and At-Tawbah is prefixed with the Basmalah.
     * For display we show the Basmalah as its own line and the ayah proper beneath it. The stored text is untouched.
     */
    public function hasLeadingBasmalah(): bool
    {
        return $this->number_in_surah === 1 && ! in_array($this->surah_id, [1, 9], true)
            && str_starts_with($this->text_uthmani, self::basmalah().' ');
    }

    public function displayText(): string
    {
        return $this->hasLeadingBasmalah() ? substr($this->text_uthmani, strlen(self::basmalah()) + 1) : $this->text_uthmani;
    }

    public static function basmalah(): string
    {
        return once(fn () => (string) static::query()->whereKey(1)->value('text_uthmani'));
    }

    public function reference(): string
    {
        return $this->surah_id.':'.$this->number_in_surah;
    }

    public static function findByReference(string $ref): ?self
    {
        if (! preg_match('/^(\d{1,3}):(\d{1,3})$/', trim($ref), $m)) {
            return null;
        }

        return static::query()->where('surah_id', (int) $m[1])->where('number_in_surah', (int) $m[2])->first();
    }
}
