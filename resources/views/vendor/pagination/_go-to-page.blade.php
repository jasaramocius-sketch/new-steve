@if ($paginator->lastPage() > 1)
    <form method="GET" action="{{ $paginator->path() }}" class="d-flex align-items-center gap-2 Go-to-page">
        @foreach(request()->except('page') as $key => $value)
            @if(is_scalar($value) && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label for="gs-go-to-page-number" class="small text-muted text-nowrap">Go to page</label>
        <input type="number" id="gs-go-to-page-number" name="page" class="form-control form-control-sm" style="width: 50px; border-radius: 8px;" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}" aria-label="Page number">
        <button type="submit" class="btn btn-sm btn-outline-primary steve-btn min-width-30 min-height-30" aria-label="Go to selected page">
            <i class="fas fa-arrow-right"></i>
        </button>
    </form>
@endif
