<?php

namespace Database\Seeders;

use App\Models\Quran\Reciter;
use App\Services\Quran\QuranImporter;
use Illuminate\Database\Seeder;

class QuranSeeder extends Seeder
{
    public function run(): void
    {
        $importer = new QuranImporter;
        $importer->importText();
        $importer->importTranslation();
        $importer->importTafsir();

        $reciters = [
            ['ar.alafasy', 'Mishary Rashid Alafasy', 'murattal'],
            ['ar.abdurrahmaansudais', 'Abdurrahman As-Sudais', 'murattal'],
            ['ar.mahermuaiqly', 'Maher Al-Muaiqly', 'murattal'],
            ['ar.husary', 'Mahmoud Khalil Al-Husary', 'murattal'],
            ['ar.minshawi', 'Muhammad Siddiq Al-Minshawi', 'murattal'],
            ['ar.abdulbasitmurattal', 'Abdul Basit Abdus-Samad', 'murattal'],
        ];
        foreach ($reciters as $i => [$id, $name, $style]) {
            Reciter::query()->updateOrCreate(['slug' => $id], [
                'name' => $name, 'style' => $style, 'sort' => $i, 'is_active' => true,
                'audio_url_pattern' => "https://cdn.islamic.network/quran/audio/64/{$id}/{ayah}.mp3",
                'source' => 'AlQuran Cloud audio CDN (cdn.islamic.network)',
            ]);
        }
    }
}
