<x-layouts.auth title="Staff sign in">
    <h1 class="text-2xl font-bold">Staff sign in</h1>
    <p class="mt-1 text-sm text-muted">This entrance is only for SIRAJ staff. Members sign in on the main app.</p>
    <form method="POST" action="{{ route('admin.login') }}" class="mt-5 space-y-4">
        @csrf
        <x-ui.input name="email" type="email" label="Work email" required autofocus autocomplete="username" />
        <x-ui.input name="password" type="password" label="Password" required autocomplete="current-password" />
        <x-ui.button type="submit" block>Continue</x-ui.button>
    </form>
</x-layouts.auth>
