<x-layouts.app title="Prayer tracker">
    <x-slot:header><x-ui.page-header title="Prayer tracker" subtitle="Private to you" :back="route('app.worship.index')" /></x-slot:header>
    <div class="mx-auto max-w-3xl space-y-5 py-5" x-data="prayerTracker({ grid: @js($grid) })">
        <div class="grid grid-cols-2 gap-3"><div class="card p-4 text-center"><p class="text-3xl font-bold" x-text="done + ' / 35'">0</p><p class="text-xs text-muted">prayers logged (7 days)</p></div><div class="card p-4 text-center"><p class="text-3xl font-bold" x-text="onTime">0</p><p class="text-xs text-muted">on time</p></div></div>
        @php($names = $prayers)
        <template x-for="row in grid" :key="row.date">
            <section class="card p-4">
                <h2 class="font-bold" x-text="row.label"></h2>
                <div class="mt-3 space-y-3">
                    @foreach ($names as $key => $label)
                        <div>
                            <div class="flex items-center justify-between"><span class="flex items-center gap-2 font-medium"><span class="grid size-6 place-items-center rounded-full text-[11px]" :class="dot(row.logs.{{ $key }})"><x-lucide-check class="size-3.5" x-show="row.logs.{{ $key }} && row.logs.{{ $key }}.status !== 'missed'" /><x-lucide-x class="size-3.5" x-show="row.logs.{{ $key }}?.status === 'missed'" /></span>{{ $label }}</span>
                                <label class="flex items-center gap-1.5 text-xs text-muted" x-show="row.logs.{{ $key }} && row.logs.{{ $key }}.status !== 'missed'"><input type="checkbox" class="size-4 rounded border-line text-forest-700" :checked="row.logs.{{ $key }}?.congregation" @change="toggleCongregation(row.date, '{{ $key }}')">Congregation</label></div>
                            <div class="mt-1.5 grid grid-cols-4 gap-1.5">
                                <template x-for="(label, status) in statuses" :key="status"><button type="button" class="min-h-9 rounded-lg border text-xs font-semibold transition" :class="row.logs.{{ $key }}?.status === status ? 'border-forest-700 bg-forest-700 text-white' : 'border-line bg-surface text-muted hover:bg-surface-2'" @click="set(row.date, '{{ $key }}', status)" x-text="label" :aria-pressed="row.logs.{{ $key }}?.status === status"></button></template>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </template>
        <p class="text-center text-xs text-muted">Logs are saved to your account only and sync automatically when you're back online.</p>
    </div>
</x-layouts.app>
