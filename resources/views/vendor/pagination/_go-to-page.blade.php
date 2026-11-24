@if ($paginator->lastPage() > 1)
    <form method="GET" action="{{ $paginator->path() }}" class="d-grid align-items-center gap-2 flex-wrap Go-to-page">
        <div class="d-flex align-items-center gap-2">
        @foreach(request()->except('page') as $key => $value)
            @if(is_scalar($value) && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <label for="gs-go-to-page-number" class="small text-muted text-nowrap">Go to</label>
        <input type="number" id="gs-go-to-page-number" name="page" class="form-control" style="width: 60px; border-radius: 8px;" min="1" max="{{ $paginator->lastPage() }}" value="{{ $paginator->currentPage() }}" aria-label="Page number" pleceholder="Page">
        <button type="submit" class="btn btn-sm btn-outline-primary steve-btn min-width-30 min-height-30 d-flex align-items-center" aria-label="Go to selected page">
            <i class="fas fa-arrow-right"></i>
        </button></div>
    </form>
@endif
