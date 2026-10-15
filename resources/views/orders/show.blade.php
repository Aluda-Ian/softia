@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-8">
        <div class="rounded-[2rem] bg-white p-8 text-center shadow-soft sm:p-12">
            <span class="mx-auto grid size-16 place-items-center rounded-full bg-tranquil/30 text-2xl">✓</span>
            <h1 class="mt-6 text-3xl font-bold tracking-tight">
                {{ $order->isQuote() ? 'Quote request received' : 'Order placed' }}
            </h1>
            <p class="mt-2 text-navy/70">
                Reference <strong class="text-navy">{{ $order->reference }}</strong>
            </p>
            <p class="mx-auto mt-4 max-w-md text-sm leading-relaxed text-navy/70">
                @if ($order->isQuote())
                    Our sales team will price your request and email a quotation to {{ $order->email }} within one business day.
                @elseif ($order->payment_reference)
                    We'll verify your M-Pesa payment ({{ $order->payment_reference }}) and call {{ $order->phone }} to schedule delivery.
                @else
                    We'll call {{ $order->phone }} to confirm delivery. Payment is due on delivery.
                @endif
            </p>

            <div class="mt-6 flex flex-wrap justify-center gap-3">
                @auth
                    <a href="{{ route('dashboard.orders.show', $order) }}"
                       class="inline-flex items-center gap-2 rounded-full bg-navy px-6 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                        Track Order in Dashboard →
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center gap-2 rounded-full bg-navy px-6 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                        Create Account to Track Order →
                    </a>
                    <a href="{{ route('login') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-navy/20 bg-white px-5 py-3 text-sm font-semibold text-navy transition hover:bg-cloud">
                        Sign In
                    </a>
                @endauth
            </div>
        </div>

        <div class="mt-6 rounded-3xl bg-white p-6 shadow-soft">
            <h2 class="font-bold">Items</h2>
            <ul class="mt-4 divide-y divide-navy/5 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4 py-3">
                        <span>{{ $item->product_name }} <span class="text-navy/50">× {{ $item->quantity }}</span></span>
                        <span class="shrink-0 font-semibold">{{ \App\Support\Money::format($item->line_total) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-2 flex justify-between border-t border-navy/10 pt-4 font-bold">
                <span>{{ $order->isQuote() ? 'Estimated subtotal' : 'Total' }}</span>
                <span>{{ \App\Support\Money::format($order->total) }}</span>
            </div>
        </div>

        <p class="mt-8 text-center text-sm text-navy/60">
            Questions? Call {{ config('shop.contact.phone') }} or email {{ config('shop.contact.email') }}.
        </p>
    </section>
@endsection
