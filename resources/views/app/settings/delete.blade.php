<x-layouts.app title="Delete account">
    <x-slot:header><x-ui.page-header title="Delete account" :back="route('app.settings.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.settings.delete.destroy') }}" class="mx-auto max-w-xl space-y-4 py-5">@csrf @method('DELETE')
        <x-ui.alert type="danger" title="This will remove your account">Your profile, reading history, notes, bookmarks, family profiles and posts will be permanently deleted after a 14-day grace period. Wallet balances cannot be recovered. You can <a class="font-semibold underline" href="{{ route('app.settings.export') }}">export your data</a> first.</x-ui.alert>
        <div class="card space-y-4 p-4">
            <x-ui.input name="password" type="password" label="Confirm your password" required />
            <x-ui.checkbox name="confirm" label="I understand and want to delete my account" :checked="false" />
        </div>
        <x-ui.button type="submit" variant="danger" block>Request account deletion</x-ui.button>
    </form>
</x-layouts.app>
