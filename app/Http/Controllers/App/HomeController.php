<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Quran\ReadingHistory;
use App\Models\Quran\Surah;
use App\Models\Worship\PrayerLog;
use App\Services\Quran\ReadingStats;
use App\Services\Worship\Hijri;
use App\Services\Worship\PrayerTimeService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke(Request $request, PrayerTimeService $prayers, ReadingStats $reading)
    {
        $user = $request->user();
        $pref = $prayers->preferenceFor($user);
        $now = CarbonImmutable::now($pref->timezone);
        $next = $prayers->nextPrayer($pref, $now);
        $times = $prayers->timesFor($pref, $now);

        $last = ReadingHistory::query()->where('user_id', $user->id)->latest('read_at')->with('ayah')->first();
        $continue = null;
        if ($last) {
            $surah = Surah::find($last->surah_id);
            $continue = [
                'surah' => $surah, 'ayah' => $last->ayah->number_in_surah,
                'percent' => (int) round($last->ayah->number_in_surah / $surah->ayah_count * 100),
            ];
        }

        $today = PrayerLog::query()->where('user_id', $user->id)->whereDate('day', $now->toDateString())->get()->keyBy('prayer');
        $events = Event::query()->published()->upcoming()->orderBy('starts_at')->limit(3)->get();

        return view('app.home', [
            'user' => $user, 'now' => $now, 'hijri' => Hijri::format($now, (int) $pref->hijri_offset),
            'next' => $next, 'times' => $times, 'pref' => $pref, 'current' => $prayers->currentPrayer($pref, $now),
            'continue' => $continue, 'todayLog' => $today, 'events' => $events, 'goal' => $reading->goalProgress($user),
            'unread' => $user->unreadNotifications()->count(),
        ]);
    }
}
