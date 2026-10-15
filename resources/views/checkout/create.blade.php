@extends('layouts.app')

@php
    $inputClass = 'mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 focus:border-navy focus:outline-none';
    $customerType = old('customer_type', $user?->customer_type ?? 'b2c');
    $paymentMethod = old('payment_method', ($paymentMethods[0] ?? null)?->value);
@endphp

@section('content')
    <section class="mx-auto max-w-5xl px-4 pt-12 sm:px-6 lg:px-8">
        <h1 class="text-3xl font-bold tracking-tight sm:text-4xl"><span class="font-brand font-normal">Checkout</span></h1>

        @guest
            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl bg-tranquil/15 px-5 py-3.5 text-sm text-navy">
                <p>Have a Softia account? <strong>Sign in to quickly auto-fill details and track this order.</strong></p>
                <a href="{{ route('login') }}" class="rounded-full bg-navy px-4 py-1.5 text-xs font-semibold text-white hover:bg-navy/90">Sign in</a>
            </div>
        @endguest

        <form method="POST" action="{{ route('checkout.store') }}" class="mt-8 grid gap-8 lg:grid-cols-[1fr_20rem]" id="checkout">
            @csrf

            <div class="space-y-6">
                @if ($errors->any())
                    <div class="rounded-2xl bg-red-50 px-5 py-4 text-sm text-red-800" role="alert">
                        Please check the highlighted fields below.
                    </div>
                @endif

                {{-- Customer type --}}
                <fieldset class="rounded-3xl bg-white p-6 shadow-soft">
                    <legend class="sr-only">I am buying as</legend>
                    <p class="font-bold">I am buying as</p>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach (\App\Enums\CustomerType::cases() as $type)
                            <label class="flex cursor-pointer items-start gap-3 rounded-2xl border border-tranquil/50 p-4 transition has-[:checked]:border-navy has-[:checked]:bg-tranquil/10">
                                <input type="radio" name="customer_type" value="{{ $type->value }}" @checked($customerType === $type->value) class="mt-1 accent-navy">
                                <span>
                                    <strong class="block">{{ $type->label() }}</strong>
                                    <span class="text-sm text-navy/60">{{ $type === \App\Enums\CustomerType::Business ? 'Submit a quotation request and pay by invoice.' : 'Pay on delivery or via M-Pesa.' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>

                {{-- Details --}}
                <fieldset class="rounded-3xl bg-white p-6 shadow-soft">
                    <legend class="sr-only">Your details</legend>
                    <p class="font-bold">Your details</p>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block text-sm font-semibold sm:col-span-2">Full name
                            <input name="customer_name" value="{{ old('customer_name', $user?->name) }}" required autocomplete="name" class="{{ $inputClass }}">
                            @error('customer_name') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold" data-business>Company / institution
                            <input name="company_name" value="{{ old('company_name', $user?->company_name) }}" autocomplete="organization" class="{{ $inputClass }}">
                            @error('company_name') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold" data-business>KRA PIN <span class="font-normal text-navy/50">(optional)</span>
                            <input name="kra_pin" value="{{ old('kra_pin', $user?->kra_pin) }}" class="{{ $inputClass }}">
                        </label>
                        <label class="block text-sm font-semibold">Email
                            <input type="email" name="email" value="{{ old('email', $user?->email) }}" required autocomplete="email" class="{{ $inputClass }}">
                            @error('email') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold">Phone
                            <input type="tel" name="phone" value="{{ old('phone', $user?->phone) }}" required autocomplete="tel" placeholder="07XX XXX XXX" class="{{ $inputClass }}">
                            @error('phone') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold sm:col-span-2">Delivery address
                            <textarea name="delivery_address" rows="3" required autocomplete="street-address" class="{{ $inputClass }}">{{ old('delivery_address', $user?->delivery_address) }}</textarea>
                            @error('delivery_address') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold sm:col-span-2">Notes <span class="font-normal text-navy/50">(optional)</span>
                            <textarea name="notes" rows="2" class="{{ $inputClass }}">{{ old('notes') }}</textarea>
                        </label>
                    </div>
                </fieldset>

                {{-- Payment (individuals only) --}}
                <fieldset class="rounded-3xl bg-white p-6 shadow-soft" data-retail>
                    <legend class="sr-only">Payment</legend>
                    <p class="font-bold">Payment</p>
                    @if ($hasQuoteOnlyItems)
                        <p class="mt-3 rounded-2xl bg-tranquil/15 px-4 py-3 text-sm">We'll send you a quotation to confirm pricing before any payment.</p>
                    @else
                        <div class="mt-4 space-y-3">
                            @foreach ($paymentMethods as $method)
                                <label class="flex cursor-pointer items-center gap-3 rounded-2xl border border-tranquil/50 p-4 has-[:checked]:border-navy has-[:checked]:bg-tranquil/10">
                                    <input type="radio" name="payment_method" value="{{ $method->value }}" @checked($paymentMethod === $method->value) class="accent-navy">
                                    <strong>{{ $method->getLabel() }}</strong>
                                </label>
                            @endforeach
                            @if (in_array(\App\Enums\PaymentMethod::Mpesa, $paymentMethods, true))
                            <div data-mpesa class="rounded-2xl bg-cloud p-4 text-sm">
                                @if (config('shop.mpesa.paybill'))
                                    <p>Pay <strong>{{ \App\Support\Money::format($subtotal) }}</strong> to Paybill <strong>{{ config('shop.mpesa.paybill') }}</strong>, account <strong>{{ config('shop.mpesa.account') }}</strong>.</p>
                                @elseif (config('shop.mpesa.till'))
                                    <p>Pay <strong>{{ \App\Support\Money::format($subtotal) }}</strong> to Till number <strong>{{ config('shop.mpesa.till') }}</strong>.</p>
                                @else
                                    <p>Our team will send M-Pesa payment details to your phone after confirming the order.</p>
                                @endif
                                <label class="mt-3 block font-semibold">M-Pesa confirmation code
                                    <input name="payment_reference" value="{{ old('payment_reference') }}" placeholder="e.g. SJK4XXXXXX" class="{{ $inputClass }} uppercase">
                                    @error('payment_reference') <span class="mt-1 block text-red-700">{{ $message }}</span> @enderror
                                </label>
                            </div>
                            @endif
                        </div>
                        @error('payment_method') <span class="mt-2 block text-sm text-red-700">{{ $message }}</span> @enderror
                    @endif
                </fieldset>
            </div>

            <aside class="h-fit rounded-3xl bg-white p-6 shadow-soft lg:sticky lg:top-24">
                <h2 class="font-bold">Order summary</h2>
                <ul class="mt-4 space-y-3 text-sm">
                    @foreach ($lines as $line)
                        <li class="flex justify-between gap-4">
                            <span>{{ $line->product->name }} <span class="text-navy/50">× {{ $line->quantity }}</span></span>
                            <span class="shrink-0 font-semibold">{{ \App\Support\Money::format($line->lineTotal()) }}</span>
                        </li>
                    @endforeach
                </ul>
                <div class="mt-4 flex justify-between border-t border-navy/10 pt-4 font-bold">
                    <span>Subtotal</span>
                    <span>{{ \App\Support\Money::format($subtotal) }}</span>
                </div>
                <button type="submit" class="mt-6 w-full rounded-full bg-navy px-6 py-3.5 font-semibold text-white shadow-lift transition hover:bg-navy/90" data-submit>
                    Place order
                </button>
            </aside>
        </form>
    </section>

    <script>
        (() => {
            const form = document.getElementById('checkout');
            const quoteOnly = @json($hasQuoteOnlyItems);

            const sync = () => {
                const isBusiness = form.querySelector('[name=customer_type]:checked')?.value === 'b2b';
                const isMpesa = form.querySelector('[name=payment_method]:checked')?.value === 'mpesa';

                form.querySelectorAll('[data-business]').forEach((el) => el.hidden = !isBusiness);
                form.querySelectorAll('[data-retail]').forEach((el) => el.hidden = isBusiness);
                form.querySelectorAll('[data-mpesa]').forEach((el) => el.hidden = !isMpesa);
                form.querySelector('[data-submit]').textContent = (isBusiness || quoteOnly) ? 'Submit quote request' : 'Place order';
            };

            form.addEventListener('change', sync);
            sync();
        })();
    </script>
@endsection
