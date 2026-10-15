@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-5xl px-4 pt-10 pb-16 sm:px-6 lg:px-8">
        {{-- Breadcrumbs & Back --}}
        <div class="flex items-center justify-between text-xs font-semibold text-navy/60">
            <nav class="flex items-center gap-2">
                <a href="{{ route('dashboard') }}" class="hover:underline">Dashboard</a>
                <span>›</span>
                <a href="{{ route('dashboard.orders') }}" class="hover:underline">My Orders</a>
                <span>›</span>
                <span class="text-navy">{{ $order->reference }}</span>
            </nav>
            <a href="{{ route('dashboard.orders') }}" class="hover:text-navy">← Back to all orders</a>
        </div>

        {{-- Order Header Card --}}
        <div class="mt-4 overflow-hidden rounded-[2rem] bg-white p-6 shadow-soft ring-1 ring-navy/5 sm:p-8">
            <div class="flex flex-wrap items-center justify-between gap-4 border-b border-navy/10 pb-6">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-2xl font-bold tracking-tight text-navy sm:text-3xl">
                            {{ $order->isQuote() ? 'Quotation Request' : 'Order' }} {{ $order->reference }}
                        </h1>
                        <span @class([
                            'rounded-full px-3 py-1 text-xs font-bold capitalize',
                            'bg-emerald-100 text-emerald-800' => $order->status === \App\Enums\OrderStatus::Completed,
                            'bg-blue-100 text-blue-800' => in_array($order->status, [\App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Dispatched]),
                            'bg-amber-100 text-amber-800' => $order->status === \App\Enums\OrderStatus::Pending,
                            'bg-rose-100 text-rose-800' => $order->status === \App\Enums\OrderStatus::Cancelled,
                            'bg-purple-100 text-purple-800' => $order->status === \App\Enums\OrderStatus::Quoted,
                        ])>
                            ● {{ $order->status->label() }}
                        </span>
                    </div>
                    <p class="mt-1 text-xs text-navy/60">
                        Placed on {{ $order->created_at->format('l, F j, Y \a\t g:i A') }}
                    </p>
                </div>

                <div class="flex items-center gap-2">
                    <button type="button" onclick="window.print()"
                            class="inline-flex items-center gap-1.5 rounded-full border border-navy/15 bg-white px-4 py-2 text-xs font-semibold text-navy transition hover:bg-cloud">
                        🖨️ Print Receipt
                    </button>
                    @if (! $order->isQuote())
                        <form method="POST" action="{{ route('dashboard.orders.reorder', $order) }}">
                            @csrf
                            <button type="submit"
                                    class="rounded-full bg-navy px-5 py-2 text-xs font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                                Reorder Items
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            {{-- LIVE TRACKING TIMELINE --}}
            @php
                $steps = $order->trackingSteps();
            @endphp
            <div class="py-8">
                <span class="block text-xs font-bold uppercase tracking-wider text-navy/50">Live Order Progression</span>

                <div class="mt-6">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                        @foreach ($steps as $step)
                            <div class="relative flex flex-col items-center text-center">
                                <div @class([
                                    'grid size-12 place-items-center rounded-full text-base font-bold shadow-soft transition',
                                    'bg-navy text-white ring-4 ring-navy/15' => $step['done'] && ! $step['current'],
                                    'bg-tranquil text-navy ring-4 ring-tranquil/40 animate-pulse' => $step['current'],
                                    'bg-cloud text-navy/35' => ! $step['done'] && ! $step['current'],
                                ])>
                                    @if ($step['done'] && ! $step['current'])
                                        ✓
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <strong @class([
                                    'mt-3 text-xs font-bold leading-tight',
                                    'text-navy' => $step['done'] || $step['current'],
                                    'text-navy/40' => ! $step['done'] && ! $step['current'],
                                ])>
                                    {{ $step['label'] }}
                                </strong>
                                <span class="mt-1 text-[0.7rem] text-navy/60">
                                    {{ $step['description'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Delivery & Payment Metadata Grid --}}
            <div class="grid gap-6 border-t border-navy/10 pt-6 sm:grid-cols-3">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-navy/50">Delivery Address</h3>
                    <p class="mt-2 text-sm font-semibold text-navy">{{ $order->customer_name }}</p>
                    <p class="mt-1 whitespace-pre-line text-xs text-navy/75">{{ $order->delivery_address }}</p>
                    <p class="mt-2 text-xs font-medium text-navy/60">Phone: {{ $order->phone }}</p>
                    @if ($order->notes)
                        <div class="mt-3 rounded-xl bg-cloud p-2.5 text-xs text-navy/70">
                            <strong>Note:</strong> {{ $order->notes }}
                        </div>
                    @endif
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-navy/50">Payment & Account</h3>
                    <p class="mt-2 text-sm font-semibold text-navy">{{ $order->payment_method->getLabel() }}</p>
                    <div class="mt-1.5 flex items-center gap-2">
                        <span class="text-xs text-navy/60">Status:</span>
                        <span @class([
                            'rounded-full px-2.5 py-0.5 text-[0.7rem] font-bold',
                            'bg-emerald-100 text-emerald-800' => $order->payment_status === \App\Enums\PaymentStatus::Paid,
                            'bg-amber-100 text-amber-800' => $order->payment_status === \App\Enums\PaymentStatus::AwaitingVerification,
                            'bg-slate-100 text-slate-800' => $order->payment_status === \App\Enums\PaymentStatus::Unpaid,
                        ])>
                            {{ $order->payment_status->label() }}
                        </span>
                    </div>
                    @if ($order->payment_reference)
                        <p class="mt-2 text-xs text-navy/75">
                            M-Pesa Reference: <strong class="font-mono text-navy">{{ $order->payment_reference }}</strong>
                        </p>
                    @endif
                    @if ($order->company_name)
                        <p class="mt-2 text-xs text-navy/75">Company: {{ $order->company_name }}</p>
                    @endif
                    @if ($order->kra_pin)
                        <p class="mt-1 text-xs text-navy/75">KRA PIN: {{ $order->kra_pin }}</p>
                    @endif
                </div>

                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-navy/50">Dispatch Information</h3>
                    <div class="mt-2 text-xs leading-relaxed text-navy/75">
                        <p>Fulfilled from: <strong>{{ config('shop.contact.logistics_hub') }}</strong></p>
                        <p class="mt-1">For urgent delivery requests, contact logistics dispatch:</p>
                        <a href="tel:{{ preg_replace('/\s+/', '', config('shop.contact.phone')) }}" class="mt-2 inline-block font-bold text-navy hover:underline">
                            📞 {{ config('shop.contact.phone') }}
                        </a>
                    </div>
                </div>
            </div>

            {{-- Ordered Items Table --}}
            <div class="mt-8 border-t border-navy/10 pt-6">
                <h3 class="text-sm font-bold text-navy">Order Summary</h3>

                <div class="mt-4 overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-navy/10 text-xs font-bold uppercase tracking-wider text-navy/50">
                            <tr>
                                <th class="pb-3">Product</th>
                                <th class="pb-3 text-center">SKU</th>
                                <th class="pb-3 text-right">Unit Price</th>
                                <th class="pb-3 text-center">Qty</th>
                                <th class="pb-3 text-right">Line Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-navy/5">
                            @foreach ($order->items as $item)
                                <tr>
                                    <td class="py-3 font-semibold text-navy">
                                        {{ $item->product_name }}
                                    </td>
                                    <td class="py-3 text-center text-xs font-mono text-navy/50">
                                        {{ $item->sku ?: '—' }}
                                    </td>
                                    <td class="py-3 text-right text-navy/80">
                                        {{ $item->unit_price ? \App\Support\Money::format($item->unit_price) : 'Quote only' }}
                                    </td>
                                    <td class="py-3 text-center font-bold text-navy">
                                        {{ $item->quantity }}
                                    </td>
                                    <td class="py-3 text-right font-bold text-navy">
                                        {{ $item->line_total ? \App\Support\Money::format($item->line_total) : 'Pending' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="border-t border-navy/10">
                            <tr>
                                <td colspan="4" class="pt-4 text-right text-xs text-navy/60">Subtotal:</td>
                                <td class="pt-4 text-right font-semibold text-navy">
                                    {{ \App\Support\Money::format($order->subtotal) }}
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4" class="py-1 text-right text-xs text-navy/60">Delivery Fee:</td>
                                <td class="py-1 text-right font-semibold text-navy">
                                    {{ (float) $order->delivery_fee > 0 ? \App\Support\Money::format($order->delivery_fee) : 'Free' }}
                                </td>
                            </tr>
                            <tr class="text-base font-bold text-navy">
                                <td colspan="4" class="pt-2 text-right">Total:</td>
                                <td class="pt-2 text-right text-xl">
                                    {{ \App\Support\Money::format($order->total) }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection
