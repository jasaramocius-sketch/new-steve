<li>
    <label class="form-check-label sc-checkbox">
        <input type="checkbox" class="form-check-input me-2 sc-category-cb"
               data-label="{{ $category->name }}"
               data-url="{{ route('category', $category->slug) }}"
               @checked($selectedLabels->contains($category->name))>
        {{ $category->name }}
    </label>

    @if($category->childrenRecursive && $category->childrenRecursive->count())
        <ul class="list-unstyled ps-4">
            @foreach($category->childrenRecursive as $child)
                @include('admin.settings.search-categories-tree', ['category' => $child, 'selectedLabels' => $selectedLabels])
            @endforeach
        </ul>
    @endif
</li>
