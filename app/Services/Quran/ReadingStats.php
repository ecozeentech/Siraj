<?php

namespace App\Services\Quran;

use App\Models\Quran\ReadingGoal;
use App\Models\Quran\ReadingHistory;
use App\Models\User;

class ReadingStats
{
    /** @return array{goal: ReadingGoal, read_today: int, percent: int}|null */
    public function goalProgress(User $user): ?array
    {
        $goal = ReadingGoal::query()->where('user_id', $user->id)->where('is_active', true)->latest()->first();
        if (! $goal) {
            return null;
        }
        $tz = $user->preferences?->timezone ?? config('app.timezone');
        $start = now($tz)->startOfDay()->utc();
        $read = ReadingHistory::query()->where('user_id', $user->id)->where('read_at', '>=', $start)->distinct()->count('ayah_id');

        return ['goal' => $goal, 'read_today' => $read, 'percent' => (int) min(100, round($read / max(1, $goal->daily_target) * 100))];
    }

    public function streak(User $user): int
    {
        $days = ReadingHistory::query()->where('user_id', $user->id)->where('read_at', '>=', now()->subDays(120))
            ->pluck('read_at')->map(fn ($d) => $d->toDateString())->unique()->flip();
        $streak = 0;
        $cursor = today();
        if (! isset($days[$cursor->toDateString()])) {
            $cursor = $cursor->subDay();
        }
        while (isset($days[$cursor->toDateString()])) {
            $streak++;
            $cursor = $cursor->subDay();
        }

        return $streak;
    }
}
