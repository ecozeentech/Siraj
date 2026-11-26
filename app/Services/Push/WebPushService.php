<?php

namespace App\Services\Push;

use App\Models\System\PushSubscription;
use App\Models\User;
use App\Services\Settings;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class WebPushService
{
    public function publicKey(): ?string
    {
        return Settings::get('push.vapid_public', config('services.vapid.public_key'));
    }

    private function privateKey(): ?string
    {
        return Settings::get('push.vapid_private', config('services.vapid.private_key'));
    }

    public function configured(): bool
    {
        return filled($this->publicKey()) && filled($this->privateKey());
    }

    /** @return int number of devices the message was accepted for */
    public function sendToUser(User $user, string $title, string $body, ?string $url = null): int
    {
        if (! $this->configured()) {
            return 0;
        }
        $subs = PushSubscription::query()->where('user_id', $user->id)->get();
        if ($subs->isEmpty()) {
            return 0;
        }

        $webPush = new WebPush(['VAPID' => ['subject' => Settings::get('push.vapid_subject', config('services.vapid.subject')), 'publicKey' => $this->publicKey(), 'privateKey' => $this->privateKey()]], ['TTL' => 3600]);
        $payload = json_encode(['title' => $title, 'body' => $body, 'url' => $url ?: '/app/notifications']);
        foreach ($subs as $s) {
            $webPush->queueNotification(Subscription::create(['endpoint' => $s->endpoint, 'publicKey' => $s->public_key, 'authToken' => $s->auth_token, 'contentEncoding' => $s->content_encoding ?: 'aesgcm']), $payload);
        }

        $ok = 0;
        $byEndpoint = $subs->keyBy('endpoint');
        foreach ($webPush->flush() as $report) {
            if ($report->isSuccess()) {
                $ok++;
            } elseif ($report->isSubscriptionExpired()) {
                $byEndpoint[$report->getEndpoint()]?->delete();
            }
        }

        return $ok;
    }
}
