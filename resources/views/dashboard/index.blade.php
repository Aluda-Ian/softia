@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        {{-- Welcome Header --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <span class="inline-block text-xs font-bold uppercase tracking-widest text-tranquil">Customer Portal</span>
                <h1 class="mt-1 text-3xl font-bold tracking-tight sm:text-4xl text-navy">
                    Hello, <span class="wave-underline">{{ $user->name }}</span>
                </h1>
                <p class="mt-1 text-sm text-navy/70">
                    {{ $user->email }} · {{ $user->customer_type === 'b2b' ? ($user->company_name ?: 'Business Account') : 'Personal Account' }}
                </p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('home') }}#product-lines"
                   class="rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                    Browse Products
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="rounded-full border border-navy/15 bg-white px-4 py-2 text-sm font-semibold text-navy/75 transition hover:bg-cloud hover:text-navy">
                        Sign Out
                    </button>
                </form>
            </div>
        </div>

        {{-- Subnavigation --}}
        <div class="mt-8">
            @include('dashboard.nav')
        </div>

        {{-- Overview Stats --}}
        <div class="mt-8 grid grid-cols-2 gap-4 sm:grid-cols-4">
            <div class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-navy/5">
                <span class="text-xs font-bold uppercase tracking-wider text-navy/50">Total Orders</span>
                <p class="mt-2 text-3xl font-bold text-navy">{{ $totalOrders }}</p>
                <span class="mt-1 block text-xs text-navy/60">Lifetime placed</span>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-navy/5">
                <span class="text-xs font-bold uppercase tracking-wider text-navy/50">Active Deliveries</span>
                <p class="mt-2 text-3xl font-bold text-tranquil">{{ $activeOrders }}</p>
                <span class="mt-1 block text-xs text-navy/60">In progress / en route</span>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-navy/5">
                <span class="text-xs font-bold uppercase tracking-wider text-navy/50">Fulfilled</span>
                <p class="mt-2 text-3xl font-bold text-emerald-600">{{ $completedOrders }}</p>
                <span class="mt-1 block text-xs text-navy/60">Successfully delivered</span>
            </div>

            <div class="rounded-3xl bg-white p-5 shadow-soft ring-1 ring-navy/5">
                <span class="text-xs font-bold uppercase tracking-wider text-navy/50">Quote Requests</span>
                <p class="mt-2 text-3xl font-bold text-indigo-900">{{ $quotesCount }}</p>
                <span class="mt-1 block text-xs text-navy/60">B2B quotations</span>
            </div>
        </div>

        {{-- Active Live Order Tracker Spotlight --}}
        @if ($activeTrackingOrder)
            @php
                $steps = $activeTrackingOrder->trackingSteps();
            @endphp
            <div class="mt-8 overflow-hidden rounded-[2rem] bg-gradient-to-br from-navy via-[#23429B] to-navy p-6 text-white shadow-lift sm:p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 border-b border-white/10 pb-6">
                    <div>
                        <span class="inline-flex items-center gap-2 rounded-full bg-tranquil/25 px-3 py-1 text-xs font-bold uppercase tracking-wider text-tranquil">
                            <span class="size-2 animate-ping rounded-full bg-tranquil"></span>
                            Live Order Tracking
                        </span>
                        <h2 class="mt-2 text-xl font-bold sm:text-2xl">Order {{ $activeTrackingOrder->reference }}</h2>
                        <p class="mt-0.5 text-xs text-white/70">
                            Placed on {{ $activeTrackingOrder->created_at->format('M j, Y \a\t g:i A') }} · {{ $activeTrackingOrder->items->count() }} item(s)
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        <span class="rounded-full bg-white/10 px-4 py-2 text-sm font-semibold capitalize backdrop-blur">
                            Status: <strong class="text-tranquil">{{ $activeTrackingOrder->status->label() }}</strong>
                        </span>
                        <a href="{{ route('dashboard.orders.show', $activeTrackingOrder) }}"
                           class="rounded-full bg-white px-5 py-2 text-sm font-bold text-navy shadow-soft transition hover:bg-cloud">
                            Full Details →
                        </a>
                    </div>
                </div>

                {{-- Interactive Multi-Stage Stepper --}}
                <div class="mt-8">
                    <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                        @foreach ($steps as $step)
                            <div class="relative flex flex-col items-center text-center">
                                <div @class([
                                    'grid size-10 place-items-center rounded-full text-sm font-bold shadow-soft transition',
                                    'bg-tranquil text-navy ring-4 ring-white/20' => $step['done'] && ! $step['current'],
                                    'bg-white text-navy ring-4 ring-tranquil animate-pulse' => $step['current'],
                                    'bg-white/10 text-white/50' => ! $step['done'] && ! $step['current'],
                                ])>
                                    @if ($step['done'] && ! $step['current'])
                                        ✓
                                    @else
                                        {{ $loop->iteration }}
                                    @endif
                                </div>
                                <strong class="mt-3 text-xs font-bold leading-tight {{ $step['done'] || $step['current'] ? 'text-white' : 'text-white/50' }}">
                                    {{ $step['label'] }}
                                </strong>
                                <span class="mt-0.5 hidden text-[0.65rem] text-white/70 sm:block">
                                    {{ $step['description'] }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Recent Orders & Account Quick Links --}}
        <div class="mt-8 grid gap-8 lg:grid-cols-[1fr_22rem]">
            {{-- Recent Orders Table / Cards --}}
            <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-navy/5 sm:p-8">
                <div class="flex items-center justify-between border-b border-navy/10 pb-4">
                    <div>
                        <h2 class="text-lg font-bold text-navy">Recent Orders & Requests</h2>
                        <p class="text-xs text-navy/60">Your latest purchases and quotations</p>
                    </div>
                    <a href="{{ route('dashboard.orders') }}" class="text-xs font-bold text-navy hover:underline">
                        View all ({{ $totalOrders }}) →
                    </a>
                </div>

                @if ($recentOrders->isEmpty())
                    <div class="py-12 text-center">
                        <div class="mx-auto grid size-16 place-items-center rounded-full bg-tranquil/20 text-navy text-2xl">
                            🛒
                        </div>
                        <h3 class="mt-4 font-bold text-navy">No orders placed yet</h3>
                        <p class="mx-auto mt-1 max-w-sm text-xs text-navy/60">
                            Explore our range of premium hygiene, tissue and packaging essentials.
                        </p>
                        <a href="{{ route('home') }}#product-lines" class="mt-5 inline-block rounded-full bg-navy px-5 py-2.5 text-xs font-bold text-white shadow-soft hover:bg-navy/90">
                            Start Shopping
                        </a>
                    </div>
                @else
                    <div class="mt-4 divide-y divide-navy/5">
                        @foreach ($recentOrders as $order)
                            <div class="flex flex-col gap-4 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('dashboard.orders.show', $order) }}" class="font-bold text-navy hover:underline">
                                            {{ $order->reference }}
                                        </a>
                                        <span @class([
                                            'rounded-full px-2.5 py-0.5 text-[0.7rem] font-bold capitalize',
                                            'bg-emerald-100 text-emerald-800' => $order->status === \App\Enums\OrderStatus::Completed,
                                            'bg-blue-100 text-blue-800' => in_array($order->status, [\App\Enums\OrderStatus::Processing, \App\Enums\OrderStatus::Dispatched]),
                                            'bg-amber-100 text-amber-800' => $order->status === \App\Enums\OrderStatus::Pending,
                                            'bg-rose-100 text-rose-800' => $order->status === \App\Enums\OrderStatus::Cancelled,
                                            'bg-purple-100 text-purple-800' => $order->status === \App\Enums\OrderStatus::Quoted,
                                        ])>
                                            {{ $order->status->label() }}
                                        </span>
                                        @if ($order->isQuote())
                                            <span class="rounded-full bg-navy/10 px-2 py-0.5 text-[0.65rem] font-bold uppercase text-navy">
                                                Quote
                                            </span>
                                        @endif
                                    </div>
                                    <p class="mt-1 truncate text-xs text-navy/60">
                                        {{ $order->items->pluck('product_name')->join(', ') ?: 'Standard order' }}
                                    </p>
                                    <span class="mt-0.5 block text-[0.7rem] text-navy/40">
                                        {{ $order->created_at->format('M j, Y \a\t g:i A') }}
                                    </span>
                                </div>

                                <div class="flex items-center justify-between gap-4 sm:justify-end">
                                    <div class="text-right">
                                        <span class="block text-sm font-bold text-navy">
                                            {{ \App\Support\Money::format($order->total) }}
                                        </span>
                                        <span class="block text-[0.7rem] text-navy/50">
                                            {{ $order->payment_method->getLabel() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ route('dashboard.orders.show', $order) }}"
                                           class="rounded-full border border-navy/15 bg-white px-3.5 py-1.5 text-xs font-semibold text-navy transition hover:bg-cloud">
                                            Track
                                        </a>
                                        @if (! $order->isQuote())
                                            <form method="POST" action="{{ route('dashboard.orders.reorder', $order) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-full bg-navy px-3.5 py-1.5 text-xs font-semibold text-white transition hover:bg-navy/90">
                                                    Reorder
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Sidebar: Saved Delivery & Profile Snapshot --}}
            <div class="space-y-6">
                <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-navy/5">
                    <div class="flex items-center justify-between border-b border-navy/10 pb-3">
                        <h3 class="text-sm font-bold text-navy">Default Delivery Address</h3>
                        <a href="{{ route('dashboard.profile') }}" class="text-xs font-semibold text-navy hover:underline">Edit</a>
                    </div>
                    <div class="mt-4 text-xs leading-relaxed text-navy/80">
                        @if ($user->delivery_address)
                            <p class="whitespace-pre-line font-medium">{{ $user->delivery_address }}</p>
                        @else
                            <p class="text-navy/50 italic">No delivery address saved yet.</p>
                            <a href="{{ route('dashboard.profile') }}" class="mt-2 inline-block font-bold text-navy underline">Add address</a>
                        @endif
                    </div>
                    <div class="mt-4 border-t border-navy/5 pt-3 text-xs text-navy/70">
                        <p><strong class="text-navy">Phone:</strong> {{ $user->phone ?: 'Not provided' }}</p>
                        @if ($user->company_name)
                            <p class="mt-1"><strong class="text-navy">Company:</strong> {{ $user->company_name }}</p>
                        @endif
                        @if ($user->kra_pin)
                            <p class="mt-1"><strong class="text-navy">KRA PIN:</strong> {{ $user->kra_pin }}</p>
                        @endif
                    </div>
                </div>

                {{-- Help Card --}}
                <div class="rounded-3xl bg-tranquil/20 p-6 text-navy">
                    <h3 class="font-bold">Need Help With An Order?</h3>
                    <p class="mt-2 text-xs leading-relaxed opacity-80">
                        Our customer support and logistics dispatch team is available from 8:00 AM to 5:00 PM.
                    </p>
                    <div class="mt-4 space-y-2 text-xs font-semibold">
                        <a href="tel:{{ preg_replace('/\s+/', '', config('shop.contact.phone')) }}" class="flex items-center gap-2 hover:underline">
                            📞 {{ config('shop.contact.phone') }}
                        </a>
                        <a href="mailto:{{ config('shop.contact.email') }}" class="flex items-center gap-2 hover:underline">
                            ✉️ {{ config('shop.contact.email') }}
                        </a>
                        @if (config('shop.contact.whatsapp'))
                            <a href="https://wa.me/{{ preg_replace('/\D+/', '', config('shop.contact.whatsapp')) }}" target="_blank" class="flex items-center gap-2 hover:underline">
                                💬 WhatsApp Chat
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
