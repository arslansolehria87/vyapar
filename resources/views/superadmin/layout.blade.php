<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ @filemtime(public_path('favicon.svg')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) ?: time() }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}">

    <title>@yield('title', 'Super Admin') — CodiceSync SaaS Hub</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --cs-dark: #121214;
            --cs-surface: #1E1E26;
            --cs-purple: #5813BC;
            --cs-magenta: #AC22CB;
            --cs-royal: #673B92;
            --cs-accent: #795DA8;
            --cs-lavender: #D4C2DF;
            --cs-light: #F3F3FF;
            --cs-muted: #575656;
            --cs-gradient: linear-gradient(135deg, #5813BC 0%, #AC22CB 100%);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #F8F9FD;
            color: #121214;
            min-height: 100vh;
        }

        /* Top Bar */
        .sa-topbar {
            background: var(--cs-dark);
            border-bottom: 1px solid rgba(88, 19, 188, 0.25);
            padding: 12px 24px;
        }

        .sa-nav-link {
            color: var(--cs-lavender);
            font-size: 13px;
            font-weight: 600;
            padding: 8px 14px;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.2s;
        }

        .sa-nav-link:hover, .sa-nav-link.active {
            color: #ffffff;
            background: rgba(88, 19, 188, 0.3);
        }

        .btn-cs {
            background: var(--cs-gradient);
            color: #ffffff;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            padding: 9px 18px;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .btn-cs:hover {
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 4px 15px rgba(172, 34, 203, 0.4);
        }

        .card-stat {
            background: #ffffff;
            border: 1px solid #E8E5F0;
            border-radius: 16px;
            padding: 22px;
            box-shadow: 0 4px 20px rgba(88, 19, 188, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .card-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(88, 19, 188, 0.08);
        }

        .card-box {
            background: #ffffff;
            border: 1px solid #E8E5F0;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(88, 19, 188, 0.03);
        }

        /* Modern Toggle Switch */
        .form-check-input.toggle-saas:checked {
            background-color: #10B981;
            border-color: #10B981;
        }
        .form-check-input.toggle-saas {
            width: 2.75rem;
            height: 1.45rem;
            cursor: pointer;
        }
    </style>
</head>

<body>

    <!-- Super Admin Header -->
    <header class="sa-topbar sticky-top">
        <div class="container-fluid d-flex align-items-center justify-content-between">
            
            <!-- Left Brand -->
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('superadmin.dashboard') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="{{ asset('images/codice-sync-logo.png') }}" alt="CodiceSync" style="height: 32px; width: auto; object-fit: contain;">
                    <div>
                        <span class="fw-bold text-white fs-5" style="letter-spacing: -0.3px;">Codice<span style="color: var(--cs-magenta)">Sync</span></span>
                        <span class="badge ms-2" style="background: rgba(172, 34, 203, 0.25); color: #E0D4F5; font-size: 10px; border: 1px solid rgba(172, 34, 203, 0.4);">
                            SaaS Master Hub
                        </span>
                    </div>
                </a>

                <!-- Nav links -->
                <nav class="d-none d-md-flex align-items-center gap-1 ms-4">
                    <a href="{{ route('superadmin.dashboard') }}" class="sa-nav-link {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-gauge-high me-1.5"></i> Dashboard
                    </a>
                    <a href="{{ route('superadmin.tenants.index') }}" class="sa-nav-link {{ request()->routeIs('superadmin.tenants.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users me-1.5"></i> Customers & Businesses
                    </a>
                </nav>
            </div>

            <!-- Right Actions -->
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('superadmin.tenants.create') }}" class="btn btn-cs btn-sm d-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-plus"></i>
                    <span>New Customer</span>
                </a>

                <a href="{{ route('dashboard') }}" class="btn btn-outline-light btn-sm rounded-3 py-1.5 px-3" style="font-size: 12px; border-color: rgba(255,255,255,0.2);">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Open POS App
                </a>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-dark btn-sm rounded-circle d-flex align-items-center justify-content-center p-0"
                            style="width: 34px; height: 34px; background: var(--cs-surface); border: 1px solid var(--cs-purple);"
                            data-bs-toggle="dropdown">
                        <i class="fa-solid fa-user-shield text-light" style="font-size: 13px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" style="font-size: 13px;">
                        <li class="px-3 py-2 text-muted small border-bottom">
                            Signed in as<br><strong class="text-dark">{{ auth()->user()->email }}</strong>
                        </li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger py-2">
                                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </header>

    <!-- Main Content Area -->
    <main class="py-4">
        <div class="container-fluid px-4">
            
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-xs border-0 mb-4" role="alert">
                    <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-xs border-0 mb-4" role="alert">
                    <i class="fa-solid fa-circle-exclamation me-2"></i> {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')

</body>
</html>
