@extends('layouts.app')

@section('content')
    <article class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-navy/60" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-navy">Home</a>
            <span class="mx-2">/</span>
            <span class="font-semibold text-navy">{{ $page->title }}</span>
        </nav>

        <header class="relative mt-6 overflow-hidden rounded-[2.5rem] bg-gradient-to-br from-[#D6E5F6] to-white px-8 pb-16 pt-12 shadow-soft sm:px-12">
            <x-wave class="absolute inset-x-0 bottom-0 h-10 text-white/80" flip />
            @if ($page->footer_group)
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-navy/60">{{ \App\Models\Page::FOOTER_GROUPS[$page->footer_group] ?? '' }}</p>
            @endif
            <h1 class="mt-3 font-brand text-4xl leading-tight sm:text-5xl">{{ $page->title }}</h1>
            @if ($page->excerpt)
                <p class="mt-4 max-w-xl text-lg text-navy/70">{{ $page->excerpt }}</p>
            @endif
        </header>

        <div class="rich-text mt-10 rounded-[2rem] bg-white p-8 shadow-soft sm:p-12">
            {!! str($page->body ?? '')->sanitizeHtml() !!}
        </div>

        <p class="mt-6 text-sm text-navy/50">Last updated {{ $page->updated_at->format('j F Y') }}</p>
    </article>
@endsection
