{{-- Original flat illustration of two children (boy with kufi, girl with hijab) for Kids Deen World. --}}
<svg viewBox="0 0 360 250" aria-hidden="true" {{ $attributes->merge(['class' => 'w-full']) }}>
    <defs>
        <linearGradient id="kd-sky" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#b9e6fd"/><stop offset="1" stop-color="#e9f8e4"/></linearGradient>
    </defs>
    <rect width="360" height="250" fill="url(#kd-sky)"/>
    <ellipse cx="60" cy="52" rx="34" ry="12" fill="#fff" opacity=".85"/><ellipse cx="300" cy="40" rx="30" ry="10" fill="#fff" opacity=".85"/>
    <ellipse cx="90" cy="250" rx="190" ry="70" fill="#8fd16a"/><ellipse cx="290" cy="255" rx="170" ry="75" fill="#6fc255"/>
    <g><circle cx="38" cy="150" r="30" fill="#4aa84a"/><rect x="34" y="165" width="8" height="50" fill="#7a5230"/></g>
    <g><circle cx="328" cy="140" r="30" fill="#58b552"/><rect x="324" y="158" width="8" height="55" fill="#7a5230"/></g>
    {{-- girl --}}
    <g transform="translate(205 70)">
        <path d="M-4 130c-6-50 6-98 52-98s58 48 52 98z" fill="#0c8a4f"/>
        <rect x="14" y="108" width="70" height="60" rx="20" fill="#3da54d"/>
        <ellipse cx="49" cy="66" rx="30" ry="33" fill="#f6c9a0"/>
        <path d="M17 70c-6-48 22-62 32-62s38 14 32 62c-4-20-14-30-32-30s-28 10-32 30z" fill="#0c8a4f"/>
        <path d="M17 70c0 40 18 62 32 62s32-22 32-62c-2 8-6 14-12 18-6 12-14 18-20 18s-14-6-20-18c-6-4-10-10-12-18z" fill="#0c8a4f" opacity=".0"/>
        <circle cx="38" cy="64" r="3" fill="#2b1a10"/><circle cx="60" cy="64" r="3" fill="#2b1a10"/>
        <path d="M38 78q11 11 22 0" stroke="#b5483a" stroke-width="3" fill="none" stroke-linecap="round"/>
        <circle cx="30" cy="74" r="5" fill="#f19a7a" opacity=".5"/><circle cx="68" cy="74" r="5" fill="#f19a7a" opacity=".5"/>
    </g>
    {{-- boy --}}
    <g transform="translate(70 80)">
        <rect x="0" y="86" width="100" height="90" rx="28" fill="#fff"/>
        <rect x="46" y="92" width="8" height="70" fill="#e6efe9"/>
        <ellipse cx="50" cy="54" rx="34" ry="36" fill="#f6c9a0"/>
        <path d="M14 46c2-26 22-34 36-34s34 8 36 34c-6-12-18-16-36-16s-30 4-36 16z" fill="#fff"/>
        <path d="M18 40c6-4 14-6 32-6s26 2 32 6" stroke="#dfe9e3" stroke-width="3" fill="none"/>
        <path d="M16 52c-2-8 0-14 4-16" stroke="#6b3d1f" stroke-width="5" fill="none" stroke-linecap="round"/>
        <circle cx="38" cy="58" r="3.500" fill="#2b1a10"/><circle cx="62" cy="58" r="3.500" fill="#2b1a10"/>
        <path d="M38 72q12 12 24 0" stroke="#b5483a" stroke-width="3.500" fill="none" stroke-linecap="round"/>
        <circle cx="29" cy="68" r="5" fill="#f19a7a" opacity=".5"/><circle cx="71" cy="68" r="5" fill="#f19a7a" opacity=".5"/>
    </g>
</svg>
