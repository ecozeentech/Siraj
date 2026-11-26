<x-layouts.auth title="Reset password">
    <h1 class="text-2xl font-bold">Choose a new password</h1>
    <form method="POST" action="{{ route('password.update') }}" class="mt-5 space-y-4">@csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <x-ui.input name="email" type="email" label="Email" icon="mail" :value="$email" required />
        <x-ui.input name="password" type="password" label="New password" icon="lock" required autocomplete="new-password" />
        <x-ui.input name="password_confirmation" type="password" label="Confirm password" icon="lock" required autocomplete="new-password" />
        <x-ui.button type="submit" variant="gold" block>Reset password</x-ui.button>
    </form>
</x-layouts.auth>
