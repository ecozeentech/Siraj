<details class="card p-4"><summary class="cursor-pointer text-sm font-semibold">Report a problem or suggest a correction</summary>
    <form method="POST" action="{{ route('app.directory.report', [$kind, $subject->id]) }}" class="mt-3 space-y-3">@csrf
        <x-ui.select name="type" label="What is wrong?" :options="$options" />
        <x-ui.textarea name="details" label="Details" rows="3" required maxlength="1500" />
        <x-ui.button type="submit" size="sm">Send report</x-ui.button>
    </form></details>
