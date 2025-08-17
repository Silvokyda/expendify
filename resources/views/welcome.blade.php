<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Expendify — Track expenses. Grow savings.</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- SEO / Social -->
    <meta name="description" content="Expendify helps you track daily spending, set savings goals, and get secure insights. Built with Laravel and Tailwind.">
    <meta property="og:title" content="Expendify — Track expenses. Grow savings.">
    <meta property="og:description" content="Track expenses, grow savings, and stay secure with Expendify.">
    <meta property="og:type" content="website">
</head>

<body class="antialiased font-sans bg-paper text-[#212529] dark:text-white selection:bg-[#0f5334]/20">
    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/50 bg-white/90 dark:bg-zinc-900 px-3 py-2 rounded-md shadow">
        Skip to content
    </a>

    <div class="min-h-screen flex flex-col">

        <!-- Header (glassy) -->
        <header class="sticky top-0 z-40 glass-strong glass-shine">
            <nav class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between" aria-label="Primary">
                <a href="/" class="inline-flex items-center min-w-0" aria-label="Expendify home">
                    <img src="{{ asset('images/logos/logo_transparent.png') }}" alt="" class="h-10 sm:h-12 w-auto" />
                    <span class="text-xl sm:text-2xl font-semibold tracking-tight text-[#0f5334] dark:text-white truncate">
                        Expendify
                    </span>
                </a>


                <div class="flex items-center gap-2">
                    @auth
                    <a href="{{ route('dashboard') }}" class="hidden sm:inline-flex items-center text-sm font-medium px-3 py-2 rounded-md btn-glass">
                        Dashboard
                    </a>
                    @endauth
                    <a href="{{ route('login') }}" class="inline-flex items-center text-sm font-medium px-3 py-2 rounded-md btn-glass">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="inline-flex items-center text-sm font-semibold px-4 py-2 rounded-md bg-[#0f5334] text-white hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40">
                        Sign Up
                    </a>
                </div>
            </nav>
        </header>

        <!-- Main -->
        <main id="main" class="flex-1">

            <!-- Hero -->
            <section class="relative overflow-hidden">
                <!-- brand gradient background -->
                <div class="absolute inset-0 -z-10 bg-gradient-to-tr from-[#094c2c] to-[#0f5334]">
                    <div aria-hidden="true" class="absolute -top-24 -left-24 h-64 sm:h-72 w-64 sm:w-72 rounded-full blur-3xl opacity-25 bg-[#0f5334]"></div>
                    <div aria-hidden="true" class="absolute -bottom-24 -right-24 h-64 sm:h-72 w-64 sm:w-72 rounded-full blur-3xl opacity-20 bg-[#eca425]"></div>
                </div>

                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-24 grid lg:grid-cols-2 items-center gap-8 md:gap-10 text-white">
                    <!-- Copy -->
                    <div>
                        <p class="text-[11px] sm:text-xs uppercase tracking-widest text-white/70">Track • Pay • Save</p>
                        <h1 class="mt-1 text-3xl sm:text-4xl md:text-6xl font-semibold leading-tight tracking-tight">
                            Take control of spending. Pay straight from your budget.
                        </h1>
                        <p class="mt-3 sm:mt-4 text-sm sm:text-base md:text-lg text-white/80 max-w-xl">
                            Expendify lets you record expenses, set savings goals, and <span class="font-semibold text-white">pay merchants via M-PESA</span>—so every payment is auto-tracked.
                        </p>

                        <!-- CTAs -->
                        <div class="mt-6 sm:mt-8 flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
                            <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-[#eca425] text-black font-semibold hover:opacity-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#eca425]/40">
                                Get started free
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-5 py-3 rounded-lg bg-white/10 ring-1 ring-white/20 hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/50">
                                I already have an account
                            </a>
                            <p class="sm:ml-2 text-xs text-white/70 sm:self-center">No credit card required</p>
                        </div>

                        <!-- Trust strip -->
                        <ul class="mt-6 sm:mt-8 grid grid-cols-1 sm:grid-cols-4 gap-2 sm:gap-4 text-sm">
                            <li class="flex items-center gap-2 bg-white/10 ring-1 ring-white/15 backdrop-blur-sm px-3 py-2 rounded-lg">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-white/85">Bank-grade encryption</span>
                            </li>
                            <li class="flex items-center gap-2 bg-white/10 ring-1 ring-white/15 backdrop-blur-sm px-3 py-2 rounded-lg">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-white/85">GDPR & Data Protection compliant</span>
                            </li>
                            <li class="flex items-center gap-2 bg-white/10 ring-1 ring-white/15 backdrop-blur-sm px-3 py-2 rounded-lg">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-white/85">Built for M-PESA & Paybill</span>
                            </li>
                            <li class="flex items-center gap-2 bg-white/10 ring-1 ring-white/15 backdrop-blur-sm px-3 py-2 rounded-lg">
                                <svg class="w-4 h-4 shrink-0" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M5 13l4 4L19 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span class="text-white/85">Backed by trusted banks</span>
                            </li>
                        </ul>

                        <!-- Payment channels note -->
                        <div class="mt-4 flex flex-wrap items-center gap-2 text-xs text-white/70">
                            <span class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/15 px-2.5 py-1 rounded-md">
                                <span class="i">✅</span> Paybill (Business No. + Account)
                            </span>
                            <span class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/15 px-2.5 py-1 rounded-md">
                                <span class="i">✅</span> Buy Goods & Services (Till)
                            </span>
                            <span class="inline-flex items-center gap-2 bg-white/10 ring-1 ring-white/15 px-2.5 py-1 rounded-md">
                                <span class="i">✅</span> Pochi la Biashara*
                            </span>
                        </div>
                        <p class="mt-1 text-[10px] text-white/50">*Pochi support via partner/aggregator where available.</p>
                    </div>

                    <!-- Phone mock / chart + pay demo -->
                    <div class="relative mx-auto w-full max-w-xs xs:max-w-sm sm:max-w-sm">
                        <div class="rounded-3xl px-4 sm:px-5 pt-5 sm:pt-6 pb-6 sm:pb-8 bg-white/10 dark:bg-white/5 backdrop-blur-xl border border-white/20 shadow-lg">
                            <div class="rounded-2xl p-3 sm:p-4 bg-white/10 dark:bg-white/5 backdrop-blur-lg border border-white/10">
                                <div class="flex items-center justify-between">
                                    <span class="text-xs sm:text-sm text-white/80">Groceries (Budget)</span>
                                    <span class="text-[10px] sm:text-xs text-white/60">Ksh. 12,000 / mo</span>
                                </div>
                                <p class="mt-1 text-xl sm:text-2xl font-semibold text-white">Spent: Ksh. 2,450.00</p>

                                <!-- simple bars -->
                                <div class="mt-3 sm:mt-4 grid grid-cols-6 gap-1.5 sm:gap-2 items-end h-24 sm:h-28">
                                    <div class="h-10 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                    <div class="h-14 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                    <div class="h-16 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                    <div class="h-20 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                    <div class="h-24 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                    <div class="h-28 rounded bg-gradient-to-t from-white/10 to-white/70"></div>
                                </div>

                                <!-- pay action mock -->
                                <div class="mt-4 p-3 rounded-xl bg-black/20 border border-white/10">
                                    <div class="flex items-center justify-between">
                                        <div class="text-white/80 text-xs sm:text-sm">
                                            Pay from Groceries
                                            <div class="text-white/60 text-[11px]">Till: 123456</div>
                                        </div>
                                        <button class="px-3 py-1.5 rounded-lg bg-[#eca425] text-black text-xs font-semibold hover:opacity-95">
                                            Pay Ksh. 500
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p class="mt-3 text-[11px] sm:text-xs text-white/70">Sample data for illustration.</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Feature highlights -->
            <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-14 sm:py-16 md:py-24">
                <header class="max-w-2xl">
                    <h2 class="text-2xl md:text-4xl font-semibold tracking-tight text-[#0f5334] dark:text-white">
                        Everything you need to master money
                    </h2>
                    <p class="mt-3 text-zinc-700 dark:text-zinc-300">
                        Track spending, pay merchants, and grow savings—without spreadsheets.
                    </p>
                </header>

                <div class="mt-8 md:mt-10 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
                    <!-- Expense tracking -->
                    <article class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0f5334]/10 text-[#0f5334] ring-1 ring-[#0f5334]/20">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <h3 class="text-base sm:text-lg font-semibold">Expense tracking</h3>
                        </div>
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">Categorize purchases in seconds and keep a tidy history.</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Quick add with categories</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Attach notes & tags</li>
                        </ul>
                    </article>

                    <!-- Payments -->
                    <article class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0f5334]/10 text-[#0f5334] ring-1 ring-[#0f5334]/20">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 3v18M5 12h14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <h3 class="text-base sm:text-lg font-semibold">Budget-linked payments</h3>
                        </div>
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">Pay via M-PESA and auto-log every transaction to the right category.</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Paybill & Buy Goods (Till)</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Pochi la Biashara*</li>
                        </ul>
                    </article>

                    <!-- Savings goals -->
                    <article class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0f5334]/10 text-[#0f5334] ring-1 ring-[#0f5334]/20">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M4 19h16M4 5h16M8 5v14M16 5v14" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <h3 class="text-base sm:text-lg font-semibold">Savings goals</h3>
                        </div>
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">Monthly targets with clear progress bars and cumulative growth.</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Goal progress tracking</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Auto-savings suggestions</li>
                        </ul>
                    </article>

                    <!-- Insights -->
                    <article class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg bg-[#0f5334]/10 text-[#0f5334] ring-1 ring-[#0f5334]/20">
                                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M12 17a5 5 0 100-10 5 5 0 000 10zM12 2v3M12 19v3M2 12h3M19 12h3" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                                </svg>
                            </span>
                            <h3 class="text-base sm:text-lg font-semibold">Insights & reports</h3>
                        </div>
                        <p class="mt-3 text-sm text-zinc-700 dark:text-zinc-300">Understand trends with monthly and yearly summaries.</p>
                        <ul class="mt-3 space-y-1.5 text-sm text-zinc-600 dark:text-zinc-300">
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Category breakdowns</li>
                            <li class="flex items-center gap-2"><span class="h-1.5 w-1.5 rounded-full bg-[#0f5334]"></span>Export CSV/PDF</li>
                        </ul>
                    </article>
                </div>

                <!-- How it works -->
                <div class="mt-12 grid grid-cols-1 lg:grid-cols-3 gap-4 md:gap-6">
                    <div class="rounded-2xl p-5 bg-white/60 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-[#0f5334]">1. Set budgets</h3>
                        <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">Create monthly limits for groceries, transport, rent, and more.</p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white/60 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-[#0f5334]">2. Pay merchants</h3>
                        <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">Use Paybill, Buy Goods, or Pochi*—we auto-categorize and deduct.</p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white/60 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h3 class="font-semibold text-[#0f5334]">3. Track & save</h3>
                        <p class="mt-2 text-sm text-zinc-700 dark:text-zinc-300">Get monthly/yearly insights and email nudges when spending spikes.</p>
                    </div>
                </div>

                <!-- Reminder preview -->
                <div class="mt-10 rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-[#0f5334]">Example reminder</h3>
                            <p class="mt-1 text-sm text-zinc-700 dark:text-zinc-300">“Heads up! Your dining expenses are 18% above your monthly average.”</p>
                        </div>
                        <a href="{{ route('register') }}" class="inline-flex items-center justify-center px-4 py-2 rounded-md bg-[#eca425] text-black font-semibold hover:opacity-95 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#eca425]/40">
                            Enable alerts
                        </a>
                    </div>
                </div>
            </section>

            <!-- FAQ mini -->
            <section class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-10">
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-[#0f5334]">Do I need M-PESA?</h4>
                        <p class="mt-1.5 text-sm text-zinc-700 dark:text-zinc-300">No—tracking works offline. Payments use your M-PESA wallet when enabled.</p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-[#0f5334]">Are payments secure?</h4>
                        <p class="mt-1.5 text-sm text-zinc-700 dark:text-zinc-300">We use secure provider APIs and never store card/PIN data.</p>
                    </div>
                    <div class="rounded-2xl p-5 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10">
                        <h4 class="font-semibold text-[#0f5334]">What about Pochi?</h4>
                        <p class="mt-1.5 text-sm text-zinc-700 dark:text-zinc-300">Supported via partner/aggregator where available. Paybill & Till work everywhere.</p>
                    </div>
                </div>
            </section>

            <!-- CTA -->
            <section>
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="rounded-2xl p-6 sm:p-8 md:p-10 bg-white/70 dark:bg-white/10 backdrop-blur-sm ring-1 ring-black/5 dark:ring-white/10 flex flex-col md:flex-row items-start md:items-center justify-between gap-5 md:gap-6">
                        <div>
                            <h3 class="text-lg sm:text-xl md:text-2xl font-semibold text-[#0f5334] dark:text-white">
                                Ready to see where your money goes?
                            </h3>
                            <p class="mt-2 text-sm sm:text-base text-zinc-700 dark:text-zinc-300">
                                Create your account in minutes and start tracking—and paying—today.
                            </p>
                        </div>
                        <div class="w-full md:w-auto flex flex-col sm:flex-row gap-3">
                            <a href="{{ route('register') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-lg bg-[#0f5334] text-white font-semibold hover:opacity-90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40">
                                Create free account
                            </a>
                            <a href="{{ route('login') }}" class="inline-flex justify-center items-center px-5 py-3 rounded-lg bg-black/5 dark:bg-white/10 ring-1 ring-black/10 dark:ring-white/20 hover:bg-black/10 dark:hover:bg-white/15 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40">
                                Login
                            </a>
                        </div>
                    </div>
                </div>
            </section>

        </main>

        <!-- Footer -->
        <footer class="border-t border-black/5 dark:border-white/10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-6">
                <p class="text-sm text-zinc-700 dark:text-zinc-300">&copy; {{ date('Y') }} Expendify. All rights reserved.</p>
                <ul class="flex flex-wrap items-center gap-x-6 gap-y-2 text-sm">
                    <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Privacy</a></li>
                    <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Terms</a></li>
                    <li><a class="hover:underline focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#0f5334]/40 rounded-sm" href="#">Security</a></li>
                </ul>
            </div>
        </footer>
    </div>
</body>

</html>