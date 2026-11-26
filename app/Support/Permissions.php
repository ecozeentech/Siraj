<?php

namespace App\Support;

/**
 * Canonical permission catalogue and default role definitions.
 * Permissions are granular; every admin route and action is protected by one of these slugs.
 */
final class Permissions
{
    public const ALL = [
        'Dashboard' => ['dashboard.view' => 'View admin dashboard'],
        'Users' => [
            'users.view' => 'View users',
            'users.manage' => 'Edit users and assign roles',
            'users.suspend' => 'Suspend and activate users',
            'users.delete' => 'Run account deletion workflows',
        ],
        'Roles' => ['roles.manage' => 'Manage roles and permissions'],
        'Quran' => [
            'quran.view' => 'View Quran data',
            'quran.manage' => 'Manage translations, tafsir and reciters',
            'quran.verify' => 'Run Quran verification workflows',
        ],
        'Worship' => ['worship.manage' => 'Manage adhkar, tajweed and Hajj/Umrah guides'],
        'Academy' => ['academy.manage' => 'Manage courses, lessons and quizzes'],
        'Kids' => ['kids.manage' => 'Manage kids content'],
        'AI' => [
            'ai.manage' => 'Configure AI providers, models and sources',
            'ai.usage' => 'View AI usage and reports',
        ],
        'Community' => ['community.moderate' => 'Moderate community content and users'],
        'Events' => ['events.manage' => 'Manage events and attendance'],
        'Directory' => [
            'directory.manage' => 'Manage mosques and businesses',
            'directory.verify' => 'Verify directory listings',
        ],
        'Finance' => [
            'subscriptions.manage' => 'Manage plans and subscriptions',
            'payments.view' => 'View payments and ledger',
            'payments.refund' => 'Process refunds',
            'finance.adjust' => 'Post manual wallet adjustments',
        ],
        'Notifications' => ['notifications.manage' => 'Create and send notifications'],
        'Reports' => ['reports.view' => 'View and export reports'],
        'Audit' => ['audit.view' => 'View audit logs and security events'],
        'Settings' => ['settings.manage' => 'Manage application settings and integrations'],
        'Support' => ['support.manage' => 'Handle support tickets'],
    ];

    public const ROLES = [
        'super-admin' => ['Super Administrator', 'Full, unrestricted access.', ['*']],
        'administrator' => ['Administrator', 'Runs the platform day to day.', [
            'dashboard.view', 'users.view', 'users.manage', 'users.suspend', 'quran.view', 'quran.manage', 'worship.manage',
            'academy.manage', 'kids.manage', 'ai.manage', 'ai.usage', 'community.moderate', 'events.manage', 'directory.manage',
            'directory.verify', 'subscriptions.manage', 'payments.view', 'notifications.manage', 'reports.view', 'audit.view',
            'settings.manage', 'support.manage',
        ]],
        'content-manager' => ['Content Manager', 'Owns worship, kids, events and directory content.', [
            'dashboard.view', 'worship.manage', 'kids.manage', 'events.manage', 'directory.manage', 'notifications.manage',
        ]],
        'quran-reviewer' => ['Quran Reviewer', 'Reviews translations, tafsir and runs verification.', [
            'dashboard.view', 'quran.view', 'quran.manage', 'quran.verify',
        ]],
        'academy-manager' => ['Academy Manager', 'Manages Islamic Academy courses and learners.', [
            'dashboard.view', 'academy.manage', 'kids.manage',
        ]],
        'community-moderator' => ['Community Moderator', 'Reviews reports and enforces community rules.', [
            'dashboard.view', 'community.moderate', 'users.view', 'directory.verify',
        ]],
        'finance-manager' => ['Finance Manager', 'Subscriptions, payments, refunds and revenue.', [
            'dashboard.view', 'subscriptions.manage', 'payments.view', 'payments.refund', 'finance.adjust', 'reports.view',
        ]],
        'support-agent' => ['Support Agent', 'Answers user support requests.', [
            'dashboard.view', 'support.manage', 'users.view',
        ]],
    ];

    public static function slugs(): array
    {
        return array_merge(...array_map('array_keys', array_values(self::ALL)));
    }
}
