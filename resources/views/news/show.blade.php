@extends('layouts.app')

@section('content')
    <article class="mx-auto max-w-3xl px-4 pt-12 sm:px-6 lg:px-8">
        <nav class="text-sm text-navy/60" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" class="hover:text-navy">Home</a>
            <span class="mx-2">/</span>
            <a href="{{ route('news.index') }}" class="hover:text-navy">News</a>
        </nav>

        <header class="mt-6">
            <time datetime="{{ $post->published_at->toDateString() }}" class="text-sm font-semibold uppercase tracking-widest text-tranquil">{{ $post->published_at->format('j F Y') }}</time>
            <h1 class="mt-3 font-brand text-4xl leading-tight sm:text-5xl">{{ $post->title }}</h1>
            @if ($post->excerpt)
                <p class="mt-4 text-lg text-navy/70">{{ $post->excerpt }}</p>
            @endif
        </header>

        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" class="mt-8 aspect-[16/9] w-full rounded-[2rem] object-cover shadow-soft">
        @endif

        <div class="rich-text mt-10 rounded-[2rem] bg-white p-8 shadow-soft sm:p-12">
            {!! str($post->body ?? '')->sanitizeHtml() !!}
        </div>
    </article>

    @if ($morePosts->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 pt-20 sm:px-6 lg:px-8">
            <h2 class="text-2xl font-bold tracking-tight">More <span class="font-brand font-normal text-tranquil">news</span></h2>
            <div class="mt-8 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($morePosts as $morePost)
                    <x-news-card :post="$morePost" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
