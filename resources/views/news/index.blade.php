@extends('layouts.app')

@section('content')
    <section class="mx-auto max-w-7xl px-4 pt-12 sm:px-6 lg:px-8">
        <header class="max-w-2xl">
            <p class="text-sm font-semibold uppercase tracking-widest text-tranquil">Newsroom</p>
            <h1 class="mt-3 text-4xl font-bold tracking-tight sm:text-5xl">Softia <span class="wave-underline font-brand font-normal text-tranquil">news</span></h1>
            <p class="mt-4 text-lg text-navy/70">Product launches, company updates and hygiene tips.</p>
        </header>

        @if ($posts->isEmpty())
            <div class="mt-10 rounded-[2rem] bg-white p-12 text-center shadow-soft">
                <p class="font-semibold">No news yet. Check back soon.</p>
            </div>
        @else
            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <x-news-card :post="$post" />
                @endforeach
            </div>

            <div class="mt-10">
                {{ $posts->links() }}
            </div>
        @endif
    </section>
@endsection
