@php
    $currentMode = old('design_mode', isset($page) ? $page->designMode() : 'classic');
    $modes = [
        'classic' => 'Classic content editor',
        'builder' => 'Page Builder blocks',
        'both' => 'Classic + Builder (legacy)',
    ];
@endphp

<label class="form-label mb-1" for="design_mode">Design mode</label>
<select name="design_mode" id="design_mode" class="form-select mb-1 @error('design_mode') is-invalid @enderror">
    @foreach ($modes as $value => $label)
        <option value="{{ $value }}" {{ $currentMode === $value ? 'selected' : '' }}>{{ $label }}</option>
    @endforeach
</select>
@error('design_mode') <div class="invalid-feedback">{{ $message }}</div> @enderror
<div class="form-text mb-3">
    The classic editor and Page Builder are separate editors. Pick one design mode so their designs do not mix on the live page.
    @if (isset($page) && count($page->getBlocks()))
        This page currently has {{ count($page->getBlocks()) }} Page Builder block(s).
    @endif
</div>
