@php
    $defaults = ['nisab_basis' => 'silver', 'gold_price_per_gram' => '', 'silver_price_per_gram' => '', 'hawl_completed' => true];
@endphp
<x-layouts.app title="Zakat Calculator">
    <x-slot:header><x-ui.page-header title="Zakat Calculator" subtitle="Know what is due, with confidence" :back="route('app.more')" /></x-slot:header>
    <div class="grid gap-5 py-5 lg:grid-cols-3" x-data="zakatCalculator({ assets: @js(array_keys($assets)), liabilities: @js(array_keys($liabilities)), old: @js(old()) })">
        <form method="POST" action="{{ route('app.zakat.store') }}" class="space-y-4 lg:col-span-2">@csrf
            <x-ui.alert type="info" title="How this works">Zakat is due at 2.5% on net wealth you've held for one full lunar year (hawl) when it reaches the nisab (85 g of gold, or 595 g of silver). Enter today's price per gram from a trusted source. This tool is a guide; consult a scholar for complex cases.</x-ui.alert>
            <section class="card space-y-4 p-4">
                <h2 class="font-bold">Nisab</h2>
                <div class="grid grid-cols-2 gap-3">
                    <x-ui.input name="gold_price_per_gram" type="number" step="0.01" min="0" label="Gold price / gram (₦)" x-model.number="gold" required />
                    <x-ui.input name="silver_price_per_gram" type="number" step="0.01" min="0" label="Silver price / gram (₦)" x-model.number="silver" required />
                </div>
                <div><p class="field-label">Nisab based on</p><div class="grid grid-cols-2 gap-2 rounded-xl bg-surface-2 p-1"><label class="cursor-pointer"><input type="radio" name="nisab_basis" value="silver" x-model="basis" class="peer sr-only"><span class="block rounded-lg py-2 text-center text-sm font-semibold peer-checked:bg-surface peer-checked:shadow-card">Silver (595 g)</span></label><label class="cursor-pointer"><input type="radio" name="nisab_basis" value="gold" x-model="basis" class="peer sr-only"><span class="block rounded-lg py-2 text-center text-sm font-semibold peer-checked:bg-surface peer-checked:shadow-card">Gold (85 g)</span></label></div><p class="field-hint">Silver gives a lower threshold and is the more cautious choice for the poor's benefit.</p></div>
                <label class="flex cursor-pointer items-start gap-3"><input type="hidden" name="hawl_completed" value="0"><input type="checkbox" name="hawl_completed" value="1" x-model="hawl" class="mt-0.5 size-5 rounded-md border-line text-forest-700"><span class="text-sm">I have held this wealth for a full lunar year</span></label>
            </section>
            <section class="card space-y-3 p-4">
                <h2 class="font-bold">Assets</h2>
                @foreach ($assets as $k => $label)<x-ui.input :name="$k" type="number" step="0.01" min="0" :label="$label.' (₦)'" x-model.number="v.{{ $k }}" />@endforeach
                <div class="grid grid-cols-2 gap-3"><x-ui.input name="gold_grams" type="number" step="0.01" min="0" label="Gold you own (grams)" x-model.number="v.gold_grams" /><x-ui.input name="silver_grams" type="number" step="0.01" min="0" label="Silver you own (grams)" x-model.number="v.silver_grams" /></div>
            </section>
            <section class="card space-y-3 p-4"><h2 class="font-bold">Liabilities</h2>@foreach ($liabilities as $k => $label)<x-ui.input :name="$k" type="number" step="0.01" min="0" :label="$label.' (₦)'" x-model.number="v.{{ $k }}" />@endforeach</section>
            <section class="card space-y-3 p-4"><x-ui.input name="name" label="Label (optional)" placeholder="e.g. Ramadan 1447" /><x-ui.button type="submit" block>Save this calculation</x-ui.button></section>
        </form>
        <aside class="space-y-4 lg:sticky lg:top-4 lg:self-start">
            <div class="hero-green pattern-geo rounded-2xl p-5">
                <p class="text-sm text-white/75">Zakat due</p>
                <p class="mt-1 text-4xl font-bold tabular-nums" x-text="money(result.due)">₦0</p>
                <p class="mt-2 text-sm text-white/80" x-text="result.note"></p>
                <dl class="mt-4 space-y-1.5 border-t border-white/15 pt-3 text-sm"><div class="flex justify-between"><dt class="text-white/70">Total assets</dt><dd class="tabular-nums" x-text="money(result.assets)"></dd></div><div class="flex justify-between"><dt class="text-white/70">Liabilities</dt><dd class="tabular-nums" x-text="money(result.liabilities)"></dd></div><div class="flex justify-between"><dt class="text-white/70">Net wealth</dt><dd class="tabular-nums" x-text="money(result.net)"></dd></div><div class="flex justify-between"><dt class="text-white/70">Nisab</dt><dd class="tabular-nums" x-text="money(result.nisab)"></dd></div></dl>
            </div>
            @if ($saved->isNotEmpty())
                <section class="card divide-y divide-line overflow-hidden"><h2 class="px-4 py-3 font-bold">Saved calculations</h2>
                    @foreach ($saved as $s)<div class="flex items-center gap-3 px-4 py-3 text-sm"><div class="min-w-0 flex-1"><p class="truncate font-medium">{{ $s->name ?: $s->created_at->format('j M Y') }}</p><p class="text-xs text-muted">Net {{ \App\Support\Money::format((int) round($s->net_wealth * 100)) }}</p></div><span class="font-bold">{{ \App\Support\Money::format((int) round($s->zakat_due * 100)) }}</span><form method="POST" action="{{ route('app.zakat.destroy', $s) }}" onsubmit="return confirm('Delete this calculation?')">@csrf @method('DELETE')<button class="text-muted" aria-label="Delete"><x-lucide-trash-2 class="size-4" /></button></form></div>@endforeach
                </section>
            @endif
        </aside>
    </div>
</x-layouts.app>
