---
name: shop-blog-filters
description: Use when adding, fixing, or clearing filters on shop or blog pages in Laravel — including Clear Filters button, vehicle year/make/model cascading filters, mobile auto grid view, sidebar filter widgets, or blog search/category filters.
---

# Shop & Blog Filters

## Shop Page — Clear Filters Button

```blade
@if(request()->hasAny(['year', 'make', 'model']))
  <a href="{{ route('shop', array_filter(request()->query(), fn($k) => !in_array($k, ['year', 'make', 'model']), ARRAY_FILTER_USE_KEY)) }}"
     class="btn btn-sm btn-outline-danger mb-3">
    <i class="fas fa-times"></i> Clear Filters
  </a>
@endif
```

### Alert Bar Clear (when filters active)
```blade
@if(request()->hasAny(['year', 'make', 'model']))
  <div class="alert alert-warning d-flex justify-content-between align-items-center">
    <span>Filters active: {{ request('year') }} {{ request('make') }} {{ request('model') }}</span>
    <a href="{{ route('shop') }}" class="btn btn-sm btn-danger">Clear All</a>
  </div>
@endif
```

## Blog — Clear Filters Button

```blade
@if(request()->hasAny(['search', 'category']))
  <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-danger mb-3">
    <i class="fas fa-times"></i> Clear Filters
  </a>
@endif
```

### Blog Controller — Pass Category Variable
```php
// BlogController::category() — MUST pass $category to view
public function category($slug) {
    $category = BlogCategory::where('slug', $slug)->firstOrFail();
    $blogs = Blog::where('blog_category_id', $category->id)
        ->where('status', true)
        ->paginate(6);
    $categories = BlogCategory::withCount('blogs')->where('status', true)->get();
    $recentBlogs = Blog::where('status', true)->latest()->take(5)->get();

    return view('blog.index', compact('blogs', 'categories', 'recentBlogs', 'category'));
}
```

## Shop — Vehicle Cascading Filters

```html
<select id="yearFilter" name="year" onchange="filterMakes()">
  <option value="">All Years</option>
  @foreach($years as $year)
    <option value="{{ $year }}" {{ request('year') == $year ? 'selected' : '' }}>{{ $year }}</option>
  @endforeach
</select>

<select id="makeFilter" name="make" onchange="filterModels()">
  <option value="">All Makes</option>
</select>

<select id="modelFilter" name="model">
  <option value="">All Models</option>
</select>
```

```javascript
const vehicleData = @json($vehicleData);

function filterMakes() {
  const year = $('#yearFilter').val();
  const makes = [...new Set(vehicleData.filter(v => !year || v.year == year).map(v => v.make))];
  $('#makeFilter').html('<option value="">All Makes</option>' + makes.map(m => `<option value="${m}" ${m === '{{ request("make") }}' ? 'selected' : ''}>${m}</option>`).join(''));
  filterModels();
}

function filterModels() {
  const year = $('#yearFilter').val();
  const make = $('#makeFilter').val();
  const models = [...new Set(vehicleData.filter(v => (!year || v.year == year) && (!make || v.make == make)).map(v => v.model))];
  $('#modelFilter').html('<option value="">All Models</option>' + models.map(m => `<option value="${m}" ${m === '{{ request("model") }}' ? 'selected' : ''}>${m}</option>`).join(''));
}

$(document).ready(function() { filterMakes(); filterModels(); });
```

### Shop Controller
```php
public function getSharedData() {
    $years = Product::where('status', true)->distinct()->pluck('year')->filter()->sort()->values();
    $makes = Product::where('status', true)->distinct()->pluck('make')->filter()->sort()->values();
    $models = Product::where('status', true)->distinct()->pluck('model')->filter()->sort()->values();
    $vehicleData = Product::where('status', true)
        ->select('year', 'make', 'model')
        ->distinct()
        ->get()
        ->toArray();

    return compact('years', 'makes', 'models', 'vehicleData');
}
```

## Mobile Auto Grid View (Shop)

```javascript
function checkMobileLayout() {
  if (window.innerWidth <= 992) {
    applyLayout('grid');
  }
}
$(document).ready(checkMobileLayout);
$(window).on('resize', checkMobileLayout);
```

## Common Pitfalls

- BlogController::category() — must pass `$category` to view (was missing, caused undefined variable)
- Clear Filters — use `array_filter()` to remove only filter params, preserve pagination/search
- Vehicle cascading — `$vehicleData` must be distinct year/make/model combos, not individual products
- Mobile grid: force `grid` view at ≤992px, respect desktop preference via localStorage
