<x-layouts.app title="Account">
    <x-slot:header><x-ui.page-header title="Account & profile" :back="route('app.settings.index')" /></x-slot:header>
    <form method="POST" action="{{ route('app.settings.account.update') }}" enctype="multipart/form-data" class="mx-auto max-w-xl space-y-4 py-5">@csrf @method('PUT')
        <div class="card flex items-center gap-4 p-4">
            <x-ui.avatar :user="$user" size="lg" />
            <div class="min-w-0 flex-1"><label class="field-label" for="avatar">Profile photo</label><input id="avatar" type="file" name="avatar" accept="image/png,image/jpeg,image/webp" class="block w-full text-sm file:me-3 file:rounded-full file:border-0 file:bg-mint file:px-4 file:py-2 file:text-sm file:font-semibold">@error('avatar')<p class="field-error">{{ $message }}</p>@enderror<p class="field-hint">JPG, PNG or WebP up to 2 MB.</p></div>
        </div>
        <div class="card space-y-4 p-4">
            <x-ui.input name="name" label="Full name" :value="$user->name" required />
            <x-ui.input name="username" label="Username" :value="$user->profile->username" hint="Letters, numbers, dots and underscores. Shown in the community." />
            <x-ui.input name="email" type="email" label="Email" :value="$user->email" required :hint="$user->hasVerifiedEmail() ? 'Verified' : 'Not verified yet'" />
            <x-ui.input name="phone" type="tel" label="Phone" :value="$user->phone" />
            <x-ui.textarea name="bio" label="Bio" :value="$user->profile->bio" rows="3" />
            <div class="grid grid-cols-2 gap-3"><x-ui.input name="country" label="Country" :value="$user->profile->country" /><x-ui.input name="city" label="City" :value="$user->profile->city" /></div>
        </div>
        <x-ui.button type="submit" variant="primary" block>Save changes</x-ui.button>
    </form>
</x-layouts.app>
