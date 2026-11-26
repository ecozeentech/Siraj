<?php

namespace App\Notifications;

use App\Models\NotificationPreference;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/** In-app (and, when the user opted in, email) notification used across the platform. */
class GeneralNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public ?string $url = null,
        public string $category = 'system',
        public string $icon = 'bell',
    ) {}

    private ?array $only = null;

    public function only(array $channels): static
    {
        $this->only = $channels;

        return $this;
    }

    public function via(object $notifiable): array
    {
        if ($this->only !== null) {
            return $this->only;
        }
        $pref = NotificationPreference::query()->where('user_id', $notifiable->id)->where('category', $this->category)->first();
        $channels = [];
        if (! $pref || $pref->in_app) {
            $channels[] = 'database';
        }
        if ($pref?->email) {
            $channels[] = 'mail';
        }
        if ($pref?->push) {
            $channels[] = \App\Notifications\Channels\WebPushChannel::class;
        }

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)->subject($this->title)->greeting('Assalamu alaikum '.$notifiable->name)->line($this->body);
        if ($this->url) {
            $mail->action('Open SIRAJ', url($this->url));
        }

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return ['title' => $this->title, 'body' => $this->body, 'url' => $this->url, 'category' => $this->category, 'icon' => $this->icon];
    }
}
