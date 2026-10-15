@extends('layouts.app')

@php
    /*
     * Card styles are tied to each product line (not its position) so the
     * artwork always sits on a background it was composed for.
     */
    $cardThemes = [
        'light' => ['card' => 'bg-white', 'icon' => 'bg-tranquil/20 text-navy', 'muted' => 'text-navy/60', 'chip' => 'bg-cloud', 'dark' => false, 'wave' => 'text-tranquil/15'],
        'navy' => ['card' => 'bg-navy text-white', 'icon' => 'bg-white/10 text-tranquil', 'muted' => 'text-white/70', 'chip' => 'bg-white/10', 'dark' => true, 'wave' => 'text-white/[0.07]'],
        'sky' => ['card' => 'bg-tranquil/25', 'icon' => 'bg-white text-navy', 'muted' => 'text-navy/70', 'chip' => 'bg-white/70', 'dark' => false, 'wave' => 'text-white/60'],
        'outline' => ['card' => 'bg-white ring-1 ring-tranquil/50', 'icon' => 'bg-navy text-white', 'muted' => 'text-navy/60', 'chip' => 'bg-tranquil/15', 'dark' => false, 'wave' => 'text-tranquil/15'],
    ];
    $themeForLine = [
        'paper-products' => 'light',
        'chemical-care' => 'navy',
        'institutional-hardware' => 'sky',
        'aluminium-foil-packaging' => 'outline',
    ];
@endphp

@section('content')
    {{-- Hero: a soft Softia-blue "sky" with the range on a lit stage --}}
    <section class="relative isolate overflow-hidden bg-gradient-to-b from-[#C9DCF3] via-[#E4EEF9] to-cloud">
        <div class="pointer-events-none absolute inset-0 -z-10" aria-hidden="true">
            <span class="absolute -left-24 top-16 h-40 w-[28rem] animate-drift rounded-full bg-white/70 blur-3xl"></span>
            <span class="absolute -right-32 top-40 h-48 w-[32rem] animate-drift rounded-full bg-white/60 blur-3xl [animation-delay:-8s]"></span>
            <span class="absolute left-1/3 top-4 h-24 w-80 animate-drift rounded-full bg-white/50 blur-2xl [animation-delay:-16s]"></span>
        </div>

        <div class="relative z-30 mx-auto max-w-4xl px-4 pt-10 text-center sm:px-6 sm:pt-14">
            <h1 class="text-4xl font-bold leading-[1.05] tracking-tight sm:text-6xl lg:text-7xl">
                Soft on <span class="font-brand font-normal">you.</span><br>
                <span class="bg-gradient-to-r from-navy via-[#3460C8] to-tranquil bg-clip-text pb-1 font-brand font-normal text-transparent">Strong for business.</span>
            </h1>
            <p class="mx-auto mt-6 max-w-2xl text-base leading-relaxed text-navy/75 sm:text-lg">
                Premium tissue, professional cleaning care, washroom hardware and food packaging, made for Kenyan homes, hotels, hospitals, schools and offices.
            </p>
            <div class="relative z-30 mt-8 flex flex-wrap justify-center gap-3">
                <a href="#product-lines" class="rounded-full bg-navy px-7 py-3.5 font-semibold text-white shadow-lift transition hover:-translate-y-0.5 hover:bg-navy/90">Shop products</a>
                <a href="#for-business" class="rounded-full bg-white/90 px-7 py-3.5 font-semibold shadow-soft backdrop-blur transition hover:-translate-y-0.5 hover:shadow-lift">Request a business quote</a>
            </div>
        </div>

        {{-- Product stage. Animation sits on inner wrappers: Tailwind v4 positions with the CSS translate property, which the float keyframes also use. --}}
        <div class="relative mx-auto mt-4 h-64 max-w-4xl px-4 sm:mt-6 sm:h-[22rem]">
            <div class="absolute bottom-4 left-1/2 aspect-square w-[60%] max-w-[13rem] -translate-x-1/2 rounded-full sm:w-[70%] sm:max-w-[22rem] bg-white shadow-[0_0_80px_30px_rgba(255,255,255,0.9)]" aria-hidden="true"></div>
            <div class="absolute bottom-3 left-1/2 h-8 w-[62%] -translate-x-1/2 rounded-[100%] bg-navy/15 blur-xl" aria-hidden="true"></div>

            <div class="absolute inset-x-0 bottom-5 flex items-end justify-center">
                <div class="relative z-20 -mr-[7%] mb-1 w-[20%] max-w-44">
                    <img src="{{ asset('images/cutouts/roll-single.png') }}" alt="" fetchpriority="high"
                         class="w-full animate-float drop-shadow-xl [animation-delay:-2s]">
                </div>
                <div class="relative z-10 w-[46%] max-w-sm">
                    <img src="{{ asset('images/cutouts/rolls-6-pack.png') }}" alt="Softia Premium Toilet Tissue 6-pack" fetchpriority="high"
                         class="w-full animate-float mix-blend-multiply">
                </div>
                <div class="relative z-20 -ml-[8%] mb-1 w-[11%] max-w-[6.5rem]">
                    <img src="{{ asset('images/cutouts/cleaner-bottle.png') }}" alt="Softia toilet cleaner" fetchpriority="high"
                         class="w-full rotate-6 animate-float drop-shadow-[0_20px_20px_rgba(30,58,138,0.25)] [animation-delay:-4s]">
                </div>
            </div>

            <span class="absolute left-[3%] top-10 z-30 hidden rounded-2xl bg-white/90 px-4 py-3 text-left shadow-lift backdrop-blur md:block">
                <span class="block text-xs font-semibold uppercase tracking-widest text-navy/60">Premium</span>
                <span class="block text-sm font-bold">2-ply softness</span>
            </span>
            <span class="absolute right-[3%] top-2 z-30 hidden rounded-2xl bg-navy px-4 py-3 text-left text-white shadow-lift md:block">
                <span class="block text-xs font-semibold uppercase tracking-widest text-tranquil">Wholesale</span>
                <span class="block text-sm font-bold">Bulk prices, automatically</span>
            </span>
        </div>

        {{-- Quick links into each product line --}}
        <nav class="relative z-10 mx-auto max-w-5xl px-4 pb-10 sm:px-6" aria-label="Shop by product line">
            <ul class="grid grid-cols-2 gap-2 rounded-[1.75rem] bg-white p-2 shadow-lift sm:grid-cols-4">
                @foreach ($productLines as $line)
                    <li>
                        <a href="{{ route('product-lines.show', $line) }}" class="group flex items-center gap-3 rounded-[1.25rem] p-3 transition hover:bg-cloud">
                            <span class="grid size-11 shrink-0 place-items-center rounded-2xl bg-tranquil/20 transition group-hover:bg-navy group-hover:text-white">
                                <x-line-icon :slug="$line->slug" class="size-6" />
                            </span>
                            <span class="min-w-0">
                                <span class="block truncate text-sm font-bold">{{ $line->menuLabel() }}</span>
                                <span class="block text-xs text-navy/60">Shop now <span class="inline-block transition group-hover:translate-x-0.5">→</span></span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </nav>

        {{-- Brand wave: the sky flows into the page --}}
        <x-wave class="absolute inset-x-0 bottom-0 h-10 text-white sm:h-16" />
    </section>

    {{-- Value strip --}}
    <section class="border-b border-navy/5 bg-white">
        <ul class="mx-auto grid max-w-7xl gap-6 px-4 py-8 text-sm sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-8">
            @foreach ([
                ['Nairobi delivery', 'Dispatched from our logistics hub'],
                ['Wholesale pricing', 'Lower unit prices on bulk quantities'],
                ['Quotes & invoicing', 'Built for institutional procurement'],
                ['M-Pesa or pay on delivery', 'Simple, flexible retail payment'],
            ] as [$title, $text])
                <li class="flex items-start gap-3">
                    <span class="mt-0.5 grid size-6 shrink-0 place-items-center rounded-full bg-tranquil/30 text-xs font-bold">✓</span>
                    <span><strong class="block">{{ $title }}</strong><span class="text-navy/60">{{ $text }}</span></span>
                </li>
            @endforeach
        </ul>
    </section>

    {{-- Product line cards --}}
    <section id="product-lines" class="mx-auto max-w-7xl scroll-mt-24 px-4 py-24 sm:px-6 lg:px-8">
        <div class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">Our product lines</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Everything your space needs to stay <span class="wave-underline font-brand font-normal text-tranquil">clean &amp; confident.</span></h2>
        </div>

        {{-- Phones: swipeable carousel (never moves on its own). Tablet and up: grid. --}}
        <div data-carousel aria-label="Product lines" role="region"
             class="-mx-4 mt-8 flex snap-x snap-mandatory scroll-px-4 gap-4 overflow-x-auto px-4 pb-3 pt-2 [scrollbar-width:none] sm:mx-0 sm:mt-12 sm:grid sm:gap-6 sm:overflow-visible sm:px-0 sm:pb-0 lg:grid-cols-2 [&::-webkit-scrollbar]:hidden">
            @foreach ($productLines as $line)
                @php($theme = $cardThemes[$themeForLine[$line->slug] ?? array_keys($cardThemes)[$loop->index % 4]])
                <a href="{{ route('product-lines.show', $line) }}"
                   data-carousel-item
                   class="group relative grid w-[85%] shrink-0 snap-start overflow-hidden rounded-[2rem] shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift sm:w-auto sm:grid-cols-[1.05fr_1fr] {{ $theme['card'] }}">
                    {{-- Brand wave band, like the band on the packaging --}}
                    <x-wave class="absolute inset-x-0 bottom-0 h-12 {{ $theme['wave'] }}" :flip="$loop->odd" />

                    <div class="relative flex flex-col p-7 sm:p-8">
                        <span class="grid size-12 place-items-center rounded-2xl {{ $theme['icon'] }}">
                            <x-line-icon :slug="$line->slug" class="size-7" />
                        </span>
                        <h3 class="mt-6 font-brand text-[1.7rem] leading-tight">{{ $line->name }}</h3>
                        <p class="mt-2 text-sm {{ $theme['muted'] }}">{{ $line->tagline }}</p>

                        <ul class="mt-5 flex flex-wrap gap-2">
                            @foreach ($line->children->take(4) as $section)
                                <li class="rounded-full px-3 py-1 text-xs font-medium {{ $theme['chip'] }}">{{ $section->name }}</li>
                            @endforeach
                        </ul>

                        <span class="mt-auto inline-flex items-center gap-2 pt-8 text-sm font-bold">
                            Explore range
                            <span class="transition group-hover:translate-x-1" aria-hidden="true">→</span>
                        </span>
                    </div>

                    <x-line-visual :line="$line" :dark="$theme['dark']"
                                   class="order-first h-52 p-6 transition duration-500 group-hover:scale-[1.03] sm:order-none sm:h-72 sm:self-center" />
                </a>
            @endforeach
        </div>

        {{-- Position dots (phones only) --}}
        <div data-carousel-dots class="mt-4 flex justify-center gap-2 sm:hidden">
            @foreach ($productLines as $line)
                <button type="button" data-carousel-dot="{{ $loop->index }}" aria-label="Show {{ $line->menuLabel() }}"
                        @class(['h-2 rounded-full transition-all duration-300', 'w-6 bg-navy' => $loop->first, 'w-2 bg-navy/20' => ! $loop->first])></button>
            @endforeach
        </div>
    </section>

    {{-- For business --}}
    <section id="for-business" class="scroll-mt-24 px-4 sm:px-6 lg:px-8">
        <div class="mx-auto grid max-w-7xl overflow-hidden rounded-[2.5rem] bg-navy text-white lg:grid-cols-2">
            <div class="p-8 sm:p-12 lg:p-16">
                <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">For institutions</p>
                <h2 class="mt-3 text-3xl font-bold tracking-tight sm:text-4xl">Procurement made <span class="wave-underline on-dark font-brand font-normal text-tranquil">simple.</span></h2>
                <p class="mt-4 max-w-md text-white/70">Hotels, hospitals, schools and offices can build a cart and submit it as a quotation request. We price it, invoice you and deliver.</p>

                <ol class="mt-10 space-y-6">
                    @foreach ([
                        ['Build your list', 'Add products and quantities from any product line.'],
                        ['Request a quote', 'Check out as a business; no payment needed upfront.'],
                        ['Receive invoice & delivery', 'Our team confirms pricing and schedules dispatch.'],
                    ] as [$title, $text])
                        <li class="flex gap-4">
                            <span class="grid size-10 shrink-0 place-items-center rounded-full bg-tranquil font-bold text-navy">{{ $loop->iteration }}</span>
                            <span><strong class="block">{{ $title }}</strong><span class="text-sm text-white/70">{{ $text }}</span></span>
                        </li>
                    @endforeach
                </ol>

                @if ($productLines->isNotEmpty())
                    <a href="{{ route('product-lines.show', $productLines->first()) }}" class="mt-10 inline-flex rounded-full bg-white px-7 py-3.5 font-semibold text-navy transition hover:-translate-y-0.5">Start a quote</a>
                @endif
            </div>
            <div class="relative min-h-72">
                <img src="{{ asset('images/delivery-truck.jpg') }}" alt="Softia delivery truck" loading="lazy" class="absolute inset-0 size-full object-cover">
            </div>
        </div>
    </section>

    {{-- Featured --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-24 sm:px-6 lg:px-8">
            <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">Popular</p>
            <h2 class="mt-3 text-3xl font-bold tracking-tight">Customer <span class="wave-underline font-brand font-normal text-tranquil">favourites</span></h2>
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($featuredProducts as $product)
                    <x-product-card :product="$product" :product-line="$product->productLine()" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
