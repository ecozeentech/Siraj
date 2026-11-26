<x-layouts.app title="Password">
    <x-slot:header><x-ui.page-header title="Change password" :back="route('app.settings.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.settings.password.update') }}" class="mx-auto max-w-xl space-y-4 py-5">@csrf @method('PUT')
        <div class="card space-y-4 p-4">
            <x-ui.input name="current_password" type="password" label="Current password" required autocomplete="current-password" />
            <x-ui.input name="password" type="password" label="New password" required autocomplete="new-password" hint="At least 8 characters with letters and numbers." />
            <x-ui.input name="password_confirmation" type="password" label="Confirm new password" required autocomplete="new-password" />
        </div>
        <x-ui.alert type="info">Changing your password signs you out of all other devices.</x-ui.alert>
        <x-ui.button type="submit" block>Update password</x-ui.button>
    </form>
</x-layouts.app>
