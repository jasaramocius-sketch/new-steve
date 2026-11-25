@php
    if (! is_array($fitmentRows ?? null) || count($fitmentRows) === 0) {
        $fitmentRows = [['year_from' => '', 'year_to' => '', 'make' => '', 'model' => '', 'engine' => '', 'notes' => '']];
    }
    $fitmentIndex = count($fitmentRows);
@endphp

<datalist id="fitment-year-hint">
    @foreach($vehicleYears as $year)<option value="{{ $year }}"></option>@endforeach
</datalist>
<datalist id="fitment-make-hint">
    @foreach($vehicleMakes as $make)<option value="{{ $make }}"></option>@endforeach
</datalist>
<div class="d-flex justify-content-between align-items-center mb-2 pb-2 flex-wrap gap-2">
    <small class="text-muted">Add one row per year range / make / model combination. Year range, make and model are required.</small>
    @if(isset($product) && $product->exists)
        <div class="d-flex gap-2">
            <a href="{{ route('admin.products.fitments.sample-csv', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-file-csv me-1"></i> Sample CSV
            </a>
            <a href="{{ route('admin.products.fitments.export-csv', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                <i class="fas fa-download me-1"></i> Export CSV
            </a>
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('fitmentImportFile').click()">
                <i class="fas fa-upload me-1"></i> Import CSV/ACES
            </button>
        </div>
    @endif
</div>

<div id="fitment-rows">
    @foreach($fitmentRows as $index => $row)
        <div class="fitment-row row g-2 align-items-end border rounded p-3 mb-2 bg-light">
            <div class="col-6 col-sm-4 col-xl-1">
                <label class="form-label mb-1">Year From *</label>
                <select name="fitments[{{ $index }}][year_from]" class="form-select">
                    <option value="">Select</option>
                    @foreach($vehicleYears as $year)
                        <option value="{{ $year }}" {{ (string) ($row['year_from'] ?? '') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-xl-1">
                <label class="form-label mb-1">Year To *</label>
                <select name="fitments[{{ $index }}][year_to]" class="form-select">
                    <option value="">Select</option>
                    @foreach($vehicleYears as $year)
                        <option value="{{ $year }}" {{ (string) ($row['year_to'] ?? '') === (string) $year ? 'selected' : '' }}>{{ $year }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-sm-4 col-xl-2">
                <label class="form-label mb-1">Make *</label>
                <input type="text" name="fitments[{{ $index }}][make]" class="form-control" list="fitment-make-hint" value="{{ $row['make'] ?? '' }}" placeholder="e.g. Toyota">
            </div>
            <div class="col-6 col-sm-4 col-xl-2">
                <label class="form-label mb-1">Model *</label>
                <input type="text" name="fitments[{{ $index }}][model]" class="form-control" value="{{ $row['model'] ?? '' }}" placeholder="e.g. Camry">
            </div>
            <div class="col-6 col-sm-4 col-xl-2">
                <label class="form-label mb-1">Engine</label>
                <input type="text" name="fitments[{{ $index }}][engine]" class="form-control" value="{{ $row['engine'] ?? '' }}" placeholder="e.g. 2.5L I4">
            </div>
            <div class="col-12 col-sm-8 col-xl-3">
                <label class="form-label mb-1">Notes</label>
                <input type="text" name="fitments[{{ $index }}][notes]" class="form-control" value="{{ $row['notes'] ?? '' }}" placeholder="e.g. Excluding Hybrid">
            </div>
            <div class="col-6 col-sm-4 col-xl-1">
                <div class="action-buttons justify-content-center justify-content-xl-end">
                    <button type="button" class="action-btn btn-view-live" onclick="addFitmentRow()" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add fitment row" aria-label="Add fitment row" title="Add fitment row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    </button>
                    <button type="button" class="action-btn btn-cancel fitment-remove" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Remove fitment" aria-label="Remove fitment" title="Remove fitment">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                    </button>
                </div>
            </div>
        </div>
    @endforeach
</div>

<template id="fitment-template">
    <div class="fitment-row row g-2 align-items-end border rounded p-3 mb-2 bg-light">
        <div class="col-6 col-sm-4 col-xl-1">
            <label class="form-label mb-1">Year From *</label>
            <select name="fitments[__INDEX__][year_from]" class="form-select">
                <option value="">Select</option>
                @foreach($vehicleYears as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-sm-4 col-xl-1">
            <label class="form-label mb-1">Year To *</label>
            <select name="fitments[__INDEX__][year_to]" class="form-select">
                <option value="">Select</option>
                @foreach($vehicleYears as $year)<option value="{{ $year }}">{{ $year }}</option>@endforeach
            </select>
        </div>
        <div class="col-6 col-sm-4 col-xl-2">
            <label class="form-label mb-1">Make *</label>
            <input type="text" name="fitments[__INDEX__][make]" class="form-control" list="fitment-make-hint" placeholder="e.g. Toyota">
        </div>
        <div class="col-6 col-sm-4 col-xl-2">
            <label class="form-label mb-1">Model *</label>
            <input type="text" name="fitments[__INDEX__][model]" class="form-control" placeholder="e.g. Camry">
        </div>
        <div class="col-6 col-sm-4 col-xl-2">
            <label class="form-label mb-1">Engine</label>
            <input type="text" name="fitments[__INDEX__][engine]" class="form-control" placeholder="e.g. 2.5L I4">
        </div>
        <div class="col-12 col-sm-8 col-xl-3">
            <label class="form-label mb-1">Notes</label>
            <input type="text" name="fitments[__INDEX__][notes]" class="form-control" placeholder="e.g. Excluding Hybrid">
        </div>
        <div class="col-6 col-sm-4 col-xl-1">
            <div class="action-buttons justify-content-center justify-content-xl-end">
                <button type="button" class="action-btn btn-view-live" onclick="addFitmentRow()" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Add fitment row" aria-label="Add fitment row" title="Add fitment row">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                </button>
                <button type="button" class="action-btn btn-cancel fitment-remove" data-bs-toggle="tooltip" data-bs-placement="top" data-bs-original-title="Remove fitment" aria-label="Remove fitment" title="Remove fitment">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
(function () {
    const container = document.getElementById('fitment-rows');
    const template = document.getElementById('fitment-template');
    if (!container || !template) return;

    let index = {{ $fitmentIndex }};

    window.addFitmentRow = function () {
        const html = template.innerHTML.replace(/__INDEX__/g, index++);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = html;
        const row = wrapper.firstElementChild;
        container.appendChild(row);

        if (window.bootstrap && window.bootstrap.Tooltip) {
            row.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function (el) {
                new window.bootstrap.Tooltip(el, { trigger: 'hover' });
            });
        }
    };

    container.addEventListener('click', function (event) {
        const remove = event.target.closest('.fitment-remove');
        if (!remove) return;

        const row = remove.closest('.fitment-row');
        const rows = container.querySelectorAll('.fitment-row');
        if (rows.length === 1) {
            row.querySelectorAll('input, select').forEach(function (field) {
                field.value = '';
            });
            return;
        }
        row.remove();
    });
})();
</script>
