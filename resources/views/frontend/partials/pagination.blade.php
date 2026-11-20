@php use App\Support\Frontend; @endphp
@if ($paginator->hasPages())
    <nav class="pager" aria-label="{{ __('Pages') }}">
        @if ($paginator->onFirstPage())
            <span class="pg disabled"><i class="mdi mdi-chevron-left"></i></span>
        @else
            <a class="pg" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="{{ __('Previous') }}"><i class="mdi mdi-chevron-left"></i></a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="pg dots">…</span>
            @endif
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="pg active" aria-current="page">{{ Frontend::num($page) }}</span>
                    @else
                        <a class="pg" href="{{ $url }}">{{ Frontend::num($page) }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="pg" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="{{ __('Next') }}"><i class="mdi mdi-chevron-right"></i></a>
        @else
            <span class="pg disabled"><i class="mdi mdi-chevron-right"></i></span>
        @endif
    </nav>
@endif
