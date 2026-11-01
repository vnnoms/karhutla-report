@extends('layouts.dashboard')

@section('title', 'Rekap data laporan')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('dashboard.coordinator') }}">← Dashboard coordinator</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Data laporan</span>
            <h1>Rekap data laporan</h1>
            <p>Ringkasan dan rincian laporan yang tercatat di sistem.</p>
        </div>
    </div>

    <section class="recap-summary" aria-label="Ringkasan seluruh laporan">
        <article class="recap-stat recap-stat-total"><span>Total laporan</span><strong>{{ number_format($summary->total ?? 0, 0, ',', '.') }}</strong></article>
        <article class="recap-stat"><span>Menunggu</span><strong>{{ number_format($summary->pending ?? 0, 0, ',', '.') }}</strong></article>
        <article class="recap-stat"><span>Ditinjau</span><strong>{{ number_format($summary->reviewed ?? 0, 0, ',', '.') }}</strong></article>
        <article class="recap-stat"><span>Diteruskan</span><strong>{{ number_format($summary->forwarded ?? 0, 0, ',', '.') }}</strong></article>
        <article class="recap-stat"><span>Selesai</span><strong>{{ number_format($summary->completed ?? 0, 0, ',', '.') }}</strong></article>
    </section>

    <p class="recap-live-status" id="recap-live-status" aria-live="polite" aria-atomic="true"></p>
    <div id="recap-results" aria-busy="false">
        @include('coordinator.reports.partials.recap-results')
    </div>
@endsection
