<?php

namespace App\Models\Ai;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends BaseModel
{
    protected function casts(): array
    {
        return ['is_default' => 'boolean', 'is_active' => 'boolean', 'temperature' => 'float', 'input_cost_per_million' => 'float', 'output_cost_per_million' => 'float'];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'ai_provider_id');
    }
}
