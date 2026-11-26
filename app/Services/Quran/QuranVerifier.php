<?php

namespace App\Services\Quran;

use App\Models\Quran\QuranEdition;
use Illuminate\Support\Facades\DB;

/**
 * Verification workflow for the stored Quran text. It is read-only: it detects problems, it never repairs text.
 */
class QuranVerifier
{
    /** Canonical ayah count for each of the 114 surahs. */
    public const AYAH_COUNTS = [
        7, 286, 200, 176, 120, 165, 206, 75, 129, 109, 123, 111, 43, 52, 99, 128, 111, 110, 98, 135, 112, 78, 118, 64, 77, 227, 93, 88, 69, 60,
        34, 30, 73, 54, 45, 83, 182, 88, 75, 85, 54, 53, 89, 59, 37, 35, 38, 29, 18, 45, 60, 49, 62, 55, 78, 96, 29, 22, 24, 13, 14, 11,
        11, 18, 12, 12, 30, 52, 52, 44, 28, 28, 20, 56, 40, 31, 50, 40, 46, 42, 29, 19, 36, 25, 22, 17, 19, 26, 30, 20, 15, 21, 11, 8,
        8, 19, 5, 8, 8, 11, 11, 8, 3, 9, 5, 4, 7, 3, 6, 3, 5, 4, 5, 6,
    ];

    public function run(?int $verifiedBy = null): array
    {
        $checks = [];
        $add = function (string $name, bool $ok, string $detail) use (&$checks) {
            $checks[] = ['name' => $name, 'passed' => $ok, 'detail' => $detail];
        };

        $surahCount = DB::table('surahs')->count();
        $ayahCount = DB::table('ayahs')->count();
        $add('Surah count is 114', $surahCount === 114, "found {$surahCount}");
        $add('Ayah count is 6236', $ayahCount === 6236, "found {$ayahCount}");
        $add('Canonical ayah counts sum to 6236', array_sum(self::AYAH_COUNTS) === 6236, (string) array_sum(self::AYAH_COUNTS));

        $mismatched = [];
        $actual = DB::table('ayahs')->select('surah_id', DB::raw('count(*) as c'), DB::raw('max(number_in_surah) as m'))->groupBy('surah_id')->get()->keyBy('surah_id');
        foreach (self::AYAH_COUNTS as $i => $expected) {
            $row = $actual->get($i + 1);
            if (! $row || (int) $row->c !== $expected || (int) $row->m !== $expected) {
                $mismatched[] = $i + 1;
            }
        }
        $add('Per-surah ayah counts and numbering match canonical order', $mismatched === [], $mismatched ? 'mismatch in surahs: '.implode(',', $mismatched) : 'all 114 surahs match');

        $minId = (int) DB::table('ayahs')->min('id');
        $maxId = (int) DB::table('ayahs')->max('id');
        $add('Ayah ids are contiguous 1..6236', $minId === 1 && $maxId === 6236 && $ayahCount === 6236, "min {$minId}, max {$maxId}");

        $tampered = 0;
        DB::table('ayahs')->select(['id', 'text_uthmani', 'text_hash'])->orderBy('id')->chunk(1000, function ($rows) use (&$tampered) {
            foreach ($rows as $row) {
                if (hash('sha256', $row->text_uthmani) !== $row->text_hash) {
                    $tampered++;
                }
            }
        });
        $add('Every ayah matches its stored sha256 hash', $tampered === 0, $tampered === 0 ? 'no altered rows detected' : "{$tampered} altered rows");

        $edition = QuranEdition::query()->where('slug', 'quran-uthmani')->first();
        $current = (new QuranImporter)->checksumFromDatabase();
        $add('Whole-text checksum matches the import record', $edition && $edition->checksum === $current, $edition ? 'checksum '.substr($current, 0, 16).'…' : 'no import record');

        $juz = DB::table('juz')->count();
        $add('30 juz are mapped', $juz === 30, "found {$juz}");

        foreach (DB::table('translation_sources')->get() as $src) {
            $n = DB::table('translations')->where('translation_source_id', $src->id)->count();
            $add("Translation “{$src->name}” covers all ayahs", $n === 6236, "{$n}/6236");
        }
        foreach (DB::table('tafsir_sources')->get() as $src) {
            $n = DB::table('tafsir_entries')->where('tafsir_source_id', $src->id)->count();
            $add("Tafsir “{$src->name}” covers all ayahs", $n === 6236, "{$n}/6236");
        }

        $passed = collect($checks)->every(fn ($c) => $c['passed']);
        $report = ['passed' => $passed, 'checks' => $checks, 'ran_at' => now()->toIso8601String(), 'notes' => 'Page numbers are imported as reference metadata only; a verified 604-page Mushaf mapping has not been authenticated.'];

        if ($edition) {
            $edition->update([
                'verification_report' => $report,
                'verified_at' => $passed ? now() : null,
                'verified_by' => $passed ? $verifiedBy : null,
            ]);
        }

        return $report;
    }
}
