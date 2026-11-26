<?php

namespace App\Jobs;

use App\Services\Finance\SubscriptionService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ExpireSubscriptions implements ShouldQueue
{
    use Queueable;

    public function handle(SubscriptionService $subscriptions): void
    {
        $subscriptions->expireDue();
    }
}
