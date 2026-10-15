@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-8 sm:px-6 lg:px-8">
        <nav class="text-sm text-navy/60" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-navy">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('product-lines.show', $productLine) }}" class="hover:text-navy">{{ $productLine->name }}</a>
            <span class="mx-2">/</span>
            <span class="font-semibold text-navy">{{ $product->name }}</span>
        </nav>

        <div class="mt-6 grid gap-10 lg:grid-cols-2">
            <div class="overflow-hidden rounded-[2.5rem] bg-white shadow-soft">
                @if ($product->imageUrl())
                    <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="aspect-square size-full object-cover">
                @else
                    <div class="grid aspect-square place-items-center bg-tranquil/15 text-navy/70">
                        <x-line-icon :slug="$productLine->slug" class="size-40" />
                    </div>
                @endif
            </div>

            <div class="lg:py-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">{{ $product->category->name }}</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight sm:text-4xl">{{ $product->name }}</h1>
                <p class="mt-2 text-navy/60">{{ $product->unit }} · SKU {{ $product->sku }}</p>

                <div class="mt-8 rounded-3xl bg-white p-6 shadow-soft">
                    <p class="text-3xl font-bold">{{ \App\Support\Money::format($product->retail_price) }}</p>
                    @if ($product->wholesale_price)
                        <p class="mt-1 text-sm text-navy/70">
                            Wholesale: <strong>{{ \App\Support\Money::format($product->wholesale_price) }}</strong> each when you order {{ $product->min_wholesale_qty }} or more.
                        </p>
                    @endif
                    @if ($product->isQuoteOnly())
                        <p class="mt-3 rounded-2xl bg-tranquil/15 px-4 py-3 text-sm">This item is priced on quotation. Add it to your cart and submit a quote request.</p>
                    @endif

                    <form method="POST" action="{{ route('cart.store') }}" class="mt-6 flex gap-3">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <label class="sr-only" for="quantity">Quantity</label>
                        <input id="quantity" type="number" name="quantity" value="{{ $product->isQuoteOnly() ? $product->min_wholesale_qty : 1 }}" min="1" max="9999"
                               class="w-24 rounded-full border border-tranquil/60 bg-cloud px-4 text-center font-semibold focus:border-navy focus:outline-none">
                        <button type="submit" @disabled(! $product->isInStock() && ! $product->isQuoteOnly())
                                class="flex-1 rounded-full bg-navy px-6 py-3.5 font-semibold text-white shadow-lift transition hover:bg-navy/90 disabled:cursor-not-allowed disabled:opacity-40">
                            {{ $product->isQuoteOnly() ? 'Add to quote' : 'Add to cart' }}
                        </button>
                    </form>
                    <p class="mt-3 text-sm {{ $product->isInStock() ? 'text-navy/60' : 'font-semibold' }}">
                        {{ $product->isInStock() ? ($product->isLowStock() ? 'Only a few left in stock' : 'In stock') : 'Currently out of stock' }}
                    </p>
                </div>

                @if ($product->description)
                    <div class="mt-8 leading-relaxed text-navy/80">
                        <h2 class="font-bold text-navy">About this product</h2>
                        <p class="mt-2">{{ $product->description }}</p>
                    </div>
                @endif
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight">More in <span class="font-brand font-normal text-tranquil">{{ $product->category->name }}</span></h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $relatedProduct)
                    <x-product-card :product="$relatedProduct" :product-line="$productLine" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
