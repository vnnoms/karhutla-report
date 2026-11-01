@extends('layouts.dashboard')

@section('title', 'Dashboard Reporter')

@section('content')
    <section class="dashboard-banner">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ruang reporter</span>
        <h1>Halo, {{ auth()->user()->name }}.</h1>
        <p>Akun reporter Anda sudah siap. Dari sini Anda dapat memantau informasi dan aktivitas pelaporan kebakaran hutan.</p>
    </section>
    <div class="dashboard-grid">
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">⌖</span>
            <h2>Laporkan kejadian</h2>
            <p>Alur pengiriman laporan kebakaran hutan akan tersedia di ruang reporter ini.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">◷</span>
            <h2>Pantau status</h2>
            <p>Perkembangan laporan Anda akan ditampilkan setelah fitur pelaporan diaktifkan.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">⌂</span>
            <h2>Jaga lingkungan</h2>
            <p>Informasi yang akurat membantu tim merespons lebih cepat dan terarah.</p>
        </article>
    </div>
@endsection
