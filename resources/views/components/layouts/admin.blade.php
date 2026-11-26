@props(['title' => null])
<x-layouts.base :title="($title ? $title.' · ' : '').'Admin'" theme-color="#00281a" body-class="bg-[#f3f5f1]">
@php($user = auth('admin')->user())
<div class="min-h-dvh lg:flex" x-data="{ nav: false }">
    <aside class="fixed inset-y-0 start-0 z-50 w-64 -translate-x-full overflow-y-auto bg-forest-950 text-white transition lg:translate-x-0" :class="{ 'translate-x-0': nav }">
        <div class="flex items-center justify-between px-5 pb-3 pt-5">
            <a href="{{ route('admin.dashboard') }}"><x-brand.logo size="sm" /></a>
            <button class="lg:hidden" @click="nav = false" aria-label="Close menu"><x-lucide-x class="size-5" /></button>
        </div>
        <p class="px-5 pb-3 text-[11px] font-semibold uppercase tracking-widest text-gold-400">Administration</p>
        <nav class="space-y-4 px-3 pb-8" aria-label="Admin">
            @foreach (\App\Support\Navigation::admin() as $group => $items)
                @php($visible = collect($items)->filter(fn ($i) => $user?->hasPermission($i[3])))
                @if ($visible->isNotEmpty())
                    <div>
                        <p class="px-3 pb-1 text-[10px] font-semibold uppercase tracking-widest text-white/35">{{ $group }}</p>
                        @foreach ($visible as [$label, $route, $icon, $perm, $patterns])
                            @php($active = \App\Support\Navigation::isActive($patterns))
                            <a href="{{ route($route) }}" @if ($active) aria-current="page" @endif class="flex items-center gap-3 rounded-xl px-3 py-2 text-sm font-medium {{ $active ? 'bg-white/10 text-gold-300' : 'text-white/70 hover:bg-white/5 hover:text-white' }}">
                                <x-dynamic-component :component="'lucide-'.$icon" class="size-[17px]" />{{ $label }}
                            </a>
                        @endforeach
                    </div>
                @endif
            @endforeach
        </nav>
    </aside>
    <div class="fixed inset-0 z-40 bg-black/40 lg:hidden" x-show="nav" x-cloak @click="nav = false"></div>

    <div class="min-w-0 flex-1 lg:ps-64">
        <header class="sticky top-0 z-30 flex h-14 items-center gap-3 border-b border-line bg-white/90 px-4 backdrop-blur lg:px-8">
            <button class="grid size-9 place-items-center rounded-lg border border-line lg:hidden" @click="nav = true" aria-label="Open menu"><x-lucide-menu class="size-5" /></button>
            <h1 class="truncate text-base font-bold text-forest-900">{{ $title }}</h1>
            <div class="ms-auto flex items-center gap-3">
                <a href="{{ route('app.home') }}" class="hidden text-sm text-muted hover:text-ink sm:block">View app</a>
                <div class="flex items-center gap-2 text-sm"><x-ui.avatar :user="$user" size="xs" /><span class="hidden font-medium sm:block">{{ $user?->name }}</span></div>
                <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="grid size-9 place-items-center rounded-lg border border-line text-muted hover:text-ink" aria-label="Sign out"><x-lucide-log-out class="size-4" /></button></form>
            </div>
        </header>
        <main id="main" class="mx-auto max-w-7xl space-y-5 px-4 py-6 lg:px-8">
            {{ $slot }}
        </main>
    </div>
</div>
</x-layouts.base>
