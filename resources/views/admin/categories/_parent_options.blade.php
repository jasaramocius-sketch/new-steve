@foreach($categories as $cat)
    @if(!empty($excluded) && in_array($cat->id, $excluded))
        @continue
    @endif
    <option value="{{ $cat->id }}" {{ $selected && (string)$selected === (string)$cat->id ? 'selected' : '' }}>
        {{ str_repeat("\u{00A0}\u{00A0}\u{00A0}\u{00A0}", $depth) }}{{ $cat->name }}
    </option>
    @if($cat->children->count())
        @include('admin.categories._parent_options', ['categories' => $cat->children, 'depth' => $depth + 1, 'selected' => $selected, 'excluded' => $excluded ?? []])
    @endif
@endforeach
