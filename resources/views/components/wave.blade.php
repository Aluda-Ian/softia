{{--
    The Softia wave: the curve from the band on Softia packaging, used as the
    brand's one recurring shape. Always this exact path; colour comes from the
    text colour (fill="currentColor"), size from the class (it stretches).

    flip   = mirror left/right    invert = mirror top/bottom (fills the top)
    Guideline: at most one or two waves visible on screen at a time.
--}}
@props(['flip' => false, 'invert' => false])

@php
    $transforms = trim(($flip ? 'scaleX(-1) ' : '').($invert ? 'scaleY(-1)' : ''));
@endphp

<svg {{ $attributes->class(['pointer-events-none block w-full']) }}
     viewBox="0 0 1440 120" preserveAspectRatio="none" aria-hidden="true" focusable="false"
     @if ($transforms) style="transform: {{ $transforms }}" @endif>
    <path fill="currentColor" d="M0 56C240 16 520 0 820 34C1040 59 1180 104 1440 76V120H0Z" />
</svg>
