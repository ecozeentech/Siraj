<?php

return [
    'name' => 'SIRAJ Muslim',
    'tagline' => 'Your Faith. Your Journey. Your Ummah.',
    'domain' => 'https://sirajmuslim.com',

    // Release identifier used to version the service worker caches.
    'version' => env('SIRAJ_VERSION', '1.0.0'),

    'require_verified_email' => (bool) env('SIRAJ_REQUIRE_VERIFIED_EMAIL', true),
    'currency' => env('SIRAJ_DEFAULT_CURRENCY', 'NGN'),

    'supported_locales' => [
        'en' => 'English',
        'ha' => 'Hausa',
        'yo' => 'Yorùbá',
        'ar' => 'العربية',
        'fr' => 'Français',
        'es' => 'Español',
        'pt' => 'Português',
        'zh' => '中文',
        'hi' => 'हिन्दी',
        'id' => 'Bahasa Indonesia',
        'ur' => 'اردو',
        'bn' => 'বাংলা',
        'ru' => 'Русский',
        'tr' => 'Türkçe',
        'sw' => 'Kiswahili',
        'de' => 'Deutsch',
    ],

    'rtl_locales' => ['ar', 'ur'],

    'stores' => [
        'play' => env('SIRAJ_PLAY_STORE_URL', 'https://play.google.com/store/search?q=SIRAJ%20Muslim&c=apps'),
        'apple' => env('SIRAJ_APP_STORE_URL', 'https://apps.apple.com/search?term=SIRAJ%20Muslim'),
    ],

    'uploads' => [
        'image_max_kb' => 4096,
        'image_mimes' => ['jpg', 'jpeg', 'png', 'webp'],
        'audio_max_kb' => 51200,
    ],

    'ai' => [
        'daily_limit_default' => 5,
        'history_messages' => 10,
    ],

    'kids' => [
        'default_daily_minutes' => 60,
    ],
];
