<x-layouts.app title="Family">
    <x-slot:header><x-ui.page-header title="Family Management" subtitle="Child profiles & parental controls" :back="route('app.profile.show')" /></x-slot:header>
    <div class="space-y-5 py-5">
        <x-ui.alert type="info" title="Child safety">While a child profile is active, community, messaging, AI, wallet and settings are locked. Leaving Kids mode requires your account password. Your plan allows {{ $max }} child profiles.</x-ui.alert>
        @foreach ($children as $c)
            @php($s = $stats[$c->id])
            <section class="card p-4" x-data="{ edit: false }">
                <div class="flex items-center gap-3"><span class="grid size-14 place-items-center rounded-full bg-gold-100 text-2xl">{{ $c->avatar === 'girl' ? '🧕' : '👦' }}</span><div class="min-w-0 flex-1"><p class="font-bold">{{ $c->name }}</p><p class="text-xs text-muted">Age {{ $c->age() }} · {{ $s['stars'] }} stars · {{ $s['lessons'] }} lessons · {{ $s['games'] }} games</p></div>
                    <form method="POST" action="{{ route('app.family.enter', $c) }}">@csrf<x-ui.button type="submit" size="sm">Start Kids mode</x-ui.button></form></div>
                <div class="mt-3 grid grid-cols-2 gap-2 text-center text-sm"><div class="rounded-xl bg-surface-2 p-2"><p class="font-bold">{{ floor($s['today'] / 60) }} min</p><p class="text-xs text-muted">today / {{ $c->controls->daily_limit_minutes }} limit</p></div><div class="rounded-xl bg-surface-2 p-2"><p class="font-bold">{{ floor($s['week'] / 60) }} min</p><p class="text-xs text-muted">last 7 days</p></div></div>
                <button type="button" class="mt-3 text-sm font-semibold text-forest-700 dark:text-emerald-500" @click="edit = !edit" x-text="edit ? 'Hide settings' : 'Edit profile & controls'"></button>
                <form x-show="edit" x-cloak method="POST" action="{{ route('app.family.children.update', $c) }}" class="mt-3 space-y-3 border-t border-line pt-3">@csrf @method('PUT')
                    <x-ui.input :name="'name'" label="Name" :value="$c->name" required />
                    <div class="grid grid-cols-2 gap-3"><x-ui.input name="birth_year" type="number" label="Birth year" :value="$c->birth_year" required /><x-ui.select name="avatar" label="Avatar" :options="['boy' => 'Boy', 'girl' => 'Girl']" :value="$c->avatar" /></div>
                    <x-ui.input name="daily_limit_minutes" type="number" min="10" max="480" label="Daily limit (minutes)" :value="$c->controls->daily_limit_minutes" />
                    <fieldset><legend class="field-label">Allowed sections</legend><div class="flex flex-wrap gap-4">@foreach (['courses' => 'Courses', 'stories' => 'Stories', 'games' => 'Games'] as $k => $l)<label class="flex items-center gap-2 text-sm"><input type="checkbox" name="allowed_sections[]" value="{{ $k }}" @checked(in_array($k, $c->controls->allowed_sections ?? [])) class="size-5 rounded-md border-line text-forest-700">{{ $l }}</label>@endforeach</div></fieldset>
                    <div class="flex gap-3"><x-ui.button type="submit" class="flex-1">Save</x-ui.button></div>
                </form>
                <x-ui.confirm class="mt-2" :action="route('app.family.children.destroy', $c)" method="DELETE" :message="'Delete '.$c->name.'\'s profile and all progress?'" label="Delete profile" variant="ghost" />
            </section>
        @endforeach
        @if ($children->count() < $max)
            <form method="POST" action="{{ route('app.family.children.store') }}" class="card space-y-3 p-4">@csrf<h2 class="font-bold">Add a child</h2>
                <x-ui.input name="name" label="Child's first name" required />
                <div class="grid grid-cols-2 gap-3"><x-ui.input name="birth_year" type="number" label="Birth year" :min="now()->year - 17" :max="now()->year" required /><x-ui.select name="avatar" label="Avatar" :options="['boy' => 'Boy', 'girl' => 'Girl']" /></div>
                <x-ui.button type="submit" block>Add child</x-ui.button></form>
        @else
            <x-ui.alert type="warning">You've reached your plan's limit. <a href="{{ route('app.subscription.index') }}" class="font-semibold underline">See plans</a></x-ui.alert>
        @endif
    </div>
</x-layouts.app>
