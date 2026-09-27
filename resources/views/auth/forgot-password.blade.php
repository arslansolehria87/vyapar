<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/codice-sync-logo.png') }}"/>
    <link rel="shortcut icon" href="{{ asset('images/codice-sync-logo.png') }}"/>

    <title>CodiceSync - Reset Password</title>

    <!-- Google Fonts: Plus Jakarta Sans & Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Font Awesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        csDark: '#121214',
                        csSurface: '#1c1c22',
                        csPurple: '#5813BC',
                        csMagenta: '#AC22CB',
                        csRoyal: '#673B92',
                        csAccent: '#795DA8',
                        csLavender: '#D4C2DF',
                        csMuted: '#6b7280',
                        csLight: '#F3F3FF',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'Tahoma', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Plus Jakarta Sans', Tahoma, sans-serif;
            -webkit-font-smoothing: antialiased;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .cs-ambient-glow {
            background-color: #121214;
            background-image: 
                radial-gradient(circle at 15% 15%, rgba(172, 34, 203, 0.28) 0%, transparent 45%),
                radial-gradient(circle at 85% 85%, rgba(88, 19, 188, 0.40) 0%, transparent 55%),
                radial-gradient(circle at 50% 50%, rgba(28, 28, 34, 0.6) 0%, transparent 70%);
        }

        .concentric-circle {
            position: absolute;
            border-radius: 50%;
            border: 1px solid rgba(212, 194, 223, 0.07);
            pointer-events: none;
        }

        .concentric-circle-1 { width: 700px; height: 700px; top: -140px; left: -140px; }
        .concentric-circle-2 { width: 1050px; height: 1050px; top: -280px; left: -280px; }

        .btn-cs-gradient {
            background: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%);
            box-shadow: 0 4px 20px rgba(172, 34, 203, 0.35);
        }
        .btn-cs-gradient:hover {
            background: linear-gradient(135deg, #673B92 0%, #c026dc 100%);
            box-shadow: 0 6px 28px rgba(172, 34, 203, 0.55);
            transform: translateY(-1px);
        }

        .glass-card-preview {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(212, 194, 223, 0.14);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.35);
        }

        .cs-input:focus {
            box-shadow: 0 0 0 3px rgba(172, 34, 203, 0.22);
            border-color: #AC22CB;
        }
    </style>
</head>

<body class="min-h-screen w-full bg-[#F3F3FF] dark:bg-[#0c0c0e] flex flex-col lg:flex-row antialiased select-none text-[#121214] dark:text-[#F3F3FF] transition-colors duration-300">

    <!-- LEFT SHOWCASE PANEL (Desktop & Tablet) -->
    <div class="lg:w-[46%] xl:w-[42%] cs-ambient-glow relative overflow-hidden flex flex-col justify-between p-8 sm:p-12 lg:p-16 text-white min-h-[380px] lg:min-h-screen">
        <div class="concentric-circle concentric-circle-1"></div>
        <div class="concentric-circle concentric-circle-2"></div>

        <!-- Brand Top Logo -->
        <div class="relative z-10 flex items-center gap-3.5">
            <a href="{{ route('login') }}" class="flex items-center gap-3.5 group">
                <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync Logo" class="h-11 w-auto object-contain transition-transform group-hover:scale-105 duration-200">
                <div>
                    <span class="text-xl sm:text-2xl font-extrabold tracking-tight text-white block leading-none">
                        Codice<span class="font-normal text-[#D4C2DF]">Sync</span>
                    </span>
                    <span class="text-[9px] font-semibold text-[#AC22CB] tracking-[2px] uppercase block mt-1">
                        Enterprise Solutions
                    </span>
                </div>
            </a>
        </div>

        <!-- Middle Reassurance -->
        <div class="relative z-10 my-auto py-10 lg:py-0 max-w-lg">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-white/90 text-xs font-semibold tracking-wide uppercase mb-6">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span>Account Recovery Shield</span>
            </div>

            <h1 class="text-3xl sm:text-4xl xl:text-5xl font-extrabold text-white leading-[1.15] tracking-tight">
                Locked out?<br>
                We've got you<br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]">covered.</span>
            </h1>

            <p class="text-[#D4C2DF]/85 text-sm sm:text-base font-normal leading-relaxed mt-5 max-w-md">
                Don't worry, forgetting passwords happens. Enter your registered email address and our automated recovery system will dispatch a secure, single-use password reset link.
            </p>

            <!-- Support Hotline Card -->
            <div class="glass-card-preview rounded-2xl p-5 mt-8 max-w-md">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-[#5813BC] to-[#AC22CB] flex items-center justify-center text-white text-sm shadow-md">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-white tracking-wide uppercase">Need Urgent Assistance?</h4>
                        <p class="text-[11px] text-[#D4C2DF]/80">Our support desk is ready to help you</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 text-xs pt-1">
                    <a href="https://wa.me/923214530103" target="_blank" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 hover:bg-emerald-600/20 border border-white/10 text-white/90 transition-colors">
                        <i class="fa-brands fa-whatsapp text-emerald-400"></i>
                        <span class="font-medium">0321-4530103</span>
                    </a>
                    <a href="tel:03710045282" class="flex items-center gap-2 px-3 py-2 rounded-xl bg-white/5 hover:bg-purple-600/20 border border-white/10 text-white/90 transition-colors">
                        <i class="fa-solid fa-phone text-[#AC22CB]"></i>
                        <span class="font-medium">0371-0045282</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Bottom Security Note -->
        <div class="relative z-10 pt-4 flex items-center gap-2.5 text-xs text-[#D4C2DF]/60 font-medium">
            <i class="fa-solid fa-shield-halved text-[#AC22CB]"></i>
            <span>256-bit encrypted data protection & instant audit logging.</span>
        </div>
    </div>

    <!-- RIGHT ACTION PANEL -->
    <div class="flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-[#F3F3FF] dark:bg-[#0c0c0e] relative">
        
        <!-- Header Controls (Back to Sign In & Theme Toggle) -->
        <div class="flex items-center justify-between w-full max-w-[500px] mx-auto mb-4">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-xl bg-white dark:bg-[#1a1a22] border border-[#D4C2DF]/50 dark:border-[#2a2a35] text-xs font-semibold text-[#5813BC] dark:text-[#D4C2DF] hover:border-[#AC22CB] hover:shadow-sm transition-all">
                <i class="fa-solid fa-arrow-left text-[11px]"></i>
                <span>Back to Sign In</span>
            </a>

            <!-- Theme Switcher -->
            <button id="themeToggleBtn" type="button" aria-label="Toggle Theme"
                class="w-9 h-9 rounded-xl bg-white dark:bg-[#1a1a22] border border-[#D4C2DF]/50 dark:border-[#2a2a35] flex items-center justify-center text-[#5813BC] dark:text-amber-400 hover:scale-105 transition-all shadow-sm">
                <i id="themeIcon" class="fa-solid fa-moon text-sm"></i>
            </button>
        </div>

        <!-- Form Card Container -->
        <div class="w-full max-w-[480px] mx-auto my-auto py-6">
            <div class="bg-white dark:bg-[#15151b] rounded-3xl border border-[#D4C2DF]/40 dark:border-[#262632] shadow-[0_15px_50px_rgba(88,19,188,0.07)] p-8 sm:p-10 transition-colors">
                
                <!-- Badge -->
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F3F3FF] dark:bg-[#1e1e28] border border-[#D4C2DF]/60 dark:border-[#353545] text-[#5813BC] dark:text-[#D4C2DF] text-[11px] font-bold tracking-wider uppercase mb-5">
                    <i class="fa-solid fa-key text-[#AC22CB]"></i>
                    <span>PASSWORD RECOVERY</span>
                </div>

                <!-- Titles -->
                <h2 class="text-2xl sm:text-3xl font-extrabold text-[#121214] dark:text-white tracking-tight">
                    Forgot Password?
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-2 font-normal leading-relaxed">
                    Enter the email address registered with your CodiceSync account and we will email you a secure link to reset your password.
                </p>

                <!-- Status Banner (Success) -->
                @if (session('status'))
                    <div class="mt-5 p-3.5 rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-300 text-xs font-medium flex items-center gap-2.5">
                        <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <!-- Error Messages -->
                @if ($errors->any())
                    <div class="mt-5 p-3.5 rounded-2xl bg-rose-50 dark:bg-rose-950/30 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-300 text-xs font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <div class="flex items-center gap-2">
                                <i class="fa-solid fa-circle-exclamation text-rose-500 shrink-0"></i>
                                <span>{{ $error }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Password Reset Form -->
                <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
                    @csrf

                    <div>
                        <label for="email" class="block text-xs font-semibold text-[#121214] dark:text-gray-200 mb-1.5">
                            Registered Work Email
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-gray-400 dark:text-gray-500">
                                <i class="fa-regular fa-envelope text-sm"></i>
                            </span>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                                class="cs-input w-full pl-10 pr-4 py-3 rounded-xl border border-[#D4C2DF] dark:border-[#2e2e3d] text-sm text-[#121214] dark:text-white bg-white dark:bg-[#1a1a24] placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none transition-all duration-150"
                                placeholder="name@company.com">
                        </div>
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                            class="w-full py-3.5 px-5 rounded-xl btn-cs-gradient text-white font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 group">
                            <span>Email Password Reset Link</span>
                            <i class="fa-solid fa-paper-plane text-xs transition-transform group-hover:translate-x-1 duration-200"></i>
                        </button>
                    </div>

                    <div class="pt-3 text-center">
                        <span class="text-xs text-gray-500 dark:text-gray-400">
                            Remember your credentials? 
                            <a href="{{ route('login') }}" class="font-bold text-[#5813BC] dark:text-[#AC22CB] hover:underline ml-1">
                                Sign In
                            </a>
                        </span>
                    </div>
                </form>

            </div>
        </div>

        <!-- Footer -->
        <div class="pt-6 text-center sm:text-right w-full max-w-[500px] mx-auto">
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                &copy; 2026 CodiceSync - Developed by <span class="font-bold text-[#5813BC] dark:text-[#D4C2DF]">CodiceSync Solutions</span>
            </p>
        </div>

    </div>

    <!-- Theme Handling Script -->
    <script>
        (function() {
            const themeBtn = document.getElementById('themeToggleBtn');
            const themeIcon = document.getElementById('themeIcon');
            const html = document.documentElement;

            const savedTheme = localStorage.getItem('cs_theme') || 'light';
            applyTheme(savedTheme);

            themeBtn.addEventListener('click', function() {
                const current = html.classList.contains('dark') ? 'dark' : 'light';
                const next = current === 'dark' ? 'light' : 'dark';
                applyTheme(next);
            });

            function applyTheme(theme) {
                if (theme === 'dark') {
                    html.classList.add('dark');
                    themeIcon.className = 'fa-solid fa-sun text-amber-400 text-sm';
                    localStorage.setItem('cs_theme', 'dark');
                } else {
                    html.classList.remove('dark');
                    themeIcon.className = 'fa-solid fa-moon text-[#5813BC] text-sm';
                    localStorage.setItem('cs_theme', 'light');
                }
            }
        })();
    </script>

</body>
</html>
