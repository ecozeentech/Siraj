<x-layouts.app title="Prayer settings">
    <x-slot:header><x-ui.page-header title="Prayer settings" subtitle="Location & calculation" :back="route('app.worship.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.worship.settings.save') }}" class="mx-auto max-w-xl space-y-4 py-5" x-data="locationPicker({ locations: @js($locations), current: @js(['name' => $pref->location_name, 'latitude' => $pref->latitude, 'longitude' => $pref->longitude, 'timezone' => $pref->timezone]) })">@csrf @method('PUT')
        <section class="card space-y-3 p-4">
            <h2 class="font-bold">Location</h2>
            <select class="field" @change="pick($event)" aria-label="Choose a city"><option value="">Choose a city…</option>@foreach ($locations->groupBy('country') as $country => $items)<optgroup label="{{ $country }}">@foreach ($items as $l)<option value="{{ $l->id }}">{{ $l->name }}</option>@endforeach</optgroup>@endforeach</select>
            <x-ui.button type="button" variant="outline" icon="locate-fixed" block x-on:click="locate()"><span x-text="busy ? 'Locating…' : 'Use my current location'"></span></x-ui.button>
            <p x-show="error" x-text="error" class="field-error"></p>
            <div class="grid grid-cols-2 gap-3">
                <div class="col-span-2"><label class="field-label" for="ln">Name</label><input id="ln" name="location_name" x-model="form.name" class="field" required></div>
                <div><label class="field-label" for="la">Latitude</label><input id="la" name="latitude" x-model="form.latitude" class="field" inputmode="decimal" required></div>
                <div><label class="field-label" for="lo">Longitude</label><input id="lo" name="longitude" x-model="form.longitude" class="field" inputmode="decimal" required></div>
                <div class="col-span-2"><label class="field-label" for="tz">Timezone</label><input id="tz" name="timezone" x-model="form.timezone" class="field" required></div>
            </div>
            @foreach (['location_name', 'latitude', 'longitude', 'timezone'] as $f)@error($f)<p class="field-error">{{ $message }}</p>@enderror @endforeach
        </section>
        <section class="card space-y-4 p-4">
            <h2 class="font-bold">Calculation</h2>
            <x-ui.select name="method" label="Method" :options="$methods" :value="$pref->method" />
            <x-ui.select name="asr_method" label="Asr" :options="\App\Services\Worship\PrayerTimeCalculator::ASR_METHODS" :value="$pref->asr_method" />
            <x-ui.select name="high_latitude_rule" label="High-latitude rule" :options="\App\Services\Worship\PrayerTimeCalculator::HIGH_LATITUDE_RULES" :value="$pref->high_latitude_rule" />
            <x-ui.select name="hijri_offset" label="Hijri date adjustment" :options="[-2 => '-2 days', -1 => '-1 day', 0 => 'None', 1 => '+1 day', 2 => '+2 days']" :value="$pref->hijri_offset" hint="Adjust for local moon sighting." />
        </section>
        <section class="card p-4">
            <h2 class="font-bold">Minute adjustments</h2><p class="text-xs text-muted">Match your local mosque timetable (-30 to +30 minutes).</p>
            <div class="mt-3 grid grid-cols-3 gap-3">@foreach ($prayers as $k => $label)<x-ui.input :name="'adjustments['.$k.']'" type="number" min="-30" max="30" :label="$label" :value="$pref->adjustments[$k] ?? 0" />@endforeach</div>
        </section>
        <section class="card space-y-3 p-4">
            <h2 class="font-bold">Reminders</h2>
            <x-ui.checkbox name="reminders_enabled" label="Notify me before each prayer" hint="Delivered in the app, and as push if enabled in Notification settings." :checked="$pref->reminders_enabled" />
            <x-ui.input name="remind_minutes_before" type="number" min="0" max="60" label="Minutes before" :value="$pref->remind_minutes_before ?? 10" />
        </section>
        <x-ui.button type="submit" block>Save settings</x-ui.button>
    </form>
</x-layouts.app>
