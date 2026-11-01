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
            <p>Kirim informasi kejadian kebakaran hutan agar tim dapat mulai menindaklanjutinya.</p>
            <a class="card-link" href="{{ route('reporter.reports.create') }}">Buat laporan <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">◷</span>
            <h2>Laporan saya</h2>
            <p>Lihat kembali laporan yang sudah Anda kirim dan pantau status penanganannya.</p>
            <a class="card-link" href="{{ route('reporter.reports.index') }}">Lihat laporan <span aria-hidden="true">→</span></a>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">⌂</span>
            <h2>Jaga lingkungan</h2>
            <p>Informasi yang akurat membantu tim merespons lebih cepat dan terarah.</p>
        </article>
    </div>
@endsection
