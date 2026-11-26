@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination" class="flex items-center justify-between gap-3 py-4">
        @if ($paginator->onFirstPage())
            <span class="btn btn-sm btn-outline opacity-40">Previous</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="btn btn-sm btn-outline">Previous</a>
        @endif
        <span class="text-sm text-muted">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>
        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="btn btn-sm btn-outline">Next</a>
        @else
            <span class="btn btn-sm btn-outline opacity-40">Next</span>
        @endif
    </nav>
@endif
