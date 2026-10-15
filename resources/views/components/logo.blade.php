@props([
    'inverted' => false,
    'alt' => 'Softia · stay confident.',
])

@php
    // Asset 2 is dark on light surfaces; Asset 1 is light on dark backgrounds
    $src = $inverted
        ? asset('images/logo-light.png')
        : asset('images/logo-dark.png');
@endphp

<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    {{ $attributes->class(['inline-block h-9 w-auto object-contain align-middle select-none']) }}
    loading="eager"
    decoding="async"
>
