<!doctype html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Karhutla Report · Jaga hutan bersama</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <div class="page-shell">
            <header class="site-header">
                <a class="brand" href="{{ route('home') }}">
                    <span class="brand-mark" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 4c-8 0-14 3-14 10a6 6 0 0 0 6 6c7 0 8-8 8-16Z" />
                            <path d="M4 21c3-5 7-8 12-11" />
                        </svg>
                    </span>
                    <span>Karhutla Report</span>
                </a>
                <nav class="header-actions" aria-label="Navigasi utama">
                    @auth
                        <a class="link-muted" href="{{ route('dashboard') }}">Dashboard</a>
                    @else
                        <a class="link-muted" href="{{ route('login') }}">Masuk</a>
                        <a class="button" href="{{ route('register') }}">Buat akun</a>
                    @endauth
                </nav>
            </header>

            <main>
                <section class="hero">
                    <div class="hero-copy">
                        <span class="eyebrow"><span class="eyebrow-dot"></span> Peduli hutan Indonesia</span>
                        <h1>Jaga hutan,<br><span>mulai dari</span><br>sebuah laporan.</h1>
                        <p>Karhutla Report membantu masyarakat dan tim lapangan berbagi informasi kejadian kebakaran hutan dengan lebih terarah.</p>
                        <div class="hero-actions">
                            @auth
                                <a class="button" href="{{ route('dashboard') }}">Buka dashboard <span aria-hidden="true">→</span></a>
                            @else
                                <a class="button" href="{{ route('register') }}">Mulai melapor <span aria-hidden="true">→</span></a>
                                <a class="button button-secondary" href="{{ route('login') }}">Saya sudah punya akun</a>
                            @endauth
                        </div>
                    </div>
                    <div class="hero-art" role="img" aria-label="Ilustrasi lanskap hutan hijau">
                        <span class="sun" aria-hidden="true"></span>
                        <span class="art-label">Lindungi alam kita</span>
                    </div>
                </section>
            </main>
            <footer class="site-footer">Informasi yang tepat membantu respons yang lebih cepat.</footer>
        </div>
    </body>
</html>
