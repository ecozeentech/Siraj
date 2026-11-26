<?php

namespace App\Models\Ai;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends BaseModel
{
    protected function casts(): array
    {
        return ['created_at' => 'datetime', 'cost_usd' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }

    public $timestamps = false;
}
