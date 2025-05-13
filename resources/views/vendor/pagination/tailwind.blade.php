@if ($paginator->hasPages())
    <div class="flex flex-col items-center">
        <!-- Pagination -->
        <div class="inline-flex mt-2 xs:mt-0">
            <!-- Previous -->
            @if ($paginator->onFirstPage())
                <button class="px-3 py-1 text-sm rounded-l bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500">
                    «
                </button>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" rel="prev" class="px-3 py-1 text-sm rounded-l bg-white text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    «
                </a>
            @endif
            
            <!-- Pages -->
            <div class="inline-flex">
                @foreach ($elements as $element)
                    <!-- Dots separator -->
                    @if (is_string($element))
                        <span class="px-3 py-1 text-sm text-gray-700 bg-white dark:bg-gray-800 dark:text-gray-400">
                            {{ $element }}
                        </span>
                    @endif
                    
                    <!-- Links array -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="px-3 py-1 text-sm text-white bg-blue-500 dark:bg-blue-600">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}" class="px-3 py-1 text-sm text-gray-700 bg-white hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                                    {{ $page }}
                                </a>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>
            
            <!-- Next -->
            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" rel="next" class="px-3 py-1 text-sm rounded-r bg-white text-gray-700 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400 dark:hover:bg-gray-700">
                    »
                </a>
            @else
                <button class="px-3 py-1 text-sm rounded-r bg-gray-100 text-gray-400 cursor-not-allowed dark:bg-gray-700 dark:text-gray-500">
                    »
                </button>
            @endif
        </div>
        
        <!-- Page info -->
        @if ($paginator->total() > 0)
        <div class="text-sm text-gray-500 mt-2 dark:text-gray-400">
            {{ $paginator->total() }} sonuçtan {{ $paginator->firstItem() ?? 0 }}-{{ $paginator->lastItem() ?? 0 }} arası
        </div>
        @endif
    </div>
@endif
