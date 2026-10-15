@extends('layouts.app')

@php
    $inputClass = 'mt-1.5 w-full rounded-2xl border border-tranquil/60 bg-cloud px-4 py-3 font-normal focus:border-navy focus:outline-none';
    $contact = config('shop.contact');
@endphp

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <header class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">Contact</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Let’s <span class="wave-underline font-brand font-normal text-tranquil">talk.</span></h1>
            <p class="mt-4 text-lg text-navy/70">{{ $page?->excerpt ?? 'Questions about an order, a quotation or becoming a distributor? Send us a message and our team will get back to you within one business day.' }}</p>
        </header>

        <div class="mt-10 grid gap-8 lg:grid-cols-[1fr_22rem]">
            {{-- Form --}}
            <div class="rounded-[2rem] bg-white p-6 shadow-soft sm:p-10">
                @if (session('contact_sent'))
                    <div class="flex flex-col items-center py-10 text-center" role="status">
                        <span class="grid size-16 place-items-center rounded-full bg-tranquil/30 text-2xl">✓</span>
                        <h2 class="mt-6 font-brand text-3xl">Message sent</h2>
                        <p class="mt-2 max-w-sm text-navy/70">Thank you for reaching out. Our team will reply to your email within one business day.</p>
                        <a href="{{ route('home') }}#product-lines" class="mt-8 rounded-full bg-navy px-6 py-3 font-semibold text-white">Continue shopping</a>
                    </div>
                @else
                    <form method="POST" action="{{ route('contact.store') }}" class="grid gap-5 sm:grid-cols-2" novalidate>
                        @csrf

                        <label class="block text-sm font-semibold">Full name
                            <input name="name" value="{{ old('name') }}" required autocomplete="name" class="{{ $inputClass }}">
                            @error('name') <span class="mt-1 block font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold">Email
                            <input type="email" name="email" value="{{ old('email') }}" required autocomplete="email" class="{{ $inputClass }}">
                            @error('email') <span class="mt-1 block font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>
                        <label class="block text-sm font-semibold">Phone <span class="font-normal text-navy/50">(optional)</span>
                            <input type="tel" name="phone" value="{{ old('phone') }}" autocomplete="tel" placeholder="07XX XXX XXX" class="{{ $inputClass }}">
                        </label>
                        <label class="block text-sm font-semibold">Company / institution <span class="font-normal text-navy/50">(optional)</span>
                            <input name="company" value="{{ old('company') }}" autocomplete="organization" class="{{ $inputClass }}">
                        </label>

                        <fieldset class="sm:col-span-2">
                            <legend class="text-sm font-semibold">I’m getting in touch about</legend>
                            <div class="mt-2 flex flex-wrap gap-2">
                                @foreach (\App\Models\ContactMessage::TOPICS as $value => $label)
                                    <label class="cursor-pointer rounded-full border border-tranquil/60 px-4 py-2 text-sm font-medium transition has-[:checked]:border-navy has-[:checked]:bg-navy has-[:checked]:text-white">
                                        <input type="radio" name="topic" value="{{ $value }}" class="sr-only" @checked(old('topic', request('topic', 'order')) === $value)>
                                        {{ $label }}
                                    </label>
                                @endforeach
                            </div>
                            @error('topic') <span class="mt-1 block text-sm text-red-700">{{ $message }}</span> @enderror
                        </fieldset>

                        <label class="block text-sm font-semibold sm:col-span-2">Message
                            <textarea name="message" rows="6" required class="{{ $inputClass }}">{{ old('message') }}</textarea>
                            @error('message') <span class="mt-1 block font-normal text-red-700">{{ $message }}</span> @enderror
                        </label>

                        {{-- Honeypot for bots --}}
                        <div class="hidden" aria-hidden="true">
                            <label>Website <input name="website" tabindex="-1" autocomplete="off"></label>
                        </div>

                        <div class="sm:col-span-2">
                            <button type="submit" class="w-full rounded-full bg-navy px-8 py-3.5 font-semibold text-white shadow-lift transition hover:bg-navy/90 sm:w-auto">Send message</button>
                        </div>
                    </form>
                @endif
            </div>

            {{-- Details --}}
            <aside class="space-y-4">
                <div class="relative overflow-hidden rounded-[2rem] bg-navy p-8 pb-16 text-white">
                    <x-wave class="absolute inset-x-0 bottom-0 h-12 text-white/[0.07]" />
                    <h2 class="font-brand text-2xl">Reach us <span class="wave-underline on-dark">directly</span></h2>
                    <ul class="mt-5 space-y-4 text-sm">
                        <li>
                            <span class="block text-xs font-semibold uppercase tracking-widest text-tranquil">Phone</span>
                            <a href="tel:{{ preg_replace('/\s+/', '', $contact['phone']) }}" class="text-base font-semibold">{{ $contact['phone'] }}</a>
                        </li>
                        <li>
                            <span class="block text-xs font-semibold uppercase tracking-widest text-tranquil">Email</span>
                            <a href="mailto:{{ $contact['email'] }}" class="break-all text-base font-semibold">{{ $contact['email'] }}</a>
                        </li>
                        <li>
                            <span class="block text-xs font-semibold uppercase tracking-widest text-tranquil">Showroom</span>
                            {{ $contact['showroom'] }}
                        </li>
                        <li>
                            <span class="block text-xs font-semibold uppercase tracking-widest text-tranquil">Logistics hub</span>
                            {{ $contact['logistics_hub'] }}
                        </li>
                    </ul>
                    @if (! empty($contact['whatsapp']))
                        <a href="https://wa.me/{{ preg_replace('/\D+/', '', $contact['whatsapp']) }}" target="_blank" rel="noopener"
                           class="mt-6 flex items-center justify-center gap-2 rounded-full bg-white px-5 py-3 font-semibold text-navy transition hover:-translate-y-0.5">
                            Chat on WhatsApp
                        </a>
                    @endif
                </div>

                @if ($page?->body)
                    <div class="rich-text rounded-[2rem] bg-white p-8 text-sm shadow-soft">
                        {!! str($page->body)->sanitizeHtml() !!}
                    </div>
                @endif
            </aside>
        </div>
    </section>
@endsection
