<?php

namespace App\Services;

use App\Models\Finance\Wallet;
use App\Models\Quran\Reciter;
use App\Models\Quran\TranslationSource;
use App\Models\User;
use App\Models\Worship\PrayerPreference;
use App\Models\Worship\QiblaPreference;
use App\Services\Finance\LedgerService;

/** Creates the per-user records every account needs (profile, preferences, wallet…). Safe to call twice. */
class UserProvisioner
{
    public function provision(User $user): void
    {
        $user->profile()->firstOrCreate([]);
        $user->preferences()->firstOrCreate([], [
            'quran_reading_mode' => 'ayah',
            'translation_source_id' => TranslationSource::query()->where('is_active', true)->orderBy('sort')->value('id'),
            'reciter_id' => Reciter::query()->where('is_active', true)->orderBy('sort')->value('id'),
        ]);
        PrayerPreference::query()->firstOrCreate(['user_id' => $user->id]);
        QiblaPreference::query()->firstOrCreate(['user_id' => $user->id]);
        $wallet = Wallet::query()->firstOrCreate(['user_id' => $user->id], ['currency' => config('siraj.currency')]);
        app(LedgerService::class)->ensureUserAccount($wallet);
    }
}
