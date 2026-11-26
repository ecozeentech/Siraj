<?php

namespace App\Console\Commands;

use App\Services\Quran\QuranImporter;
use App\Services\Quran\QuranVerifier;
use Illuminate\Console\Command;

class QuranImportCommand extends Command
{
    protected $signature = 'quran:import {--verify : Run the verification workflow after import}';

    protected $description = 'Import the authenticated Quran text, translation and tafsir datasets';

    public function handle(QuranImporter $importer, QuranVerifier $verifier): int
    {
        $this->info('Importing Quran text…');
        $importer->importText();
        $this->info('Importing Saheeh International translation…');
        $importer->importTranslation();
        $this->info('Importing Al-Muyassar tafsir…');
        $importer->importTafsir();

        if ($this->option('verify')) {
            $report = $verifier->run();
            foreach ($report['checks'] as $c) {
                $this->line(($c['passed'] ? '  PASS ' : '  FAIL ').$c['name'].' — '.$c['detail']);
            }

            return $report['passed'] ? self::SUCCESS : self::FAILURE;
        }

        return self::SUCCESS;
    }
}
