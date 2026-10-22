<div class="item-pagination-container px-1">
@if ($paginator->hasPages())
    @include('vendor.pagination._go-to-page', ['paginator' => $paginator])
    <div class="small text-muted">
        {!! __('Showing') !!}
        <span class="fw-semibold">{{ $paginator->firstItem() }}</span>
        {!! __('to') !!}
        <span class="fw-semibold">{{ $paginator->lastItem() }}</span>
        {!! __('of') !!}
        <span class="fw-semibold">{{ $paginator->total() }}</span>
        {!! __('results') !!}
    </div>
    <div class="d-flex justify-content-between align-items-center">
        <ul class="gs-pagination mb-0">
            {{-- First Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="First page">
                    <span class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline><polyline points="9 18 3 12 9 6"></polyline></svg>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->url(1) }}" aria-label="First page" class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline><polyline points="9 18 3 12 9 6"></polyline></svg>
                    </a>
                </li>
            @endif

            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </span>
                </li>
            @else
                <li>
                    <a href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')" class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $window = 5;
    $half = floor($window / 2);

    if ($last <= $window) {
        $start = 1; $end = $last;
    } elseif ($current <= $half + 1) {
        $start = 1; $end = $window;
    } elseif ($current >= $last - $half) {
        $start = $last - $window + 1; $end = $last;
    } else {
        $start = $current - $half; $end = $current + $half;
    }
@endphp

@for ($page = $start; $page <= $end; $page++)
    <li class="{{ $page == $current ? 'active' : '' }}">
        @if ($page == $current)
            <span>{{ $page }}</span>
        @else
            <a href="{{ $paginator->url($page) }}">{{ $page }}</a>
        @endif
    </li>
@endfor

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')" class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                    </span>
                </li>
            @endif

            {{-- Last Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a href="{{ $paginator->url($last) }}" aria-label="Last page" class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline><polyline points="15 18 21 12 15 6"></polyline></svg>
                    </a>
                </li>
            @else
                <li class="disabled" aria-disabled="true" aria-label="Last page">
                    <span class="pagination-arrow-left-right-icon">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline><polyline points="15 18 21 12 15 6"></polyline></svg>
                    </span>
                </li>
            @endif
        </ul>
    </div>
@endif
</div>