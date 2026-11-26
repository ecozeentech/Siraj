@php($unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
<a href="{{ route('app.notifications') }}" {{ $attributes->class(['relative grid size-10 place-items-center rounded-full bg-white/10 text-white hover:bg-white/20']) }} aria-label="Notifications{{ $unread ? ' ('.$unread.' unread)' : '' }}">
    <x-lucide-bell class="size-5" />
    @if ($unread)<span class="absolute -end-0.5 -top-0.5 grid min-w-4.5 place-items-center rounded-full bg-gold-300 px-1 text-[10px] font-bold leading-4 text-forest-950">{{ $unread > 9 ? '9+' : $unread }}</span>@endif
</a>
