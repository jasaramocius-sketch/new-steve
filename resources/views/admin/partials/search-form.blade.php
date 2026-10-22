@php
    $size = $size ?? null;
    $showClear = $showClear ?? (bool)request('search');
    $clearRoute = $clearRoute ?? $route;
@endphp
<form action="{{ $route }}" method="GET" class="d-flex gap-2 align-items-center">
    <input type="hidden" name="page" value="">
    <div class="input-group <!--{{ $size ? ' input-group-' . $size : '' }}-->">
        <button type="submit" class="input-group-text bg-white" title="Search" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fas fa-search"></i>
        </button>
        <input type="text"
               name="search"
               class="form-control"
               placeholder="{{ $placeholder }}"
               value="{{ request('search') }}">
    </div>
    @if($showClear)
        <a href="{{ $clearRoute }}" class="btn btn-lg btn-outline-danger d-flex align-items-center" title="Clear search" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fas fa-times"></i>
        </a>
    @endif
</form>
