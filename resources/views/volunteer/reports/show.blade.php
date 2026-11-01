@extends('layouts.dashboard')

@section('title', 'Detail laporan #'.$report->report_id)

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('dashboard.volunteer') }}#laporan-menunggu">← Kembali ke laporan masuk</a>
            <span class="eyebrow"><span class="eyebrow-dot"></span>Detail pemeriksaan</span>
            <h1>Laporan #{{ $report->report_id }}</h1>
            <p>Periksa kelengkapan laporan dan catat rekomendasi tindak lanjut.</p>
        </div>
        <span class="status-badge status-{{ $report->status }}">{{ ['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'forwarded' => 'Diteruskan', 'completed' => 'Selesai'][$report->status] ?? str($report->status)->headline() }}</span>
    </div>

    @if (session('status'))
        <div class="form-status volunteer-status" role="status">{{ session('status') }}</div>
    @endif

    <div class="volunteer-detail-grid">
        <div>
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
                    <div class="detail-section volunteer-evidence-section">
                        <span class="detail-label">Foto bukti</span>
                        <img class="evidence-preview" src="{{ route('volunteer.reports.evidence', $report) }}" alt="Foto bukti laporan #{{ $report->report_id }}">
                        <a class="link-muted" href="{{ route('volunteer.reports.evidence', $report) }}" target="_blank" rel="noopener">Buka foto ukuran penuh</a>
                    </div>
                @endif
            </section>
        </div>

        <aside class="volunteer-review-column">
            @if ($report->status === 'pending')
                <form class="report-form" method="POST" action="{{ route('volunteer.reports.reviews.store', $report) }}">
                    @csrf
                    <section class="form-section">
                        <div class="form-section-heading">
                            <span class="form-step">01</span>
                            <div><h2>Catatan pemeriksaan</h2><p>Catat kelengkapan, kewajaran, atau informasi yang perlu dikonfirmasi.</p></div>
                        </div>
                        <div class="field">
                            <label for="review_note">Catatan relawan</label>
                            <textarea id="review_note" name="review_note" rows="6" required maxlength="10000" placeholder="Tuliskan hasil pemeriksaan laporan...">{{ old('review_note') }}</textarea>
                            @error('review_note') <span class="field-error">{{ $message }}</span> @enderror
                        </div>
                        <label class="review-flag">
                            <input type="checkbox" name="followed_up_required" value="1" @checked(old('followed_up_required'))>
                            <span><strong>Perlu diteruskan ke koordinator</strong><small>Tandai jika laporan membutuhkan tindak lanjut atau pemeriksaan lanjutan.</small></span>
                        </label>
                        @error('followed_up_required') <span class="field-error">{{ $message }}</span> @enderror
                        @if ($errors->any())
                            <div class="form-alert" role="alert"><p>Catatan pemeriksaan belum dapat disimpan. Periksa isian di atas.</p></div>
                        @endif
                        <button class="button" type="submit">Simpan hasil pemeriksaan</button>
                    </section>
                </form>
            @else
                <section class="notice-card volunteer-reviewed-notice">
                    <strong>Laporan sudah ditinjau.</strong>
                    <p>Status tindak lanjut berikutnya menjadi kewenangan koordinator.</p>
                </section>
            @endif

            <section class="form-section volunteer-history">
                <div class="form-section-heading">
                    <span class="form-step">02</span>
                    <div><h2>Riwayat pemeriksaan</h2><p>Catatan relawan yang sudah tercatat.</p></div>
                </div>
                @forelse ($report->reviews->sortByDesc('review_date') as $review)
                    <article class="volunteer-review-entry">
                        <div class="volunteer-review-entry-heading">
                            <strong>{{ $review->volunteer?->name ?? 'Relawan' }}</strong>
                            <time>{{ $review->review_date?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</time>
                        </div>
                        <p>{{ $review->review_note }}</p>
                        @if ($review->followed_up_required)
                            <span class="role-badge">Perlu diteruskan ke koordinator</span>
                        @endif
                    </article>
                @empty
                    <p class="volunteer-no-reviews">Belum ada catatan pemeriksaan.</p>
                @endforelse
            </section>
        </aside>
    </div>

    <section class="volunteer-related-section">
        <div class="page-heading">
            <div><span class="eyebrow"><span class="eyebrow-dot"></span>Perbandingan lokasi</span><h2>Laporan lain di area yang sama</h2><p>Area dicocokkan berdasarkan kabupaten/kota dan provinsi.</p></div>
        </div>
        @if ($relatedReports->isEmpty())
            <div class="empty-state"><h2>Belum ada laporan terkait</h2><p>Belum ada laporan lain pada kabupaten/kota dan provinsi yang sama.</p></div>
        @else
            <div class="volunteer-report-list">
                @foreach ($relatedReports as $relatedReport)
                    <article class="volunteer-report-card">
                        <div class="volunteer-report-heading">
                            <div><span class="report-date">Laporan #{{ $relatedReport->report_id }}</span><h3>{{ $relatedReport->address?->city_regency }}, {{ $relatedReport->address?->province }}</h3></div>
                            <span class="status-badge status-{{ $relatedReport->status }}">{{ ['pending' => 'Menunggu', 'reviewed' => 'Ditinjau', 'forwarded' => 'Diteruskan', 'completed' => 'Selesai'][$relatedReport->status] ?? str($relatedReport->status)->headline() }}</span>
                        </div>
                        <p class="volunteer-report-description">{{ str($relatedReport->report_description)->limit(200) }}</p>
                        <a class="card-link" href="{{ route('volunteer.reports.show', $relatedReport) }}">Buka laporan terkait →</a>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
@endsection
