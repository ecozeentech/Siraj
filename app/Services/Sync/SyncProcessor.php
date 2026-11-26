<?php

namespace App\Services\Sync;

use App\Models\Quran\Ayah;
use App\Models\Quran\AyahNote;
use App\Models\Quran\Bookmark;
use App\Models\Quran\MemorisationProgress;
use App\Models\Quran\ReadingHistory;
use App\Models\User;
use App\Models\Worship\AdhkarEntry;
use App\Models\Worship\AdhkarProgress;
use App\Models\Worship\DhikrSession;
use App\Models\Worship\PrayerLog;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * Applies actions that were queued by the client (possibly while offline). Every action carries a
 * client-generated id; a receipt row guarantees at-most-once application, so retries cannot duplicate data.
 * All handlers only ever touch rows owned by $user (no client-supplied user ids are trusted).
 */
class SyncProcessor
{
    public const TYPES = ['reading.progress', 'bookmark.toggle', 'note.save', 'dhikr.session', 'adhkar.progress', 'prayer.log', 'memorisation.set'];

    /** @return array{processed: string[], rejected: array<string, string>} */
    public function process(User $user, array $actions): array
    {
        $processed = [];
        $rejected = [];

        foreach ($actions as $action) {
            $id = (string) ($action['id'] ?? '');
            $type = (string) ($action['type'] ?? '');
            if ($id === '' || strlen($id) > 64) {
                continue;
            }
            if (DB::table('sync_receipts')->where('user_id', $user->id)->where('client_id', $id)->exists()) {
                $processed[] = $id;

                continue;
            }
            if (! in_array($type, self::TYPES, true)) {
                $rejected[$id] = 'unknown_type';
                $processed[] = $id;

                continue;
            }

            try {
                DB::transaction(function () use ($user, $id, $type, $action) {
                    $this->handle($user, $type, (array) ($action['payload'] ?? []));
                    DB::table('sync_receipts')->insert(['user_id' => $user->id, 'client_id' => $id, 'type' => $type, 'created_at' => now(), 'updated_at' => now()]);
                });
                $processed[] = $id;
            } catch (\Illuminate\Validation\ValidationException $e) {
                $rejected[$id] = 'invalid';
                $processed[] = $id; // permanently invalid: drop instead of retrying forever
            } catch (QueryException $e) {
                if (str_contains(strtolower($e->getMessage()), 'unique')) {
                    $processed[] = $id; // concurrent duplicate

                    continue;
                }
                report($e);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return compact('processed', 'rejected');
    }

    private function handle(User $user, string $type, array $p): void
    {
        match ($type) {
            'reading.progress' => $this->readingProgress($user, $p),
            'bookmark.toggle' => $this->bookmark($user, $p),
            'note.save' => $this->note($user, $p),
            'dhikr.session' => $this->dhikr($user, $p),
            'adhkar.progress' => $this->adhkar($user, $p),
            'prayer.log' => $this->prayer($user, $p),
            'memorisation.set' => $this->memorisation($user, $p),
        };
    }

    private function validate(array $p, array $rules): array
    {
        return Validator::make($p, $rules)->validate();
    }

    private function readingProgress(User $user, array $p): void
    {
        $d = $this->validate($p, ['ayah_id' => 'required|integer|between:1,6236']);
        $ayah = Ayah::query()->findOrFail($d['ayah_id']);
        $last = ReadingHistory::query()->where('user_id', $user->id)->latest('read_at')->first();
        if ($last && $last->ayah_id === $ayah->id && $last->read_at->gt(now()->subMinute())) {
            return;
        }
        ReadingHistory::query()->create(['user_id' => $user->id, 'ayah_id' => $ayah->id, 'surah_id' => $ayah->surah_id, 'read_at' => now()]);
    }

    private function bookmark(User $user, array $p): void
    {
        $d = $this->validate($p, ['ayah_id' => 'required|integer|between:1,6236', 'on' => 'required|boolean']);
        if ($d['on']) {
            Bookmark::query()->firstOrCreate(['user_id' => $user->id, 'ayah_id' => $d['ayah_id']]);
        } else {
            Bookmark::query()->where('user_id', $user->id)->where('ayah_id', $d['ayah_id'])->delete();
        }
    }

    private function note(User $user, array $p): void
    {
        $d = $this->validate($p, ['ayah_id' => 'required|integer|between:1,6236', 'body' => 'required|string|max:2000']);
        AyahNote::query()->updateOrCreate(['user_id' => $user->id, 'ayah_id' => $d['ayah_id']], ['body' => $d['body']]);
    }

    private function dhikr(User $user, array $p): void
    {
        $d = $this->validate($p, ['phrase' => 'required|string|max:120', 'count' => 'required|integer|min:1|max:100000', 'target' => 'nullable|integer|min:1|max:100000']);
        DhikrSession::query()->create($d + ['user_id' => $user->id, 'completed_at' => now()]);
    }

    private function adhkar(User $user, array $p): void
    {
        $d = $this->validate($p, ['entry_id' => 'required|integer|exists:adhkar_entries,id', 'count' => 'required|integer|min:0|max:1000', 'day' => 'required|date_format:Y-m-d']);
        $max = AdhkarEntry::query()->whereKey($d['entry_id'])->value('repeat_count');
        AdhkarProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'adhkar_entry_id' => $d['entry_id'], 'day' => $d['day']],
            ['count' => min($d['count'], (int) $max)],
        );
    }

    private function prayer(User $user, array $p): void
    {
        $d = $this->validate($p, [
            'prayer' => 'required|in:fajr,dhuhr,asr,maghrib,isha', 'day' => 'required|date_format:Y-m-d|before_or_equal:tomorrow',
            'status' => 'required|in:on_time,late,missed,qada,none', 'in_congregation' => 'sometimes|boolean',
        ]);
        $key = ['user_id' => $user->id, 'day' => $d['day'], 'prayer' => $d['prayer']];
        if ($d['status'] === 'none') {
            PrayerLog::query()->where($key)->delete();

            return;
        }
        PrayerLog::query()->updateOrCreate($key, ['status' => $d['status'], 'in_congregation' => (bool) ($d['in_congregation'] ?? false), 'prayed_at' => $d['status'] === 'missed' ? null : now()]);
    }

    private function memorisation(User $user, array $p): void
    {
        $d = $this->validate($p, ['ayah_id' => 'required|integer|between:1,6236', 'status' => 'required|in:learning,memorised,revising,none', 'strength' => 'sometimes|integer|between:1,5']);
        $ayah = Ayah::query()->findOrFail($d['ayah_id']);
        if ($d['status'] === 'none') {
            MemorisationProgress::query()->where('user_id', $user->id)->where('ayah_id', $ayah->id)->delete();

            return;
        }
        MemorisationProgress::query()->updateOrCreate(
            ['user_id' => $user->id, 'ayah_id' => $ayah->id],
            ['surah_id' => $ayah->surah_id, 'status' => $d['status'], 'strength' => $d['strength'] ?? 1, 'last_reviewed_at' => now()],
        );
    }
}
