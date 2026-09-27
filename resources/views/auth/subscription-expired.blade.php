<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ @filemtime(public_path('favicon.svg')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}">

    <title>Subscription Notice — CodiceSync</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        .cs-ambient-glow {
            background-color: #121214;
            background-image: 
                radial-gradient(circle at 20% 20%, rgba(172, 34, 203, 0.25) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(88, 19, 188, 0.3) 0%, transparent 50%);
        }
        .btn-cs-gradient {
            background: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%);
        }
        .btn-cs-gradient:hover {
            background: linear-gradient(135deg, #673B92 0%, #B82FDE 100%);
        }
    </style>
</head>

<body class="min-h-screen cs-ambient-glow flex items-center justify-center p-4 sm:p-6 text-white antialiased select-none">

    <div class="w-full max-w-lg bg-[#1B1B22]/90 backdrop-blur-xl border border-[#5813BC]/30 rounded-3xl p-8 sm:p-10 shadow-[0_20px_60px_rgba(0,0,0,0.6)] text-center relative overflow-hidden">
        
        <!-- Subtle Top Glow Bar -->
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-gradient-to-r from-[#5813BC] via-[#AC22CB] to-[#5813BC]"></div>

        <!-- Brand Emblem -->
        <div class="mx-auto mb-6 flex justify-center">
            <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync" class="h-14 w-auto object-contain">
        </div>

        <!-- Badge -->
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#5813BC]/20 border border-[#AC22CB]/40 text-[#D4C2DF] text-xs font-bold uppercase tracking-wider mb-4">
            <span class="w-2 h-2 rounded-full bg-[#AC22CB] animate-pulse"></span>
            <span>Subscription Status Notice</span>
        </div>

        <!-- Main Title -->
        <h1 class="text-2xl sm:text-3xl font-extrabold text-white tracking-tight">
            Workspace Access Paused
        </h1>

        @php
            $companyName = session('company_name') ?: (auth()->user()?->currentCompany()?->name ?: 'Your Business');
            $reason = session('reason') ?: 'expired';
            $expiredAt = session('expired_at');
        @endphp

        <p class="text-sm text-[#D4C2DF]/80 mt-3 leading-relaxed">
            Access for <strong class="text-white font-semibold">{{ $companyName }}</strong> is currently inactive.
        </p>

        <!-- Zero Data Loss Assurance Box -->
        <div class="my-6 p-4 rounded-2xl bg-[#121214] border border-[#5813BC]/30 text-left flex items-start gap-3.5">
            <div class="w-8 h-8 rounded-xl bg-[#5813BC]/20 text-[#AC22CB] flex items-center justify-center shrink-0 mt-0.5">
                <i class="fa-solid fa-shield-halved text-sm"></i>
            </div>
            <div>
                <h4 class="text-xs font-bold text-white uppercase tracking-wider">Your Data is 100% Safe</h4>
                <p class="text-xs text-[#D4C2DF]/70 mt-0.5 leading-normal">
                    All your sales, products, stock counts, and financial records are securely preserved. Access will be instantly restored as soon as your monthly plan is renewed.
                </p>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="space-y-3">
            <a href="https://wa.me/923214530103?text={{ urlencode('Hello CodiceSync Support, I would like to renew/reactivate subscription for my business: ' . $companyName) }}"
               target="_blank"
               class="w-full py-3.5 px-6 rounded-xl btn-cs-gradient text-white font-bold text-sm shadow-[0_4px_20px_rgba(172,34,203,0.35)] hover:shadow-[0_6px_25px_rgba(172,34,203,0.5)] transition-all flex items-center justify-center gap-2">
                <i class="fa-brands fa-whatsapp text-lg"></i>
                <span>WhatsApp Support: 0321-4530103</span>
            </a>

            <div class="bg-white/5 border border-white/10 rounded-xl p-3 text-xs text-[#D4C2DF] space-y-1">
                <div>📞 Direct Line: <strong class="text-white">0371-0045282</strong> / <strong class="text-white">0321-4530103</strong></div>
                <div>✉️ Email: <a href="mailto:codicesync@gmail.com" class="text-[#AC22CB] hover:underline font-semibold">codicesync@gmail.com</a></div>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full py-2.5 px-4 rounded-xl bg-[#2A2A33] hover:bg-[#343440] text-xs font-semibold text-[#D4C2DF] transition-colors">
                    <i class="fa-solid fa-right-from-bracket mr-1.5"></i> Sign Out
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-4 border-t border-white/5 text-[11px] text-[#575656]">
            CodiceSync Software Solutions — Customer Support Team
        </div>

    </div>

</body>
</html>
