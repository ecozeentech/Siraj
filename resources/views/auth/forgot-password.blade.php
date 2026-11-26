<x-layouts.auth title="Forgot password" :back="route('login')">
    <h1 class="text-2xl font-bold">Forgot your password?</h1>
    <p class="mt-1 text-sm text-muted">Enter your email and we'll send you a link to choose a new one.</p>
    <form method="POST" action="{{ route('password.email') }}" class="mt-5 space-y-4">@csrf
        <x-ui.input name="email" type="email" label="Email" icon="mail" required autofocus />
        <x-ui.button type="submit" variant="gold" block>Send reset link</x-ui.button>
    </form>
</x-layouts.auth>
