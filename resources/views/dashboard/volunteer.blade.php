@extends('layouts.dashboard')

@section('title', 'Dashboard Volunteer')

@section('content')
    <section class="dashboard-banner">
        <span class="eyebrow"><span class="eyebrow-dot"></span> Ruang volunteer</span>
        <h1>Selamat bertugas, {{ auth()->user()->name }}.</h1>
        <p>Ruang volunteer menjadi pusat untuk meninjau laporan dan membantu memastikan informasi kejadian tersampaikan dengan baik.</p>
    </section>
    <div class="dashboard-grid">
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">▤</span>
            <h2>Tinjau laporan</h2>
            <p>Laporan yang perlu ditinjau akan muncul di ruang kerja volunteer.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">✓</span>
            <h2>Catatan pemeriksaan</h2>
            <p>Catatan tindak lanjut akan membantu koordinator memahami kondisi lapangan.</p>
        </article>
        <article class="dashboard-card">
            <span class="dashboard-card-icon" aria-hidden="true">⌖</span>
            <h2>Informasi lokasi</h2>
            <p>Lokasi dan detail kejadian menjadi bagian penting dari proses verifikasi.</p>
        </article>
    </div>
@endsection
