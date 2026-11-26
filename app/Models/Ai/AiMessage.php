<?php

namespace App\Models\Ai;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiMessage extends BaseModel
{
    protected function casts(): array
    {
        return ['citations' => 'array'];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(AiConversation::class, 'ai_conversation_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(AiReport::class);
    }
}
