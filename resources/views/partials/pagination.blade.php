{{-- Big, simple page buttons instead of Laravel's small numbered links. --}}
@if ($paginator->hasPages())
    <nav aria-label="Pages" class="mt-6 flex flex-wrap items-center justify-between gap-3">
        @if ($paginator->onFirstPage())
            <span class="btn btn-secondary cursor-default opacity-50" aria-disabled="true">← Previous page</span>
        @else
            <a href="{{ $paginator->previousPageUrl() }}" class="btn btn-secondary" rel="prev">← Previous page</a>
        @endif

        <span class="font-semibold text-stone-700">Page {{ $paginator->currentPage() }} of {{ $paginator->lastPage() }}</span>

        @if ($paginator->hasMorePages())
            <a href="{{ $paginator->nextPageUrl() }}" class="btn btn-secondary" rel="next">Next page →</a>
        @else
            <span class="btn btn-secondary cursor-default opacity-50" aria-disabled="true">Next page →</span>
        @endif
    </nav>
@endif
