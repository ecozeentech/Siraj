{{-- One shared report dialog per page. Open with: $dispatch('report', { url: '...', label: 'post' }) --}}
<div x-data="{ open: false, url: '', label: 'content' }" x-on:report.window="url = $event.detail.url; label = $event.detail.label || 'content'; open = true" x-on:keydown.escape.window="open = false" x-show="open" x-cloak class="fixed inset-0 z-[70] flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-label="Report">
    <div class="absolute inset-0 bg-forest-950/50" @click="open = false"></div>
    <form method="POST" :action="url" class="relative w-full space-y-3 rounded-t-3xl bg-surface p-5 shadow-pop sm:max-w-md sm:rounded-3xl" style="padding-bottom: calc(1.25rem + var(--safe-bottom))">
        @csrf
        <h3 class="text-lg font-bold">Report <span x-text="label"></span></h3>
        <x-ui.select name="reason" label="Reason" :options="['spam' => 'Spam', 'harassment' => 'Harassment or bullying', 'hate' => 'Hate speech', 'misinformation' => 'Religious misinformation', 'inappropriate' => 'Inappropriate content', 'impersonation' => 'Impersonation', 'other' => 'Other']" />
        <x-ui.textarea name="details" label="Details (optional)" rows="3" maxlength="1000" />
        <div class="flex gap-3"><button type="button" class="btn btn-outline flex-1" @click="open = false">Cancel</button><button class="btn btn-primary flex-1">Send report</button></div>
    </form>
</div>
