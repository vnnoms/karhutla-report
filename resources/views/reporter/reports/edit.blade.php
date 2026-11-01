@extends('layouts.dashboard')

@section('title', 'Edit Laporan')

@section('content')
    <div class="page-heading">
        <div>
            <a class="back-link" href="{{ route('reporter.reports.show', $report) }}">← Kembali ke detail</a>
            <h1>Edit laporan</h1>
            <p>Laporan dapat diubah selama masih berstatus pending.</p>
        </div>
    </div>

    @include('reporter.reports._form', [
        'report' => $report,
        'action' => route('reporter.reports.update', $report),
        'submitLabel' => 'Simpan perubahan',
    ])
@endsection
