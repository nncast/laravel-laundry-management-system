@if ($paginator->hasPages())
<nav class="pagination-nav" role="navigation" aria-label="Pagination">
    @if (method_exists($paginator, 'total'))
    <div class="pagination-info">
        Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}
    </div>
    @endif

    <ul class="pagination">
        @if ($paginator->onFirstPage())
            <li class="disabled" aria-disabled="true"><span aria-label="Previous">&lsaquo;</span></li>
        @else
            <li><a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Previous">&lsaquo;</a></li>
        @endif

        @if (isset($elements))
            @foreach ($elements as $element)
                @if (is_string($element))
                    <li class="disabled" aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active" aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li><a href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach
        @endif

        @if ($paginator->hasMorePages())
            <li><a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Next">&rsaquo;</a></li>
        @else
            <li class="disabled" aria-disabled="true"><span aria-label="Next">&rsaquo;</span></li>
        @endif
    </ul>
</nav>
@endif
