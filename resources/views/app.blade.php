<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#121214">
        <meta name="description" content="{{ $site['site_description'] }}">
        @if ($site['meta_keywords'])
            <meta name="keywords" content="{{ $site['meta_keywords'] }}">
        @endif
        <meta property="og:site_name" content="{{ $site['site_name'] }}">
        <meta property="og:type" content="website">
        <meta property="og:url" content="{{ url()->current() }}">
        <meta property="og:description" content="{{ $site['site_description'] }}">
        <meta property="og:image" content="{{ $site['og_image'] ?: asset('og-image.png') }}">
        <meta name="twitter:card" content="summary_large_image">

        <link rel="icon" href="{{ $site['favicon'] ?: asset('favicon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

        <style>:root { --accent: {{ $site['accent_color'] }}; }</style>

        <title inertia>{{ $site['site_name'] }}</title>

        {{-- The two text fonts are referenced from CSS, so preload them to avoid a late swap. --}}
        @foreach (['onest/files/onest-latin-wght-normal.woff2', 'geist/files/geist-latin-wght-normal.woff2'] as $font)
            <link rel="preload" as="font" type="font/woff2" href="{{ Vite::asset('node_modules/@fontsource-variable/'.$font) }}" crossorigin>
        @endforeach

        @vite(['resources/js/app.js', "resources/js/pages/{$page['component']}.vue"])
        @inertiaHead

        @if ($site['google_analytics_id'] && app()->isProduction())
            <script async src="https://www.googletagmanager.com/gtag/js?id={{ $site['google_analytics_id'] }}"></script>
            <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}gtag('js',new Date());gtag('config',@json($site['google_analytics_id']));</script>
        @endif
    </head>
    <body>
        @inertia
    </body>
</html>
