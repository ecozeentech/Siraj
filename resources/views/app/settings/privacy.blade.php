<x-layouts.app title="Privacy">
    <x-slot:header><x-ui.page-header title="Privacy" :back="route('app.settings.index')" /></x-slot:header>
    <div class="mx-auto max-w-xl space-y-4 py-5">
        <form method="POST" action="{{ route('app.settings.privacy.update') }}" class="space-y-4">@csrf @method('PUT')
            <div class="card space-y-4 p-4">
                <x-ui.select name="profile_visibility" label="Who can see my profile" :options="['public' => 'Everyone', 'followers' => 'Followers only', 'private' => 'Only me']" :value="$profile->profile_visibility" />
                <x-ui.select name="message_privacy" label="Who can message me" :options="['everyone' => 'Everyone (as requests)', 'followers' => 'People I follow', 'nobody' => 'Nobody']" :value="$profile->message_privacy" />
                <x-ui.checkbox name="show_location" label="Show my city on my profile" :checked="$profile->show_location" />
                <x-ui.checkbox name="share_prayer_activity" label="Share prayer activity with followers" hint="Off by default. Your prayer tracker is private unless you turn this on." :checked="$profile->share_prayer_activity" />
            </div>
            <x-ui.button type="submit" block>Save</x-ui.button>
        </form>
        <section class="card p-4">
            <h2 class="font-bold">Blocked people</h2>
            @forelse ($blocked as $b)
                <form method="POST" action="{{ route('app.community.block', $b) }}" class="mt-3 flex items-center gap-3">@csrf @method('DELETE')<x-ui.avatar :user="$b" size="sm" /><span class="flex-1 font-medium">{{ $b->name }}</span><x-ui.button type="submit" variant="outline" size="sm">Unblock</x-ui.button></form>
            @empty
                <p class="mt-2 text-sm text-muted">You haven't blocked anyone.</p>
            @endforelse
        </section>
    </div>
</x-layouts.app>
