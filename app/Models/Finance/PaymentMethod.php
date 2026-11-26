<?php

namespace App\Models\Finance;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentMethod extends BaseModel
{
    protected function casts(): array
    {
        return ['authorization_code' => 'encrypted', 'is_default' => 'boolean'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    protected $hidden = ['authorization_code'];
}
