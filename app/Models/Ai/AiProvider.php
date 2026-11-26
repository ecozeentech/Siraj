<?php

namespace App\Models\Ai;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AiProvider extends BaseModel
{
    protected function casts(): array
    {
        return ['api_key' => 'encrypted', 'is_active' => 'boolean', 'is_default' => 'boolean', 'settings' => 'array'];
    }

    public function models(): HasMany
    {
        return $this->hasMany(AiModel::class);
    }

    protected $hidden = ['api_key'];

    public function hasCredentials(): bool
    {
        return filled($this->api_key) && filled($this->base_url);
    }
}
