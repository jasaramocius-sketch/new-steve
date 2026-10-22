<div class="blog-tag-input">
    <input type="hidden" name="tags" class="blog-tag-hidden" value="{{ $selectedTags }}" data-all-tags="{{ $allTags }}">
    <input type="text" class="form-control blog-tag-text" placeholder="Type or paste tags separated by commas, or press Enter" autocomplete="off">
    <div class="blog-tag-suggest" style="display:none"></div>
    <small class="text-muted">Type or paste tags separated by commas (e.g. "brake pads, engine oil"). Each tag appears as a chip; everything is saved automatically with the blog.</small>
    <div class="blog-tag-chips"></div>
</div>

<style>
    .blog-tag-input { position: relative; }
    .blog-tag-chips { display: flex; flex-wrap: wrap; gap: .375rem; margin-bottom: .5rem; }
    .blog-tag-chips:empty { display: none; }
    .blog-tag-chip {
        display: inline-flex;
        align-items: center;
        gap: .375rem;
        padding: .2rem .625rem;
        background: rgba(31,3,0,.05);
        border: 1px solid rgba(31,3,0,.15);
        border-radius: 999px;
        font-size: .8125rem;
        color: #1f0300;
    }
    .blog-tag-chip .blog-tag-remove {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 20px;
        height: 20px;
        border: 0;
        border-radius: 50%;
        background: rgba(31,3,0,.15);
        color: #1f0300;
        font-size: 1rem;
        line-height: 1;
        padding: 0;
        cursor: pointer;
    }
    .blog-tag-chip .blog-tag-remove:hover { background: #e62e04; color: #fff; }
    .blog-tag-suggest {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        z-index: 50;
        margin-top: .1875rem;
        background: #fff;
        border: 1px solid #e9ecef;
        border-radius: .375rem;
        box-shadow: 0 .25rem .5rem rgba(31,3,0,.08);
        max-height: 180px;
        overflow-y: auto;
    }
    .blog-tag-suggest .blog-tag-suggest-item {
        padding: .375rem .625rem;
        font-size: .875rem;
        cursor: pointer;
    }
    .blog-tag-suggest .blog-tag-suggest-item:hover,
    .blog-tag-suggest .blog-tag-suggest-item.active { background: #eef1f4; }
</style>

@push('scripts')
<script>
(function() {
    var $wrapper = document.querySelector('.blog-tag-input');
    if (!$wrapper) return;
    var hidden = $wrapper.querySelector('.blog-tag-hidden');
    var chipsBox = $wrapper.querySelector('.blog-tag-chips');
    var textInput = $wrapper.querySelector('.blog-tag-text');
    var suggestBox = $wrapper.querySelector('.blog-tag-suggest');

    var allTags = (hidden.getAttribute('data-all-tags') || '').split(',').filter(Boolean);
    var selected = (hidden.value || '').split(',').map(function(t) { return t.trim(); }).filter(Boolean);

    function uniqueTags(arr) {
        var seen = {};
        return arr.filter(function(t) {
            var k = t.toLowerCase();
            if (seen[k]) return false;
            seen[k] = true;
            return true;
        });
    }

    function syncHidden() {
        hidden.value = uniqueTags(selected).join(',');
    }

    function renderChips() {
        chipsBox.innerHTML = '';
        selected.forEach(function(tag) {
            if (!tag) return;
            var chip = document.createElement('span');
            chip.className = 'blog-tag-chip';
            var label = document.createElement('span');
            label.textContent = tag;
            var remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'blog-tag-remove';
            remove.setAttribute('aria-label', 'Remove tag');
            remove.innerHTML = '&times;';
            remove.addEventListener('click', function() {
                selected = selected.filter(function(t) { return t.toLowerCase() !== tag.toLowerCase(); });
                renderChips();
                syncHidden();
            });
            chip.appendChild(label);
            chip.appendChild(remove);
            chipsBox.appendChild(chip);
        });
        syncHidden();
    }

    function addTag(raw) {
        var tag = (raw || '').trim().replace(/^#/, '').replace(/,/g, '');
        if (!tag) return;
        var exists = selected.some(function(t) { return t.toLowerCase() === tag.toLowerCase(); });
        if (!exists) selected.push(tag);
        renderChips();
        hideSuggest();
        textInput.value = '';
    }

    function commitText(raw) {
        String(raw || '').split(',').forEach(function(part) {
            addTag(part);
        });
    }

    function showSuggest(items) {
        suggestBox.innerHTML = '';
        if (!items.length) { hideSuggest(); return; }
        items.forEach(function(tag) {
            var item = document.createElement('div');
            item.className = 'blog-tag-suggest-item';
            item.textContent = tag;
            item.addEventListener('click', function() {
                addTag(tag);
            });
            suggestBox.appendChild(item);
        });
        suggestBox.style.display = 'block';
    }

    function hideSuggest() { suggestBox.style.display = 'none'; suggestBox.innerHTML = ''; }

    function currentSuggestions() {
        return allTags.filter(function(t) {
            return !selected.some(function(s) { return s.toLowerCase() === t.toLowerCase(); });
        });
    }

    textInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' || e.key === ',') {
            e.preventDefault();
            var active = suggestBox.querySelector('.blog-tag-suggest-item.active');
            if (active) { addTag(active.textContent); }
            else { commitText(textInput.value); }
        } else if (e.key === 'Backspace' && !textInput.value && selected.length) {
            selected.pop();
            renderChips();
        } else if (e.key === 'Escape') {
            hideSuggest();
        }
    });

    textInput.addEventListener('input', function() {
        var v = textInput.value;
        if (v.indexOf(',') !== -1) {
            var parts = v.split(',');
            var last = parts.pop();
            commitText(parts.join(','));
            textInput.value = last.trimStart();
        }
        var openItems = suggestBox.querySelectorAll('.blog-tag-suggest-item');
        [].slice.call(openItems).forEach(function(el) { el.classList.remove('active'); });
        var q = textInput.value.trim().toLowerCase();
        var base = currentSuggestions();
        var filtered = q ? base.filter(function(t) { return t.toLowerCase().indexOf(q) !== -1; }) : base;
        if (filtered.length) showSuggest(filtered.slice(0, 8)); else hideSuggest();
    });

    textInput.addEventListener('blur', function() {
        setTimeout(function() { hideSuggest(); }, 120);
    });

    document.addEventListener('keydown', function(e) {
        var openItems = suggestBox.querySelectorAll('.blog-tag-suggest-item');
        if (!openItems.length) return;
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            var idx = [].slice.call(openItems).findIndex(function(el) { return el.classList.contains('active'); });
            openItems.forEach(function(el) { el.classList.remove('active'); });
            if (e.key === 'ArrowDown') {
                var next = (idx + 1) % openItems.length;
                openItems[next].classList.add('active');
                openItems[next].scrollIntoView({ block: 'nearest' });
            } else {
                var prev = idx <= 0 ? openItems.length - 1 : idx - 1;
                openItems[prev].classList.add('active');
                openItems[prev].scrollIntoView({ block: 'nearest' });
            }
        }
    });

    var form = $wrapper.closest('form');
    if (form) {
        form.addEventListener('submit', function() {
            commitText(textInput.value);
        });
    }

    renderChips();
})();
</script>
@endpush