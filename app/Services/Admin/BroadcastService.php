<?php

namespace App\Services\Admin;

use App\Models\NotificationPreference;
use App\Models\System\BroadcastDelivery;
use App\Models\System\BroadcastNotification;
use App\Models\User;
use App\Notifications\GeneralNotification;
use App\Services\Push\WebPushService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Notification;

class BroadcastService
{
    public function __construct(private WebPushService $push) {}

    public function audienceQuery(BroadcastNotification $b): Builder
    {
        $q = User::query()->where('status', 'active')->where('is_staff', false);
        $f = $b->audience_filter ?? [];

        return match ($b->audience) {
            'plan' => $q->whereHas('subscriptions', fn ($s) => $s->where('status', 'active')->whereIn('subscription_plan_id', (array) ($f['plan_ids'] ?? []))),
            'role' => $q->whereHas('roles', fn ($r) => $r->whereIn('roles.id', (array) ($f['role_ids'] ?? []))),
            'users' => $q->whereIn('id', (array) ($f['user_ids'] ?? [])),
            default => $q,
        };
    }

    public function audienceCount(BroadcastNotification $b): int
    {
        return $this->audienceQuery($b)->count();
    }

    /** Deliver a broadcast. Each user receives it once per channel; user opt-outs for email/push are respected. */
    public function send(BroadcastNotification $b): void
    {
        $locked = BroadcastNotification::query()->whereKey($b->id)->whereIn('status', ['draft', 'scheduled'])->update(['status' => 'sending']);
        if (! $locked) {
            return;
        }
        $channels = $b->channels;
        $stats = ['recipients' => 0, 'delivered' => 0, 'failed' => 0];

        $this->audienceQuery($b)->chunkById(200, function ($users) use ($b, $channels, &$stats) {
            foreach ($users as $user) {
                $stats['recipients']++;
                $prefs = NotificationPreference::query()->where('user_id', $user->id)->where('category', $b->category)->first();
                foreach ($channels as $channel) {
                    $status = 'delivered';
                    $error = null;
                    try {
                        if ($channel === 'in_app') {
                            if ($prefs && ! $prefs->in_app) {
                                $status = 'skipped';
                            } else {
                                $user->notifications()->create(['id' => (string) \Illuminate\Support\Str::uuid(), 'type' => GeneralNotification::class, 'data' => ['title' => $b->title, 'body' => $b->body, 'url' => $b->url, 'category' => $b->category, 'icon' => 'megaphone']]);
                            }
                        } elseif ($channel === 'email') {
                            if (! $prefs?->email) {
                                $status = 'skipped';
                            } else {
                                Notification::sendNow($user, (new GeneralNotification($b->title, $b->body, $b->url, $b->category, 'megaphone'))->only(['mail']));
                            }
                        } elseif ($channel === 'push') {
                            if (! $prefs?->push || $this->push->sendToUser($user, $b->title, $b->body, $b->url) === 0) {
                                $status = 'skipped';
                            }
                        }
                    } catch (\Throwable $e) {
                        $status = 'failed';
                        $error = mb_substr($e->getMessage(), 0, 200);
                    }
                    BroadcastDelivery::query()->create(['broadcast_notification_id' => $b->id, 'user_id' => $user->id, 'channel' => $channel, 'status' => $status, 'error' => $error]);
                    $status === 'delivered' && $stats['delivered']++;
                    $status === 'failed' && $stats['failed']++;
                }
            }
        });

        $b->update(['status' => $stats['failed'] && ! $stats['delivered'] ? 'failed' : 'sent', 'sent_at' => now(), 'recipients_count' => $stats['recipients'], 'delivered_count' => $stats['delivered'], 'failed_count' => $stats['failed']]);
    }
}
