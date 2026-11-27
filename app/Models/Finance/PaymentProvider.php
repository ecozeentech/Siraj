<?php

namespace App\Models\Finance;

use App\Models\BaseModel;

class PaymentProvider extends BaseModel
{
    protected function casts(): array
    {
        return ['enabled' => 'boolean', 'public_details' => 'array'];
    }

    public function isManual(): bool
    {
        return $this->driver === 'manual' || $this->driver === 'custom';
    }
}
