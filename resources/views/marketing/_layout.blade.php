@php
    $appName = config('app.name', 'crmoffice');
    $currentPath = '/' . trim(request()->path(), '/');
@endphp
<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($appName . ' — Self-hostable Business CRM') }}</title>

    <meta name="description" content="{{ $description ?? 'Self-hostable Business CRM untuk agency, freelancer, dan SMB. Clients, leads, invoices, projects, tickets dalam satu suite.' }}">
    <link rel="canonical" href="{{ $canonical ?? url($currentPath) }}">

    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonical ?? url($currentPath) }}">
    <meta property="og:title" content="{{ $title ?? $appName }}">
    <meta property="og:description" content="{{ $description ?? '' }}">
    <meta property="og:image" content="{{ asset('marketing/og.svg') }}">
    <meta property="og:image:type" content="image/svg+xml">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? $appName }}">
    <meta name="twitter:description" content="{{ $description ?? '' }}">
    <meta name="twitter:image" content="{{ asset('marketing/og.svg') }}">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <meta name="theme-color" content="#059669">

    @vite(['resources/css/app.css', 'resources/js/marketing.js'])

    @yield('head')
    @stack('head')
</head>
<body class="font-sans bg-white text-stone-900 antialiased min-h-screen flex flex-col">

@include('marketing.partials._nav')

<main class="flex-1">
    @yield('content')
</main>

@include('marketing.partials._footer')

@include('components.cookie-consent')

@stack('scripts')
</body>
</html>
