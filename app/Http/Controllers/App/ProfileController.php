<?php

namespace App\Http\Controllers\App;

use App\Http\Controllers\Controller;
use App\Models\Academy\CourseEnrolment;
use App\Models\Quran\ReadingHistory;
use App\Models\Worship\DhikrSession;
use App\Models\Worship\PrayerLog;
use App\Services\Quran\ReadingStats;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        return view('app.profile', ['user' => $request->user()->load('profile'), 'plan' => $request->user()->plan()]);
    }

    public function dashboard(Request $request, ReadingStats $stats)
    {
        $user = $request->user();
        $week = now()->subDays(6)->startOfDay();

        $readByDay = ReadingHistory::query()->where('user_id', $user->id)->where('read_at', '>=', $week)->get()
            ->groupBy(fn ($r) => $r->read_at->toDateString())->map(fn ($g) => $g->pluck('ayah_id')->unique()->count());
        $chart = collect(range(6, 0))->map(fn ($i) => ['label' => now()->subDays($i)->format('D'), 'value' => (int) ($readByDay[now()->subDays($i)->toDateString()] ?? 0)]);

        return view('app.dashboard', [
            'user' => $user, 'streak' => $stats->streak($user), 'chart' => $chart,
            'ayahsTotal' => ReadingHistory::query()->where('user_id', $user->id)->distinct()->count('ayah_id'),
            'prayersWeek' => PrayerLog::query()->where('user_id', $user->id)->where('day', '>=', now()->subDays(6)->toDateString())->whereIn('status', ['on_time', 'late', 'qada'])->count(),
            'dhikrTotal' => (int) DhikrSession::query()->where('user_id', $user->id)->sum('count'),
            'courses' => CourseEnrolment::query()->where('user_id', $user->id)->with('course')->latest()->limit(5)->get(),
            'bookmarks' => $user->hasMany(\App\Models\Quran\Bookmark::class)->count(),
        ]);
    }
}
