@extends('layouts.dashboard')

@section('title', 'Detail laporan #'.$report->report_id)

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('coordinator.reports.recap') }}">← Kembali ke rekap laporan</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Pemantauan coordinator</span>
            <h1>Detail laporan #{{ $report->report_id }}</h1>
            <p>Informasi laporan, hasil pemeriksaan relawan, dan tindak lanjut.</p>
        </div>
        <span class="status-badge status-{{ $report->status }}">{{ ['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'forwarded' => 'Diteruskan', 'completed' => 'Selesai'][$report->status] ?? str($report->status)->headline() }}</span>
    </div>

    @if (session('status'))
        <div class="form-status coordinator-report-status" role="status">{{ session('status') }}</div>
    @endif

    <div class="coordinator-report-grid">
        <section class="detail-card">
            <div class="detail-section">
                <span class="detail-label">Deskripsi kejadian</span>
                <p class="detail-description">{{ $report->report_description }}</p>
            </div>
            <div class="detail-grid">
                <div class="detail-section"><span class="detail-label">Waktu kejadian</span><p>{{ $report->incident_time?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</p></div>
                <div class="detail-section"><span class="detail-label">Tingkat kondisi</span><p>{{ ['low' => 'Rendah', 'medium' => 'Sedang', 'high' => 'Tinggi', 'critical' => 'Kritis'][$report->condition_level] ?? str($report->condition_level)->headline() }}</p></div>
            </div>
            <div class="detail-section">
                <span class="detail-label">Alamat kejadian</span>
                <p>{{ $report->address->detail_address }}</p>
                <p>{{ $report->address->city_regency }}, {{ $report->address->province }}, Pulau {{ $report->address->island }}</p>
                @if ($report->latitude !== null && $report->longitude !== null)
                    <p>Koordinat: {{ $report->latitude }}, {{ $report->longitude }}</p>
                @endif
            </div>
            <div class="detail-section">
                <span class="detail-label">Informasi pelapor</span>
                <p>{{ $report->reporter?->name ?? 'Pengguna tidak ditemukan' }}</p>
                @if ($report->reporter?->phone_number)
                    <p>Telepon: {{ $report->reporter->phone_number }}</p>
                @endif
            </div>
            @if ($report->evidence_file)
                <div class="detail-section coordinator-evidence-section">
                    <span class="detail-label">Foto bukti</span>
                    <img class="evidence-preview" src="{{ route('coordinator.reports.evidence', $report) }}" alt="Foto bukti laporan #{{ $report->report_id }}">
                    <a class="link-muted" href="{{ route('coordinator.reports.evidence', $report) }}" target="_blank" rel="noopener">Buka foto ukuran penuh</a>
                </div>
            @endif
        </section>

        <aside class="coordinator-report-sidebar">
            @if ($report->status === 'pending')
                <section class="notice-card coordinator-report-notice"><strong>Menunggu pemeriksaan relawan.</strong><p>Relawan perlu meninjau laporan sebelum coordinator mencatat penerusan.</p></section>
            @elseif ($report->status === 'reviewed')
                <form class="report-form" method="POST" action="{{ route('coordinator.reports.forwardings.store', $report) }}">
                    @csrf
                    <section class="form-section">
                        <div class="form-section-heading"><span class="form-step">01</span><div><h2>Teruskan laporan</h2><p>Catat instansi atau pihak tujuan tindak lanjut.</p></div></div>
                        <div class="field">
                            <label for="destination_name">Tujuan penerusan</label>
                            <input id="destination_name" name="destination_name" type="text" maxlength="150" value="{{ old('destination_name') }}" required placeholder="Contoh: BPBD Kabupaten ...">
                            @error('destination_name') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <div class="field">
                            <label for="forwarding_note">Catatan penerusan <span class="field-hint">(opsional)</span></label>
                            <textarea id="forwarding_note" name="forwarding_note" rows="4" maxlength="10000" placeholder="Informasi tambahan untuk pihak tujuan...">{{ old('forwarding_note') }}</textarea>
                            @error('forwarding_note') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <button class="button" type="submit">Simpan penerusan</button>
                    </section>
                </form>
            @elseif ($report->status === 'forwarded')
                <section class="notice-card coordinator-report-notice"><strong>Laporan sudah diteruskan.</strong><p>Setelah tindak lanjut dikonfirmasi, coordinator dapat menandai laporan selesai.</p></section>
                <form method="POST" action="{{ route('coordinator.reports.complete', $report) }}" onsubmit="return confirm('Tandai laporan ini sebagai selesai?')">
                    @csrf
                    <button class="button coordinator-complete-button" type="submit">Tandai selesai</button>
                </form>
            @else
                <section class="notice-card coordinator-report-notice"><strong>Penanganan laporan selesai.</strong><p>Riwayat pemeriksaan dan penerusan tersimpan di bawah.</p></section>
            @endif

            <section class="form-section coordinator-history-section">
                <div class="form-section-heading"><span class="form-step">02</span><div><h2>Catatan relawan</h2><p>Hasil pemeriksaan dan rekomendasi tindak lanjut.</p></div></div>
                @forelse ($report->reviews->sortByDesc('review_date') as $review)
                    <article class="coordinator-history-entry">
                        <div class="coordinator-history-meta"><strong>{{ $review->volunteer?->name ?? 'Relawan' }}</strong><time>{{ $review->review_date?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</time></div>
                        <p>{{ $review->review_note }}</p>
                        @if ($review->followed_up_required)
                            <span class="role-badge">Direkomendasikan untuk diteruskan</span>
                        @else
                            <span class="role-badge">Tidak memerlukan penerusan</span>
                        @endif
                    </article>
                @empty
                    <p class="coordinator-history-empty">Belum ada catatan pemeriksaan relawan.</p>
                @endforelse
            </section>

            <section class="form-section coordinator-history-section">
                <div class="form-section-heading"><span class="form-step">03</span><div><h2>Riwayat penerusan</h2><p>Tujuan dan catatan yang pernah dikirim.</p></div></div>
                @forelse ($report->forwardings->sortByDesc('forwarded_at') as $forwarding)
                    <article class="coordinator-history-entry">
                        <div class="coordinator-history-meta"><strong>{{ $forwarding->destination_name }}</strong><time>{{ $forwarding->forwarded_at?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</time></div>
                        <p>{{ $forwarding->forwarding_note ?: 'Tidak ada catatan tambahan.' }}</p>
                        <span class="coordinator-forwarded-by">Dicatat oleh {{ $forwarding->coordinator?->name ?? 'Coordinator' }}</span>
                    </article>
                @empty
                    <p class="coordinator-history-empty">Belum ada penerusan yang dicatat.</p>
                @endforelse
            </section>
        </aside>
    </div>

    <section class="volunteer-related-section coordinator-related-section">
        <div class="page-heading"><div><span class="eyebrow"><span class="eyebrow-dot"></span>Perbandingan lokasi</span><h2>Laporan lain di area yang sama</h2><p>Dicocokkan berdasarkan kabupaten/kota dan provinsi.</p></div></div>
        @if ($relatedReports->isEmpty())
            <div class="empty-state"><h2>Belum ada laporan terkait</h2><p>Belum ada laporan lain di kabupaten/kota dan provinsi yang sama.</p></div>
        @else
            <div class="volunteer-report-list">
                @foreach ($relatedReports as $relatedReport)
                    <article class="volunteer-report-card">
                        <div class="volunteer-report-heading">
                            <div><span class="report-date">Laporan #{{ $relatedReport->report_id }}</span><h3>{{ $relatedReport->address?->city_regency }}, {{ $relatedReport->address?->province }}</h3></div>
                            <span class="status-badge status-{{ $relatedReport->status }}">{{ ['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'forwarded' => 'Diteruskan', 'completed' => 'Selesai'][$relatedReport->status] ?? str($relatedReport->status)->headline() }}</span>
                        </div>
                        <p class="volunteer-report-description">{{ str($relatedReport->report_description)->limit(200) }}</p>
                        <a class="card-link" href="{{ route('coordinator.reports.show', $relatedReport) }}">Buka laporan terkait →</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
