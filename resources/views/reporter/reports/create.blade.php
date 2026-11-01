@extends('layouts.dashboard')

@section('title', 'Buat Laporan')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('reporter.reports.index') }}">← Kembali ke laporan</a>
            <h1>Buat laporan kejadian</h1>
            <p>Lengkapi informasi di bawah ini agar laporan dapat segera ditinjau.</p>
        </div>
    </div>

    @include('reporter.reports._form', [
        'report' => $report,
        'action' => route('reporter.reports.store'),
        'submitLabel' => 'Kirim laporan',
    ])
@endsection
