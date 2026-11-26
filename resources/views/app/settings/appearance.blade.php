<x-layouts.app title="Appearance">
    <x-slot:header><x-ui.page-header title="Appearance & language" :back="route('app.settings.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.settings.appearance.update') }}" class="mx-auto max-w-xl space-y-4 py-5">@csrf @method('PUT')
        <div class="card space-y-4 p-4">
            <x-ui.select name="theme" label="Theme" :options="['light' => 'Light', 'dark' => 'Dark', 'system' => 'Match my device']" :value="$prefs->theme" />
            <x-ui.select name="language" label="Language" :options="config('siraj.supported_locales')" :value="$prefs->language" hint="Only English is available in this release. Additional interface languages are planned." />
        </div>
        <x-ui.button type="submit" block>Save</x-ui.button>
    </form>
</x-layouts.app>
