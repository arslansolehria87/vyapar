<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/png" href="{{ asset('images/codice-sync-logo.png') }}"/>
    <link rel="shortcut icon" href="{{ asset('images/codice-sync-logo.png') }}"/>

    <title>CodiceSync — Enterprise Sign In</title>

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

        /* CodiceSync Ambient Glow Background */
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
            transition: transform 12s ease-in-out;
        }

        .concentric-circle-1 { width: 700px; height: 700px; top: -140px; left: -140px; }
        .concentric-circle-2 { width: 1050px; height: 1050px; top: -280px; left: -280px; }
        .concentric-circle-3 { width: 1400px; height: 1400px; top: -420px; left: -420px; }

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

        .pulse-dot {
            animation: pulseGlow 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
        @keyframes pulseGlow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: .4; transform: scale(1.15); }
        }

        /* Smooth input glow */
        .cs-input {
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .cs-input:focus {
            border-color: #AC22CB;
            box-shadow: 0 0 0 4px rgba(88, 19, 188, 0.15);
        }

        /* RTL Urdu override */
        body.lang-ur {
            font-family: 'Tahoma', 'Plus Jakarta Sans', sans-serif;
            direction: rtl;
        }
        body.lang-ur .ltr-override {
            direction: ltr;
        }
    </style>
</head>

<body id="page-body" class="min-h-screen w-full bg-[#F3F3FF] dark:bg-[#0e0e12] flex flex-col lg:flex-row select-none">

    <!-- ════════════════════════════════════════════════════════════
         LEFT PANEL: Hero, Animated Live Stats & Brand Showcase
         ════════════════════════════════════════════════════════════ -->
    <div class="lg:w-[48%] xl:w-[46%] cs-ambient-glow relative overflow-hidden flex flex-col justify-between p-8 sm:p-12 lg:p-14 text-white min-h-[500px] lg:min-h-screen">
        
        <!-- Animated Concentric Background Rings -->
        <div class="concentric-circle concentric-circle-1"></div>
        <div class="concentric-circle concentric-circle-2"></div>
        <div class="concentric-circle concentric-circle-3"></div>

        <!-- Top Left Brand Header -->
        <div class="relative z-10 flex items-center justify-between">
            <div class="flex items-center gap-3.5">
                <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync" class="h-11 w-auto object-contain filter drop-shadow-[0_4px_12px_rgba(172,34,203,0.4)]">
                <div>
                    <span class="text-2xl font-extrabold tracking-tight text-white block leading-none">
                        Codice<span class="font-normal text-[#D4C2DF]">Sync</span>
                    </span>
                    <span class="text-[10px] font-bold text-[#AC22CB] tracking-[2.5px] uppercase block mt-1">
                        Software Solutions
                    </span>
                </div>
            </div>

            <!-- Version pill -->
            <span class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 border border-white/15 text-[11px] font-semibold text-[#D4C2DF]">
                <span class="w-2 h-2 rounded-full bg-emerald-400 pulse-dot"></span>
                v2.4 Enterprise
            </span>
        </div>

        <!-- Center Main Showcase -->
        <div class="relative z-10 my-auto py-8 lg:py-6 max-w-lg">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-gradient-to-r from-[#5813BC]/40 to-[#AC22CB]/30 border border-[#AC22CB]/40 text-[#D4C2DF] text-xs font-semibold mb-6">
                <i class="fa-solid fa-sparkles text-[#AC22CB]"></i>
                <span id="txt-hero-badge">NEXT-GEN POINT OF SALE & ERP</span>
            </div>

            <!-- Main Heading -->
            <h1 id="txt-hero-title" class="text-3xl sm:text-4xl xl:text-5xl font-extrabold text-white leading-[1.15] tracking-tight">
                Point of Sale & ERP,<br>
                engineered for <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]">clarity.</span>
            </h1>
            <p id="txt-hero-desc" class="text-[#D4C2DF]/80 text-sm sm:text-base font-normal leading-relaxed mt-4 max-w-md">
                A complete unified platform for retail counter billing, inventory synchronization, and double-entry accounting.
            </p>

            <!-- ── Interactive Live POS Pulse Card (Glassmorphism) ── -->
            <div class="mt-8 glass-card-preview rounded-2xl p-4 sm:p-5 border border-white/10">
                <div class="flex items-center justify-between pb-3 border-b border-white/10 text-xs">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-400 pulse-dot"></span>
                        <span class="font-bold text-white tracking-wide uppercase text-[11px]" id="txt-live-status">LIVE STORE METRICS</span>
                    </div>
                    <span class="text-[#D4C2DF]/70 text-[11px]">Real-Time Sync</span>
                </div>

                <div class="grid grid-cols-2 gap-3 pt-3">
                    <div class="bg-white/5 rounded-xl p-2.5 border border-white/5">
                        <span class="text-[11px] text-[#D4C2DF]/70 block" id="txt-stat-sales-lbl">Today's Revenue</span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-lg font-bold text-white">Rs 184,520</span>
                            <span class="text-[10px] text-emerald-400 font-semibold">↑ 24.8%</span>
                        </div>
                    </div>
                    <div class="bg-white/5 rounded-xl p-2.5 border border-white/5">
                        <span class="text-[11px] text-[#D4C2DF]/70 block" id="txt-stat-bills-lbl">Counter Invoices</span>
                        <div class="flex items-baseline gap-1.5 mt-0.5">
                            <span class="text-lg font-bold text-white">142 Bills</span>
                            <span class="text-[10px] text-[#AC22CB] font-semibold">⚡ Fast POS</span>
                        </div>
                    </div>
                </div>

                <!-- Feature Pill Tickers -->
                <div class="flex flex-wrap items-center gap-2 mt-3 pt-2 text-[11px] text-[#D4C2DF]/90 font-medium">
                    <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded-md border border-white/5">
                        <i class="fa-solid fa-receipt text-[#AC22CB] text-[10px]"></i> 80mm ESC/POS
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded-md border border-white/5">
                        <i class="fa-brands fa-whatsapp text-emerald-400 text-[11px]"></i> WhatsApp Invoicing
                    </span>
                    <span class="inline-flex items-center gap-1 bg-white/5 px-2 py-0.5 rounded-md border border-white/5">
                        <i class="fa-solid fa-shield-halved text-[#5813BC] text-[10px]"></i> Cloud Security
                    </span>
                </div>
            </div>

            <!-- Trust Badge -->
            <div class="mt-6 flex items-center gap-3 text-xs text-[#D4C2DF]/70">
                <div class="flex text-amber-400 text-xs">
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                    <i class="fa-solid fa-star"></i>
                </div>
                <span id="txt-trust-badge">Trusted by 500+ Wholesale & Retail Counters across Pakistan & UAE</span>
            </div>
        </div>

        <!-- Left Footer: Contact & Support Links -->
        <div class="relative z-10 pt-4 border-t border-white/10 flex flex-wrap items-center justify-between gap-3 text-xs text-[#D4C2DF]/70">
            <div class="flex items-center gap-2">
                <i class="fa-solid fa-phone text-[#AC22CB]"></i>
                <span>Helpline: <strong class="text-white font-semibold">0371-0045282 / 0321-4530103</strong></span>
            </div>
            <a href="https://wa.me/923214530103" target="_blank" class="inline-flex items-center gap-1.5 text-emerald-400 hover:text-emerald-300 font-semibold transition-colors">
                <i class="fa-brands fa-whatsapp text-sm"></i> WhatsApp Support
            </a>
        </div>
    </div>

    <!-- ════════════════════════════════════════════════════════════
         RIGHT PANEL: Authentication, Dynamic Lang & Dark Mode
         ════════════════════════════════════════════════════════════ -->
    <div id="right-panel" class="flex-1 flex flex-col justify-between p-6 sm:p-10 lg:p-14 bg-[#F3F3FF] dark:bg-[#0e0e12] relative transition-colors duration-300">
        
        <!-- Top Header Controls (Dynamic Language Switcher + Working Dark Mode Toggle) -->
        <div class="flex items-center justify-between gap-3 mb-6 lg:mb-0">
            <!-- System Status indicator -->
            <div class="hidden sm:inline-flex items-center gap-2 text-xs font-semibold text-[#575656] dark:text-[#a1a1aa]">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span id="txt-system-status">All Systems Operational</span>
            </div>

            <div class="flex items-center gap-3 ms-auto">
                <!-- 🌐 Dynamic Language Switcher -->
                <div class="inline-flex items-center bg-white dark:bg-[#1c1c24] border border-[#D4C2DF]/50 dark:border-white/10 rounded-full p-1 text-[11px] font-bold text-[#575656] dark:text-[#d4d4d8] shadow-xs">
                    <button type="button" onclick="setLanguage('en')" id="lang-btn-en" class="lang-btn px-2.5 py-1 rounded-full transition-all">EN</button>
                    <button type="button" onclick="setLanguage('ur')" id="lang-btn-ur" class="lang-btn px-2.5 py-1 rounded-full transition-all">اردو</button>
                    <button type="button" onclick="setLanguage('fr')" id="lang-btn-fr" class="lang-btn px-2.5 py-1 rounded-full transition-all">FR</button>
                    <button type="button" onclick="setLanguage('nl')" id="lang-btn-nl" class="lang-btn px-2.5 py-1 rounded-full transition-all">NL</button>
                    <button type="button" onclick="setLanguage('de')" id="lang-btn-de" class="lang-btn px-2.5 py-1 rounded-full transition-all">DE</button>
                </div>

                <!-- 🌓 Working Interactive Theme Mode Toggle -->
                <button type="button" onclick="toggleDarkMode()" id="theme-toggle-btn"
                        class="w-14 h-7 bg-white dark:bg-[#1c1c24] border border-[#D4C2DF]/60 dark:border-white/15 rounded-full p-0.5 flex items-center cursor-pointer transition-colors shadow-xs relative"
                        title="Switch Dark / Light theme" aria-label="Toggle theme">
                    <div id="theme-toggle-circle" class="w-6 h-6 rounded-full shadow-md transition-transform duration-200 flex items-center justify-center text-[10px] text-white"
                         style="background: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%); transform: translateX(0px);">
                        <i id="theme-icon" class="fa-solid fa-sun text-white"></i>
                    </div>
                </button>
            </div>
        </div>

        <!-- Center Login Card -->
        <div class="w-full max-w-[440px] mx-auto my-auto py-6">
            
            <div class="bg-white dark:bg-[#181820] rounded-3xl border border-[#D4C2DF]/50 dark:border-white/10 shadow-[0_16px_50px_rgba(88,19,188,0.08)] dark:shadow-[0_20px_60px_rgba(0,0,0,0.5)] p-8 sm:p-10 transition-all duration-300">
                
                <!-- Card Header -->
                <div class="flex items-center justify-between mb-5">
                    <!-- Secure Access Badge -->
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-[#F3F3FF] dark:bg-white/5 border border-[#D4C2DF]/70 dark:border-white/10 text-[#5813BC] dark:text-[#AC22CB] text-[11px] font-bold tracking-wider uppercase">
                        <i class="fa-solid fa-lock text-[10px]"></i>
                        <span id="txt-badge-secure">SECURE ACCESS</span>
                    </div>

                    <!-- Quick Admin Demo Pill -->
                    <button type="button" onclick="fillAdminCredentials()"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-[#5813BC] dark:text-[#AC22CB] hover:underline cursor-pointer bg-purple-50 dark:bg-purple-950/40 px-2.5 py-1 rounded-lg border border-purple-200 dark:border-purple-800/40 transition-colors"
                            title="Click to automatically fill Admin credentials">
                        <i class="fa-solid fa-bolt text-amber-500 text-[10px]"></i>
                        <span id="txt-quick-fill">Quick Admin Fill</span>
                    </button>
                </div>

                <!-- Form Title -->
                <h2 id="txt-form-title" class="text-3xl font-extrabold text-[#121214] dark:text-white tracking-tight">Sign in</h2>
                <p id="txt-form-subtitle" class="text-sm text-[#575656] dark:text-[#a1a1aa] mt-1.5 font-normal">Enter your account details to access your workspace.</p>

                <!-- Session / Validation Alerts -->
                @if (session('status'))
                    <div class="mt-4 p-3.5 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/50 text-emerald-800 dark:text-emerald-300 text-xs rounded-xl font-medium flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-500"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mt-4 p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/50 text-rose-700 dark:text-rose-300 text-xs rounded-xl font-medium space-y-1">
                        @foreach ($errors->all() as $error)
                            <p class="flex items-center gap-1.5"><i class="fa-solid fa-circle-exclamation text-rose-500"></i> {{ $error }}</p>
                        @endforeach
                    </div>
                @endif

                <!-- Login Form -->
                <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                    @csrf

                    <!-- Email Address -->
                    <div>
                        <label for="email" id="txt-label-email" class="block text-xs font-bold text-[#121214] dark:text-[#d4d4d8] mb-1.5">
                            Email address
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#575656] dark:text-[#71717a]">
                                <i class="fa-regular fa-envelope text-sm"></i>
                            </div>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                class="cs-input w-full pl-10 pr-4 py-2.5 rounded-xl border border-[#D4C2DF] dark:border-white/10 text-sm text-[#121214] dark:text-white bg-white dark:bg-[#121216] placeholder-[#575656]/50 dark:placeholder-[#71717a] focus:outline-none"
                                placeholder="name@company.com">
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" id="txt-label-password" class="block text-xs font-bold text-[#121214] dark:text-[#d4d4d8] mb-1.5">
                            Password
                        </label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#575656] dark:text-[#71717a]">
                                <i class="fa-solid fa-lock text-sm"></i>
                            </div>
                            <input id="password" type="password" name="password" required autocomplete="current-password"
                                class="cs-input w-full pl-10 pr-10 py-2.5 rounded-xl border border-[#D4C2DF] dark:border-white/10 text-sm text-[#121214] dark:text-white bg-white dark:bg-[#121216] placeholder-[#575656]/50 dark:placeholder-[#71717a] focus:outline-none"
                                placeholder="••••••••">
                            <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#575656] dark:text-[#71717a] hover:text-[#5813BC] dark:hover:text-[#AC22CB] focus:outline-none" title="Show/Hide password">
                                <i id="eye-icon" class="fa-regular fa-eye text-sm"></i>
                                <i id="eye-slash-icon" class="fa-regular fa-eye-slash text-sm hidden"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded text-[#5813BC] border-[#D4C2DF] dark:border-white/20 dark:bg-[#121216] focus:ring-[#5813BC] focus:ring-offset-0 transition cursor-pointer">
                            <span id="txt-label-remember" class="text-xs text-[#575656] dark:text-[#a1a1aa] font-medium">Remember my device</span>
                        </label>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" id="txt-link-forgot" class="text-xs text-[#5813BC] dark:text-[#AC22CB] hover:underline font-semibold transition-colors">
                                Forgot password?
                            </a>
                        @endif
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-2">
                        <button type="submit" id="btn-submit"
                            class="w-full py-3 px-4 rounded-xl btn-cs-gradient text-white font-bold text-sm transition-all duration-200 flex items-center justify-center gap-2 group cursor-pointer">
                            <span id="txt-btn-signin">Sign In to Workspace</span>
                            <span class="text-base font-bold transition-transform group-hover:translate-x-1 duration-200">→</span>
                        </button>
                    </div>
                </form>

                <!-- Security Assurance -->
                <div class="mt-6 pt-5 border-t border-[#D4C2DF]/40 dark:border-white/10 flex items-center gap-2.5 text-[11px] text-[#575656] dark:text-[#a1a1aa]">
                    <i class="fa-solid fa-shield-halved text-[#AC22CB] text-xs shrink-0"></i>
                    <span id="txt-footer-security">Protected by enterprise-grade 256-bit encryption.</span>
                </div>

            </div>

        </div>

        <!-- ════════════════════════════════════════════════════════════
             Bottom Footer: Strictly "Developed by CodiceSync Solutions"
             ════════════════════════════════════════════════════════════ -->
        <div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-2 text-xs text-[#575656] dark:text-[#a1a1aa]">
            <p class="font-medium text-center sm:text-left">
                © 2026 CodiceSync — <span class="text-[#121214] dark:text-white font-semibold">Developed by CodiceSync Solutions</span>
            </p>
            <p class="text-[11px] text-[#575656]/80 dark:text-[#a1a1aa]/80">
                Support: <a href="mailto:codicesync@gmail.com" class="text-[#5813BC] dark:text-[#AC22CB] hover:underline">codicesync@gmail.com</a>
            </p>
        </div>

    </div>

    <!-- ════════════════════════════════════════════════════════════
         SCRIPTS: Dynamic Multilingual Dictionary & Interactive Theme
         ════════════════════════════════════════════════════════════ -->
    <script>
        // ── 1. Comprehensive Dynamic Translations Dictionary ──
        const translations = {
            en: {
                heroBadge: "NEXT-GEN POINT OF SALE & ERP",
                heroTitle: "Point of Sale & ERP,<br>engineered for <span class='text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]'>clarity.</span>",
                heroDesc: "A complete unified platform for retail counter billing, inventory synchronization, and double-entry accounting.",
                liveStatus: "LIVE STORE METRICS",
                statSales: "Today's Revenue",
                statBills: "Counter Invoices",
                trustBadge: "Trusted by 500+ Wholesale & Retail Counters across Pakistan & UAE",
                systemStatus: "All Systems Operational",
                badgeSecure: "SECURE ACCESS",
                quickFill: "Quick Admin Fill",
                formTitle: "Sign in",
                formSubtitle: "Enter your account details to access your workspace.",
                labelEmail: "Email address",
                placeholderEmail: "name@company.com",
                labelPassword: "Password",
                placeholderPassword: "••••••••",
                labelRemember: "Remember my device",
                linkForgot: "Forgot password?",
                btnSignin: "Sign In to Workspace",
                footerSecurity: "Protected by enterprise-grade 256-bit encryption.",
                isRtl: false
            },
            ur: {
                heroBadge: "جدید ترین پوائنٹ آف سیل اور ای آر پی",
                heroTitle: "پوائنٹ آف سیل اور ای آر پی،<br>کاروباری ترقی کی <span class='text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]'>ضمانت۔</span>",
                heroDesc: "ریٹیل کاؤنٹر بلنگ، لائیو اسٹاک انوینٹری اور مکمل مالیاتی اکاؤنٹس کا جدید ترین پلیٹ فارم۔",
                liveStatus: "لائیو کاروباری رپورٹ",
                statSales: "آج کی کل سیل",
                statBills: "کاؤنٹر انوائسز",
                trustBadge: "پاکستان اور امارات بھر میں 500 سے زائد ہول سیل و ریٹیل دکانوں کا اعتماد",
                systemStatus: "تمام سسٹمز بالکل ٹھیک کام کر رہے ہیں",
                badgeSecure: "محفوظ رسائی",
                quickFill: "ایڈمن لاگ ان آٹو فل",
                formTitle: "لاگ ان کریں",
                formSubtitle: "اپنے اکاؤنٹ میں داخل ہونے کے لیے معلومات درج کریں۔",
                labelEmail: "ای میل ایڈریس",
                placeholderEmail: "name@company.com",
                labelPassword: "پاس ورڈ",
                placeholderPassword: "••••••••",
                labelRemember: "مجھے یاد رکھیں",
                linkForgot: "پاس ورڈ بھول گئے؟",
                btnSignin: "سسٹم میں داخل ہوں",
                footerSecurity: "آپ کا ڈیٹا 256 بٹ انکرپشن سے مکمل محفوظ ہے۔",
                isRtl: true
            },
            fr: {
                heroBadge: "POINT DE VENTE & ERP DE NOUVELLE GÉNÉRATION",
                heroTitle: "Point de vente & ERP,<br>conçu pour la <span class='text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]'>clarté.</span>",
                heroDesc: "Une plateforme unifiée pour la facturation en magasin, les stocks et la comptabilité.",
                liveStatus: "MÉTRIQUES EN DIRECT",
                statSales: "Ventes du Jour",
                statBills: "Factures Émises",
                trustBadge: "Approuvé par plus de 500 commerces de gros et de détail",
                systemStatus: "Tous les systèmes opérationnels",
                badgeSecure: "ACCÈS SÉCURISÉ",
                quickFill: "Remplissage Admin",
                formTitle: "Connexion",
                formSubtitle: "Entrez vos identifiants pour accéder à votre espace.",
                labelEmail: "Adresse e-mail",
                placeholderEmail: "nom@entreprise.com",
                labelPassword: "Mot de passe",
                placeholderPassword: "••••••••",
                labelRemember: "Se souvenir de moi",
                linkForgot: "Mot de passe oublié ?",
                btnSignin: "Se Connecter à l'Espace",
                footerSecurity: "Protégé par un chiffrement bancaire 256 bits.",
                isRtl: false
            },
            nl: {
                heroBadge: "NEXT-GEN KASSASYSTEEM & ERP",
                heroTitle: "Kassasysteem & ERP,<br>ontworpen voor <span class='text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]'>helderheid.</span>",
                heroDesc: "Een complete werkruimte voor dagelijkse winkelverkopen, voorraad en boekhouding.",
                liveStatus: "LIVE WINKELSTATISTIEKEN",
                statSales: "Omzet Vandaag",
                statBills: "Kassabonnen",
                trustBadge: "Vertrouwd door 500+ groothandels en winkels",
                systemStatus: "Alle systemen operationeel",
                badgeSecure: "VEILIGE TOEGANG",
                quickFill: "Snelle Admin Vulling",
                formTitle: "Aanmelden",
                formSubtitle: "Vul uw gegevens in om toegang te krijgen tot uw werkruimte.",
                labelEmail: "E-mailadres",
                placeholderEmail: "naam@bedrijf.nl",
                labelPassword: "Wachtwoord",
                placeholderPassword: "••••••••",
                labelRemember: "Onthoud mijn apparaat",
                linkForgot: "Wachtwoord vergeten?",
                btnSignin: "Inloggen op Werkruimte",
                footerSecurity: "Beveiligd met enterprise 256-bit encryptie.",
                isRtl: false
            },
            de: {
                heroBadge: "KASSENSYSTEM & ERP DER NÄCHSTEN GENERATION",
                heroTitle: "Kassensystem & ERP,<br>entwickelt für <span class='text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]'>Klarheit.</span>",
                heroDesc: "Eine einheitliche Plattform für Ladenverkauf, Lagerverwaltung und doppelte Buchführung.",
                liveStatus: "LIVE LADENSTATISTIKEN",
                statSales: "Tagesumsatz",
                statBills: "Erstellte Rechnungen",
                trustBadge: "Von über 500 Groß- und Einzelhändlern geschätzt",
                systemStatus: "Alle Systeme betriebsbereit",
                badgeSecure: "SICHERER ZUGANG",
                quickFill: "Admin Schnellfüllung",
                formTitle: "Anmelden",
                formSubtitle: "Geben Sie Ihre Daten ein, um fortzufahren.",
                labelEmail: "E-Mail-Adresse",
                placeholderEmail: "name@firma.de",
                labelPassword: "Passwort",
                placeholderPassword: "••••••••",
                labelRemember: "Angemeldet bleiben",
                linkForgot: "Passwort vergessen?",
                btnSignin: "Im Arbeitsbereich Anmelden",
                footerSecurity: "Geschützt durch 256-Bit-Verschlüsselung.",
                isRtl: false
            }
        };

        // ── 2. Dynamic Language Switcher Function ──
        function setLanguage(lang) {
            const data = translations[lang] || translations.en;
            localStorage.setItem('codicesync_login_lang', lang);

            // Update All Content Dynamically
            document.getElementById('txt-hero-badge').innerHTML = data.heroBadge;
            document.getElementById('txt-hero-title').innerHTML = data.heroTitle;
            document.getElementById('txt-hero-desc').innerHTML = data.heroDesc;
            document.getElementById('txt-live-status').innerHTML = data.liveStatus;
            document.getElementById('txt-stat-sales-lbl').innerHTML = data.statSales;
            document.getElementById('txt-stat-bills-lbl').innerHTML = data.statBills;
            document.getElementById('txt-trust-badge').innerHTML = data.trustBadge;
            document.getElementById('txt-system-status').innerHTML = data.systemStatus;

            document.getElementById('txt-badge-secure').innerHTML = data.badgeSecure;
            document.getElementById('txt-quick-fill').innerHTML = data.quickFill;
            document.getElementById('txt-form-title').innerHTML = data.formTitle;
            document.getElementById('txt-form-subtitle').innerHTML = data.formSubtitle;
            document.getElementById('txt-label-email').innerHTML = data.labelEmail;
            document.getElementById('email').placeholder = data.placeholderEmail;
            document.getElementById('txt-label-password').innerHTML = data.labelPassword;
            document.getElementById('password').placeholder = data.placeholderPassword;
            document.getElementById('txt-label-remember').innerHTML = data.labelRemember;
            if (document.getElementById('txt-link-forgot')) {
                document.getElementById('txt-link-forgot').innerHTML = data.linkForgot;
            }
            document.getElementById('txt-btn-signin').innerHTML = data.btnSignin;
            document.getElementById('txt-footer-security').innerHTML = data.footerSecurity;

            // Handle RTL/LTR direction
            const body = document.getElementById('page-body');
            if (data.isRtl) {
                body.classList.add('lang-ur');
            } else {
                body.classList.remove('lang-ur');
            }

            // Update Active Pill Styling
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.className = 'lang-btn px-2.5 py-1 rounded-full transition-all text-[#575656] dark:text-[#d4d4d8] hover:text-[#5813BC]';
            });
            const activeBtn = document.getElementById(`lang-btn-${lang}`);
            if (activeBtn) {
                activeBtn.className = 'lang-btn px-2.5 py-1 rounded-full transition-all bg-[#121214] dark:bg-[#AC22CB] text-white font-bold shadow-xs';
            }
        }

        // ── 3. Interactive Theme Mode Toggle ──
        function toggleDarkMode() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('codicesync_login_theme', isDark ? 'dark' : 'light');
            updateThemeUI(isDark);
        }

        function updateThemeUI(isDark) {
            const circle = document.getElementById('theme-toggle-circle');
            const icon = document.getElementById('theme-icon');
            if (isDark) {
                circle.style.transform = 'translateX(26px)';
                icon.className = 'fa-solid fa-moon text-white';
            } else {
                circle.style.transform = 'translateX(0px)';
                icon.className = 'fa-solid fa-sun text-white';
            }
        }

        // ── 4. Quick Admin Credentials Auto-Fill ──
        function fillAdminCredentials() {
            const email = document.getElementById('email');
            const pwd = document.getElementById('password');
            email.value = 'admin1@gmail.com';
            pwd.value = 'password';
            
            // Visual highlight
            email.classList.add('ring-2', 'ring-[#AC22CB]');
            pwd.classList.add('ring-2', 'ring-[#AC22CB]');
            setTimeout(() => {
                email.classList.remove('ring-2', 'ring-[#AC22CB]');
                pwd.classList.remove('ring-2', 'ring-[#AC22CB]');
            }, 1000);
        }

        // ── 5. Password Visibility Toggle ──
        function togglePasswordVisibility() {
            const pwd = document.getElementById('password');
            const eyeIcon = document.getElementById('eye-icon');
            const eyeSlashIcon = document.getElementById('eye-slash-icon');
            if (pwd.type === 'password') {
                pwd.type = 'text';
                eyeIcon.classList.add('hidden');
                eyeSlashIcon.classList.remove('hidden');
            } else {
                pwd.type = 'password';
                eyeIcon.classList.remove('hidden');
                eyeSlashIcon.classList.add('hidden');
            }
        }

        // ── 6. Initial State Restoration ──
        document.addEventListener('DOMContentLoaded', () => {
            // Restore Theme
            const savedTheme = localStorage.getItem('codicesync_login_theme') || 'light';
            if (savedTheme === 'dark') {
                document.documentElement.classList.add('dark');
                updateThemeUI(true);
            } else {
                document.documentElement.classList.remove('dark');
                updateThemeUI(false);
            }

            // Restore Language
            const savedLang = localStorage.getItem('codicesync_login_lang') || 'en';
            setLanguage(savedLang);
        });
    </script>

</body>
</html>
