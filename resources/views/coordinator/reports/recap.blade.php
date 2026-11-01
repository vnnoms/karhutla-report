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

    <section class="recap-regions" aria-label="Rekap laporan berdasarkan wilayah">
        <article class="recap-panel">
            <div class="recap-panel-heading"><div><h2>Menurut provinsi</h2><p>Jumlah laporan mengikuti filter aktif.</p></div></div>
            @if ($reportsByProvince->isEmpty())
                <p class="recap-empty-inline">Belum ada laporan untuk filter ini.</p>
            @else
                <ul class="recap-region-list">
                    @foreach ($reportsByProvince as $region)
                        <li><span>{{ $region->province }}</span><strong>{{ number_format($region->total, 0, ',', '.') }}</strong></li>
                    @endforeach
                </ul>
            @endif
        </article>
        <article class="recap-panel">
            <div class="recap-panel-heading"><div><h2>Menurut kabupaten/kota</h2><p>Jumlah laporan mengikuti filter aktif.</p></div></div>
            @if ($reportsByCity->isEmpty())
                <p class="recap-empty-inline">Belum ada laporan untuk filter ini.</p>
            @else
                <ul class="recap-region-list">
                    @foreach ($reportsByCity as $region)
                        <li><span><strong>{{ $region->city_regency }}</strong><small>{{ $region->province }}</small></span><strong>{{ number_format($region->total, 0, ',', '.') }}</strong></li>
                    @endforeach
                </ul>
            @endif
        </article>
    </section>

    <section class="recap-panel recap-table-panel">
        <div class="recap-panel-heading recap-table-heading">
            <div><h2>Daftar laporan</h2><p>{{ number_format($reports->total(), 0, ',', '.') }} laporan sesuai filter.</p></div>
        </div>

        <form class="recap-filters" method="GET" action="{{ route('coordinator.reports.recap') }}">
            <div class="field">
                <label for="province">Provinsi</label>
                <select id="province" name="province" onchange="this.form.elements.city_regency.value = ''; this.form.requestSubmit()">
                    <option value="">Semua provinsi</option>
                    @foreach ($provinces as $province)
                        <option value="{{ $province }}" @selected(($filters['province'] ?? '') === $province)>{{ $province }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="city_regency">Kabupaten/kota</label>
                <select id="city_regency" name="city_regency">
                    <option value="">Semua kabupaten/kota</option>
                    @foreach ($cityOptions as $city)
                        <option value="{{ $city }}" @selected(($filters['city_regency'] ?? '') === $city)>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label for="status">Status laporan</label>
                <select id="status" name="status">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ $statusLabels[$status] ?? str($status)->headline() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="recap-filter-actions">
                <button class="button" type="submit">Terapkan filter</button>
                <a class="button button-secondary" href="{{ route('coordinator.reports.recap') }}">Atur ulang</a>
            </div>
        </form>

        @if ($reports->isEmpty())
            <div class="empty-state recap-empty-state">
                <span class="empty-state-icon" aria-hidden="true">⌕</span>
                <h2>Belum ada laporan</h2>
                <p>Belum ada laporan yang cocok dengan filter yang dipilih.</p>
            </div>
        @else
            <div class="recap-table-scroll">
                <table class="recap-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Reporter</th>
                            <th>Deskripsi</th>
                            <th>Waktu kejadian</th>
                            <th>Kabupaten/kota</th>
                            <th>Provinsi</th>
                            <th>Kondisi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reports as $report)
                            <tr>
                                <td>#{{ $report->report_id }}</td>
                                <td>{{ $report->reporter?->name ?? 'Pengguna tidak ditemukan' }}</td>
                                <td class="recap-description">{{ str($report->report_description)->limit(120) }}</td>
                                <td>{{ $report->incident_time?->locale('id')->translatedFormat('d M Y, H:i') ?? '—' }}</td>
                                <td>{{ $report->address?->city_regency ?? '—' }}</td>
                                <td>{{ $report->address?->province ?? '—' }}</td>
                                <td>{{ $conditionLabels[$report->condition_level] ?? str($report->condition_level)->headline() }}</td>
                                <td><span class="status-badge status-{{ $report->status }}">{{ $statusLabels[$report->status] ?? str($report->status)->headline() }}</span></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="pagination-wrap">{{ $reports->links('pagination.recap') }}</div>
        @endif
    </section>
@endsection
