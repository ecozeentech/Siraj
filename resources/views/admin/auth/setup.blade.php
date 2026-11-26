<x-layouts.auth title="Set up two-factor">
    @if ($codes = session('admin.recovery_codes'))
        <h1 class="text-2xl font-bold">Save your recovery codes</h1>
        <p class="mt-1 text-sm text-muted">These are shown once. Each code signs you in if you lose your phone.</p>
        <ul class="mt-4 grid grid-cols-2 gap-2 font-mono text-sm">@foreach ($codes as $code)<li class="rounded-lg bg-surface-2 px-3 py-2">{{ $code }}</li>@endforeach</ul>
        <x-ui.button :href="route('admin.dashboard')" class="mt-5" block>I have saved them</x-ui.button>
    @else
        <h1 class="text-2xl font-bold">Set up an authenticator</h1>
        <p class="mt-1 text-sm text-muted">Staff accounts need a second step. Add this account to an authenticator app, then enter the first code.</p>
        <p class="mt-4 break-all rounded-xl bg-surface-2 p-3 font-mono text-sm">{{ $secret }}</p>
        <p class="mt-2 text-xs text-muted">Or use this setup link on a phone that already has an authenticator: <a class="break-all underline" href="{{ $uri }}">{{ $uri }}</a></p>
        <form method="POST" action="{{ route('admin.two-factor.confirm') }}" class="mt-5 space-y-4">
            @csrf
            <x-ui.input name="code" label="6-digit code" required autofocus inputmode="numeric" />
            <x-ui.button type="submit" block>Confirm and continue</x-ui.button>
        </form>
    @endif
</x-layouts.auth>
