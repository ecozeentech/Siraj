<x-layouts.contextual title="Pricing">
    <section class="mx-auto max-w-6xl px-4 py-12 lg:px-8">
        <h1 class="text-3xl font-bold">Simple pricing</h1>
        <p class="mt-2 text-muted">Start free. Upgrade to SIRAJ Plus for premium learning, family profiles and higher AI limits.</p>
        <div class="mt-8 grid gap-4 md:grid-cols-3">
            @foreach ($plans as $plan)
                <div @class(['card flex flex-col p-6', 'ring-2 ring-gold-400' => $plan->is_featured])>
                    @if ($plan->is_featured)<span class="badge mb-2 w-fit bg-gold-200 text-forest-900">Most popular</span>@endif
                    <h2 class="text-xl font-bold">{{ $plan->name }}</h2>
                    <p class="mt-1 text-sm text-muted">{{ $plan->description }}</p>
                    <p class="mt-4 text-3xl font-bold">{{ $plan->price_kobo ? $plan->priceFormatted() : 'Free' }}<span class="text-sm font-medium text-muted">{{ $plan->interval === 'month' ? ' / month' : ($plan->interval === 'year' ? ' / year' : '') }}</span></p>
                    <ul class="mt-4 flex-1 space-y-2 text-sm">
                        @foreach ($plan->features ?? [] as $f)<li class="flex gap-2"><x-lucide-circle-check class="mt-0.5 size-4 shrink-0 text-forest-700" />{{ $f }}</li>@endforeach
                    </ul>
                    <x-ui.button :href="auth()->check() ? route('app.subscription.index') : route('register')" :variant="$plan->is_featured ? 'gold' : 'outline'" class="mt-6" block>{{ $plan->price_kobo ? 'Choose '.$plan->name : 'Get started' }}</x-ui.button>
                </div>
            @endforeach
        </div>
        @if ($plans->isEmpty())<x-ui.empty icon="crown" title="Plans coming soon" text="Subscription plans haven't been published yet." />@endif
    </section>
</x-layouts.contextual>
