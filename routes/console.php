<?php

use App\Jobs\ExpireSubscriptions;
use App\Jobs\SendDueBroadcasts;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment('Your Faith. Your Journey. Your Ummah.');
})->purpose('Display a SIRAJ reminder');

Schedule::job(new SendDueBroadcasts)->everyMinute()->name('broadcasts:send-due')->withoutOverlapping();
Schedule::job(new ExpireSubscriptions)->hourly()->name('subscriptions:expire')->withoutOverlapping();
Schedule::command('queue:prune-batches --hours=48')->daily();
