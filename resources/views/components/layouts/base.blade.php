@props(['title' => null, 'description' => 'SIRAJ Muslim — Your Faith. Your Journey. Your Ummah.', 'bodyClass' => '', 'themeColor' => '#087A55'])
@php
    $theme = auth()->user()?->preferences?->theme ?? 'light';
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ in_array(app()->getLocale(), config('siraj.rtl_locales'), true) ? 'rtl' : 'ltr' }}" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $title ? $title.' · ' : '' }}SIRAJ Muslim</title>
    <meta name="description" content="{{ $description }}">
    <meta name="theme-color" content="{{ $themeColor }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="{{ route('pwa.manifest') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SIRAJ">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <script>
        (function () {
            var t = document.documentElement.dataset.theme;
            var dark = t === 'dark' || (t === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
            if (dark) document.documentElement.classList.add('dark');
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('head')
</head>
<body class="min-h-dvh antialiased {{ $bodyClass }}" data-vapid="{{ \App\Services\Settings::get('vapid.public_key') ?: config('services.vapid.public_key') }}">
    <a href="#main" class="sr-only z-[100] rounded-full bg-white px-4 py-2 text-forest-900 focus:not-sr-only focus:fixed focus:start-3 focus:top-3">Skip to content</a>
    {{ $slot }}
    <x-ui.toasts />
    <x-pwa.status />
    @livewireScripts
    @stack('scripts')
</body>
</html>
