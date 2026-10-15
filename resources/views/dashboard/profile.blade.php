@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-4xl px-4 pt-10 pb-16 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div>
            <span class="inline-block text-xs font-bold uppercase tracking-widest text-tranquil">Account Settings</span>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-navy">Profile & Delivery Details</h1>
            <p class="mt-1 text-sm text-navy/70">Manage your contact information, default delivery address and security.</p>
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

        <form method="POST" action="{{ route('dashboard.profile.update') }}" class="mt-8 space-y-8"
              x-data="{ type: '{{ old('customer_type', $user->customer_type ?: 'b2c') }}' }">
            @csrf
            @method('PATCH')

            {{-- Contact Information --}}
            <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-navy/5 sm:p-8">
                <h2 class="text-lg font-bold text-navy">Personal & Contact Info</h2>
                <p class="mt-1 text-xs text-navy/60">This information will be pre-filled during checkout.</p>

                <div class="mt-6 space-y-5">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-navy/60">Account Type</label>
                        <div class="mt-2 grid grid-cols-2 gap-3 sm:w-80">
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-tranquil/50 p-2.5 text-xs font-bold text-navy transition has-[:checked]:border-navy has-[:checked]:bg-tranquil/15">
                                <input type="radio" name="customer_type" value="b2c" x-model="type" class="accent-navy">
                                <span>Personal / Retail</span>
                            </label>
                            <label class="flex cursor-pointer items-center justify-center gap-2 rounded-2xl border border-tranquil/50 p-2.5 text-xs font-bold text-navy transition has-[:checked]:border-navy has-[:checked]:bg-tranquil/15">
                                <input type="radio" name="customer_type" value="b2b" x-model="type" class="accent-navy">
                                <span>Business / Corporate</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="name" class="block text-sm font-semibold text-navy">Full Name</label>
                            <input id="name" type="text" name="name" value="{{ old('name', $user->name) }}" required
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                            @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-semibold text-navy">Email Address</label>
                            <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                            @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="phone" class="block text-sm font-semibold text-navy">Phone Number</label>
                            <input id="phone" type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required placeholder="07XX XXX XXX"
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                            @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div x-show="type === 'b2b'">
                            <label for="company_name" class="block text-sm font-semibold text-navy">Company / Institution Name</label>
                            <input id="company_name" type="text" name="company_name" value="{{ old('company_name', $user->company_name) }}"
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                            @error('company_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div x-show="type === 'b2b'" class="sm:w-1/2">
                        <label for="kra_pin" class="block text-sm font-semibold text-navy">KRA PIN <span class="font-normal text-navy/50">(optional)</span></label>
                        <input id="kra_pin" type="text" name="kra_pin" value="{{ old('kra_pin', $user->kra_pin) }}"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        @error('kra_pin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Default Delivery Address --}}
            <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-navy/5 sm:p-8">
                <h2 class="text-lg font-bold text-navy">Default Delivery Address</h2>
                <p class="mt-1 text-xs text-navy/60">Where should we deliver your Softia orders by default?</p>

                <div class="mt-4">
                    <textarea name="delivery_address" rows="3" placeholder="Building, Room / Office, Street, City / Town"
                              class="w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">{{ old('delivery_address', $user->delivery_address) }}</textarea>
                    @error('delivery_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Change Password --}}
            <div class="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-navy/5 sm:p-8">
                <h2 class="text-lg font-bold text-navy">Change Password</h2>
                <p class="mt-1 text-xs text-navy/60">Leave blank if you do not wish to change your password.</p>

                <div class="mt-6 space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-semibold text-navy">Current Password</label>
                        <input id="current_password" type="password" name="current_password" autocomplete="current-password"
                               class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none sm:w-80">
                        @error('current_password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label for="password" class="block text-sm font-semibold text-navy">New Password</label>
                            <input id="password" type="password" name="password" autocomplete="new-password"
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                            @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-semibold text-navy">Confirm New Password</label>
                            <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                                   class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('dashboard') }}" class="rounded-full px-5 py-2.5 text-sm font-semibold text-navy/70 hover:bg-cloud">
                    Cancel
                </a>
                <button type="submit"
                        class="rounded-full bg-navy px-7 py-3 text-sm font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                    Save Changes
                </button>
            </div>
        </form>
    </section>
@endsection
