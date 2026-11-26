<x-layouts.auth title="Sign in" :back="route('home')">
    <h1 class="text-2xl font-bold">Assalamu alaikum</h1>
    <p class="mt-1 text-sm text-muted">Sign in to continue your journey.</p>
    <form method="POST" action="{{ route('login') }}" class="mt-5 space-y-4">@csrf
        <x-ui.input name="email" type="email" label="Email" icon="mail" required autofocus autocomplete="username" />
        <x-ui.input name="password" type="password" label="Password" icon="lock" required autocomplete="current-password" />
        <div class="flex items-center justify-between text-sm">
            <label class="flex items-center gap-2"><input type="checkbox" name="remember" class="size-4 rounded border-line text-forest-700">Remember me</label>
            <a href="{{ route('password.request') }}" class="font-semibold text-forest-700 dark:text-emerald-500">Forgot password?</a>
        </div>
        <x-ui.button type="submit" variant="gold" block>Sign In</x-ui.button>
    </form>
    <p class="mt-5 text-center text-sm text-muted">New to SIRAJ? <a href="{{ route('register') }}" class="font-semibold text-forest-700 dark:text-emerald-500">Create an account</a></p>
</x-layouts.auth>
