<?php

namespace App\Notifications\Channels;

use App\Services\Push\WebPushService;
use Illuminate\Notifications\Notification;

class WebPushChannel
{
    public function __construct(private WebPushService $push) {}

    public function send(object $notifiable, Notification $notification): void
    {
        $data = $notification->toArray($notifiable);
        $this->push->sendToUser($notifiable, $data['title'], $data['body'], $data['url'] ?? null);
    }
}
