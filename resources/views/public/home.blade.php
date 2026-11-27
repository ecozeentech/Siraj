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
                    <x-ui.button :href="route('register')" variant="gold" block class="min-h-12 text-base font-bold">{{ __('Get Started') }}</x-ui.button>
                    <x-ui.button :href="route('login')" variant="outline-gold" block class="min-h-12 border-2 border-[#e4c56a] text-base font-semibold">{{ __('Sign In') }}</x-ui.button>
                @endauth
                <button type="button" @click="$dispatch('open-sheet', 'language')" class="mx-auto mt-1 inline-flex min-h-11 items-center gap-1 text-sm font-semibold text-white">
                    {{ __('Choose Language') }} <x-lucide-chevron-down class="size-4" />
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
        <div class="mx-auto max-w-3xl px-4 py-14 text-center">
            <h2 class="text-2xl font-bold text-ink">{{ __('Take SIRAJ with you') }}</h2>
            <div x-data="pwaStatus()" x-init="init()" class="mt-6 grid gap-3 sm:grid-cols-3">
                <button type="button" @click="canInstall ? install() : document.getElementById('pwa-help').showModal()" class="icon-pop flex min-h-14 items-center justify-center gap-2 rounded-2xl bg-[#128a49] px-4 font-bold text-white"><x-lucide-smartphone class="size-5" />{{ __('PWA') }}</button>
                <a href="{{ config('siraj.stores.play') }}" class="icon-pop flex min-h-14 items-center justify-center gap-2 rounded-2xl bg-ink px-4 font-bold text-white" rel="noopener"><x-lucide-play class="size-5" />{{ __('Play Store') }}</a>
                <a href="{{ config('siraj.stores.apple') }}" class="icon-pop flex min-h-14 items-center justify-center gap-2 rounded-2xl bg-white px-4 font-bold text-ink ring-1 ring-line" rel="noopener"><x-lucide-apple class="size-5" />{{ __('Apple Store') }}</a>
            </div>
            <dialog id="pwa-help" class="max-w-sm rounded-2xl p-5 text-start text-ink backdrop:bg-black/40">
                <p class="font-bold">{{ __('Install app') }}</p>
                <p class="mt-2 text-sm">On Chrome or Edge, open the browser menu and choose Install app. On iPhone, tap Share, then Add to Home Screen.</p>
                <form method="dialog" class="mt-4"><button class="btn btn-primary">Close</button></form>
            </dialog>
        </div>
    </section>

    <footer class="bg-forest-950 py-4 text-center text-xs text-white/60">© {{ date('Y') }} SIRAJ Muslim · <a href="{{ route('privacy') }}" class="underline-offset-2 hover:underline">Privacy</a> · <a href="{{ route('terms') }}" class="underline-offset-2 hover:underline">Terms</a></footer>

    <x-ui.sheet name="language" title="Choose language">
        <form method="POST" action="{{ route('language') }}" class="space-y-2">@csrf
            @foreach (config('siraj.supported_locales') as $code => $label)
                <button name="locale" value="{{ $code }}" class="icon-pop flex min-h-12 w-full items-center justify-between rounded-xl border border-line px-4 text-start font-semibold text-ink">{{ $label }}@if (app()->getLocale() === $code)<x-lucide-check class="size-5 text-[#128a49]" />@endif</button>
            @endforeach
        </form>
    </x-ui.sheet>
</x-layouts.base>
