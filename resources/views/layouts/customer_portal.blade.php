<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Freight Portal')</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    @livewireStyles
    <style>
        body { background: #f4f6f8; }
        .portal-topbar { background: #fff; border-bottom: 1px solid #e2e5e8; padding: 12px 24px; margin-bottom: 24px; }
        .portal-topbar .brand { font-weight: 600; }
        .portal-container { max-width: 1100px; margin: 0 auto; padding: 0 15px 40px; }
    </style>
</head>
<body>

    <div class="portal-topbar">
        <div class="portal-container" style="padding:0; display:flex; justify-content:space-between; align-items:center;">
            <span class="brand"><i class="fa fa-ship"></i> Freight Portal</span>
            @auth('customer')
                <span>
                    <a href="{{ route('customer.dashboard') }}">My Jobs</a>
                    &nbsp;|&nbsp;
                    <a href="{{ route('customer.trips') }}">My Trips</a>
                    &nbsp;|&nbsp;
                    {{ Auth::guard('customer')->user()->name }}
                    &nbsp;&mdash;&nbsp;
                    <a href="{{ route('customer.logout') }}">Logout</a>
                </span>
            @endauth
        </div>
    </div>

    <div class="portal-container">
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </div>

    @livewireScripts
</body>
</html>
