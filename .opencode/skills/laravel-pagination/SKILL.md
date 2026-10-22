---
name: laravel-pagination
description: Use when adding, fixing, or customizing pagination in Laravel — including custom pagination views, first/last buttons, results info display, server-side search/filter with pagination, LengthAwarePaginator, per-page selectors, or per-page "All" option.
---

# Laravel Pagination

## Custom Pagination View

File: `resources/views/vendor/pagination/gs-pagination.blade.php`

### Setup in AppServiceProvider
```php
// app/Providers/AppServiceProvider.php
use Illuminate\Pagination\Paginator;
Paginator::defaultView('vendor.pagination.gs-pagination');
```

### Results Info Display
```blade
<div class="item-pagination-container">
  <p class="pagination-info">
    Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
  </p>
</div>
```

### Smart Window (Max 5 Pages Visible)
```blade
@php
  $start = max(1, $paginator->currentPage() - 2);
  $end = min($paginator->lastPage(), $start + 4);
  $start = max(1, $end - 4);
@endphp
```

### First/Last Buttons
```blade
@if($paginator->currentPage() > 1)
  <li class="page-item">
    <a class="page-link" href="{{ $paginator->url(1) }}">&laquo; First</a>
  </li>
@endif

@if($paginator->currentPage() < $paginator->lastPage())
  <li class="page-item">
    <a class="page-link" href="{{ $paginator->url($paginator->lastPage()) }}">Last &raquo;</a>
  </li>
@endif
```

## Server-Side Pagination with Filters

```php
// Controller
public function reviews(Request $request) {
    $query = Review::where('user_id', auth()->id());

    // Search filter
    if ($request->search) {
        $query->where('review', 'LIKE', "%{$request->search}%");
    }

    // Status filter
    if ($request->status) {
        $query->where('status', $request->status);
    }

    $items = $query->paginate(10)->withQueryString(); // preserves query params
    return view('user.reviews', compact('items'));
}
```

### With LengthAwarePaginator (custom collection)
```php
$paginator = new LengthAwarePaginator(
    $collection->forPage($page, $perPage),
    $collection->count(),
    $perPage,
    $page,
    ['path' => request()->url(), 'query' => request()->query()]
);
```

## Per-Page Selector with "All" Option

```blade
<select onchange="window.location.href=addParam('per_page', this.value)">
  <option value="10" {{ request('per_page') == 10 ? 'selected' : '' }}>10</option>
  <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
  <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
  <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>All</option>
</select>
```

```php
// Controller
$perPage = request('per_page', 10);
if ($perPage === 'all') {
    $items = $query->get();
} else {
    $items = $query->paginate($perPage)->withQueryString();
}
```

## onEachSide(2)

Limits visible page links:
```php
$products = Product::paginate(9)->onEachSide(2);
```

## Query String Preservation

Always use `->withQueryString()` to preserve search/filter params:
```php
$items = $query->paginate(10)->withQueryString();
```

## Common Pitfalls

- `withQueryString()` is essential — without it, filters reset on page change
- `LengthAwarePaginator` needed for custom collections (not just Eloquent queries)
- Per-page "All" needs special handling (`get()` instead of `paginate()`)
- First/Last buttons use `$paginator->url(1)` and `$paginator->url($paginator->lastPage())`
