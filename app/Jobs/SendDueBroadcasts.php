<?php

namespace App\Jobs;

use App\Models\System\BroadcastNotification;
use App\Services\Admin\BroadcastService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDueBroadcasts implements ShouldQueue
{
    use Queueable;

    public function handle(BroadcastService $service): void
    {
        BroadcastNotification::query()->where('status', 'scheduled')->where('scheduled_at', '<=', now())->each(fn ($b) => $service->send($b));
    }
}
