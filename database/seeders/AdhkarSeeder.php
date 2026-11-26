<?php

namespace Database\Seeders;

use App\Models\Worship\AdhkarCategory;
use App\Models\Worship\AdhkarEntry;
use Illuminate\Database\Seeder;

/**
 * Imports the Hisn al-Muslim collection (Arabic text and repeat counts) from database/data/adhkar/hisn.json.
 * English translations and per-entry hadith references are NOT included in this dataset and are left empty
 * (is_verified = false) until a reviewer adds them in Admin > Worship.
 */
class AdhkarSeeder extends Seeder
{
    private const GROUPS = [1 => 'morning_evening', 2 => 'sleep', 3 => 'daily', 27 => 'after_prayer'];

    private const TITLES = [
        1 => 'Morning & evening adhkar', 2 => 'Before sleep', 3 => 'Upon waking', 4 => 'Entering the restroom', 5 => 'Leaving the restroom',
        6 => 'Before wudu', 7 => 'After wudu', 8 => 'Leaving the home', 9 => 'Entering the home', 10 => 'Going to the masjid',
        11 => 'Entering the masjid', 12 => 'Leaving the masjid', 13 => 'Adhan', 18 => 'Opening supplication of prayer',
        19 => 'In ruku', 20 => 'Rising from ruku', 21 => 'In sujud', 22 => 'Between the two prostrations', 24 => 'Tashahhud',
        25 => 'Salawat after tashahhud', 26 => 'Before the final salam', 27 => 'After the obligatory prayer', 28 => 'Istikhara prayer',
    ];

    public function run(): void
    {
        if (AdhkarCategory::query()->exists()) {
            return;
        }
        $data = json_decode(file_get_contents(database_path('data/adhkar/hisn.json')), true, 512, JSON_THROW_ON_ERROR);
        foreach ($data as $i => $cat) {
            $category = AdhkarCategory::query()->create([
                'slug' => 'hisn-'.$cat['id'],
                'title_ar' => $cat['title_ar'],
                'title_en' => self::TITLES[$cat['id']] ?? null,
                'group' => self::GROUPS[$cat['id']] ?? 'other',
                'source' => 'Hisn al-Muslim (Arabic text via rn0x/Adhkar-json). Needs scholarly review before launch.',
                'sort' => $i,
            ]);
            AdhkarEntry::query()->insert(collect($cat['items'])->map(fn ($item, $n) => [
                'adhkar_category_id' => $category->id,
                'text_ar' => $item['text'],
                'repeat_count' => max(1, (int) $item['count']),
                'sort' => $n,
                'is_verified' => false,
                'created_at' => now(), 'updated_at' => now(),
            ])->all());
        }
    }
}
