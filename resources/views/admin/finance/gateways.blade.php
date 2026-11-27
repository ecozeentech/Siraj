<x-layouts.admin title="Payment gateways">
    <section class="rounded-2xl border border-line bg-white p-5">
        <h2 class="font-bold">Add a custom gateway</h2>
        <p class="mt-1 text-sm text-ink">Members see the instructions and the payment stays pending until you confirm the money arrived.</p>
        <form method="POST" action="{{ route('admin.gateways.store') }}" class="mt-3 grid gap-3 sm:grid-cols-2">@csrf
            <x-ui.input name="name" label="Name" required />
            <x-ui.input name="instructions" label="Instructions shown to the member" required />
            <x-ui.button type="submit" class="sm:w-fit">Add gateway</x-ui.button>
        </form>
    </section>
    <div class="space-y-4">
        @foreach ($providers as $provider)
            <form method="POST" action="{{ route('admin.gateways.update', $provider) }}" class="space-y-3 rounded-2xl border border-line bg-white p-5">@csrf @method('PUT')
                <div class="flex items-center justify-between gap-3">
                    <h2 class="font-bold">{{ $provider->name }} <span class="text-sm font-medium text-ink">{{ $provider->driver }}</span></h2>
                    <label class="flex items-center gap-2 text-sm font-semibold"><input type="checkbox" name="enabled" value="1" @checked($provider->enabled)> Enabled</label>
                </div>
                <x-ui.textarea name="instructions" label="Instructions" rows="2" :value="$provider->instructions" />
                <x-ui.textarea name="public_details" label="Public details JSON" rows="2" hint="Bank name, account number, or crypto address. Leave blank to keep the current details."></x-ui.textarea>
                <div class="grid gap-3 sm:grid-cols-3">
                    <x-ui.input name="public_key" label="Public key" autocomplete="off" />
                    <x-ui.input name="secret" type="password" label="Secret key" autocomplete="off" />
                    <x-ui.input name="merchant_id" label="Merchant id" autocomplete="off" />
                </div>
                <x-ui.button type="submit">Save {{ $provider->name }}</x-ui.button>
            </form>
        @endforeach
    </div>
</x-layouts.admin>
