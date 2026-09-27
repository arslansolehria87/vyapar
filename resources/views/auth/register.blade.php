<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ @filemtime(public_path('favicon.svg')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}">

    <title>CodiceSync — Create Account</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', Tahoma, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .cs-ambient-glow {
            background-color: #121214;
            background-image: 
                radial-gradient(circle at 15% 20%, rgba(172, 34, 203, 0.22) 0%, transparent 45%),
                radial-gradient(circle at 85% 80%, rgba(88, 19, 188, 0.35) 0%, transparent 55%),
                radial-gradient(circle at 50% 50%, rgba(42, 42, 51, 0.5) 0%, transparent 60%);
        }

        .concentric-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(212, 194, 223, 0.05);
            pointer-events: none;
        }

        .concentric-circle-1 { width: 750px; height: 750px; top: -150px; left: -150px; }
        .concentric-circle-2 { width: 1100px; height: 1100px; top: -300px; left: -300px; }

        .btn-cs-gradient {
            background: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%);
        }
        .btn-cs-gradient:hover {
            background: linear-gradient(135deg, #673B92 0%, #B82FDE 100%);
        }
    </style>
</head>

<body class="min-h-screen w-full bg-[#F3F3FF] flex flex-col lg:flex-row antialiased select-none">

    <!-- LEFT PANEL -->
    <div class="lg:w-[48%] xl:w-[45%] cs-ambient-glow relative overflow-hidden flex flex-col justify-between p-8 sm:p-12 lg:p-16 text-white min-h-[460px] lg:min-h-screen">
        <div class="concentric-circle concentric-circle-1"></div>
        <div class="concentric-circle concentric-circle-2"></div>

        <div class="relative z-10 flex items-center gap-3.5">
            <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync" class="h-11 w-auto object-contain">
            <div>
                <span class="text-xl sm:text-2xl font-extrabold tracking-tight text-white block leading-none">
                    Codice<span class="font-normal text-[#D4C2DF]">Sync</span>
                </span>
                <span class="text-[9px] font-semibold text-[#AC22CB] tracking-[2px] uppercase block mt-1">
                    Software Solutions
                </span>
            </div>
        </div>

        <div class="relative z-10 my-auto py-12 lg:py-0 max-w-lg">
            <h1 class="text-4xl sm:text-5xl xl:text-6xl font-extrabold text-white leading-[1.1] tracking-tight">
                Start smarter,<br>
                grow faster<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]">together.</span>
            </h1>
            <p class="text-[#D4C2DF]/80 text-base sm:text-lg font-normal leading-relaxed mt-6 max-w-md">
                Create your business workspace today. Manage your invoices, stock, accounts and customers seamlessly.
            </p>
        </div>

        <div class="relative z-10 pt-6 flex items-center gap-2.5 text-xs text-[#D4C2DF]/60 font-medium">
            <svg class="w-4 h-4 text-[#AC22CB] shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
            </svg>
            <span>Bank-grade encryption & data privacy assured.</span>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-[#F3F3FF] relative">
        <div class="flex items-center justify-end mb-4">
            <span class="text-xs text-[#575656]">Already have an account? 
                <a href="{{ route('login') }}" class="font-bold text-[#5813BC] hover:text-[#AC22CB] transition-colors ml-1">Sign in</a>
            </span>
        </div>

        <div class="w-full max-w-[460px] mx-auto my-auto py-6">
            <div class="bg-white rounded-2xl border border-[#D4C2DF]/40 shadow-[0_12px_45px_rgba(88,19,188,0.06)] p-8 sm:p-10">
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F3F3FF] border border-[#D4C2DF]/60 text-[#5813BC] text-[11px] font-bold tracking-wider uppercase mb-5">
                    <svg class="w-3.5 h-3.5 text-[#AC22CB]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    <span>START FREE TRIAL</span>
                </div>

                <h2 class="text-3xl font-extrabold text-[#121214] tracking-tight">Create Account</h2>
                <p class="text-sm text-[#575656] mt-1.5 font-normal">Start your 14-day free trial. No credit card required.</p>

                @if ($errors->any())
                    <div class="mt-4 p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <p>{{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-3.5">
                    @csrf

                    <div>
                        <label for="name" class="block text-xs font-semibold text-[#121214] mb-1">Full Name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D4C2DF] text-sm text-[#121214] bg-white placeholder-[#575656]/50 focus:outline-none focus:border-[#AC22CB] focus:ring-2 focus:ring-[#5813BC]/20 transition-all duration-150"
                            placeholder="John Doe">
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#121214] mb-1">Work Email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D4C2DF] text-sm text-[#121214] bg-white placeholder-[#575656]/50 focus:outline-none focus:border-[#AC22CB] focus:ring-2 focus:ring-[#5813BC]/20 transition-all duration-150"
                            placeholder="name@company.com">
                    </div>

                    <div>
                        <label for="password" class="block text-xs font-semibold text-[#121214] mb-1">Password</label>
                        <input id="password" type="password" name="password" required autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D4C2DF] text-sm text-[#121214] bg-white placeholder-[#575656]/50 focus:outline-none focus:border-[#AC22CB] focus:ring-2 focus:ring-[#5813BC]/20 transition-all duration-150"
                            placeholder="••••••••">
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-xs font-semibold text-[#121214] mb-1">Confirm Password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                            class="w-full px-4 py-2.5 rounded-xl border border-[#D4C2DF] text-sm text-[#121214] bg-white placeholder-[#575656]/50 focus:outline-none focus:border-[#AC22CB] focus:ring-2 focus:ring-[#5813BC]/20 transition-all duration-150"
                            placeholder="••••••••">
                    </div>

                    <div class="pt-3">
                        <button type="submit"
                            class="w-full py-3 px-4 rounded-xl btn-cs-gradient text-white font-bold text-sm shadow-[0_4px_20px_rgba(172,34,203,0.35)] hover:shadow-[0_6px_25px_rgba(172,34,203,0.5)] transition-all duration-200 flex items-center justify-center gap-2 group">
                            <span>Get Started Now</span>
                            <span class="text-base font-bold transition-transform group-hover:translate-x-1 duration-200">→</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="pt-4 text-center sm:text-right">
            <p class="text-[11px] text-[#575656] font-medium">
                © 2026 CodiceSync — Developed by <span class="font-semibold text-[#121214]">CodiceSync Solutions</span>
            </p>
        </div>
    </div>

</body>
</html>
