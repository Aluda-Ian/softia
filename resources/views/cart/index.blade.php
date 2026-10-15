@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl">Your <span class="font-brand font-normal text-tranquil">cart</span></h1>

        @if ($lines->isEmpty())
            <div class="mt-8 rounded-[2rem] bg-white p-12 text-center shadow-soft">
                <p class="text-lg font-semibold">Your cart is empty.</p>
                <a href="{{ route('home') }}#product-lines" class="mt-6 inline-flex rounded-full bg-navy px-6 py-3 font-semibold text-white">Browse products</a>
            </div>
        @else
            <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]">
                <ul class="space-y-4">
                    @foreach ($lines as $line)
                        @php($product = $line->product)
                        <li class="flex gap-4 rounded-3xl bg-white p-4 shadow-soft sm:gap-6">
                            <a href="{{ route('products.show', [$product->productLine(), $product]) }}" class="size-24 shrink-0 overflow-hidden rounded-2xl bg-cloud">
                                @if ($product->imageUrl())
                                    <img src="{{ $product->imageUrl() }}" alt="" class="size-full object-cover">
                                @else
                                    <span class="grid size-full place-items-center bg-tranquil/15"><x-line-icon :slug="$product->productLine()->slug" class="size-10" /></span>
                                @endif
                            </a>

                            <div class="flex min-w-0 flex-1 flex-col gap-3 sm:flex-row sm:items-center">
                                <div class="min-w-0 flex-1">
                                    <p class="font-bold leading-snug">{{ $product->name }}</p>
                                    <p class="text-sm text-navy/60">{{ $product->unit }}</p>
                                    <p class="mt-1 text-sm">
                                        {{ \App\Support\Money::format($line->unitPrice()) }}
                                        @if ($line->isWholesale())
                                            <span class="ml-1 rounded-full bg-tranquil/25 px-2 py-0.5 text-xs font-semibold">Wholesale</span>
                                        @endif
                                    </p>
                                </div>

                                <div class="flex items-center gap-3">
                                    <form method="POST" action="{{ route('cart.update', $product) }}" class="flex items-center gap-2">
                                        @csrf
                                        @method('PATCH')
                                        <label class="sr-only" for="qty-{{ $product->id }}">Quantity</label>
                                        <input id="qty-{{ $product->id }}" type="number" name="quantity" value="{{ $line->quantity }}" min="0" max="9999"
                                               class="w-20 rounded-full border border-tranquil/60 bg-cloud px-3 py-2 text-center text-sm font-semibold focus:border-navy focus:outline-none">
                                        <button type="submit" class="rounded-full px-3 py-2 text-sm font-semibold hover:bg-tranquil/20">Update</button>
                                    </form>
                                    <form method="POST" action="{{ route('cart.destroy', $product) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="grid size-9 place-items-center rounded-full text-navy/60 hover:bg-tranquil/20 hover:text-navy" aria-label="Remove {{ $product->name }}">✕</button>
                                    </form>
                                </div>

                                <p class="w-28 text-right font-bold sm:text-right">{{ \App\Support\Money::format($line->lineTotal()) }}</p>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <aside class="h-fit rounded-3xl bg-white p-6 shadow-soft lg:sticky lg:top-24">
                    <h2 class="font-bold">Summary</h2>
                    <dl class="mt-4 space-y-2 text-sm">
                        <div class="flex justify-between"><dt class="text-navy/60">Subtotal</dt><dd class="font-semibold">{{ \App\Support\Money::format($subtotal) }}</dd></div>
                        <div class="flex justify-between"><dt class="text-navy/60">Delivery</dt><dd class="font-semibold">Confirmed on dispatch</dd></div>
                    </dl>
                    @if ($hasQuoteOnlyItems)
                        <p class="mt-4 rounded-2xl bg-tranquil/15 px-4 py-3 text-sm">Some items are priced on quotation, so this cart will be sent as a quote request.</p>
                    @endif
                    <a href="{{ route('checkout.create') }}" class="mt-6 block rounded-full bg-navy px-6 py-3.5 text-center font-semibold text-white shadow-lift transition hover:bg-navy/90">Proceed to checkout</a>
                    <a href="{{ route('home') }}#product-lines" class="mt-3 block text-center text-sm font-semibold underline underline-offset-4">Continue shopping</a>
                </aside>
            </div>
        @endif
    </section>
@endsection
