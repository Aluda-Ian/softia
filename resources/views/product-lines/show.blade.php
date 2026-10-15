@extends('layouts.app')

@section('content')
    {{-- Product line hero --}}
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <nav class="text-sm text-navy/60" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-navy">Home</a>
            <span class="mx-2">/</span>
            <span class="font-semibold text-navy">{{ $productLine->name }}</span>
        </nav>

        <div class="relative mt-6 grid items-center gap-8 overflow-hidden rounded-[2.5rem] bg-white p-8 shadow-soft sm:p-12 md:grid-cols-[1fr_auto]">
            <x-wave class="absolute inset-x-0 bottom-0 h-14 text-tranquil/15" />
            <div class="relative">
                <span class="grid size-14 place-items-center rounded-2xl bg-tranquil/20">
                    <x-line-icon :slug="$productLine->slug" class="size-8" />
                </span>
                <h1 class="mt-6 font-brand text-4xl leading-tight sm:text-6xl">{{ $productLine->name }}</h1>
                <p class="mt-3 text-lg font-semibold text-tranquil">{{ $productLine->tagline }}</p>
                <p class="mt-4 max-w-xl leading-relaxed text-navy/70">{{ $productLine->description }}</p>
            </div>
            <x-line-visual :line="$productLine" class="hidden size-72 rounded-[2rem] bg-cloud p-6 md:grid" />
        </div>
    </section>

    {{-- Shop --}}
    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <div class="-mx-4 overflow-x-auto px-4 pb-2">
            <ul class="flex w-max gap-2" aria-label="Sections">
                <li>
                    <a href="{{ route('product-lines.show', $productLine) }}"
                       @class([
                           'block rounded-full px-5 py-2.5 text-sm font-semibold transition',
                           'bg-navy text-white shadow-soft' => ! $activeSection,
                           'bg-white hover:bg-tranquil/20' => $activeSection,
                       ])>All products</a>
                </li>
                @foreach ($sections as $section)
                    <li>
                        <a href="{{ route('product-lines.show', [$productLine, 'section' => $section->slug]) }}"
                           @class([
                               'block rounded-full px-5 py-2.5 text-sm font-semibold transition',
                               'bg-navy text-white shadow-soft' => $activeSection?->is($section),
                               'bg-white hover:bg-tranquil/20' => ! $activeSection?->is($section),
                           ])>{{ $section->name }}</a>
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="mt-6 text-sm text-navy/60">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>

        @if ($products->isEmpty())
            <div class="mt-6 rounded-3xl bg-white p-12 text-center shadow-soft">
                <p class="font-semibold">No products here yet.</p>
                <p class="mt-1 text-sm text-navy/60">Contact us at {{ config('shop.contact.email') }} for availability.</p>
            </div>
        @else
            <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" :product-line="$productLine" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $products->links() }}
            </div>
        @endif
    </section>
@endsection
