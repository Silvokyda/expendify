<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Expendify — Create Account</title>
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
        <!-- LEFT: Register form -->
        <main id="main" class="relative flex items-center justify-center px-4 sm:px-6 lg:px-10 py-10">
            <div class="w-full max-w-md">
                <!-- Logo -->
                <a href="/" class="inline-flex items-center gap-2" aria-label="Expendify home">
                    <img src="{{ asset('images/logos/logo_transparent.png') }}" class="h-10 w-auto" alt="">
                    <span class="text-2xl font-semibold tracking-tight text-[#0f5334] dark:text-white">Expendify</span>
                </a>

                <h1 class="mt-8 text-3xl font-semibold tracking-tight">Create an account</h1>
                <p class="mt-2 text-sm">
                    Already have an account?
                    <a href="{{ route('login') }}" class="font-medium text-[#0f5334] hover:underline">Log in</a>.
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
                <div class="mt-6 rounded-2xl bg-white/70 dark:bg-white/10 backdrop-blur-md ring-1 ring-black/5 dark:ring-white/10 shadow-sm p-5 sm:p-6">
                    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                        @csrf

                        <!-- Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium">Full Name</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                                   class="mt-1 block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm"
                                   placeholder="Jane Doe">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium">Email Address</label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required
                                   class="mt-1 block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm"
                                   placeholder="you@example.com">
                            @error('email')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password" class="block text-sm font-medium">Password</label>
                            <input id="password" name="password" type="password" required
                                   class="mt-1 block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm"
                                   placeholder="••••••••">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600 dark:text-red-300">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Confirm Password -->
                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium">Confirm Password</label>
                            <input id="password_confirmation" name="password_confirmation" type="password" required
                                   class="mt-1 block w-full rounded-lg bg-white/80 dark:bg-zinc-900/60 ring-1 ring-black/10 dark:ring-white/20 focus:ring-2 focus:ring-[#0f5334] focus:outline-none px-3 py-2.5 text-sm"
                                   placeholder="••••••••">
                        </div>

                        <!-- Submit -->
                        <button type="submit"
                                class="w-full inline-flex items-center justify-center rounded-lg bg-[#0f5334] text-white font-semibold px-4 py-2.5 hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40">
                            Create Account
                        </button>

                        <!-- Divider -->
                        <div class="flex items-center gap-3">
                            <div class="h-px flex-1 bg-black/10 dark:bg-white/10"></div>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">or</span>
                            <div class="h-px flex-1 bg-black/10 dark:bg-white/10"></div>
                        </div>

                        <a href="{{ route('login') }}"
                           class="w-full inline-flex items-center justify-center rounded-lg bg-[#eca425] text-black font-semibold px-4 py-2.5 hover:opacity-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#eca425]/40">
                            Log in instead
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

        <!-- RIGHT: Promo panel -->
        <aside class="relative hidden lg:flex items-center justify-center">
            <div class="absolute inset-0 -z-10 bg-gradient-to-tr from-[#094c2c] to-[#0f5334]">
                <div aria-hidden="true" class="absolute -top-24 -left-24 h-72 w-72 rounded-full blur-3xl opacity-25 bg-[#0f5334]"></div>
                <div aria-hidden="true" class="absolute -bottom-24 -right-24 h-72 w-72 rounded-full blur-3xl opacity-20 bg-[#eca425]"></div>
                <div class="absolute inset-0 pointer-events-none opacity-25"
                     style="background-image: radial-gradient(transparent 1px, rgba(255,255,255,.12) 1px); background-size: 32px 32px;"></div>
            </div>

            <div class="mx-auto max-w-xl">
                <div class="rounded-2xl bg-white/10 dark:bg-white/5 backdrop-blur-xl border border-white/20 shadow-xl p-6">
                    <h2 class="text-2xl font-semibold leading-tight">Why join Expendify?</h2>
                    <ul class="mt-4 space-y-2 text-sm text-white/90">
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span> Track expenses effortlessly</li>
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span> Link payments directly to budgets</li>
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span> Create & manage savings goals</li>
                        <li class="flex items-start gap-2"><span class="mt-1 h-1.5 w-1.5 rounded-full bg-white/70"></span> Insights & reminders when spending spikes</li>
                    </ul>
                </div>
            </div>
        </aside>
    </div>
</body>
</html>
