<x-layouts.auth title="Welcome">
    <div x-data="{ step: 1 }">
        <p class="text-xs font-semibold uppercase tracking-widest text-muted">Step <span x-text="step"></span> of 3</p>
        <div class="mt-2 flex gap-1.5" aria-hidden="true">
            @foreach ([1, 2, 3] as $i)<span class="h-1 flex-1 rounded-full" :class="step >= {{ $i }} ? 'bg-forest-700' : 'bg-surface-2'"></span>@endforeach
        </div>
        <form method="POST" action="{{ route('app.onboarding.store') }}" class="mt-5" x-data="locationPicker({ locations: @js($locations), current: @js(['name' => $pref->location_name, 'latitude' => $pref->latitude, 'longitude' => $pref->longitude, 'timezone' => $pref->timezone]) })">@csrf
            <div x-show="step === 1" class="space-y-4">
                <h1 class="text-2xl font-bold">Where do you pray?</h1>
                <p class="text-sm text-muted">Your location is used only to calculate prayer times and the Qibla. It is optional and you can change it any time.</p>
                <div>
                    <label class="field-label" for="city">Choose a city</label>
                    <select id="city" class="field" @change="pick($event)">
                        <option value="">Select a city…</option>
                        @foreach ($locations->groupBy('country') as $country => $items)
                            <optgroup label="{{ $country }}">@foreach ($items as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</optgroup>
                        @endforeach
                    </select>
                </div>
                <x-ui.button type="button" variant="outline" icon="locate-fixed" block x-on:click="locate()" x-bind:disabled="busy"><span x-text="busy ? 'Locating…' : 'Use my current location'"></span></x-ui.button>
                <p x-show="error" x-text="error" class="field-error"></p>
                <div class="rounded-xl bg-surface-2 p-3 text-sm">
                    <input type="hidden" name="location_name" :value="form.name"><input type="hidden" name="latitude" :value="form.latitude"><input type="hidden" name="longitude" :value="form.longitude"><input type="hidden" name="timezone" :value="form.timezone">
                    <p class="font-semibold" x-text="form.name || 'No location chosen'"></p>
                    <p class="text-xs text-muted" x-show="form.latitude" x-text="form.latitude + ', ' + form.longitude + ' · ' + form.timezone"></p>
                </div>
                @error('location_name')<p class="field-error">Please choose a location to calculate prayer times.</p>@enderror
                <x-ui.button type="button" variant="gold" block x-on:click="form.latitude ? step = 2 : (error = 'Please choose a city or use your location.')">Continue</x-ui.button>
            </div>

            <div x-show="step === 2" x-cloak class="space-y-4">
                <h1 class="text-2xl font-bold">Prayer & Quran</h1>
                <x-ui.select name="method" label="Calculation method" :options="$methods" value="mwl" hint="Pick the method used by your local mosque or community." />
                <x-ui.select name="asr_method" label="Asr calculation" :options="\App\Services\Worship\PrayerTimeCalculator::ASR_METHODS" value="standard" />
                <x-ui.select name="translation_source_id" label="Quran translation" :options="$translations->pluck('name', 'id')->all()" placeholder="Default" />
                <div class="flex gap-3"><x-ui.button type="button" variant="outline" x-on:click="step = 1">Back</x-ui.button><x-ui.button type="button" variant="gold" class="flex-1" x-on:click="step = 3">Continue</x-ui.button></div>
            </div>

            <div x-show="step === 3" x-cloak class="space-y-4">
                <h1 class="text-2xl font-bold">Appearance</h1>
                <x-ui.select name="theme" label="Theme" :options="['light' => 'Light', 'dark' => 'Dark', 'system' => 'Match my device']" value="light" />
                <div class="flex gap-3"><x-ui.button type="button" variant="outline" x-on:click="step = 2">Back</x-ui.button><x-ui.button type="submit" variant="gold" class="flex-1">Finish</x-ui.button></div>
            </div>
        </form>
        <form method="POST" action="{{ route('app.onboarding.skip') }}" class="mt-4 text-center">@csrf<button class="text-sm text-muted underline-offset-2 hover:underline">Skip for now</button></form>
    </div>
</x-layouts.auth>
