<?php

namespace App\Models;

class NotificationPreference extends BaseModel
{
    public const CATEGORIES = [
        'prayer' => 'Prayer reminders', 'quran' => 'Quran & reading goals', 'academy' => 'Academy & learning',
        'community' => 'Community & messages', 'events' => 'Events', 'system' => 'Account & security', 'marketing' => 'News & offers',
    ];

    protected function casts(): array
    {
        return ['in_app' => 'boolean', 'email' => 'boolean', 'push' => 'boolean'];
    }
}
