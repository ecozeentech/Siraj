<x-layouts.app title="Settings">
    <x-slot:header><x-ui.page-header title="Settings" :back="route('app.profile.show')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-5 py-5">
        <div class="card divide-y divide-line overflow-hidden">
            <x-ui.menu-row icon="user" label="Account & profile" :href="route('app.settings.account')" hint="Name, email, phone, photo" />
            <x-ui.menu-row icon="lock" label="Password" :href="route('app.settings.password')" />
            <x-ui.menu-row icon="shield-check" label="Security & devices" :href="route('app.settings.security')" hint="Signed-in devices" />
            <x-ui.menu-row icon="bell" label="Notifications" :href="route('app.settings.notifications')" />
            <x-ui.menu-row icon="palette" label="Appearance & language" :href="route('app.settings.appearance')" />
        </div>
        <div class="card divide-y divide-line overflow-hidden">
            <x-ui.menu-row icon="landmark" label="Prayer times & location" :href="route('app.worship.settings')" />
            <x-ui.menu-row icon="book-open" label="Quran reading" :href="route('app.settings.quran')" hint="Translation, tafsir, reciter, text size" />
            <x-ui.menu-row icon="download" label="Offline Quran" :href="route('app.quran.offline')" />
        </div>
        <div class="card divide-y divide-line overflow-hidden">
            <x-ui.menu-row icon="eye-off" label="Privacy" :href="route('app.settings.privacy')" />
            <x-ui.menu-row icon="file-down" label="Export my data" :href="route('app.settings.export')" />
            <x-ui.menu-row icon="trash-2" label="Delete account" :href="route('app.settings.delete')" />
        </div>
    </div>
</x-layouts.app>
