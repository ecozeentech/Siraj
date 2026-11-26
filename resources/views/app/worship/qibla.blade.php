<x-layouts.app title="Qibla">
    <x-slot:header><x-ui.page-header title="Qibla" :subtitle="$location" :back="route('app.worship.index')" /></x-slot:header>
    <div class="mx-auto max-w-md space-y-4 py-6" x-data="qiblaCompass({ bearing: {{ $bearing }}, calibration: {{ $calibration }} })">
        <div class="card flex flex-col items-center p-6">
            <div class="relative size-64 sm:size-72">
                <svg viewBox="0 0 200 200" class="size-full" role="img" aria-label="Compass showing Qibla direction">
                    <circle cx="100" cy="100" r="96" class="fill-surface stroke-line" stroke-width="2"/>
                    <circle cx="100" cy="100" r="86" fill="none" class="stroke-gold-300" stroke-width="1" stroke-dasharray="2 4"/>
                    @foreach (['N' => 0, 'E' => 90, 'S' => 180, 'W' => 270] as $l => $deg)<text x="100" y="22" text-anchor="middle" class="fill-muted text-[11px] font-semibold" transform="rotate({{ $deg }} 100 100)">{{ $l }}</text>@endforeach
                    <g :style="`transform-origin:100px 100px; transform: rotate(${status === 'active' ? rotation : bearing}deg); transition: transform .25s linear`">
                        <path d="M100 30 L112 100 L100 92 L88 100 Z" :class="aligned ? 'fill-emerald-500' : 'fill-forest-700'"/>
                        <path d="M100 170 L108 100 L100 108 L92 100 Z" class="fill-gold-300"/>
                        <circle cx="100" cy="100" r="6" class="fill-surface stroke-forest-700" stroke-width="2"/>
                        <g transform="translate(92 6)"><rect width="16" height="16" rx="2" class="fill-forest-950"/><rect y="4" width="16" height="2.5" class="fill-gold-400"/></g>
                    </g>
                </svg>
            </div>
            <p class="mt-4 text-3xl font-bold tabular-nums">{{ $bearing }}°</p>
            <p class="text-sm text-muted">from true north · about {{ number_format($distance) }} km to the Kaaba</p>
            <p x-show="aligned" x-cloak class="mt-3 rounded-full bg-emerald-100 px-4 py-1 text-sm font-semibold text-forest-800">You are facing the Qibla</p>
        </div>
        <template x-if="status === 'idle'"><x-ui.button type="button" block variant="primary" icon="compass" x-on:click="start()">Enable live compass</x-ui.button></template>
        <p x-show="message" x-text="message" class="rounded-xl bg-gold-100 p-3 text-sm text-[#6b5314]" role="status"></p>
        <x-ui.alert type="info" title="For best accuracy">Hold your phone flat and away from metal or magnets, and move it in a figure-8 to calibrate the sensor. Compasses can be off by several degrees, so confirm with local mosque direction if unsure.</x-ui.alert>
        <form method="POST" action="{{ route('app.worship.qibla.save') }}" class="card flex items-end gap-3 p-4">@csrf @method('PUT')<x-ui.input class="flex-1" name="calibration_offset" type="number" min="-45" max="45" label="Compass calibration (degrees)" :value="$calibration" /><x-ui.button type="submit" variant="outline">Save</x-ui.button></form>
    </div>
</x-layouts.app>
