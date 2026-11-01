@extends('layouts.dashboard')

@section('title', 'Dashboard Coordinator')

@section('content')
    <section class="dashboard-banner">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ruang coordinator</span>
        <h1>Selamat datang, {{ auth()->user()->name }}.</h1>
        <p>Ruang coordinator membantu mengatur tindak lanjut laporan dan koordinasi dengan pihak yang menangani kejadian.</p>
    </section>
    <div class="dashboard-grid">
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">▤</span>
            <h2>Ringkasan laporan</h2>
            <p>Lihat jumlah laporan, rekap wilayah, dan rincian terbaru.</p>
            <a class="card-link" href="{{ route('coordinator.reports.recap') }}">Buka rekap data laporan</a>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">↗</span>
            <h2>Koordinasi tindak lanjut</h2>
            <p>Informasi penerusan kejadian akan membantu menghubungkan tim terkait.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">⌖</span>
            <h2>Pantau area</h2>
            <p>Data wilayah dan kondisi menjadi dasar dalam menentukan prioritas respons.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">U</span>
            <h2>Manajemen pengguna</h2>
            <p>Lihat pengguna dan kelola role reporter serta volunteer.</p>
            <a class="card-link" href="{{ route('coordinator.users.index') }}">Buka manajemen pengguna</a>
        </article>
    </div>
@endsection
