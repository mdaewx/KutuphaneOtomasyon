@if ($paginator->hasPages())
    <nav aria-label="Sayfalama" class="d-flex justify-content-center">
        <ul class="pagination pagination-sm">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">«</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">«</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                        @else
                            <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">»</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">»</span>
                </li>
            @endif
        </ul>
    </nav>
    
    @if ($paginator->total() > 0)
    <div class="text-center mt-2">
        <p class="small text-muted mb-0">
            {{ $paginator->total() }} sonuçtan {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} arası
        </p>
    </div>
    @endif
@endif
