<?php

namespace App\Support;

final class Navigation
{
    /** Bottom navigation exactly as in the reference: Home, Quran, Worship, Learn, More. */
    public static function primary(): array
    {
        return [
            ['Home', 'app.home', 'house', ['app.home']],
            ['Quran', 'app.quran.index', 'book-open', ['app.quran.*']],
            ['Worship', 'app.worship.index', 'landmark', ['app.worship.*', 'app.hajj.*']],
            ['Learn', 'app.academy.index', 'graduation-cap', ['app.academy.*']],
            ['More', 'app.more', 'layout-grid', ['app.more', 'app.community.*', 'app.messages.*', 'app.events.*', 'app.directory.*', 'app.zakat.*', 'app.ai.*', 'app.wallet.*', 'app.profile.*', 'app.settings.*', 'app.notifications', 'app.family.*', 'app.support.*', 'app.subscription.*']],
        ];
    }

    public static function sidebar(): array
    {
        return [
            'Main' => [
                ['Home', 'app.home', 'house', ['app.home']],
                ['Quran & Tafsir', 'app.quran.index', 'book-open', ['app.quran.*']],
                ['Prayer & Worship', 'app.worship.index', 'landmark', ['app.worship.*']],
                ['Islamic Academy', 'app.academy.index', 'graduation-cap', ['app.academy.*']],
                ['Kids Deen World', 'app.kids.index', 'baby', ['app.kids.*']],
            ],
            'Connect' => [
                ['Community', 'app.community.index', 'users', ['app.community.*']],
                ['Messages', 'app.messages.index', 'message-circle', ['app.messages.*']],
                ['Events', 'app.events.index', 'calendar-days', ['app.events.*']],
                ['Directory', 'app.directory.index', 'map-pin', ['app.directory.*']],
            ],
            'Tools' => [
                ['SIRAJ AI', 'app.ai.index', 'sparkles', ['app.ai.*']],
                ['Hajj & Umrah', 'app.hajj.index', 'compass', ['app.hajj.*']],
                ['Zakat Calculator', 'app.zakat.index', 'calculator', ['app.zakat.*']],
            ],
            'Account' => [
                ['Wallet & Plan', 'app.wallet.index', 'wallet', ['app.wallet.*', 'app.subscription.*']],
                ['Notifications', 'app.notifications', 'bell', ['app.notifications']],
                ['Profile', 'app.profile.show', 'user', ['app.profile.*']],
                ['Settings', 'app.settings.index', 'settings', ['app.settings.*']],
            ],
        ];
    }

    public static function admin(): array
    {
        return [
            'Overview' => [
                ['Dashboard', 'admin.dashboard', 'layout-dashboard', 'dashboard.view', ['admin.dashboard']],
            ],
            'People' => [
                ['Users', 'admin.users.index', 'users', 'users.view', ['admin.users.*']],
                ['Roles & permissions', 'admin.roles.index', 'key-round', 'roles.manage', ['admin.roles.*']],
                ['Support', 'admin.support.index', 'life-buoy', 'support.manage', ['admin.support.*']],
            ],
            'Content' => [
                ['Quran', 'admin.quran.index', 'book-open', 'quran.view', ['admin.quran.*']],
                ['Worship', 'admin.worship.index', 'landmark', 'worship.manage', ['admin.worship.*']],
                ['Academy', 'admin.academy.index', 'graduation-cap', 'academy.manage', ['admin.academy.*']],
                ['Kids', 'admin.kids.index', 'baby', 'kids.manage', ['admin.kids.*']],
                ['Events', 'admin.events.index', 'calendar-days', 'events.manage', ['admin.events.*']],
                ['Directories', 'admin.directories.index', 'map-pin', 'directory.manage', ['admin.directories.*']],
            ],
            'Platform' => [
                ['AI', 'admin.ai.index', 'sparkles', 'ai.usage', ['admin.ai.*']],
                ['Community', 'admin.community.index', 'shield-alert', 'community.moderate', ['admin.community.*']],
                ['Notifications', 'admin.notifications.index', 'megaphone', 'notifications.manage', ['admin.notifications.*']],
            ],
            'Finance' => [
                ['Subscriptions', 'admin.subscriptions.index', 'crown', 'subscriptions.manage', ['admin.subscriptions.*']],
                ['Referrals', 'admin.referrals.index', 'gift', 'subscriptions.manage', ['admin.referrals.*']],
                ['Payments', 'admin.payments.index', 'credit-card', 'payments.view', ['admin.payments.*']],
                ['Gateways', 'admin.gateways.index', 'landmark', 'payments.view', ['admin.gateways.*']],
            ],
            'System' => [
                ['Reports', 'admin.reports.index', 'chart-column', 'reports.view', ['admin.reports.*']],
                ['Audit logs', 'admin.audit.index', 'scroll-text', 'audit.view', ['admin.audit.*']],
                ['Settings', 'admin.settings.index', 'settings', 'settings.manage', ['admin.settings.*']],
            ],
        ];
    }

    public static function isActive(array $patterns): bool
    {
        return request()->routeIs(...$patterns);
    }
}
