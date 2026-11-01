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
            <p><strong>{{ number_format($pendingReports->total(), 0, ',', '.') }} laporan</strong> menunggu pemeriksaan.</p>
            <a class="card-link" href="#laporan-menunggu">Lihat laporan masuk</a>
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
    <section class="volunteer-inbox" id="laporan-menunggu">
        <div class="page-heading">
            <div>
                <span class="eyebrow"><span class="eyebrow-dot"></span>Ruang kerja volunteer</span>
                <h2>Laporan yang perlu ditinjau</h2>
                <p>Laporan baru berstatus menunggu ditampilkan di sini.</p>
            </div>
        </div>

        @if ($pendingReports->isEmpty())
            <div class="empty-state">
                <span class="empty-state-icon" aria-hidden="true">✓</span>
                <h2>Belum ada laporan baru</h2>
                <p>Semua laporan yang masuk akan muncul di ruang kerja ini untuk ditinjau.</p>
            </div>
        @else
            <div class="volunteer-report-list">
                @foreach ($pendingReports as $report)
                    <article class="volunteer-report-card">
                        <div class="volunteer-report-heading">
                            <div>
                                <span class="report-date">Laporan #{{ $report->report_id }}</span>
                                <h3>{{ $report->address?->city_regency ?? 'Wilayah tidak tersedia' }}, {{ $report->address?->province ?? 'Provinsi tidak tersedia' }}</h3>
                            </div>
                            <span class="status-badge">Menunggu</span>
                        </div>
                        <p class="volunteer-report-description">{{ str($report->report_description)->limit(280) }}</p>
                        <div class="volunteer-report-meta">
                            <span><strong>Pelapor:</strong> {{ $report->reporter?->name ?? 'Pengguna tidak ditemukan' }}</span>
                            <span><strong>Waktu kejadian:</strong> {{ $report->incident_time?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</span>
                            <span><strong>Kondisi:</strong> {{ ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'][$report->condition_level] ?? str($report->condition_level)->headline() }}</span>
                            @if ($report->evidence_file)
                                <span class="volunteer-evidence-note">Foto bukti tersedia</span>
                            @endif
                        </div>
                        <a class="button button-secondary volunteer-report-link" href="{{ route('volunteer.reports.show', $report) }}">Buka detail dan tinjau laporan</a>
                    </article>
                @endforeach
            </div>
            <div class="pagination-wrap">{{ $pendingReports->links('pagination.recap') }}</div>
        @endif
    </section>
@endsection
