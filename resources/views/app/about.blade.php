<x-layouts.app title="About SIRAJ">
    <x-slot:header><x-ui.page-header title="About SIRAJ" :back="route('app.profile.show')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <div class="card flex flex-col items-center p-6 text-center"><x-brand.mark class="size-16" /><h2 class="mt-3 font-display text-2xl font-bold">SIRAJ Muslim</h2><p class="font-tagline text-lg italic text-muted">Your Faith. Your Journey. Your Ummah.</p><p class="mt-3 text-sm text-muted">Version {{ config('siraj.version') }}</p></div>
        <div class="card divide-y divide-line overflow-hidden">
            <x-ui.menu-row icon="file-text" label="Terms of Service" :href="route('terms')" />
            <x-ui.menu-row icon="shield" label="Privacy Policy" :href="route('privacy')" />
            <x-ui.menu-row icon="mail" label="Contact us" :href="route('contact')" />
        </div>
        <p class="px-2 text-center text-xs text-muted">Quran text is verified against its source dataset. See Settings > Quran for the sources and licenses of translations and tafsir.</p>
    </div>
</x-layouts.app>
