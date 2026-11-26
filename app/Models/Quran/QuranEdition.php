<?php

namespace App\Models\Quran;

use App\Models\BaseModel;

class QuranEdition extends BaseModel
{
    protected function casts(): array
    {
        return ['imported_at' => 'datetime', 'verified_at' => 'datetime', 'verification_report' => 'array'];
    }
}
