<x-layouts.base title="Your Faith. Your Journey. Your Ummah." body-class="bg-[#042616]" theme-color="#042616">
    <section id="main" x-data="{}" class="relative isolate flex min-h-dvh flex-col overflow-hidden text-white" style="background: radial-gradient(120% 70% at 50% 18%, #1a7a45 0%, #0b4d2c 38%, #042616 72%, #02170d 100%)">
        <div class="splash-glow pointer-events-none absolute -top-24 left-1/2 -z-10 h-[28rem] w-[28rem] -translate-x-1/2 rounded-full bg-[radial-gradient(circle,rgba(232,197,106,0.28),transparent_68%)]"></div>
        <div class="splash-glow pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-2/3 bg-[radial-gradient(80%_70%_at_50%_100%,rgba(46,168,92,0.45),transparent_70%)]" style="animation-delay: -8s"></div>
        <div class="pattern-geo pointer-events-none absolute inset-x-0 top-0 -z-10 h-[58%] opacity-80 [mask-image:radial-gradient(70%_80%_at_50%_0%,#000_20%,transparent_72%)]"></div>

        <div class="mx-auto flex w-full max-w-md flex-1 flex-col items-center px-8 pt-16 text-center sm:pt-20">
            <x-brand.logo variant="stacked" size="xl" tagline class="drop-shadow-[0_0_24px_rgba(232,197,106,0.35)]" />

            <div class="mt-8 flex w-full flex-col gap-3.5">
                @auth
                    <x-ui.button :href="route('app.home')" variant="gold" block class="min-h-12 text-base font-bold">Open SIRAJ</x-ui.button>
                @else
                    <x-ui.button :href="route('register')" variant="gold" block class="min-h-12 text-base font-bold">Get Started</x-ui.button>
                    <x-ui.button :href="route('login')" variant="outline-gold" block class="min-h-12 border-2 border-[#e4c56a] text-base font-semibold">Sign In</x-ui.button>
                @endauth
                <button type="button" @click="$dispatch('open-sheet', 'language')" class="mx-auto mt-1 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-white">
                    Choose Language <x-lucide-chevron-down class="size-4" />
                </button>
            </div>
        </div>

        <div class="splash-scene pointer-events-none relative mt-4 h-64 w-full overflow-hidden sm:h-80">
            <img src="{{ asset('images/splash-mosque.png') }}" alt="" class="absolute inset-0 h-full w-full object-cover object-bottom">
            <div class="splash-mist pointer-events-none absolute inset-0 bg-[radial-gradient(50%_40%_at_50%_30%,rgba(255,236,180,0.18),transparent_70%)]"></div>
        </div>
    </section>

    <section class="bg-surface">
        <div class="mx-auto max-w-6xl px-4 py-14 lg:px-8">
            <h2 class="text-center text-2xl font-bold text-ink sm:text-3xl">Everything in one place</h2>
            <p class="mx-auto mt-2 max-w-xl text-center text-muted">A cohesive experience across worship, learning, family and community.</p>
            <div class="mt-8">@include('public.partials-features')</div>
        </div>
    </section>

    <section class="bg-mint/60">
        <div class="mx-auto flex max-w-6xl flex-col items-center gap-6 px-4 py-14 text-center lg:flex-row lg:justify-between lg:px-8 lg:text-start">
            <div>
                <h2 class="text-2xl font-bold text-ink">Install SIRAJ on your phone</h2>
                <p class="mt-2 max-w-lg text-muted">SIRAJ is a Progressive Web App: install it from your browser for a full-screen experience and offline Quran reading. No app store required.</p>
            </div>
            <div x-data="pwaStatus()" x-init="init()" class="flex flex-col items-center gap-2">
                <button x-show="canInstall" @click="install()" class="btn btn-primary" x-cloak><x-lucide-download class="size-4" />Install app</button>
                <p x-show="!canInstall" class="max-w-xs text-sm text-muted"><strong>Android / desktop Chrome &amp; Edge:</strong> use the browser menu → “Install app”. <strong>iPhone:</strong> Share → “Add to Home Screen”.</p>
                <p class="text-xs text-muted">Google Play and App Store apps: coming later.</p>
            </div>
        </div>
    </section>

    <footer class="bg-forest-950 py-4 text-center text-xs text-white/60">© {{ date('Y') }} SIRAJ Muslim · <a href="{{ route('privacy') }}" class="underline-offset-2 hover:underline">Privacy</a> · <a href="{{ route('terms') }}" class="underline-offset-2 hover:underline">Terms</a></footer>

    <x-ui.sheet name="language" title="Choose language">
        <form method="POST" action="{{ route('language') }}" class="space-y-2">@csrf
            @foreach (config('siraj.supported_locales') as $code => $label)
                <button name="locale" value="{{ $code }}" class="flex w-full items-center justify-between rounded-xl border border-line px-4 py-3 text-start font-medium">{{ $label }}<x-lucide-check class="size-5 text-forest-700" /></button>
            @endforeach
            <p class="pt-2 text-sm text-muted">More interface languages are planned. You can already choose your preferred Quran translation language after signing up.</p>
        </form>
    </x-ui.sheet>
</x-layouts.base>
