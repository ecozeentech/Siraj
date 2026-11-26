<?php

namespace App\Models\Worship;

use App\Models\BaseModel;

class AdhkarProgress extends BaseModel
{
    protected $table = 'adhkar_progress';

    protected function casts(): array
    {
        return ['day' => 'date'];
    }
}
