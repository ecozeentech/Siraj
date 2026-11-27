<x-layouts.contextual title="Contact">
    <section class="mx-auto max-w-xl px-4 py-12">
        <h1 class="text-3xl font-bold">Contact us</h1>
        <p class="mt-2 text-muted">Questions, feedback or partnership enquiries — we read every message.</p>
        <form method="POST" action="{{ route('contact.submit') }}" class="card mt-6 space-y-4 p-5">@csrf
            <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
            <x-ui.input name="name" label="Your name" required />
            <x-ui.input name="email" type="email" label="Email" required />
            <x-ui.input name="subject" label="Subject" required />
            <x-ui.textarea name="message" label="Message" rows="5" required />
            <x-ui.button type="submit" variant="primary" block>Send message</x-ui.button>
        </form>
    </section>
</x-layouts.contextual>
