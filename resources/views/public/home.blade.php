<x-layouts.public title="Your Faith. Your Journey. Your Ummah.">
    {{-- Hero: mirrors the reference welcome screen (logo, tagline, gold Get Started, outlined Sign In, Choose Language) --}}
    <section class="hero-green pattern-geo relative isolate flex min-h-[calc(100dvh-4rem)] items-center overflow-hidden">
        <x-art.skyline class="pointer-events-none absolute inset-x-0 bottom-0 -z-10 h-44 text-forest-950/85 sm:h-60" />
        <div class="absolute inset-x-0 bottom-0 -z-10 h-1/2 bg-gradient-to-t from-forest-950 via-forest-950/40 to-transparent"></div>
        <div class="mx-auto grid w-full max-w-6xl items-center gap-10 px-5 pb-48 pt-10 lg:grid-cols-2 lg:px-8 lg:pb-40">
            <div class="text-center lg:text-start">
                <x-brand.logo variant="stacked" size="xl" class="lg:hidden" tagline />
                <h1 class="hidden font-tagline text-5xl font-semibold italic leading-tight lg:block">Your Faith.<br>Your Journey.<br>Your Ummah.</h1>
                <p class="mx-auto mt-5 hidden max-w-lg text-lg text-white/80 lg:block">Quran, prayer, learning, family and community — one premium app built for the global Muslim community, starting in Nigeria.</p>
                <div class="mx-auto mt-8 flex max-w-sm flex-col gap-3 lg:mx-0">
                    @auth
                        <x-ui.button :href="route('app.home')" variant="gold" block>Open SIRAJ</x-ui.button>
                    @else
                        <x-ui.button :href="route('register')" variant="gold" block>Get Started</x-ui.button>
                        <x-ui.button :href="route('login')" variant="outline-gold" block>Sign In</x-ui.button>
                    @endauth
                    <button type="button" @click="$dispatch('open-sheet', 'language')" class="mx-auto flex items-center gap-1 text-sm font-medium text-white/85 lg:mx-0">Choose Language <x-lucide-chevron-down class="size-4" /></button>
                </div>
            </div>
            <div class="hidden justify-center lg:flex"><x-brand.logo variant="stacked" size="xl" tagline class="scale-125" /></div>
        </div>
    </section>

    <section class="mx-auto max-w-6xl px-4 py-14 lg:px-8">
        <h2 class="text-center text-2xl font-bold sm:text-3xl">Everything in one place</h2>
        <p class="mx-auto mt-2 max-w-xl text-center text-muted">A cohesive experience across worship, learning, family and community.</p>
        <div class="mt-8">@include('public.partials-features')</div>
    </section>

    <section class="bg-mint/60 dark:bg-mint/10">
        <div class="mx-auto flex max-w-6xl flex-col items-center gap-6 px-4 py-14 text-center lg:flex-row lg:justify-between lg:px-8 lg:text-start">
            <div>
                <h2 class="text-2xl font-bold">Install SIRAJ on your phone</h2>
                <p class="mt-2 max-w-lg text-muted">SIRAJ is a Progressive Web App: install it from your browser for a full-screen experience and offline Quran reading. No app store required.</p>
            </div>
            <div x-data="pwaStatus()" x-init="init()" class="flex flex-col items-center gap-2">
                <button x-show="canInstall" @click="install()" class="btn btn-primary" x-cloak><x-lucide-download class="size-4" />Install app</button>
                <p x-show="!canInstall" class="max-w-xs text-sm text-muted"><strong>Android / desktop Chrome &amp; Edge:</strong> use the browser menu → “Install app”. <strong>iPhone:</strong> Share → “Add to Home Screen”.</p>
                <p class="text-xs text-muted">Google Play and App Store apps: coming later.</p>
            </div>
        </div>
    </section>

    <x-ui.sheet name="language" title="Choose language">
        <form method="POST" action="{{ route('language') }}" class="space-y-2">@csrf
            @foreach (config('siraj.supported_locales') as $code => $label)
                <button name="locale" value="{{ $code }}" class="flex w-full items-center justify-between rounded-xl border border-line px-4 py-3 text-start font-medium">{{ $label }}<x-lucide-check class="size-5 text-forest-700" /></button>
            @endforeach
            <p class="pt-2 text-sm text-muted">More interface languages are planned. You can already choose your preferred Quran translation language after signing up.</p>
        </form>
    </x-ui.sheet>
</x-layouts.public>
