@php
    $initialSpecLines = collect(is_string($initialSpecifications ?? null) ? preg_split('/\r\n|\r|\n/', $initialSpecifications) : [])
        ->map(fn ($line) => trim((string) $line))
        ->filter(fn ($line) => $line !== '')
        ->values()
        ->all();
@endphp
<div class="specifications-preview mt-2" id="specificationsPreview">
    <small class="text-muted d-block mb-1">Preview</small>
    <table class="table table-sm table-bordered mb-0 admin-specs-table">
        <thead class="table-light">
            <tr><th>Label</th><th>Value</th></tr>
        </thead>
        <tbody>
        @forelse($initialSpecLines as $spec)
            @php
                $lbl = $spec;
                $val = '';
                if (str_contains($spec, '::')) {
                    [$lbl, $val] = array_map('trim', explode('::', $spec, 2));
                }
            @endphp
            <tr><td>{{ $lbl }}</td><td>{{ $val }}</td></tr>
        @empty
            <tr><td colspan="2" class="text-muted text-center">No specifications added yet...</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<script>
(function() {
    var input = document.querySelector('textarea[name="specifications"]');
    var previewBody = document.querySelector('#specificationsPreview tbody');
    if (!input || !previewBody) return;

    function renderSpecifications() {
        var lines = input.value.split(/\r\n|\r|\n/).map(function(line) {
            return line.trim();
        }).filter(function(line) {
            return line !== '';
        });
        previewBody.innerHTML = '';
        if (lines.length === 0) {
            var empty = document.createElement('tr');
            empty.innerHTML = '<td colspan="2" class="text-muted text-center">No specifications added yet...</td>';
            previewBody.appendChild(empty);
            return;
        }
        lines.forEach(function(line) {
            var label = line;
            var value = '';
            if (line.indexOf('::') !== -1) {
                label = line.slice(0, line.indexOf('::')).trim();
                value = line.slice(line.indexOf('::') + 2).trim();
            }
            var tr = document.createElement('tr');
            var tdLabel = document.createElement('td');
            tdLabel.textContent = label;
            var tdValue = document.createElement('td');
            tdValue.textContent = value;
            tr.appendChild(tdLabel);
            tr.appendChild(tdValue);
            previewBody.appendChild(tr);
        });
    }

    input.addEventListener('input', renderSpecifications);
})();
</script>