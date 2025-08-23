<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>
        {{ config('app.name', 'Expendify') }}
        @isset($title) — {{ $title }} @endisset
    </title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SEO -->
    <meta name="description" content="Expendify is your personal finance dashboard to track income, expenses, savings goals, and generate reports.">
    <meta property="og:title" content="Expendify — Personal Finance Dashboard">
    <meta property="og:description" content="Track income & expenses, manage savings, and stay on top of your finances with Expendify.">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:site_name" content="Expendify">
    <script>
        window.csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    </script>

</head>

<body class="antialiased font-sans bg-paper text-[#212529] dark:text-white selection:bg-[#0f5334]/20" x-data="{ open: false }">
    <!-- Skip link -->
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/50 bg-white/90 dark:bg-zinc-900 px-3 py-2 rounded-md shadow">
        Skip to content
    </a>

    <div class="min-h-screen flex items-stretch">
        <!-- Sidebar (now part of the main flex container) -->
        @include('layouts.sidebar')

        <!-- Main content area -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- PAGE CONTENT -->
            <main class="flex-1">
        <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
            @if (trim($__env->yieldContent('content')))
                {{-- Section-style layout: @extends('layouts.app') --}}
                @yield('content')
            @elseif (isset($slot))
                {{-- Component-style layout: <x-app-layout> --}}
                {{ $slot }}
            @endif
        </div>
    </main>

            <!-- FOOTER -->
            <footer class="mt-auto">
                <div class="mx-auto px-4 sm:px-6 lg:px-8 py-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <p class="text-sm text-zinc-700 dark:text-zinc-300">&copy; {{ date('Y') }} Expendify. All rights reserved.</p>
                    <ul class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                        <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Privacy</a></li>
                        <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Terms</a></li>
                        <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Security</a></li>
                    </ul>
                </div>
            </footer>
        </div>
    </div>
</body>

</html>