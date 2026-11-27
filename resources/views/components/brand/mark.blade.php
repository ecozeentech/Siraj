@php($uid = \Illuminate\Support\Str::random(5))
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 124" fill="none" {{ $attributes->merge(['class' => 'h-12 w-auto', 'aria-hidden' => 'true']) }}>
  <defs>
    <linearGradient id="sg-{{ $uid }}" x1="0" y1="0" x2="1" y2="1">
      <stop offset="0" stop-color="#fff4d2"/>
      <stop offset=".5" stop-color="#f0c85a"/>
      <stop offset="1" stop-color="#c9962e"/>
    </linearGradient>
    <linearGradient id="sg2-{{ $uid }}" x1="0" y1="1" x2="1" y2="0">
      <stop offset="0" stop-color="#b58a2c"/>
      <stop offset=".6" stop-color="#e8c26a"/>
      <stop offset="1" stop-color="#f6e3ae"/>
    </linearGradient>
  </defs>
  <path d="M57 2.2A11 11 0 1 0 57 22.6 8.4 8.4 0 1 1 57 2.2Z" fill="url(#sg-{{ $uid }})"/>
  <path d="M50 24.5c-1 0 0 0 0 0M50 22.5v6" stroke="url(#sg-{{ $uid }})" stroke-width="2.2" stroke-linecap="round"/>
  <path d="M14.5 117C11 83 22 56 50 29c28 27 39 54 35.500 88" stroke="url(#sg-{{ $uid }})" stroke-width="4.6" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M22 116c-4-30 5-52 28-76" stroke="url(#sg2-{{ $uid }})" stroke-width="1.5" stroke-linecap="round" opacity=".7"/>
  <path d="M72 54C60 44 36 46 33 62c-3 15 34 17 42 29 8 12-7 22-27 21" stroke="#fffef8" stroke-width="9" stroke-linecap="round" stroke-linejoin="round"/>
  <path d="M30 82c8 7 30 8 40 16 6 5 6 10-1 13-8 4-30 4-52 7" stroke="url(#sg-{{ $uid }})" stroke-width="7" stroke-linecap="round" stroke-linejoin="round"/>
</svg>
