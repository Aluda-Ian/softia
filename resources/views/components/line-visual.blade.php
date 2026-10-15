{{--
    Product-line artwork for cards and headers. An image uploaded in the admin
    always wins; otherwise each Softia line gets its own composed mockup.
    Products stand on a shared floor line with a ground shadow; the dispensers
    are wall-mounted, so they stay centred with their shadow on the wall.
    `dark` = shown on the navy card, where only true cut-outs are used.
--}}
@props(['line', 'dark' => false])

@php
    $floorShadow = $dark ? 'bg-black/45' : 'bg-navy/25';
@endphp

<div {{ $attributes->class(['relative flex justify-center']) }}>
    <span @class([
        'pointer-events-none absolute inset-[8%] rounded-full blur-2xl',
        'bg-tranquil/40' => $dark,
        'bg-white' => ! $dark,
    ]) aria-hidden="true"></span>

    @if ($line->imageUrl())
        <div class="relative flex h-full w-full items-end justify-center pb-[6%]">
            <span class="absolute bottom-[5%] h-4 w-1/2 rounded-[100%] blur-md {{ $floorShadow }}" aria-hidden="true"></span>
            <img src="{{ $line->imageUrl() }}" alt="{{ $line->name }}" loading="lazy" class="relative max-h-full max-w-full object-contain">
        </div>
    @else
        @switch($line->slug)
            @case('paper-products')
                <div class="relative flex h-full w-full items-end justify-center pb-[6%]">
                    <span class="absolute bottom-[5%] h-5 w-[58%] rounded-[100%] blur-md {{ $floorShadow }}" aria-hidden="true"></span>
                    <img src="{{ asset('images/cutouts/roll-single.png') }}" alt="Softia Premium Toilet Tissue roll" loading="lazy"
                         class="relative w-56 max-w-full object-contain sm:w-60">
                </div>
                @break
            @case('chemical-care')
                <div class="relative flex h-full w-full items-end justify-center pb-[6%]">
                    <span class="absolute bottom-[5%] h-5 w-[34%] rounded-[100%] blur-md {{ $floorShadow }}" aria-hidden="true"></span>
                    <img src="{{ asset('images/cutouts/cleaner-bottle.png') }}" alt="Softia toilet cleaner bottle" loading="lazy"
                         class="relative h-44 w-auto object-contain sm:h-52">
                </div>
                @break
            @case('institutional-hardware')
                <div class="relative flex h-full w-full items-center justify-center">
                    <x-mockups.dispenser class="w-[92%] max-h-full" />
                </div>
                @break
            @case('aluminium-foil-packaging')
                <div class="relative flex h-full w-full items-end justify-center pb-[2%]">
                    <x-mockups.foil class="w-full max-h-full" />
                </div>
                @break
            @default
                <div class="relative flex h-full w-full items-center justify-center">
                    <span class="grid size-2/3 place-items-center rounded-full bg-white/70 text-navy">
                        <x-line-icon :slug="$line->slug" class="size-1/2" />
                    </span>
                </div>
        @endswitch
    @endif
</div>
