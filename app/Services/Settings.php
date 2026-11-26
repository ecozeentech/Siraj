<?php

namespace App\Services;

use App\Models\System\AppSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Key/value application settings persisted in app_settings (DB), with optional encryption for secrets.
 * Secret values are never returned to the browser by any controller; admin forms only show whether a value is set.
 */
class Settings
{
    private const CACHE_KEY = 'siraj.settings';

    public static function all(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, function () {
                if (! Schema::hasTable('app_settings')) {
                    return [];
                }

                return AppSetting::query()->get()->mapWithKeys(function (AppSetting $s) {
                    try {
                        return [$s->key => $s->is_encrypted && $s->value ? Crypt::decryptString($s->value) : $s->value];
                    } catch (Throwable) {
                        return [$s->key => null];
                    }
                })->all();
            });
        } catch (Throwable) {
            return [];
        }
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $value = self::all()[$key] ?? null;

        return filled($value) ? $value : $default;
    }

    public static function has(string $key): bool
    {
        return filled(self::all()[$key] ?? null);
    }

    public static function set(string $key, mixed $value, string $group = 'general', bool $encrypted = false): void
    {
        AppSetting::query()->updateOrCreate(['key' => $key], [
            'value' => $encrypted && filled($value) ? Crypt::encryptString((string) $value) : $value,
            'group' => $group,
            'is_encrypted' => $encrypted,
        ]);
        Cache::forget(self::CACHE_KEY);
    }

    public static function forget(string $key): void
    {
        AppSetting::query()->where('key', $key)->delete();
        Cache::forget(self::CACHE_KEY);
    }
}
