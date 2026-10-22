@php
    $selected = $selectedCategoryId ?? '';
    $selectedAdditional = $selectedAdditionalIds ?? [];
    if (! is_array($selectedAdditional)) {
        $selectedAdditional = $selectedAdditional ? [$selectedAdditional] : [];
    }
@endphp
<div class="blog-category-field">
    <!-- <label class="form-label">Category</label> -->

    <div class="input-group">
        <select name="blog_category_id" class="form-control @error('blog_category_id') is-invalid @enderror">
            <option value="">None</option>

            @foreach($blogCategories as $cat)
                <option value="{{ $cat->id }}" {{ $selected == $cat->id ? 'selected' : '' }}>
                    {{ $cat->name }}
                </option>

                @foreach($cat->children as $child)
                    <option value="{{ $child->id }}" {{ $selected == $child->id ? 'selected' : '' }}>
                        &nbsp;&nbsp;&nbsp;{{ $child->name }}
                    </option>
                @endforeach
            @endforeach
        </select>

        <button
            type="button"
            class="btn btn-outline-secondary"
            title="Add New Category"
            onclick="var d=this.closest('.blog-category-field').querySelector('.blog-new-category'); if (d) d.classList.toggle('d-none');"
        >
            <i class="fas fa-plus"></i>
        </button>
    </div>

    @error('blog_category_id')
        <div class="invalid-feedback d-block">{{ $message }}</div>
    @enderror

    <div class="mt-2">
        <label class="form-label small mb-1 d-block">Additional Categories (optional — check one or more)</label>
        <div class="blog-additional-categories border rounded p-2 bg-white">
            @php $additionalSelected = collect($selectedAdditionalIds ?? []); @endphp
            @foreach($blogCategories as $cat)
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        name="additional_categories[]"
                        value="{{ $cat->id }}"
                        id="additional-cat-{{ $cat->id }}"
                        @checked($additionalSelected->contains($cat->id))
                    >
                    <label class="form-check-label small" for="additional-cat-{{ $cat->id }}">{{ $cat->name }}</label>
                </div>

                @foreach($cat->children as $child)
                    <div class="form-check ms-4">
                        <input
                            class="form-check-input"
                            type="checkbox"
                            name="additional_categories[]"
                            value="{{ $child->id }}"
                            id="additional-cat-{{ $child->id }}"
                            @checked($additionalSelected->contains($child->id))
                        >
                        <label class="form-check-label small" for="additional-cat-{{ $child->id }}">{{ $child->name }}</label>
                    </div>
                @endforeach
            @endforeach
        </div>

        @error('additional_categories')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>

    <div class="blog-new-category {{ old('new_category') ? '' : 'd-none' }} mt-2 p-2 border rounded bg-light">
        <div class="row g-2">
            <div class="col-md-6">
                <label class="form-label small mb-1">New Category Name *</label>
                <input
                    type="text"
                    name="new_category"
                    value="{{ old('new_category') }}"
                    class="form-control form-control-sm @error('new_category') is-invalid @enderror"
                    placeholder="e.g. Suspension"
                    autocomplete="off"
                >
            </div>

            <div class="col-md-6">
                <label class="form-label small mb-1">Parent Category</label>
                <select name="new_category_parent_id" class="form-control form-control-sm">
                    <option value="">— Top Level —</option>
                    @foreach($blogCategories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('new_category_parent_id') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        @error('new_category')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>