<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @include('partials.seo', ['seo' => $seo])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poetsen+One&display=swap" rel="stylesheet">

    {{-- Installable web app --}}
    <link rel="manifest" href="/manifest.webmanifest">
    <meta name="theme-color" content="#1E3A8A">
    <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="Softia">

    @include('partials.styles')
    @include('partials.analytics')
</head>
<body class="bg-cloud pb-[calc(4.5rem+env(safe-area-inset-bottom))] font-sans text-navy antialiased lg:pb-0">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-50 focus:rounded-full focus:bg-navy focus:px-4 focus:py-2 focus:text-white">Skip to content</a>

    <header class="sticky top-0 z-40 border-b border-navy/5 bg-cloud/85 backdrop-blur">
        <div class="mx-auto flex h-18 max-w-7xl items-center justify-between gap-6 px-4 sm:px-6 lg:px-8">
            <a href="{{ route('home') }}" aria-label="Softia home" class="inline-flex items-center">
                <x-logo class="h-9 sm:h-10" />
            </a>

            <nav class="hidden items-center gap-1 lg:flex" aria-label="Product lines">
                @foreach ($navProductLines as $line)
                    <a href="{{ route('product-lines.show', $line) }}"
                       @class([
                           'rounded-full px-4 py-2 text-sm font-semibold transition hover:bg-tranquil/20',
                           'bg-tranquil/25' => request()->route('productLine')?->is($line),
                       ])>
                        {{ $line->menuLabel() }}
                    </a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route('dashboard') }}"
                       class="inline-flex items-center gap-2 rounded-full border border-navy/15 bg-white px-3.5 py-2 text-xs font-semibold text-navy transition hover:border-navy/30 hover:bg-cloud">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <span class="hidden md:inline">{{ Str::limit(auth()->user()->name, 14) }}</span>
                        <span class="md:hidden">Account</span>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="hidden sm:inline-flex items-center gap-1.5 rounded-full border border-navy/15 bg-white px-3.5 py-2 text-xs font-semibold text-navy transition hover:border-navy/30 hover:bg-cloud">
                        <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
                        <span>Sign In</span>
                    </a>
                @endauth

                {{-- Notification opt-in (hidden by script where push isn't supported) --}}
                <button type="button" data-notify-toggle data-state="off" aria-pressed="false" title="Get offers & order updates"
                        class="group relative grid size-11 place-items-center rounded-full transition hover:bg-tranquil/20 data-[state=on]:bg-tranquil/25 data-[state=blocked]:opacity-40">
                    <span class="sr-only">Notifications</span>
                    <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" /></svg>
                    <span class="absolute right-2 top-2 hidden size-2.5 rounded-full bg-tranquil ring-2 ring-cloud group-data-[state=on]:block" aria-hidden="true"></span>
                </button>

                <a href="{{ route('cart.index') }}" class="relative inline-flex items-center gap-2 rounded-full bg-navy px-4 py-2.5 text-sm font-semibold text-white shadow-soft transition hover:bg-navy/90">
                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
                    <span class="hidden sm:inline">Cart</span>
                    <span class="grid min-w-6 place-items-center rounded-full bg-tranquil px-1.5 text-xs font-bold text-navy">{{ $cartCount }}</span>
                </a>

                <details class="relative lg:hidden">
                    <summary class="grid size-11 cursor-pointer list-none place-items-center rounded-full transition hover:bg-tranquil/20" aria-label="Open menu">
                        <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    </summary>
                    <nav class="absolute right-0 mt-2 w-72 rounded-2xl bg-white p-2 shadow-lift" aria-label="Product lines">
                        @foreach ($navProductLines as $line)
                            <a href="{{ route('product-lines.show', $line) }}" class="block rounded-xl px-4 py-3 text-sm font-semibold hover:bg-cloud">{{ $line->menuLabel() }}</a>
                        @endforeach
                        <div class="my-2 border-t border-navy/10 pt-2">
                            @auth
                                <a href="{{ route('dashboard') }}" class="block rounded-xl px-4 py-2.5 text-sm font-bold text-navy hover:bg-cloud">My Dashboard & Orders</a>
                                <a href="{{ route('dashboard.profile') }}" class="block rounded-xl px-4 py-2 text-xs text-navy/70 hover:bg-cloud">Delivery & Profile</a>
                                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                                    @csrf
                                    <button type="submit" class="w-full text-left rounded-xl px-4 py-2 text-xs font-semibold text-red-600 hover:bg-red-50">Sign out</button>
                                </form>
                            @else
                                <a href="{{ route('login') }}" class="block rounded-xl px-4 py-2.5 text-sm font-bold text-navy hover:bg-cloud">Sign in to account</a>
                                <a href="{{ route('register') }}" class="block rounded-xl px-4 py-2 text-xs font-semibold text-navy/70 hover:bg-cloud">Create an account</a>
                            @endauth
                        </div>
                    </nav>
                </details>
            </div>
        </div>
    </header>

    @if (session('status'))
        <div class="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8" role="status">
            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white px-5 py-4 text-sm font-medium shadow-soft">
                <span class="flex items-center gap-3">
                    <span class="grid size-7 place-items-center rounded-full bg-tranquil/30">✓</span>
                    {{ session('status') }}
                </span>
                <a href="{{ route('cart.index') }}" class="font-semibold underline underline-offset-4">View cart</a>
            </div>
        </div>
    @endif

    <main id="main">
        @yield('content')
    </main>

    {{-- Brand wave: the footer rises into the page --}}
    <x-wave class="mt-24 -mb-px h-10 text-navy sm:h-16" flip />
    <footer class="bg-navy text-white">
        <div class="mx-auto grid max-w-7xl gap-10 px-4 py-16 sm:grid-cols-2 sm:px-6 lg:grid-cols-[1.4fr_repeat(4,1fr)] lg:px-8">
            <div>
                <x-logo class="h-10 sm:h-11" inverted />
                <p class="mt-4 max-w-xs text-sm leading-relaxed text-white/70">Hygiene and packaging essentials for homes, businesses and institutions across Kenya.</p>
            </div>

            <nav aria-label="Product lines">
                <h2 class="text-sm font-bold uppercase tracking-widest text-tranquil">Shop</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-white/80">
                    @foreach ($navProductLines as $line)
                        <li><a href="{{ route('product-lines.show', $line) }}" class="transition hover:text-white">{{ $line->menuLabel() }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <nav aria-label="Company">
                <h2 class="text-sm font-bold uppercase tracking-widest text-tranquil">Company</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-white/80">
                    @foreach ($footerCompanyPages as $page)
                        <li><a href="{{ $page->slug === 'contact-us' ? route('contact') : route('pages.show', $page) }}" class="transition hover:text-white">{{ $page->title }}</a></li>
                        @if ($loop->first)
                            <li><a href="{{ route('news.index') }}" class="transition hover:text-white">News</a></li>
                        @endif
                    @endforeach
                    @if ($footerCompanyPages->isEmpty())
                        <li><a href="{{ route('news.index') }}" class="transition hover:text-white">News</a></li>
                    @endif
                </ul>
            </nav>

            <nav aria-label="Policies">
                <h2 class="text-sm font-bold uppercase tracking-widest text-tranquil">Policies</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-white/80">
                    @foreach ($footerPolicyPages as $page)
                        <li><a href="{{ route('pages.show', $page) }}" class="transition hover:text-white">{{ $page->title }}</a></li>
                    @endforeach
                </ul>
            </nav>

            <div>
                <h2 class="text-sm font-bold uppercase tracking-widest text-tranquil">Talk to us</h2>
                <ul class="mt-4 space-y-2.5 text-sm text-white/80">
                    <li><a href="tel:{{ preg_replace('/\s+/', '', config('shop.contact.phone')) }}" class="transition hover:text-white">{{ config('shop.contact.phone') }}</a></li>
                    <li><a href="mailto:{{ config('shop.contact.email') }}" class="break-all transition hover:text-white">{{ config('shop.contact.email') }}</a></li>
                    @if (config('shop.contact.whatsapp'))
                        <li><a href="https://wa.me/{{ preg_replace('/\D+/', '', config('shop.contact.whatsapp')) }}" target="_blank" rel="noopener" class="transition hover:text-white">WhatsApp us</a></li>
                    @endif
                    <li>Showroom: {{ config('shop.contact.showroom') }}</li>
                    <li>Logistics hub: {{ config('shop.contact.logistics_hub') }}</li>
                </ul>
            </div>
        </div>
        <div class="border-t border-white/10">
            <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-6 text-xs text-white/50 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
                <p>&copy; {{ date('Y') }} Softia Consumer Products Limited. Stay confident.</p>
                <p class="flex gap-4">
                    @foreach ($footerPolicyPages->take(2) as $page)
                        <a href="{{ route('pages.show', $page) }}" class="transition hover:text-white">{{ $page->title }}</a>
                    @endforeach
                </p>
            </div>
        </div>
    </footer>

    {{-- "Install the app" prompt: shown by public/js/softia-app.js when the browser supports it --}}
    <div data-install-banner hidden
         class="fixed inset-x-3 bottom-[calc(5.25rem+env(safe-area-inset-bottom))] z-50 mx-auto max-w-md rounded-3xl bg-white p-4 shadow-lift ring-1 ring-navy/5 lg:bottom-6 lg:left-auto lg:right-6 lg:mx-0">
        <div class="flex items-start gap-3">
            <img src="/icons/icon-192.png" alt="" class="size-12 shrink-0 rounded-2xl">
            <div class="min-w-0 flex-1">
                <p class="font-bold">Get the Softia app</p>
                <p data-install-mode="prompt" class="text-sm text-navy/70">Shop faster and reorder in one tap, right from your home screen.</p>
                <p data-install-mode="ios" hidden class="text-sm text-navy/70">Tap <strong>Share</strong> <span aria-hidden="true">⎋</span> then <strong>Add to Home Screen</strong>.</p>
                <div class="mt-3 flex gap-2">
                    <button type="button" data-install-mode="prompt" data-install-accept class="rounded-full bg-navy px-4 py-2 text-sm font-semibold text-white">Install</button>
                    <button type="button" data-install-dismiss class="rounded-full px-4 py-2 text-sm font-semibold text-navy/70 hover:bg-cloud">Not now</button>
                </div>
            </div>
        </div>
    </div>

    @php
        $appConfig = [
            'csrf' => csrf_token(),
            'vapidPublicKey' => \App\Support\Push\VapidKeys::publicKey(),
            'subscribeUrl' => route('push.subscribe'),
            'unsubscribeUrl' => route('push.unsubscribe'),
        ];
    @endphp
    <script>
        window.SoftiaApp = @json($appConfig);
    </script>
    <script src="/js/softia-app.js?v={{ filemtime(public_path('js/softia-app.js')) }}" defer></script>

    {{-- Phone bottom navigation (the top menu stays as well) --}}
    @php
        $bottomNav = [
            ['label' => 'Home', 'url' => route('home'), 'active' => request()->routeIs('home'), 'icon' => 'M2.25 12 11.204 3.045c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25'],
            ['label' => 'Shop', 'url' => route('home').'#product-lines', 'active' => request()->routeIs('product-lines.*', 'products.*'), 'icon' => 'M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.015a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72l1.189-1.19A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72M6.75 18h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .414.336.75.75.75Z'],
            ['label' => auth()->check() ? 'Account' : 'Sign In', 'url' => auth()->check() ? route('dashboard') : route('login'), 'active' => request()->routeIs('dashboard*', 'login', 'register'), 'icon' => 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z'],
            ['label' => 'Contact', 'url' => route('contact'), 'active' => request()->routeIs('contact'), 'icon' => 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z'],
        ];
    @endphp
    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-navy/10 bg-white/95 pb-[env(safe-area-inset-bottom)] shadow-[0_-8px_24px_-12px_rgba(30,58,138,0.25)] backdrop-blur lg:hidden" aria-label="Mobile">
        <ul class="mx-auto grid h-18 max-w-md grid-cols-4">
            @foreach ($bottomNav as $item)
                <li>
                    <a href="{{ $item['url'] }}" @if ($item['active']) aria-current="page" @endif
                       @class([
                           'relative flex h-full flex-col items-center justify-center gap-1 text-[0.7rem] font-semibold transition',
                           'text-navy' => $item['active'],
                           'text-navy/50 hover:text-navy' => ! $item['active'],
                       ])>
                        @if ($item['active'])
                            <span class="wave-underline absolute top-1 h-2.5 w-10 [background-size:100%_100%]" aria-hidden="true"></span>
                        @endif
                        <span class="relative">
                            <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" /></svg>
                            @if (($item['badge'] ?? 0) > 0)
                                <span class="absolute -right-2.5 -top-1.5 grid min-w-5 place-items-center rounded-full bg-navy px-1 text-[0.65rem] font-bold text-white">{{ $item['badge'] }}</span>
                            @endif
                        </span>
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>
</body>
</html>
