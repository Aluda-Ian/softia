@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-md px-4 py-16 sm:px-6 sm:py-24">
        <div class="rounded-3xl bg-white p-8 shadow-soft ring-1 ring-navy/5 sm:p-10">
            <div class="text-center">
                <a href="{{ route('home') }}" class="inline-block">
                    <x-logo class="h-10 w-auto" />
                </a>
                <h1 class="mt-6 text-2xl font-bold tracking-tight text-navy">Welcome back</h1>
                <p class="mt-2 text-sm text-navy/70">Sign in to track orders, manage quotations and view delivery updates.</p>
            </div>

            @if (session('status'))
                <div class="mt-6 rounded-2xl bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.store') }}" class="mt-8 space-y-5">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-semibold text-navy">Email address</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="email"
                           class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                    @error('email')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm font-semibold text-navy">Password</label>
                    </div>
                    <input id="password" type="password" name="password" required autocomplete="current-password"
                           class="mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 text-sm text-navy focus:border-navy focus:bg-white focus:outline-none">
                    @error('password')
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 cursor-pointer text-navy/75">
                        <input type="checkbox" name="remember" class="size-4 rounded border-tranquil accent-navy">
                        <span>Remember me</span>
                    </label>
                </div>

                <button type="submit"
                        class="w-full rounded-full bg-navy py-3.5 text-sm font-bold text-white shadow-soft transition hover:bg-navy/90 hover:shadow-lift">
                    Sign in to Account
                </button>
            </form>

            <div class="mt-8 border-t border-navy/10 pt-6 text-center text-sm text-navy/70">
                Don't have an account yet?
                <a href="{{ route('register') }}" class="font-bold text-navy underline underline-offset-4 hover:text-navy/80">Create an account</a>
            </div>
        </div>
    </section>
@endsection
