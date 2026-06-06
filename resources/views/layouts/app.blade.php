<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'Vyapar')</title>
  <meta name="description" content="@yield('description', 'Vyapar billing and accounting software.')">
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
  @endphp
 window.App = {
    isAuthenticated: @json(Auth::check()),
    user: {
      ...@json(Auth::user()?->only('id', 'name')),
      role: @json(Auth::user()?->role ?? ''),
      roles: @json(Auth::user()?->roles()->pluck('name')->toArray() ?? []),
      permissions: @json(Auth::user()?->getAllPermissions() ?? [])
    },
    logoutUrl: "{{ route('logout') }}",
    csrfToken: "{{ csrf_token() }}",
    current_company_id: @json(session('current_company_id') ?? Auth::user()?->current_company_id ?? null),
    generalSettings: @json($generalSidebarSettings)
  };
</script>

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

