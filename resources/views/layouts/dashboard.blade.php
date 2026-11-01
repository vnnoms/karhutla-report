<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') · Karhutla Report</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="dashboard-page">
        <div class="page-shell">
            <header class="dashboard-header">
                <a class="brand" href="{{ route('dashboard') }}">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 4c-8 0-14 3-14 10a6 6 0 0 0 6 6c7 0 8-8 8-16Z" />
                            <path d="M4 21c3-5 7-8 12-11" />
                        </svg>
                    </span>
                    <span>Karhutla Report</span>
                </a>
                <div class="header-actions">
                    @if (auth()->user()->role === 'reporter')
                        <a class="link-muted report-nav-link" href="{{ route('reporter.reports.index') }}">Laporan saya</a>
                    @endif
                    <div class="user-chip">
                        <span class="user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}</span>
                        <span>{{ auth()->user()->name }}</span>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="button button-secondary" type="submit">Keluar</button>
                    </form>
                </div>
            </header>

            <main class="dashboard-main">
                @yield('content')
            </main>
            <footer class="site-footer">Karhutla Report · Bersama menjaga hutan Indonesia</footer>
        </div>
    </body>
</html>
