@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6 sm:py-16">
        <div class="rounded-3xl bg-white p-8 shadow-soft ring-1 ring-navy/5 sm:p-10">
            <div class="text-center">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo class="h-10 w-auto" />
                </a>
                <h1 class="mt-6 text-2xl font-bold tracking-tight text-navy">Create your account</h1>
                <p class="mt-2 text-sm text-navy/70">Track your deliveries in real-time, save addresses and reorder essentials in one tap.</p>
            </div>

            <form method="POST" action="{{ route('register.store') }}" class="mt-8 space-y-5" x-data="{ type: '{{ old('customer_type', 'b2c') }}' }">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-navy">I am ordering for</label>
                    <div class="mt-2 grid grid-cols-2 gap-3">
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-tranquil/50 p-3 text-sm font-semibold text-navy transition has-[:checked]:border-navy has-[:checked]:bg-tranquil/15">
                            <input type="radio" name="customer_type" value="b2c" x-model="type" class="accent-navy">
                            <span>Personal / Home</span>
                        </label>
                        <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-tranquil/50 p-3 text-sm font-semibold text-navy transition has-[:checked]:border-navy has-[:checked]:bg-tranquil/15">
                            <input type="radio" name="customer_type" value="b2b" x-model="type" class="accent-navy">
                            <span>Business / Institution</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="name" class="block text-sm font-semibold text-navy">Full name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autocomplete="name"
                           class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div x-show="type === 'b2b'" x-cloak class="space-y-4">
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-navy">Company / Organization name</label>
                        <input id="company_name" type="text" name="company_name" value="{{ old('company_name') }}"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        @error('company_name')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="email" class="block text-sm font-semibold text-navy">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autocomplete="email"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-semibold text-navy">Phone number</label>
                        <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" placeholder="07XX XXX XXX"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        @error('phone')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="delivery_address" class="block text-sm font-semibold text-navy">Default Delivery address <span class="font-normal text-navy/50">(optional)</span></label>
                    <textarea id="delivery_address" name="delivery_address" rows="2" placeholder="e.g. Building, Street, City / Area"
                              class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">{{ old('delivery_address') }}</textarea>
                    @error('delivery_address')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="password" class="block text-sm font-semibold text-navy">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        @error('password')
                            <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-semibold text-navy">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                    </div>
                </div>

                <div class="rounded-2xl bg-cloud p-3.5 text-xs text-navy/70">
                    Already placed an order using this email? We'll automatically connect your previous order history right after registration.
                </div>

                <button type="submit"
                        class="w-full rounded-full bg-navy py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                    Create Account & Continue
                </button>
            </form>

            <div class="mt-8 border-t border-navy/10 pt-6 text-center text-sm text-navy/70">
                Already have an account?
                <a href="{{ route('login') }}" class="font-bold text-navy underline underline-offset-4 hover:text-navy/80">Sign in</a>
            </div>
        </div>
    </section>
@endsection
