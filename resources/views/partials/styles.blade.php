{{--
    Production: a compiled stylesheet at public/css/app.css (built with the
    Tailwind standalone CLI, no Node) is used when present. Until then the
    Tailwind browser build compiles classes on the fly for local work.
--}}
@if (file_exists(public_path('css/app.css')))
    <link rel="stylesheet" href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}">
@else
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4.1.13"></script>
    <style type="text/tailwindcss">
        @include('partials.theme')
    </style>
@endif
