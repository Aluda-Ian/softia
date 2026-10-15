@props(['product', 'productLine'])

@php($url = route('products.show', [$productLine, $product]))

<article class="group flex flex-col overflow-hidden rounded-3xl bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
    <a href="{{ $url }}" class="relative block aspect-square overflow-hidden bg-cloud">
        @if ($product->imageUrl())
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <span class="grid size-full place-items-center bg-tranquil/15 text-navy/70">
                <x-line-icon :slug="$productLine->slug" class="size-20" />
            </span>
        @endif

        @if ($product->isQuoteOnly())
            <span class="absolute left-3 top-3 rounded-full bg-navy px-3 py-1 text-xs font-semibold text-white">Quote only</span>
        @elseif (! $product->isInStock())
            <span class="absolute left-3 top-3 rounded-full bg-white px-3 py-1 text-xs font-semibold text-navy shadow-soft">Out of stock</span>
        @endif

        <x-wave class="absolute inset-x-0 -bottom-px h-5 text-white" />
    </a>

    <div class="flex flex-1 flex-col p-5">
        <p class="text-xs font-semibold uppercase tracking-widest text-tranquil">{{ $product->category->name }}</p>
        <h3 class="mt-1 font-bold leading-snug">
            <a href="{{ $url }}" class="hover:underline hover:underline-offset-4">{{ $product->name }}</a>
        </h3>
        <p class="mt-1 text-sm text-navy/60">{{ $product->unit }}</p>

        <div class="mt-auto pt-4">
            <p class="text-lg font-bold">{{ \App\Support\Money::format($product->retail_price) }}</p>
            @if ($product->wholesale_price)
                <p class="text-xs text-navy/60">{{ \App\Support\Money::format($product->wholesale_price) }} each for {{ $product->min_wholesale_qty }}+</p>
            @endif

            <form method="POST" action="{{ route('cart.store') }}" class="mt-4 flex gap-2">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <label class="sr-only" for="qty-{{ $product->id }}">Quantity</label>
                <input id="qty-{{ $product->id }}" type="number" name="quantity" value="1" min="1" max="9999"
                       class="w-16 rounded-full border border-tranquil/60 bg-cloud px-3 text-center text-sm font-semibold focus:border-navy focus:outline-none">
                <button type="submit" @disabled(! $product->isInStock() && ! $product->isQuoteOnly())
                        class="flex-1 rounded-full bg-navy px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-navy/90 disabled:cursor-not-allowed disabled:opacity-40">
                    {{ $product->isQuoteOnly() ? 'Add to quote' : 'Add to cart' }}
                </button>
            </form>
        </div>
    </div>
</article>
