<?php

namespace App\Jobs;

use App\Models\Worship\PrayerPreference;
use App\Notifications\GeneralNotification;
use App\Services\Worship\PrayerTimeService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;

/** Notifies members who turned prayer reminders on, once per prayer, in their own timezone. */
class SendPrayerReminders implements ShouldQueue
{
    use Queueable;

    public function handle(PrayerTimeService $prayers): void
    {
        PrayerPreference::query()->where('reminders_enabled', true)->with('user')->chunkById(200, function ($rows) use ($prayers) {
            foreach ($rows as $pref) {
                $user = $pref->user;
                if (! $user || $user->status !== 'active') {
                    continue;
                }
                $now = CarbonImmutable::now($pref->timezone ?: 'Africa/Lagos');
                $times = $prayers->timesFor($pref, $now);
                $lead = max(0, (int) $pref->remind_minutes_before);
                foreach (['fajr', 'dhuhr', 'asr', 'maghrib', 'isha'] as $name) {
                    $at = $times[$name] ?? null;
                    if (! $at) {
                        continue;
                    }
                    $opens = $at->subMinutes($lead);
                    if ($now->lt($opens) || $now->gt($at)) {
                        continue;
                    }
                    $key = 'prayer-remind:'.$user->id.':'.$now->toDateString().':'.$name;
                    if (! Cache::add($key, 1, $now->endOfDay()->addHour())) {
                        continue;
                    }
                    $user->notify(new GeneralNotification(
                        ucfirst($name).' in '.$lead.' min',
                        ucfirst($name).' is at '.$at->format('g:ia').' ('.$pref->location_name.').',
                        '/app/worship/prayer-times',
                        'prayer',
                        'clock',
                    ));
                }
            }
        });
    }
}
