<x-layouts.auth title="Authenticator code">
    <h1 class="text-2xl font-bold">Confirm it's you</h1>
    <p class="mt-1 text-sm text-muted">Enter the 6-digit code from your authenticator app, or one of your recovery codes.</p>
    <form method="POST" action="{{ route('admin.two-factor.verify') }}" class="mt-5 space-y-4">
        @csrf
        <x-ui.input name="code" label="Code" required autofocus autocomplete="one-time-code" inputmode="numeric" />
        <x-ui.button type="submit" block>Enter the portal</x-ui.button>
    </form>
    <form method="POST" action="{{ route('admin.logout') }}" class="mt-4 text-center">@csrf<button class="text-sm text-muted underline">Sign out</button></form>
</x-layouts.auth>
