<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expendify — Log in</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="h-[1005px] overflow-hidden font-sans antialiased bg-paper text-[#212529] dark:text-white selection:bg-[#0f5334]/20">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/50 bg-white/90 dark:bg-zinc-900 px-3 py-2 rounded-md shadow">
        Skip to content
    </a>

    <div class="grid lg:grid-cols-2 min-h-screen">
        <!-- LEFT: Login form -->
        <main id="main" class="relative flex items-center justify-center px-4 sm:px-6 lg:px-10 py-10">
            <div class="w-full max-w-md">
                <!-- Logo / Brand -->
                <a href="/" class="inline-flex items-center gap-2" aria-label="Expendify home">
                    <img src="{{ asset('images/logos/logo_transparent.png') }}" class="h-10 w-auto" alt="">
                    <span class="text-2xl font-semibold tracking-tight text-[#0f5334] dark:text-white">Expendify</span>
                </a>

                <h1 class="mt-8 text-3xl font-semibold tracking-tight">Log in</h1>
                <p class="mt-2 text-sm">
                    Don’t have an account?
                    <a href="{{ route('register') }}" class="font-medium text-[#0f5334] hover:underline">Create one</a>.
                </p>

                {{-- Validation Errors --}}
                @if ($errors->any())
                <div class="mt-6 rounded-lg bg-[#ffefef] dark:bg-red-950/40 text-red-700 dark:text-red-200 ring-1 ring-red-300/60 dark:ring-red-800 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <!-- Form Card -->
                <div x-data="{ showPassword: false }"
                    class="mt-6 rounded-2xl bg-white/70 dark:bg-white/10 backdrop-blur-md ring-1 ring-black/5 dark:ring-white/10 shadow-sm p-5 sm:p-6">
                    <form method="POST" action="{{ route('login') }}" class="space-y-4">
                        @csrf

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium">Email Address</label>
                            <input
                                id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                                class="mt-1 block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm"
                                placeholder="you@example.com" />
                        </div>

                        <!-- Password -->
                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-sm font-medium">Password</label>
                                @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-sm text-[#0f5334] hover:underline">
                                    Forgot password?
                                </a>
                                @endif
                            </div>
                            <div class="mt-1 relative">
                                <input
                                    :type="showPassword ? 'text' : 'password'"
                                    id="password" name="password" required
                                    class="block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm pr-10"
                                    placeholder="••••••••" />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 px-3 text-zinc-500 hover:text-zinc-700 dark:hover:text-zinc-300"
                                    aria-label="Toggle password visibility">
                                    <!-- Eye icon (show password) -->
                                    <svg x-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                    </svg>
                                    <!-- Eye slash icon (hide password) -->
                                    <svg x-show="showPassword" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a10.05 10.05 0 012.45-3.982M6.1 6.1A9.99 9.99 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-3.008 4.28M15 12a3 3 0 00-3-3M3 3l18 18" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Remember -->
                        <div class="flex items-center justify-between">
                            <label class="inline-flex items-center gap-2 text-sm">
                                <input id="remember_me" name="remember" type="checkbox"
                                    class="rounded border-zinc-300 text-[#0f5334] focus:ring-[#0f5334]">
                                Remember me
                            </label>
                        </div>

                        <!-- Submit -->
                        <button
                            type="submit"
                            class="w-full inline-flex items-center justify-center rounded-lg bg-[#0f5334] text-white font-semibold px-4 py-2.5 hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40">
                            Log In
                        </button>

                        <!-- Divider -->
                        <div class="flex items-center gap-3">
                            <div class="h-px flex-1 bg-black/10 dark:bg-white/10"></div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">or</span>
                            <div class="h-px flex-1 bg-black/10 dark:bg-white/10"></div>
                        </div>

                        <a href="{{ route('register') }}"
                            class="w-full inline-flex items-center justify-center rounded-lg bg-[#eca425] text-black font-semibold px-4 py-2.5 hover:opacity-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#eca425]/40">
                            Create free account
                        </a>
                    </form>
                </div>

                <!-- Footer links -->
                <div class="mt-6 flex items-center gap-6 text-sm text-zinc-600 dark:text-zinc-300">
                    <a href="#" class="hover:underline">Privacy</a>
                    <a href="#" class="hover:underline">Terms</a>
                    <a href="#" class="hover:underline">Security</a>
                </div>
                <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">&copy; {{ date('Y') }} Expendify</p>
            </div>
        </main>

        <!-- RIGHT: Brand gradient + promo card -->
        <aside class="relative hidden lg:flex items-center justify-center">
            <!-- Brand gradient background -->
            <div class="absolute inset-0 -z-10 bg-gradient-to-tr from-[#094c2c] to-[#0f5334]">
                <div aria-hidden="true" class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-25 bg-[#0f5334]"></div>
                <div aria-hidden="true" class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-20 bg-[#eca425]"></div>
                <!-- subtle network line motif (optional) -->
                <div class="absolute inset-0 pointer-events-none opacity-25"
                    style="background-image: radial-gradient(transparent 1px, rgba(255,255,255,.12) 1px); background-size: 32px 32px;"></div>
            </div>

            <!-- Promo card -->
            <div class="mx-auto max-w-xl">
                <div class="rounded-2xl bg-white/10 dark:bg-white/5 backdrop-blur-xl border border-white/20 shadow-xl p-6">
                    <div class="flex items-start gap-4">
                        <img src="{{ asset('images/illustrations/login-hero.png') }}" alt="" class="w-28 h-28 object-contain rounded-xl bg-white/10 border border-white/15">
                        <div>
                            <h2 class="text-2xl font-semibold leading-tight">Track. Pay. Save.</h2>
                            <p class="mt-1 text-white/80">Learn how Expendify helps you control spending and grow savings—effortlessly.</p>
                        </div>
                    </div>

                    <ul class="mt-5 space-y-2 text-sm text-white/90">
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            Unlock insights with monthly & yearly summaries
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            Budget-linked M-PESA payments (Paybill, Till, Pochi*)
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            Smart reminders when expenses spike
                        </li>
                        <li class="flex items-start gap-2">
                            <span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span>
                            Bank-grade security & data protection
                        </li>
                    </ul>

                    <p class="mt-3 text-[11px] text-white/60">*Pochi support via partner/aggregator where available.</p>
                </div>
            </div>
        </aside>
    </div>
</body>

</html>