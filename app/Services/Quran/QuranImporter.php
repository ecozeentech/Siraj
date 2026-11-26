<?php

namespace App\Services\Quran;

use App\Models\Quran\Ayah;
use App\Models\Quran\QuranEdition;
use App\Models\Quran\Surah;
use App\Models\Quran\TafsirSource;
use App\Models\Quran\TranslationSource;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Imports the authenticated Quran datasets stored in database/data/quran.
 *
 * Text provenance: Tanzil Project "Uthmani" text (https://tanzil.net), as redistributed by
 * the AlQuran Cloud API (https://alquran.cloud). We never generate or edit Quran text: rows are
 * inserted verbatim, hashed (sha256) and later re-verified by QuranVerifier.
 */
class QuranImporter
{
    public function __construct(private ?string $dataPath = null)
    {
        $this->dataPath ??= database_path('data/quran');
    }

    public function importText(): QuranEdition
    {
        $data = $this->read('uthmani.json');
        $surahs = $data['surahs'];
        $ayahs = $data['ayahs'];

        if (count($surahs) !== 114 || count($ayahs) !== 6236) {
            throw new RuntimeException('Quran dataset failed structural validation (expected 114 surahs and 6236 ayahs).');
        }

        DB::transaction(function () use ($surahs, $ayahs) {
            DB::table('surahs')->upsert(array_map(fn ($s) => [
                'id' => $s['number'], 'name_ar' => $s['name_ar'], 'name_en' => $s['name_en'], 'meaning_en' => $s['meaning_en'],
                'revelation' => $s['revelation'], 'ayah_count' => $s['ayah_count'], 'created_at' => now(), 'updated_at' => now(),
            ], $surahs), ['id'], ['name_ar', 'name_en', 'meaning_en', 'revelation', 'ayah_count', 'updated_at']);

            Ayah::unlockedFor(function () use ($ayahs) {
                foreach (array_chunk($ayahs, 500) as $chunk) {
                    $rows = array_map(fn ($a) => [
                        'id' => $a[0], 'surah_id' => $a[1], 'number_in_surah' => $a[2], 'text_uthmani' => $a[3],
                        'text_hash' => hash('sha256', $a[3]), 'juz' => $a[4], 'page' => $a[5], 'manzil' => $a[6],
                        'ruku' => $a[7], 'hizb_quarter' => $a[8], 'sajda' => $a[9],
                    ], $chunk);
                    DB::table('ayahs')->upsert($rows, ['id'], ['surah_id', 'number_in_surah', 'text_uthmani', 'text_hash', 'juz', 'page', 'manzil', 'ruku', 'hizb_quarter', 'sajda']);
                }
            });

            $byJuz = [];
            foreach ($ayahs as $a) {
                $byJuz[$a[4]]['first'] ??= $a[0];
                $byJuz[$a[4]]['last'] = $a[0];
            }
            foreach ($byJuz as $juz => $range) {
                DB::table('juz')->upsert([['id' => $juz, 'first_ayah_id' => $range['first'], 'last_ayah_id' => $range['last']]], ['id'], ['first_ayah_id', 'last_ayah_id']);
            }
        });

        return QuranEdition::query()->updateOrCreate(['slug' => 'quran-uthmani'], [
            'name' => 'Quran (Uthmani script)',
            'source' => 'Tanzil Project via AlQuran Cloud API',
            'source_url' => 'https://tanzil.net',
            'license' => 'Tanzil terms: verbatim redistribution with attribution; text must not be altered.',
            'attribution' => 'Quran text © Tanzil Project (tanzil.net), used unmodified under the Tanzil license terms.',
            'checksum' => $this->checksumFromDatabase(),
            'surah_count' => 114,
            'ayah_count' => 6236,
            'imported_at' => now(),
        ]);
    }

    public function importTranslation(): TranslationSource
    {
        $data = $this->read('trans-en-sahih.json');
        $source = TranslationSource::query()->updateOrCreate(['slug' => 'en-sahih'], [
            'name' => 'Saheeh International', 'translator' => 'Saheeh International', 'language' => 'en',
            'source' => 'Tanzil Project via AlQuran Cloud API', 'source_url' => 'https://tanzil.net/trans/en.sahih',
            'license' => 'Tanzil translation terms (non-commercial redistribution with attribution; verify before commercial launch).',
            'attribution' => 'Saheeh International translation, obtained via Tanzil Project. Verify licence for commercial use.',
            'is_active' => true, 'sort' => 1,
        ]);
        $this->bulk('translations', 'translation_source_id', $source->id, $data['rows']);

        return $source;
    }

    public function importTafsir(): TafsirSource
    {
        $data = $this->read('tafsir-ar-muyassar.json');
        $source = TafsirSource::query()->updateOrCreate(['slug' => 'ar-muyassar'], [
            'name' => 'Al-Tafsir Al-Muyassar', 'author' => 'King Fahd Glorious Quran Printing Complex', 'language' => 'ar',
            'source' => 'AlQuran Cloud API (edition ar.muyassar)', 'source_url' => 'https://alquran.cloud',
            'license' => 'King Fahd Complex publication; verify redistribution terms before commercial launch.',
            'attribution' => 'Tafsir al-Muyassar, King Fahd Glorious Quran Printing Complex, Madinah.',
            'is_active' => true, 'sort' => 1,
        ]);
        $this->bulk('tafsir_entries', 'tafsir_source_id', $source->id, $data['rows']);

        return $source;
    }

    private function bulk(string $table, string $fk, int $sourceId, array $rows): void
    {
        DB::transaction(function () use ($table, $fk, $sourceId, $rows) {
            foreach (array_chunk($rows, 500) as $chunk) {
                DB::table($table)->upsert(
                    array_map(fn ($r) => [$fk => $sourceId, 'ayah_id' => $r[0], 'text' => $r[1]], $chunk),
                    [$fk, 'ayah_id'],
                    ['text'],
                );
            }
        });
    }

    public function checksumFromDatabase(): string
    {
        $ctx = hash_init('sha256');
        DB::table('ayahs')->orderBy('id')->select(['id', 'text_uthmani'])->chunk(1000, function ($rows) use ($ctx) {
            foreach ($rows as $row) {
                hash_update($ctx, $row->id.'|'.$row->text_uthmani."\n");
            }
        });

        return hash_final($ctx);
    }

    private function read(string $file): array
    {
        $path = $this->dataPath.'/'.$file;
        if (! is_file($path)) {
            throw new RuntimeException("Missing Quran dataset file: {$path}");
        }

        return json_decode(file_get_contents($path), true, 512, JSON_THROW_ON_ERROR);
    }
}
