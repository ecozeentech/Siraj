<section class="rounded-2xl border border-line bg-white p-5">
    <h2 class="font-bold">Check in a ticket</h2>
    <form method="POST" action="{{ route('admin.events.attend') }}" class="mt-3 grid gap-3 sm:grid-cols-[1fr_12rem_auto] sm:items-end">
        @csrf
        <x-ui.select name="event_id" label="Event" :options="$events->mapWithKeys(fn ($e) => [$e->id => $e->title])" />
        <x-ui.input name="ticket_code" label="Ticket code" class="uppercase" />
        <x-ui.button type="submit">Mark attended</x-ui.button>
    </form>
</section>
