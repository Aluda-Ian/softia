@props(['post'])

<article class="group flex flex-col overflow-hidden rounded-[2rem] bg-white shadow-soft transition duration-300 hover:-translate-y-1 hover:shadow-lift">
    <a href="{{ route('news.show', $post) }}" class="relative block aspect-[16/10] overflow-hidden bg-gradient-to-br from-[#D6E5F6] to-white">
        @if ($post->coverUrl())
            <img src="{{ $post->coverUrl() }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
        @else
            <span class="grid size-full place-items-center p-6">
                <x-logo class="h-12 max-w-[70%] opacity-80" />
            </span>
        @endif
    </a>
    <div class="flex flex-1 flex-col p-6">
        <time datetime="{{ $post->published_at->toDateString() }}" class="text-xs font-semibold uppercase tracking-widest text-tranquil">{{ $post->published_at->format('j M Y') }}</time>
        <h3 class="mt-2 text-lg font-bold leading-snug">
            <a href="{{ route('news.show', $post) }}" class="hover:underline hover:underline-offset-4">{{ $post->title }}</a>
        </h3>
        @if ($post->excerpt)
            <p class="mt-2 text-sm leading-relaxed text-navy/70">{{ $post->excerpt }}</p>
        @endif
        <a href="{{ route('news.show', $post) }}" class="mt-auto inline-flex items-center gap-2 pt-5 text-sm font-bold">
            Read more <span class="transition group-hover:translate-x-1" aria-hidden="true">→</span>
        </a>
    </div>
</article>
