<x-layouts.auth title="Create account" :back="route('home')">
    <h1 class="text-2xl font-bold">Create your account</h1>
    <p class="mt-1 text-sm text-muted">Join SIRAJ — it only takes a minute.</p>
    <form method="POST" action="{{ route('register') }}" class="mt-5 space-y-4">@csrf
        <x-ui.input name="name" label="Full name" icon="user" required autofocus autocomplete="name" />
        <x-ui.input name="email" type="email" label="Email" icon="mail" required autocomplete="email" />
        <x-ui.input name="phone" type="tel" label="Phone (optional)" icon="phone" autocomplete="tel" hint="Used only for account security when SMS verification is enabled." />
        <x-ui.input name="password" type="password" label="Password" icon="lock" required autocomplete="new-password" hint="At least 8 characters with letters and numbers." />
        <x-ui.input name="password_confirmation" type="password" label="Confirm password" icon="lock" required autocomplete="new-password" />
        <x-ui.checkbox name="terms" :checked="false" label="I agree to the Terms of Service and Privacy Policy" />
        <x-ui.button type="submit" variant="gold" block>Create account</x-ui.button>
    </form>
    <p class="mt-5 text-center text-sm text-muted">Already have an account? <a href="{{ route('login') }}" class="font-semibold text-forest-700 dark:text-emerald-500">Sign in</a></p>
</x-layouts.auth>
