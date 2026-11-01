@if ($paginator->hasPages())
    <nav class="recap-pagination" role="navigation" aria-label="Navigasi halaman laporan">
        <p>Menampilkan {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} dari {{ $paginator->total() }} laporan</p>
        <div class="recap-pagination-actions">
            @if ($paginator->onFirstPage())
                <span class="button button-secondary recap-page-disabled" aria-disabled="true">← Sebelumnya</span>
            @else
                <a class="button button-secondary" href="{{ $paginator->previousPageUrl() }}" rel="prev">← Sebelumnya</a>
            @endif

            @if ($paginator->hasMorePages())
                <a class="button" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya →</a>
            @else
                <span class="button recap-page-disabled" aria-disabled="true">Berikutnya →</span>
            @endif
        </div>
    </nav>
@endif
