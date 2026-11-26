{{-- Mosque skyline silhouette. Colour comes from currentColor so the same art works on every surface. --}}
@props(['lights' => false])
<svg viewBox="0 0 400 100" preserveAspectRatio="xMidYMax slice" fill="currentColor" aria-hidden="true" {{ $attributes->merge(['class' => 'w-full']) }}>
    <rect x="0" y="86" width="400" height="14"/>
    {{-- left small mosque --}}
    <path d="M14 86V70a20 20 0 0 1 40 0v16z"/><rect x="32" y="44" width="4" height="8"/><circle cx="34" cy="42" r="2.5"/>
    <path d="M62 86V62a16 16 0 0 1 32 0v24z"/>
    {{-- left minaret --}}
    <rect x="104" y="38" width="9" height="48"/><path d="M102 38l6.500-20L115 38z"/><rect x="101" y="52" width="15" height="3"/><circle cx="108.500" cy="14" r="2.200"/>
    {{-- central mosque --}}
    <rect x="132" y="66" width="136" height="20"/>
    <path d="M146 66c0-32 22-46 54-46s54 14 54 46z"/>
    <rect x="198" y="6" width="4" height="16"/><circle cx="200" cy="5" r="3.500"/>
    <path d="M128 66c0-14 9-22 18-22s18 8 18 22zM236 66c0-14 9-22 18-22s18 8 18 22z"/>
    {{-- right minaret --}}
    <rect x="287" y="38" width="9" height="48"/><path d="M285 38l6.500-20L298 38z"/><rect x="284" y="52" width="15" height="3"/><circle cx="291.500" cy="14" r="2.200"/>
    {{-- right small mosques --}}
    <path d="M306 86V64a16 16 0 0 1 32 0v22z"/>
    <path d="M346 86V68a20 20 0 0 1 40 0v18z"/><rect x="364" y="42" width="4" height="8"/><circle cx="366" cy="40" r="2.500"/>
    @if ($lights)
        <g fill="#f6c453" opacity=".95">
            <rect x="188" y="72" width="6" height="10" rx="3"/><rect x="206" y="72" width="6" height="10" rx="3"/>
            <rect x="150" y="74" width="4" height="8" rx="2"/><rect x="246" y="74" width="4" height="8" rx="2"/>
            <rect x="24" y="74" width="4" height="8" rx="2"/><rect x="40" y="74" width="4" height="8" rx="2"/>
            <rect x="352" y="74" width="4" height="8" rx="2"/><rect x="372" y="74" width="4" height="8" rx="2"/>
            <circle cx="108.500" cy="62" r="1.800"/><circle cx="291.500" cy="62" r="1.800"/>
        </g>
    @endif
</svg>
