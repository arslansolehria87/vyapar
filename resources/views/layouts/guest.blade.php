<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="icon" type="image/png" href="{{ asset('images/codice-sync-logo.png') }}"/>
    <link rel="shortcut icon" href="{{ asset('images/codice-sync-logo.png') }}"/>

    <title>{{ config('app.name', 'CodiceSync POS') }}</title>

    <!-- Google Fonts -->
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
    </style>
</head>

<body class="min-h-screen flex items-center justify-center bg-[#F3F3FF] p-4 sm:p-6 antialiased text-[#121214]">

    <div class="grid grid-cols-1 md:grid-cols-2 bg-white shadow-2xl rounded-3xl overflow-hidden max-w-4xl w-full border border-[#D4C2DF]/40">

        <!-- LEFT HERO PANEL -->
        <div class="hidden md:flex flex-col justify-between text-white p-10 lg:p-12 cs-ambient-glow relative overflow-hidden">
            <div class="concentric-circle concentric-circle-1"></div>
            <div class="concentric-circle concentric-circle-2"></div>

            <div class="relative z-10 flex items-center gap-3">
                <a href="{{ route('login') }}" class="flex items-center gap-3">
                    <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync Logo" class="h-10 w-auto object-contain">
                    <div>
                        <span class="text-xl font-extrabold text-white block leading-none">
                            Codice<span class="font-normal text-[#D4C2DF]">Sync</span>
                        </span>
                        <span class="text-[9px] font-semibold text-[#AC22CB] tracking-[2px] uppercase block mt-1">
                            Enterprise ERP & POS
                        </span>
                    </div>
                </a>
            </div>

            <div class="relative z-10 my-8">
                <h2 class="text-3xl font-extrabold text-white leading-tight">
                    Smart Business<br>
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-white via-[#D4C2DF] to-[#AC22CB]">Simplified.</span>
                </h2>
                <p class="text-xs text-[#D4C2DF]/80 mt-3 leading-relaxed">
                    Access your CodiceSync enterprise workspace securely with high-grade data protection.
                </p>
            </div>

            <div class="relative z-10 flex items-center gap-2 text-[11px] text-[#D4C2DF]/70">
                <i class="fa-solid fa-shield-halved text-[#AC22CB]"></i>
                <span>Protected by CodiceSync Solutions</span>
            </div>
        </div>

        <!-- RIGHT CONTENT PANEL -->
        <div class="p-8 sm:p-10 flex flex-col justify-between">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center gap-2.5 md:hidden">
                    <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync" class="h-8 w-auto">
                    <span class="text-lg font-bold text-[#121214]">Codice<span class="text-[#5813BC]">Sync</span></span>
                </div>
                <a href="{{ route('login') }}" class="text-xs font-semibold text-[#5813BC] hover:text-[#AC22CB] transition-colors ml-auto flex items-center gap-1">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i>
                    <span>Back to Login</span>
                </a>
            </div>

            <div class="w-full my-auto">
                {{ $slot }}
            </div>

            <div class="pt-6 mt-6 border-t border-gray-100 text-center">
                <p class="text-[11px] text-gray-400 font-medium">
                    &copy; 2026 CodiceSync - Developed by <span class="font-bold text-[#5813BC]">CodiceSync Solutions</span>
                </p>
            </div>
        </div>

    </div>

</body>
</html>
