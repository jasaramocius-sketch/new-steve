@php
    $initialFeatureLines = collect(is_string($initialFeatures ?? null) ? preg_split('/\r\n|\r|\n/', $initialFeatures) : [])
        ->map(fn ($line) => trim((string) $line))
        ->filter(fn ($line) => $line !== '')
        ->values()
        ->all();
@endphp
<div class="key-features-preview mt-2" id="keyFeaturesPreview">
    <small class="text-muted d-block mb-1">Preview</small>
    <ul class="key-features-list">
        @forelse($initialFeatureLines as $feature)
            <li>{!! $feature !!}</li>
        @empty
            <li class="key-features-empty text-muted">No features added yet...</li>
        @endforelse
    </ul>
</div>
<script>
(function() {
    var input = document.querySelector('textarea[name="features"]');
    var previewList = document.querySelector('#keyFeaturesPreview .key-features-list');
    if (!input || !previewList) return;

    function renderFeatures() {
        var lines = input.value.split(/\r\n|\r|\n/).map(function(line) {
            return line.trim();
        }).filter(function(line) {
            return line !== '';
        });
        previewList.innerHTML = '';
        if (lines.length === 0) {
            var empty = document.createElement('li');
            empty.className = 'key-features-empty text-muted';
            empty.textContent = 'No features added yet...';
            previewList.appendChild(empty);
            return;
        }
        lines.forEach(function(line) {
            var li = document.createElement('li');
            li.innerHTML = line;
            previewList.appendChild(li);
        });
    }

    input.addEventListener('input', renderFeatures);
})();
</script>