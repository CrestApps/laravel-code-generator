@if ($paginator->hasPages())
<nav class="d-flex justify-content-center">
    <ul class="pagination">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <li class="page-item disabled">
            	<a href="#" class="page-link"><i class="fa-solid fa-angle-left"></i></a>
            </li>
        @else
            <li class="page-item">
            	<a  class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="fa-solid fa-angle-left"></i></a>
            </li>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <li class="page-item disabled"><span>{{ $element }}</span></li>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <li class="page-item active">
							<a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @else
                        <li class="page-item">
                        	<a class="page-link" href="{{ $url }}">{{ $page }}</a>
                        </li>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <li class="page-item">
            	<a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next"><i class="fa-solid fa-angle-right"></i></a>
            </li>
        @else
            <li class="page-item disabled">
            	<a href="#" class="page-link"><i class="fa-solid fa-angle-right"></i></a>
            </li>
        @endif
    </ul>
</nav>
@endif
