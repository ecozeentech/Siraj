@props(['title' => null, 'description' => null])
<x-layouts.base :title="$title" :description="$description ?? 'SIRAJ Muslim — the Muslim digital lifestyle app. Your Faith. Your Journey. Your Ummah.'">
<div class="flex min-h-dvh flex-col">
    <header class="hero-green pattern-geo sticky top-0 z-40" style="padding-top: var(--safe-top)">
        <div class="mx-auto flex h-16 max-w-6xl items-center justify-between gap-4 px-4 lg:px-8">
            <a href="{{ route('home') }}" aria-label="SIRAJ Muslim"><x-brand.logo size="sm" /></a>
            <nav class="hidden items-center gap-6 text-sm font-medium text-white/80 md:flex" aria-label="Main">
                @foreach ([['Features', 'features'], ['Pricing', 'pricing'], ['About', 'about'], ['Contact', 'contact']] as [$l, $r])
                    <a href="{{ route($r) }}" class="hover:text-white {{ request()->routeIs($r) ? 'text-gold-300' : '' }}">{{ $l }}</a>
                @endforeach
            </nav>
            <div class="flex items-center gap-2">
                @auth
                    <x-ui.button :href="route('app.home')" variant="gold" size="sm">Open app</x-ui.button>
                @else
                    <a href="{{ route('login') }}" class="hidden px-3 text-sm font-semibold text-white sm:block">Sign in</a>
                    <x-ui.button :href="route('register')" variant="gold" size="sm">Get started</x-ui.button>
                @endauth
            </div>
        </div>
    </header>
    <main id="main" class="flex-1">{{ $slot }}</main>
    <footer class="bg-forest-950 text-white/70">
        <div class="mx-auto grid max-w-6xl gap-8 px-4 py-10 sm:grid-cols-3 lg:px-8">
            <div><x-brand.logo size="sm" /><p class="mt-4 max-w-xs font-tagline text-lg italic text-white/80">Your Faith. Your Journey. Your Ummah.</p></div>
            <div class="text-sm"><p class="mb-2 font-semibold text-white">Explore</p><ul class="space-y-1.5"><li><a class="hover:text-white" href="{{ route('features') }}">Features</a></li><li><a class="hover:text-white" href="{{ route('pricing') }}">Pricing</a></li><li><a class="hover:text-white" href="{{ route('about') }}">About</a></li><li><a class="hover:text-white" href="{{ route('contact') }}">Contact</a></li></ul></div>
            <div class="text-sm"><p class="mb-2 font-semibold text-white">Legal</p><ul class="space-y-1.5"><li><a class="hover:text-white" href="{{ route('privacy') }}">Privacy</a></li><li><a class="hover:text-white" href="{{ route('terms') }}">Terms</a></li></ul></div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs">© {{ date('Y') }} SIRAJ Muslim · sirajmuslim.com</div>
    </footer>
</div>
</x-layouts.base>
