<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}?v={{ @filemtime(public_path('favicon.svg')) ?: time() }}">
  <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('favicon.png') }}?v={{ @filemtime(public_path('favicon.png')) ?: time() }}">
  <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}?v={{ @filemtime(public_path('favicon-32x32.png')) ?: time() }}">
  <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ @filemtime(public_path('favicon.ico')) ?: time() }}">
  <title>@yield('title', 'CodiceSync')</title>
  <meta name="description" content="@yield('description', 'CodiceSync POS & Business Management')">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <!-- Font Awesome 6 -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
  <!-- Custom Styles -->
  <link href="{{ asset('css/styles.css') }}" rel="stylesheet">

</head>
  @stack('styles')
  
<body data-page="@yield('page')">

<script>
  @php
    $generalSidebarSettings = json_decode((string) \App\Models\AppSetting::getValue('general_settings', '{}'), true) ?: [];
    $currentComp = Auth::user()?->currentCompany();
    $enabledFeatures = $currentComp ? ($currentComp->enabled_features ?? ['*']) : ['*'];
    if (Auth::user()?->is_super_admin || Auth::user()?->id === 1) {
        $enabledFeatures = ['*'];
    }
  @endphp
 window.App = {
    isAuthenticated: @json(Auth::check()),
    user: {
      ...@json(Auth::user()?->only('id', 'name', 'is_super_admin')),
      role: @json(Auth::user()?->role ?? ''),
      roles: @json(Auth::user()?->roles()->pluck('name')->toArray() ?? []),
      permissions: @json(Auth::user()?->getAllPermissions() ?? [])
    },
    logoutUrl: "{{ route('logout') }}",
    csrfToken: "{{ csrf_token() }}",
    current_company_id: @json(session('current_company_id') ?? Auth::user()?->current_company_id ?? null),
    generalSettings: @json($generalSidebarSettings),
    companyName: @json($currentComp?->name ?? 'My Company'),
    enabledFeatures: @json($enabledFeatures),
    isSuperAdmin: @json(Auth::user()?->is_super_admin || Auth::user()?->id === 1),
    isImpersonating: @json(session()->has('impersonated_by'))
  };
</script>

@if(session()->has('impersonated_by'))
<div style="background: linear-gradient(135deg, #5813BC, #AC22CB); color: #fff; padding: 7px 16px; font-size: 13px; font-weight: 600; display: flex; align-items: center; justify-content: space-between; z-index: 99999; position: sticky; top: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.25);">
  <div style="display: flex; align-items: center; gap: 8px;">
    <i class="fa-solid fa-user-secret" style="font-size: 16px;"></i>
    <span>Super Admin Mode: Currently testing/viewing <strong>{{ Auth::user()?->currentCompany()?->name ?? 'Tenant' }}</strong></span>
  </div>
  <a href="{{ route('superadmin.stop-impersonate') }}" style="background: rgba(255,255,255,0.2); color: #fff; text-decoration: none; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: 700; transition: all 0.2s;">
    <i class="fa-solid fa-arrow-left"></i> Exit to Super Admin Hub
  </a>
</div>
@endif

<!-- Navbar & Sidebar injected by components.js -->

<main class="main-content" id="mainContent">
  @yield('content')
</main>

<!-- Page Modals -->
@yield('modals')


<script>
  window.routes = {
    saleCreate: "{{ route('sale.create') }}"
  };
</script>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('js/components.js') }}?v={{ filemtime(public_path('js/components.js')) }}"></script>
<script src="{{ asset('js/common.js') }}"></script>
@stack('scripts')

</body>
</html>

