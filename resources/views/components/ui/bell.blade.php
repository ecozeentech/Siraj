@props(['dot' => false, 'plain' => false])
@php($unread = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0)
<a href="{{ route('app.notifications') }}" {{ $attributes->class(['relative grid place-items-center rounded-full text-white', $plain ? 'size-11 bg-transparent hover:bg-white/10' : 'size-10 bg-white/10 hover:bg-white/20']) }} aria-label="Notifications{{ $unread ? ' ('.$unread.' unread)' : '' }}">
    <x-lucide-bell class="size-5" />
    @if ($unread && $dot)
        <span class="absolute end-2 top-2 size-2.5 rounded-full bg-red-500 ring-2 ring-forest-800"></span>
    @elseif ($unread)
        <span class="absolute -end-0.5 -top-0.5 grid min-w-4.5 place-items-center rounded-full bg-gold-300 px-1 text-[10px] font-bold leading-4 text-forest-950">{{ $unread > 9 ? '9+' : $unread }}</span>
    @endif
</a>
