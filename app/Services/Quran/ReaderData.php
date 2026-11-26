<?php

namespace App\Services\Quran;

use App\Models\Quran\Ayah;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Builds the JSON payload the reader (and the offline downloader) consumes. Text is read verbatim from the verified tables. */
class ReaderData
{
    /**
     * @param  Collection<int, Ayah>  $ayahs
     * @return array<int, array<string, mixed>>
     */
    public function ayahs(Collection $ayahs, ?int $translationId, ?int $tafsirId): array
    {
        $ids = $ayahs->pluck('id');
        $tr = $translationId ? DB::table('translations')->where('translation_source_id', $translationId)->whereIn('ayah_id', $ids)->pluck('text', 'ayah_id') : collect();
        $tf = $tafsirId ? DB::table('tafsir_entries')->where('tafsir_source_id', $tafsirId)->whereIn('ayah_id', $ids)->pluck('text', 'ayah_id') : collect();

        return $ayahs->map(fn (Ayah $a) => [
            'id' => $a->id, 's' => $a->surah_id, 'n' => $a->number_in_surah, 'ar' => $a->displayText(), 'bm' => $a->hasLeadingBasmalah(),
            'tr' => $tr[$a->id] ?? null, 'tf' => $tf[$a->id] ?? null, 'p' => $a->page, 'j' => $a->juz, 'sj' => $a->sajda,
        ])->values()->all();
    }

    public function sources(?int $translationId, ?int $tafsirId): array
    {
        $t = $translationId ? TranslationSource::find($translationId) : null;
        $f = $tafsirId ? TafsirSource::find($tafsirId) : null;

        return [
            'translation' => $t ? ['id' => $t->id, 'name' => $t->name, 'attribution' => $t->attribution] : null,
            'tafsir' => $f ? ['id' => $f->id, 'name' => $f->name, 'attribution' => $f->attribution] : null,
        ];
    }
}
