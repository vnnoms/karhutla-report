<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title') · Karhutla Report</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="auth-page">
            <section class="auth-panel">
                <div class="auth-panel-inner">
                    <a class="brand auth-brand" href="{{ route('home') }}">
                        <span class="brand-mark" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 4c-8 0-14 3-14 10a6 6 0 0 0 6 6c7 0 8-8 8-16Z" />
                                <path d="M4 21c3-5 7-8 12-11" />
                            </svg>
                        </span>
                        <span>Karhutla Report</span>
                    </a>
                    @yield('content')
                </div>
            </section>
            <aside class="auth-art" aria-label="Ilustrasi hutan">
                <div class="auth-art-content">
                    <span class="auth-art-kicker">Jaga hutan, jaga masa depan</span>
                    <h2>Informasi cepat.<br>Respons lebih sigap.</h2>
                    <p>Setiap laporan membantu tim di lapangan memahami kondisi dan mengambil langkah yang tepat.</p>
                </div>
            </aside>
        </main>
    </body>
</html>
