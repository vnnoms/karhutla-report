@extends('layouts.dashboard')

@section('title', 'Detail Laporan')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('reporter.reports.index') }}">← Kembali ke laporan</a>
            <h1>Detail laporan</h1>
            <p>Dikirim {{ $report->created_at->format('d M Y · H:i') }}</p>
        </div>
        <span class="status-badge status-{{ $report->status }}">{{ ucfirst($report->status) }}</span>
    </div>

    @if (session('status'))
        <p class="form-status" role="status">{{ session('status') }}</p>
    @endif

    <article class="detail-card">
        <section class="detail-section">
            <span class="detail-label">Deskripsi laporan</span>
            <p class="detail-description">{{ $report->report_description }}</p>
        </section>
        <div class="detail-grid">
            <section class="detail-section">
                <span class="detail-label">Waktu kejadian</span>
                <p>{{ $report->incident_time->format('d M Y · H:i') }}</p>
            </section>
            <section class="detail-section">
                <span class="detail-label">Tingkat kondisi</span>
                <p>{{ ucfirst($report->condition_level) }}</p>
            </section>
        </div>
        <section class="detail-section">
            <span class="detail-label">Alamat kejadian</span>
            <p>{{ $report->address->detail_address }}</p>
            <p>{{ $report->address->city_regency }}, {{ $report->address->province }}, {{ $report->address->island }}</p>
        </section>
        @if ($report->latitude !== null || $report->longitude !== null)
            <section class="detail-section">
                <span class="detail-label">Koordinat</span>
                <p>{{ $report->latitude ?? '—' }}, {{ $report->longitude ?? '—' }}</p>
            </section>
        @endif
        @if ($report->evidence_file)
            <section class="detail-section evidence-row">
                <div>
                    <span class="detail-label">Foto bukti</span>
                    <p>Foto bukti kejadian yang dikirim bersama laporan.</p>
                </div>
            </section>
            <img class="evidence-preview" src="{{ route('reporter.reports.evidence', $report) }}" alt="Foto bukti laporan karhutla">
        @endif
    </article>

    @if ($report->status === 'pending')
        <div class="detail-actions">
            <a class="button" href="{{ route('reporter.reports.edit', $report) }}">Edit laporan</a>
            <form method="POST" action="{{ route('reporter.reports.destroy', $report) }}" onsubmit="return confirm('Hapus laporan ini? Tindakan ini tidak dapat dibatalkan.')">
                @csrf
                @method('DELETE')
                <button class="button button-danger" type="submit">Hapus laporan</button>
            </form>
        </div>
    @else
        <p class="notice-card">Laporan yang sudah diproses tidak dapat diedit atau dihapus.</p>
    @endif
@endsection
