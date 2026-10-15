{{-- Branded aluminium foil box + foil food container, both resting on the floor line (y≈318). --}}
@php($id = 'foil'.uniqid())
<svg {{ $attributes->merge(['viewBox' => '0 100 400 240', 'role' => 'img', 'aria-label' => 'Softia aluminium foil and food container']) }}>
    <defs>
        <linearGradient id="{{ $id }}-metal" x1="0" y1="0" x2="1" y2="0">
            <stop offset="0" stop-color="#C3CCD8" />
            <stop offset=".25" stop-color="#F7F9FB" />
            <stop offset=".5" stop-color="#B7C1CE" />
            <stop offset=".75" stop-color="#EEF2F6" />
            <stop offset="1" stop-color="#AEB9C7" />
        </linearGradient>
        <linearGradient id="{{ $id }}-sheet" x1="0" y1="1" x2="0" y2="0">
            <stop offset="0" stop-color="#D9E0E9" />
            <stop offset="1" stop-color="#FFFFFF" />
        </linearGradient>
        <linearGradient id="{{ $id }}-front" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0" stop-color="#2A4BA3" />
            <stop offset="1" stop-color="#1E3A8A" />
        </linearGradient>
        <radialGradient id="{{ $id }}-shadow">
            <stop offset="0" stop-color="#0B1B4D" stop-opacity=".30" />
            <stop offset="1" stop-color="#0B1B4D" stop-opacity="0" />
        </radialGradient>
        <radialGradient id="{{ $id }}-contact">
            <stop offset="0" stop-color="#0B1B4D" stop-opacity=".45" />
            <stop offset="1" stop-color="#0B1B4D" stop-opacity="0" />
        </radialGradient>
    </defs>

    {{-- Floor: soft ambient shadow plus tight contact shadows under each item --}}
    <ellipse cx="200" cy="320" rx="186" ry="16" fill="url(#{{ $id }}-shadow)" />
    <ellipse cx="166" cy="310" rx="150" ry="7" fill="url(#{{ $id }}-contact)" />
    <ellipse cx="326" cy="320" rx="70" ry="6" fill="url(#{{ $id }}-contact)" />

    {{-- Foil sheet unrolling from the box --}}
    <path d="M62 196C94 132 234 108 304 140l-6 46H66z" fill="url(#{{ $id }}-sheet)" stroke="#CBD4E0" stroke-width="1.5" />
    <path d="M94 172c48-30 128-40 188-24" fill="none" stroke="#FFFFFF" stroke-width="3" stroke-linecap="round" opacity=".9" />

    {{-- Box: top, side, front (bottom edge on the floor at y=308) --}}
    <polygon points="24,208 62,180 312,180 274,208" fill="#3557B0" />
    <rect x="60" y="184" width="212" height="11" rx="5.5" fill="url(#{{ $id }}-metal)" />
    <polygon points="274,208 312,180 312,280 274,308" fill="#16306F" />
    <rect x="24" y="208" width="250" height="100" rx="8" fill="url(#{{ $id }}-front)" />
    <path d="M24 280h250v20a8 8 0 0 1-8 8H32a8 8 0 0 1-8-8z" fill="#8DB3E2" />
    <text x="46" y="258" font-family="'Poetsen One', sans-serif" font-size="40" fill="#FFFFFF">s<tspan fill="#8DB3E2">o</tspan>ftia</text>
    <text x="48" y="298" font-family="Montserrat, sans-serif" font-size="11" font-weight="700" letter-spacing="1.5" fill="#1E3A8A">ALUMINIUM FOIL · 10 m</text>
    <text x="196" y="240" font-family="Montserrat, sans-serif" font-size="9.5" font-weight="600" fill="#FFFFFF" fill-opacity=".75">Food safe</text>
    <text x="196" y="255" font-family="Montserrat, sans-serif" font-size="9.5" font-weight="600" fill="#FFFFFF" fill-opacity=".75">Heavy duty</text>

    {{-- Foil container with lid, standing on the floor in front of the box --}}
    <path d="M266 276h120l-11 42H277z" fill="url(#{{ $id }}-metal)" stroke="#9FAAB8" stroke-width="1.5" />
    <path d="M272 286h108M274 297h104M276 308h100" stroke="#FFFFFF" stroke-opacity=".6" stroke-width="2" />
    <rect x="258" y="262" width="136" height="15" rx="5" fill="#FFFFFF" stroke="#C7D2E2" stroke-width="1.5" />
    <rect x="258" y="262" width="136" height="6" rx="3" fill="#8DB3E2" fill-opacity=".45" />
</svg>
