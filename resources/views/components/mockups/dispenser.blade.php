{{-- Branded washroom hardware mockup: wall-mounted jumbo roll dispenser + soap dispenser (shadows fall on the wall behind, not a floor). --}}
@php($id = 'dsp'.uniqid())
<svg {{ $attributes->merge(['viewBox' => '0 10 400 320', 'role' => 'img', 'aria-label' => 'Softia jumbo roll and soap dispensers']) }}>
    <defs>
        <linearGradient id="{{ $id }}-body" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#FFFFFF" />
            <stop offset="1" stop-color="#DCE6F4" />
        </linearGradient>
        <linearGradient id="{{ $id }}-edge" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#FFFFFF" stop-opacity=".9" />
            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
        </linearGradient>
        <linearGradient id="{{ $id }}-window" x1="0" y1="0" x2="0" y2="1">
            <stop offset="0" stop-color="#8DB3E2" stop-opacity=".55" />
            <stop offset="1" stop-color="#1E3A8A" stop-opacity=".35" />
        </linearGradient>
        <filter id="{{ $id }}-wall" x="-30%" y="-30%" width="160%" height="170%">
            <feDropShadow dx="0" dy="14" stdDeviation="12" flood-color="#0B1B4D" flood-opacity=".22" />
        </filter>
    </defs>

    {{-- Jumbo roll dispenser --}}
    <rect x="58" y="28" width="224" height="250" rx="46" fill="url(#{{ $id }}-body)" stroke="#C7D5EA" stroke-width="2" filter="url(#{{ $id }}-wall)" />
    <rect x="70" y="38" width="200" height="60" rx="30" fill="url(#{{ $id }}-edge)" />
    <rect x="92" y="62" width="156" height="92" rx="46" fill="url(#{{ $id }}-window)" />
    <circle cx="170" cy="108" r="36" fill="#FFFFFF" />
    <circle cx="170" cy="108" r="36" fill="none" stroke="#E3EAF5" stroke-width="10" />
    <circle cx="170" cy="108" r="10" fill="#C9D6EA" />
    <text x="170" y="214" text-anchor="middle" font-family="'Poetsen One', sans-serif" font-size="46" fill="#1E3A8A">s<tspan fill="#8DB3E2">o</tspan>ftia</text>
    <text x="207" y="232" text-anchor="middle" font-family="Montserrat, sans-serif" font-size="11" font-weight="600" fill="#1E3A8A" fill-opacity=".7">stay confident.</text>
    <rect x="128" y="256" width="84" height="10" rx="5" fill="#1E3A8A" fill-opacity=".15" />
    {{-- Paper tail --}}
    <path d="M136 266h68v44l-8.5-5-8.5 5-8.5-5-8.5 5-8.5-5-8.5 5-8.5-5-8.5 5z" fill="#FFFFFF" stroke="#D8E1EE" stroke-width="1.5" />

    {{-- Soap dispenser --}}
    <rect x="288" y="132" width="68" height="150" rx="22" fill="url(#{{ $id }}-body)" stroke="#C7D5EA" stroke-width="2" filter="url(#{{ $id }}-wall)" />
    <rect x="300" y="152" width="44" height="62" rx="14" fill="url(#{{ $id }}-window)" />
    <rect x="303" y="182" width="38" height="29" rx="11" fill="#8DB3E2" fill-opacity=".8" />
    <text x="322" y="244" text-anchor="middle" font-family="'Poetsen One', sans-serif" font-size="17" fill="#1E3A8A">s<tspan fill="#8DB3E2">o</tspan>ftia</text>
    <rect x="304" y="282" width="36" height="12" rx="6" fill="#1E3A8A" />
    <path d="M318 294h8v10a4 4 0 0 1-8 0z" fill="#1E3A8A" fill-opacity=".85" />
</svg>
