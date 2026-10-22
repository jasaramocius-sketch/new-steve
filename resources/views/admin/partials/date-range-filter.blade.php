@php
    $filterRoute = $filterRoute ?? null;
    $dateFrom = $dateFrom ?? request('date_from');
    $dateTo = $dateTo ?? request('date_to');
    $clearKeep = array_filter([
        'per_page' => request('per_page'),
        'sort_by' => request('sort_by'),
        'sort_dir' => request('sort_dir'),
    ], fn($v) => $v !== null);
    $clearUrl = request()->url() . ($clearKeep ? '?' . http_build_query($clearKeep) : '');
@endphp
@if($filterRoute)
<form method="GET" action="{{ route($filterRoute) }}" class="d-flex align-items-center gap-2 flex-wrap date-range-filter-form">
    <input type="hidden" name="page" value="">
    @if(request()->has('per_page'))
        <input type="hidden" name="per_page" value="{{ request('per_page') }}">
    @endif
    @if(request()->has('sort_by'))
        <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
        <input type="hidden" name="sort_dir" value="{{ request('sort_dir') }}">
    @endif
    <span class="text-muted small">From</span>
    <input type="date" name="date_from" value="{{ $dateFrom }}" max="{{ $dateTo ?: '' }}" class="form-control form-control-sm w-auto" onchange="if(this.value){var to=this.form.date_to;to.min=this.value;if(to.value && to.value < this.value){to.value=this.value;}if(to.value){this.form.submit();}}else{this.form.date_to.min='';}">
    <span class="text-muted small">To</span>
    <input type="date" name="date_to" value="{{ $dateTo }}" min="{{ $dateFrom ?: '' }}" class="form-control form-control-sm w-auto" onchange="if(this.value){var from=this.form.date_from;from.max=this.value;if(from.value && this.value < from.value){from.value=this.value;}if(from.value){this.form.submit();}}else{this.form.date_from.max='';}">
    @if(request()->has('date_from') || request()->has('date_to'))
        <a href="{{ $clearUrl }}" class="btn btn-sm date-range-filter-clear btn-outline-danger" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Clear filter"><i class="fas fa-times"></i></a>
    @endif
</form>
@endif
