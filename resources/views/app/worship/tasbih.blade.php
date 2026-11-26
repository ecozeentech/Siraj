<x-layouts.app title="Tasbih">
    <x-slot:header><x-ui.page-header title="Tasbih" :subtitle="number_format($todayTotal).' counted today'" :back="route('app.worship.index')" /></x-slot:header>
    <div class="mx-auto max-w-md space-y-5 py-6" x-data="tasbih({ phrases: @js($phrases) })">
        <div class="flex flex-wrap justify-center gap-2">
            @foreach ($phrases as $p)<button type="button" class="chip" :class="phrase === @js($p['text']) ? 'chip-active' : ''" @click="selectPhrase(@js($p))">{{ $p['text'] }}</button>@endforeach
        </div>
        <div class="flex flex-col items-center">
            <button type="button" @click="tap()" class="relative grid size-64 select-none place-items-center rounded-full bg-gradient-to-b from-forest-700 to-forest-900 text-white shadow-pop transition active:scale-[0.97]" aria-label="Tap to count">
                <svg class="absolute inset-0 size-full -rotate-90" viewBox="0 0 100 100" aria-hidden="true"><circle cx="50" cy="50" r="46" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="3"/><circle cx="50" cy="50" r="46" fill="none" stroke="#f1cc7b" stroke-width="3" stroke-linecap="round" stroke-dasharray="289" :stroke-dashoffset="289 - 289 * progress / 100" class="transition-all"/></svg>
                <span class="text-center"><span class="block text-7xl font-bold tabular-nums" x-text="count">0</span><span class="mt-1 block text-sm text-white/75" x-text="target ? 'of ' + target : 'no target'"></span></span>
            </button>
            <p class="mt-4 text-lg font-semibold" x-text="phrase"></p>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <button type="button" class="btn btn-outline" @click="reset()">Reset</button>
            <div><label class="sr-only" for="target">Target</label><input id="target" type="number" min="0" class="field text-center" x-model.number="target" @change="persist()" placeholder="Target"></div>
            <button type="button" class="btn btn-primary" @click="save()">Save</button>
        </div>
        <section class="card divide-y divide-line overflow-hidden">
            <h2 class="px-4 py-3 font-bold">Recent sessions</h2>
            @forelse ($sessions as $s)<div class="flex items-center justify-between px-4 py-3 text-sm"><span>{{ $s->phrase }}</span><span class="text-muted">{{ number_format($s->count) }} · {{ $s->created_at->diffForHumans() }}</span></div>@empty<p class="px-4 py-4 text-sm text-muted">Saved sessions will appear here.</p>@endforelse
        </section>
    </div>
</x-layouts.app>
