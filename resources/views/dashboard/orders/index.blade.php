@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-10 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-center">
            <div>
                <span class="inline-block text-xs font-bold uppercase tracking-widest text-tranquil">Tracking & History</span>
                <h1 class="mt-1 text-3xl font-bold tracking-tight text-navy">My Orders & Quotations</h1>
                <p class="mt-1 text-sm text-navy/70">Track live order statuses, view items and reorder essentials.</p>
            </div>
            <a href="{{ route('home') }}#product-lines"
               class="rounded-full bg-navy px-5 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                Place New Order
            </a>
        </div>

        {{-- Subnavigation --}}
        <div class="mt-8">
            @include('dashboard.nav')
        </div>

        @if (session('status'))
            <div class="mt-6 rounded-2xl bg-emerald-50 px-5 py-4 text-sm font-medium text-emerald-800 shadow-soft">
                {{ session('status') }}
            </div>
        @endif

        {{-- Filters & Search --}}
        <div class="mt-6 flex flex-col justify-between gap-4 rounded-3xl bg-white p-4 shadow-soft ring-1 ring-navy/5 sm:flex-row sm:items-center">
            <div class="flex flex-wrap items-center gap-1.5">
                @php
                    $tabs = [
                        'all' => 'All Orders',
                        'active' => 'Active / En Route',
                        'completed' => 'Completed',
                        'quotes' => 'Quotations',
                    ];
                @endphp
                @foreach ($tabs as $key => $label)
                    <a href="{{ route('dashboard.orders', array_filter(['tab' => $key !== 'all' ? $key : null, 'q' => $search])) }}"
                       @class([
                           'rounded-full px-4 py-2 text-xs font-bold transition',
                           'bg-navy text-white shadow-soft' => $currentTab === $key,
                           'bg-cloud text-navy/75 hover:bg-tranquil/20 hover:text-navy' => $currentTab !== $key,
                       ])>
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('dashboard.orders') }}" class="relative sm:w-72">
                @if ($currentTab !== 'all')
                    <input type="hidden" name="tab" value="{{ $currentTab }}">
                @endif
                <input type="text" name="q" value="{{ $search }}" placeholder="Search reference or product..."
                       class="w-full rounded-full border border-tranquil/60 bg-cloud py-2 pl-4 pr-10 text-xs text-navy focus:border-navy focus:bg-white focus:outline-none">
                <button type="submit" class="absolute right-3 top-1/2 -translate-y-1/2 text-navy/50 hover:text-navy">
                    🔍
                </button>
            </form>
        </div>

        {{-- Orders Listing --}}
        <div class="mt-6 space-y-4">
            @if ($orders->isEmpty())
                <div class="rounded-3xl bg-white p-12 text-center shadow-soft ring-1 ring-navy/5">
                    <div class="mx-auto grid size-16 place-items-center rounded-full bg-tranquil/20 text-navy text-2xl">
                        📦
                    </div>
                    <h3 class="mt-4 font-bold text-navy">No orders found</h3>
                    <p class="mx-auto mt-1 max-w-sm text-xs text-navy/60">
                        @if ($search !== '')
                            No orders match your search term "{{ $search }}".
                        @else
                            No orders match this filter.
                        @endif
                    </p>
                    <a href="{{ route('dashboard.orders') }}" class="mt-4 inline-block text-xs font-bold text-navy underline">
                        Clear filters
                    </a>
                </div>
            @else
                @foreach ($orders as $order)
                    @php
                        $steps = $order->trackingSteps();
                        $currentStep = collect($steps)->firstWhere('current', true) ?? collect($steps)->last();
                    @endphp
                    <div class="overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-navy/5 transition hover:shadow-lift">
                        <div class="flex flex-wrap items-center justify-between gap-4 border-b border-navy/5 bg-cloud/50 px-6 py-4">
                            <div class="flex flex-wrap items-center gap-3">
                                <span class="text-sm font-bold text-navy">{{ $order->reference }}</span>
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
                                @if ($order->isQuote())
                                    <span class="rounded-full bg-navy/10 px-2.5 py-0.5 text-[0.65rem] font-bold uppercase text-navy">
                                        Quotation Request
                                    </span>
                                @endif
                            </div>

                            <div class="text-xs text-navy/60">
                                Placed on <strong>{{ $order->created_at->format('M j, Y') }}</strong>
                            </div>
                        </div>

                        <div class="p-6">
                            {{-- Visual Mini-Progress Tracker Bar --}}
                            @if ($order->status !== \App\Enums\OrderStatus::Cancelled)
                                <div class="mb-6 rounded-2xl bg-cloud p-4">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-bold text-navy">
                                            Current Step: <span class="text-navy/70">{{ $currentStep['label'] ?? $order->status->label() }}</span>
                                        </span>
                                        <span class="text-navy/50">{{ $currentStep['description'] ?? '' }}</span>
                                    </div>
                                    <div class="mt-2.5 flex items-center gap-1.5">
                                        @foreach ($steps as $step)
                                            <div @class([
                                                'h-2 flex-1 rounded-full transition-all duration-300',
                                                'bg-tranquil' => $step['done'] && ! $step['current'],
                                                'bg-navy animate-pulse' => $step['current'],
                                                'bg-navy/10' => ! $step['done'] && ! $step['current'],
                                            ]) title="{{ $step['label'] }}"></div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Items summary --}}
                            <div class="grid gap-4 sm:grid-cols-[1fr_auto]">
                                <div>
                                    <h4 class="text-xs font-bold uppercase tracking-wider text-navy/50">Items ordered</h4>
                                    <ul class="mt-2 space-y-1.5 text-sm text-navy">
                                        @foreach ($order->items as $item)
                                            <li class="flex items-center gap-2">
                                                <span class="grid size-5 place-items-center rounded-md bg-tranquil/20 text-xs font-bold text-navy">
                                                    {{ $item->quantity }}
                                                </span>
                                                <span class="font-medium">{{ $item->product_name }}</span>
                                                @if ($item->unit_price)
                                                    <span class="text-xs text-navy/50">({{ \App\Support\Money::format($item->unit_price) }} each)</span>
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>

                                <div class="flex flex-col justify-between border-t border-navy/5 pt-4 text-left sm:border-l sm:border-t-0 sm:pl-6 sm:text-right">
                                    <div>
                                        <span class="text-xs text-navy/60">Total Amount</span>
                                        <p class="text-xl font-bold text-navy">{{ \App\Support\Money::format($order->total) }}</p>
                                        <span class="text-[0.7rem] text-navy/50">Payment: {{ $order->payment_method->getLabel() }}</span>
                                    </div>

                                    <div class="mt-4 flex items-center gap-2 sm:justify-end">
                                        <a href="{{ route('dashboard.orders.show', $order) }}"
                                           class="rounded-full bg-navy px-4 py-2 text-xs font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                                            Track Order →
                                        </a>
                                        @if (! $order->isQuote())
                                            <form method="POST" action="{{ route('dashboard.orders.reorder', $order) }}">
                                                @csrf
                                                <button type="submit"
                                                        class="rounded-full border border-navy/15 bg-white px-3.5 py-2 text-xs font-semibold text-navy transition hover:bg-cloud">
                                                    Reorder
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                {{-- Pagination --}}
                <div class="mt-6">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </section>
@endsection
