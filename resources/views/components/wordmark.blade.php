@props([
    'inverted' => false,
    'alt' => 'Softia · stay confident.',
])

<x-logo :inverted="$inverted" :alt="$alt" {{ $attributes }} />
