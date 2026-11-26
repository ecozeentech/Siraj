<x-layouts.app title="Add a listing">
    <x-slot:header><x-ui.page-header title="Add a business" subtitle="Reviewed by our team before it appears" :back="route('app.directory.index', ['tab' => 'businesses'])" /></x-slot:header>
    <form method="POST" action="{{ route('app.directory.submit.store') }}" class="mx-auto max-w-2xl space-y-4 py-5">@csrf
        <x-ui.alert type="info">A halal certificate number you enter below is only a claim. SIRAJ marks a business as halal verified only after staff check the certificate with the certifier.</x-ui.alert>
        <section class="card space-y-3 p-5">
            <x-ui.input name="name" label="Business name" required maxlength="120" />
            <x-ui.select name="business_category_id" label="Category" required :options="$categories->pluck('name', 'id')->all()" placeholder="Choose a category" />
            <x-ui.textarea name="description" label="Description" rows="3" maxlength="2000" />
            <x-ui.input name="address" label="Street address" maxlength="255" />
            <div class="grid gap-3 sm:grid-cols-2"><x-ui.input name="city" label="City" required maxlength="120" /><x-ui.input name="state" label="State" maxlength="120" /></div>
            <div class="grid gap-3 sm:grid-cols-2"><x-ui.input name="phone" label="Phone" maxlength="40" /><x-ui.input name="email" type="email" label="Email" /></div>
            <x-ui.input name="website" type="url" label="Website" placeholder="https://" />
        </section>
        <section class="card space-y-3 p-5"><h2 class="font-bold">Halal certification (optional)</h2>
            <x-ui.input name="halal_certifier" label="Certifying body" maxlength="160" /><x-ui.input name="halal_certificate_number" label="Certificate number" maxlength="80" /></section>
        <x-ui.button type="submit" block>Submit for review</x-ui.button>
    </form>
</x-layouts.app>
