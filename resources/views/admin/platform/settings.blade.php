<x-layouts.admin title="Settings">
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-2xl space-y-6">@csrf
        <section class="space-y-3 rounded-2xl border border-line bg-white p-5">
            <h2 class="font-bold">General</h2>
            <x-ui.input name="support_email" type="email" label="Support email" :value="$supportEmail" />
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="require_verified_email" value="1" @checked(old('require_verified_email', $requireVerified))>Require a verified email before the member app</label>
            <p class="text-xs text-muted">The interface language is English. Other languages are not offered until real translations exist. SMS verification is not available.</p>
        </section>
        <section class="space-y-3 rounded-2xl border border-line bg-white p-5">
            <h2 class="font-bold">Paystack</h2>
            <p class="text-sm text-muted">Public key {{ $paystackPublic ? 'is set' : 'is not set' }}. Secret key {{ $paystackSecret ? 'is set' : 'is not set' }}. Leave a box empty to keep the current value. Test keys (sk_test_) stay in sandbox; live keys are charged for real.</p>
            <x-ui.input name="paystack_public_key" label="Public key" autocomplete="off" />
            <x-ui.input name="paystack_secret_key" type="password" label="Secret key" autocomplete="off" />
        </section>
        <section class="space-y-3 rounded-2xl border border-line bg-white p-5">
            <h2 class="font-bold">Web push</h2>
            <p class="text-sm text-muted">Push {{ $pushPublic ? 'is configured' : 'needs a public and private VAPID key' }}.</p>
            <x-ui.input name="vapid_public_key" label="VAPID public key" autocomplete="off" />
            <x-ui.input name="vapid_private_key" type="password" label="VAPID private key" autocomplete="off" />
            <x-ui.input name="vapid_subject" label="VAPID subject (mailto: or https URL)" :value="$pushSubject" />
        </section>
        <x-ui.button type="submit">Save settings</x-ui.button>
    </form>
</x-layouts.admin>
