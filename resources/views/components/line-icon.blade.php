@props(['slug'])

<svg {{ $attributes->merge(['viewBox' => '0 0 64 64', 'fill' => 'none', 'stroke' => 'currentColor', 'stroke-width' => '2.5', 'stroke-linecap' => 'round', 'stroke-linejoin' => 'round', 'aria-hidden' => 'true']) }}>
    @switch($slug)
        @case('paper-products')
            <ellipse cx="26" cy="20" rx="14" ry="6" />
            <path d="M12 20v26c0 3.3 6.3 6 14 6s14-2.7 14-6V20" />
            <ellipse cx="26" cy="20" rx="4" ry="1.8" />
            <path d="M40 30h12v18H40" />
            @break
        @case('chemical-care')
            <path d="M26 8h12v8l6 8v28a4 4 0 0 1-4 4H24a4 4 0 0 1-4-4V24l6-8z" />
            <path d="M20 34h24" />
            <path d="M30 42c0-2 2-4 2-4s2 2 2 4a2 2 0 0 1-4 0z" />
            @break
        @case('institutional-hardware')
            <rect x="14" y="8" width="36" height="26" rx="6" />
            <path d="M24 34v6h16v-6" />
            <circle cx="32" cy="21" r="5" />
            <path d="M20 46h24l-2 10H22z" />
            @break
        @default
            <path d="M10 22l22-10 22 10-22 10z" />
            <path d="M10 22v20l22 10 22-10V22" />
            <path d="M32 32v20" />
    @endswitch
</svg>
