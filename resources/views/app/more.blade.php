<x-layouts.app title="More">
    <x-slot:header>
        <x-ui.page-header title="More" subtitle="Everything in SIRAJ">
            <x-slot:actions><x-ui.bell /></x-slot:actions>
        </x-ui.page-header>
    </x-slot:header>
    <div class="space-y-6 py-5">
        <a href="{{ route('app.profile.show') }}" class="card flex items-center gap-4 p-4">
            <x-ui.avatar :user="$user" size="lg" />
            <span class="min-w-0 flex-1"><span class="block truncate text-lg font-bold">{{ $user->name }}</span><span class="block truncate text-sm text-muted">{{ $user->email }}</span></span>
            <x-lucide-chevron-right class="size-5 text-muted" />
        </a>
        @foreach ([
            'Connect' => [['users', 'Community', 'app.community.index'], ['message-circle', 'Messages', 'app.messages.index'], ['calendar-days', 'Events', 'app.events.index'], ['map-pin', 'Directory', 'app.directory.index']],
            'Tools' => [['sparkles', 'SIRAJ AI', 'app.ai.index'], ['compass', 'Hajj & Umrah', 'app.hajj.index'], ['calculator', 'Zakat Calculator', 'app.zakat.index'], ['baby', 'Kids Deen World', 'app.kids.index']],
            'Account' => [['wallet', 'Wallet & Plan', 'app.wallet.index'], ['bell', 'Notifications', 'app.notifications'], ['settings', 'Settings', 'app.settings.index'], ['life-buoy', 'Help & Support', 'app.support.index']],
        ] as $group => $items)
            <section>
                <h2 class="mb-2 px-1 text-xs font-semibold uppercase tracking-widest text-muted">{{ $group }}</h2>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    @foreach ($items as [$icon, $label, $route])
                        <x-ui.tile :icon="$icon" :label="$label" :href="route($route)" class="!flex-row !justify-start !gap-3 !px-3 !py-3 !text-start" />
                    @endforeach
                </div>
            </section>
        @endforeach
    </div>
</x-layouts.app>
