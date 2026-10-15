<title>{{ $seo->title }}</title>
<meta name="description" content="{{ $seo->description }}">
{{-- Staging/review sites are never indexed, whatever the SEO settings say. --}}
<meta name="robots" content="{{ app()->environment('production') ? $seo->robots : 'noindex, nofollow' }}">
<link rel="canonical" href="{{ $seo->canonical }}">

<meta property="og:type" content="website">
<meta property="og:site_name" content="Softia">
<meta property="og:title" content="{{ $seo->title }}">
<meta property="og:description" content="{{ $seo->description }}">
<meta property="og:url" content="{{ $seo->canonical }}">
@if ($seo->image)
    <meta property="og:image" content="{{ $seo->image }}">
@endif

<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $seo->title }}">
<meta name="twitter:description" content="{{ $seo->description }}">
@if ($seo->image)
    <meta name="twitter:image" content="{{ $seo->image }}">
@endif
