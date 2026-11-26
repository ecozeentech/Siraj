<?php

namespace App\Models\Finance;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionPlan extends BaseModel
{
    protected function casts(): array
    {
        return ['features' => 'array', 'entitlements' => 'array', 'is_active' => 'boolean', 'is_featured' => 'boolean'];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscription_plan_id');
    }

    public function priceFormatted(): string
    {
        return \App\Support\Money::format($this->price_kobo, $this->currency);
    }
}
