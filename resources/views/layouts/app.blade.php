<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Laravel') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-icons/font/bootstrap-icons.min.css') }}">
    <link rel="stylesheet" href="{{ asset('vendor/adminlte/css/adminlte.min.css') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="{{ asset('vendor/app-theme.css') }}">
    @stack('styles')
    <style>
        /* keep header user dropdown above header bar (AdminLTE can clip it) */
        .app-header { overflow: visible; }
        .app-header .dropdown-menu { z-index: 1030; }
        /* native <details> user menu (no Bootstrap/Popper dependency) */
        .user-menu > summary { list-style: none; cursor: pointer; }
        .user-menu > summary::-webkit-details-marker { display: none; }
        .user-menu .dropdown-menu { display: none; position: absolute; right: 0; z-index: 1030; }
        .user-menu[open] .dropdown-menu { display: block; }
    </style>
</head>
<body class="layout-fixed sidebar-open">
<div class="app-wrapper">

    @include('partials.layout.header')
    @include('partials.layout.sidebar')

    {{-- Main --}}
    <main class="app-main">
        <div class="app-content-header">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-sm-6"><h3 class="mb-0">{{ $title ?? '' }}</h3></div>
                </div>
            </div>
        </div>
        <div class="app-content">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </main>

    @include('partials.layout.footer')
</div>

@include('partials.layout.scripts')
@stack('scripts')
</body>
</html>
