@props(['title' => null, 'description' => null])
@if (request()->routeIs('app.explore.*'))
    <x-layouts.app :title="$title" headerless>
        <div class="mx-auto max-w-3xl px-4 py-4 lg:px-8">
            <a href="{{ route('app.more') }}" data-page-back onclick="if (document.referrer && document.referrer.startsWith(location.origin)) { event.preventDefault(); history.back(); }" class="icon-pop mb-3 inline-flex min-h-11 items-center gap-1 rounded-full px-2 text-sm font-semibold text-ink" aria-label="{{ __('Back') }}"><x-lucide-chevron-left class="size-5" />{{ __('Back') }}</a>
            {{ $slot }}
        </div>
    </x-layouts.app>
@else
    <x-layouts.public :title="$title" :description="$description">{{ $slot }}</x-layouts.public>
@endif
