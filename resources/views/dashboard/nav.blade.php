@php
    $current = request()->route()->getName();
@endphp

<div class="flex flex-wrap items-center gap-2 border-b border-navy/10 pb-4">
    <a href="{{ route('dashboard') }}"
       @class([
           'rounded-full px-5 py-2 text-sm font-semibold transition',
           'bg-navy text-white shadow-soft' => $current === 'dashboard',
           'bg-white text-navy hover:bg-tranquil/20' => $current !== 'dashboard',
       ])>
        Overview
    </a>
    <a href="{{ route('dashboard.orders') }}"
       @class([
           'rounded-full px-5 py-2 text-sm font-semibold transition',
           'bg-navy text-white shadow-soft' => str_starts_with($current, 'dashboard.orders'),
           'bg-white text-navy hover:bg-tranquil/20' => ! str_starts_with($current, 'dashboard.orders'),
       ])>
        Orders & Tracking
    </a>
    <a href="{{ route('dashboard.profile') }}"
       @class([
           'rounded-full px-5 py-2 text-sm font-semibold transition',
           'bg-navy text-white shadow-soft' => $current === 'dashboard.profile',
           'bg-white text-navy hover:bg-tranquil/20' => $current !== 'dashboard.profile',
       ])>
        Delivery & Profile
    </a>
    @if (auth()->user()?->is_admin)
        <a href="/admin" target="_blank"
           class="ml-auto rounded-full bg-tranquil/20 px-4 py-2 text-xs font-bold uppercase tracking-wider text-navy transition hover:bg-tranquil/30">
            Admin Panel ↗
        </a>
    @endif
</div>
