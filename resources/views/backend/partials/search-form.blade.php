@php
    $embedded = $embedded ?? false;
    $size = $size ?? null;
    $route = $route ?? request()->url();
    $searchValue = (string) request('search', '');
    $hasSearch = trim($searchValue) !== '';
    $showClear = $showClear ?? $hasSearch;
    $clearRoute = $clearRoute ?? ($embedded ? request()->fullUrlWithQuery(['search' => null, 'page' => null]) : $route);
    $inputClass = $inputClass ?? 'form-control';
    $inputId = $inputId ?? null;
    $inputGroupClass = $inputGroupClass ?? '';
    $inputGroupStyle = $inputGroupStyle ?? null;
@endphp
@if(! $embedded)
<form action="{{ $route }}" method="GET" class="d-flex gap-2 align-items-center admin-search-form">
    <input type="hidden" name="page" value="">
@endif
    <div class="input-group {{ $size ? 'input-group-' . $size : '' }} {{ $inputGroupClass }}" @if($inputGroupStyle) style="{{ $inputGroupStyle }}" @endif>
        <button type="submit"
                class="input-group-text btn steve-btn gap-1 admin-search-toggle {{ $hasSearch ? 'btn-outline-danger' : 'btn-primary' }}"
                title="{{ $hasSearch ? 'Clear search' : 'Search' }}"
                data-bs-toggle="tooltip"
                data-bs-placement="top"
                data-clear-url="{{ $clearRoute }}"
                data-submitted="{{ $searchValue }}">
            @if($hasSearch)
                <i class="fas fa-times"></i>
            @else
                <i class="fas fa-search"></i>
            @endif
        </button>
        <input type="text"
               name="search"
               class="{{ $inputClass }}"
               @if($inputId) id="{{ $inputId }}" @endif
               placeholder="{{ $placeholder }}"
               value="{{ $searchValue }}">
    </div>
    @if($showClear && ! $hasSearch)
        <a href="{{ $clearRoute }}" class="btn btn-outline-danger d-flex align-items-center h-40px w-40px justify-content-center" title="Clear search" data-bs-toggle="tooltip" data-bs-placement="top">
            <i class="fas fa-times"></i>
        </a>
    @endif
@if(! $embedded)
</form>
@endif

@once
<script>
(function () {
    if (window.__adminSearchToggleInit) return;
    window.__adminSearchToggleInit = true;

    function isClearMode(input, btn) {
        var value = input.value.trim();
        var submitted = (btn.getAttribute('data-submitted') || '').trim();
        return value !== '' && value === submitted;
    }

    function setButtonState(form) {
        var input = form.querySelector('input[name="search"]');
        var btn = form.querySelector('.admin-search-toggle');
        if (!input || !btn) return;

        var clearMode = isClearMode(input, btn);
        btn.classList.toggle('btn-primary', !clearMode);
        btn.classList.toggle('btn-outline-danger', clearMode);
        btn.innerHTML = clearMode ? '<i class="fas fa-times"></i>' : '<i class="fas fa-search"></i>';

        var title = clearMode ? 'Clear search' : 'Search';
        btn.setAttribute('title', title);

        if (window.bootstrap && window.bootstrap.Tooltip) {
            var tip = window.bootstrap.Tooltip.getInstance(btn);
            if (tip && typeof tip.setContent === 'function') {
                tip.setContent({'.tooltip-inner': title});
            }
        }
    }

    document.addEventListener('input', function (e) {
        if (!e.target.matches('input[name="search"]')) return;
        var form = e.target.closest('form');
        if (!form || !form.querySelector('.admin-search-toggle')) return;
        setButtonState(form);
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.admin-search-toggle');
        if (!btn) return;

        var form = btn.closest('form');
        if (!form) return;

        var input = form.querySelector('input[name="search"]');
        if (!input || !isClearMode(input, btn)) return; // allow the form to submit (search)

        e.preventDefault();
        window.location.href = btn.getAttribute('data-clear-url');
    });
})();
</script>
@endonce
