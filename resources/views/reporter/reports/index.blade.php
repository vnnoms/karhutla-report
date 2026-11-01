@extends('layouts.dashboard')

@section('title', 'Laporan Saya')

@section('content')
    <section class="page-heading">
        <div>
            <span class="eyebrow"><span class="eyebrow-dot"></span> Ruang reporter</span>
            <h1>Laporan saya</h1>
            <p>Semua laporan yang Anda kirim, tersusun berdasarkan waktu terbaru.</p>
        </div>
        <a class="button" href="{{ route('reporter.reports.create') }}">+ Buat laporan</a>
    </section>

    @if (session('status'))
        <p class="form-status" role="status">{{ session('status') }}</p>
    @endif

    @if ($reports->isEmpty())
        <section class="empty-state">
            <span class="empty-state-icon" aria-hidden="true">⌖</span>
            <h2>Belum ada laporan</h2>
            <p>Buat laporan pertama Anda agar informasi kejadian dapat segera ditinjau.</p>
            <a class="button" href="{{ route('reporter.reports.create') }}">Buat laporan pertama</a>
        </section>
    @else
        <div class="report-list">
            @foreach ($reports as $report)
                <article class="report-card">
                    <a class="report-card-main" href="{{ route('reporter.reports.show', $report) }}">
                        <div class="report-card-topline">
                            <span class="report-date">{{ $report->incident_time->format('d M Y · H:i') }}</span>
                            <span class="status-badge status-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
                        </div>
                        <h2>{{ \Illuminate\Support\Str::limit($report->report_description, 125) }}</h2>
                        <p>{{ $report->address->city_regency }}, {{ $report->address->province }} · Kondisi {{ $report->condition_level }}</p>
                    </a>
                    <a class="report-arrow" href="{{ route('reporter.reports.show', $report) }}" aria-label="Lihat detail laporan">→</a>
                </article>
            @endforeach
        </div>

        <div class="pagination-wrap">{{ $reports->links() }}</div>
    @endif
@endsection
