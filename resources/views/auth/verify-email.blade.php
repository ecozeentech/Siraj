<x-layouts.auth title="Verify email">
    <span class="grid size-14 place-items-center rounded-full bg-mint text-forest-700"><x-lucide-mail-check class="size-7" /></span>
    <h1 class="mt-4 text-2xl font-bold">Verify your email</h1>
    <p class="mt-2 text-sm leading-6 text-muted">We sent a verification link to <strong class="text-ink">{{ auth()->user()->email }}</strong>. Open it to activate your account. Didn't get it? Check spam, or request a new link.</p>
    <form method="POST" action="{{ route('verification.send') }}" class="mt-5">@csrf<x-ui.button type="submit" variant="gold" block>Resend verification email</x-ui.button></form>
    <form method="POST" action="{{ route('logout') }}" class="mt-3">@csrf<x-ui.button type="submit" variant="outline" block>Sign out</x-ui.button></form>
</x-layouts.auth>
