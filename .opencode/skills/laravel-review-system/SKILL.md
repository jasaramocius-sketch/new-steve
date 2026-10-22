---
name: laravel-review-system
description: Use when adding, editing, or fixing product review functionality in Laravel — including review CRUD, image upload, modal popups, SVG star pickers, lightbox gallery, review display on product pages, or dashboard "My Reviews" page. Covers reviews stored as JSON in products table.
---

# Laravel Review System

Reviews are stored as JSON in `products.reviews_data` column (no separate model). One review per user per product.

## Architecture

- **Storage**: `products.reviews_data` JSON column — array of review objects
- **Images**: `public/assets/images/reviews/` directory (chmod 777)
- **Routes**: `POST /product/{slug}/review`, `PUT /product/{slug}/review/{reviewId}`, `DELETE /product/{slug}/review/{reviewId}`
- **Controller**: `app/Http/Controllers/ReviewController.php`

## Review JSON Structure

```json
[{
  "id": "unique_id",
  "user_id": 1,
  "user_name": "John",
  "rating": 5,
  "review": "Great product!",
  "images": ["reviews/img1.jpg", "reviews/img2.jpg"],
  "created_at": "2026-07-20 10:00:00"
}]
```

## ReviewController Pattern

```php
// store() — append review to JSON array
public function store(Request $request, $slug) {
    $product = Product::where('slug', $slug)->firstOrFail();
    $reviews = $product->reviews_data ?? [];

    // Duplicate check
    foreach ($reviews as $r) {
        if ($r['user_id'] == auth()->id()) {
            return response()->json(['success' => false, 'message' => 'Already reviewed'], 403);
        }
    }

    // Image upload (max 5, 2MB each)
    $images = [];
    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $img) {
            if ($img->getSize() > 2 * 1024 * 1024) continue;
            $name = time() . '_' . rand(1000, 9999) . '.' . $img->getClientOriginalExtension();
            $img->move(public_path('assets/images/reviews'), $name);
            $images[] = 'reviews/' . $name;
        }
    }

    $reviews[] = [
        'id' => uniqid(),
        'user_id' => auth()->id(),
        'user_name' => auth()->user()->name,
        'rating' => $request->rating,
        'review' => $request->review,
        'images' => $images,
        'created_at' => now()->format('Y-m-d H:i:s'),
    ];

    $product->update(['reviews_data' => $reviews]);
    return response()->json(['success' => true]);
}
```

## SVG Star Picker (Not FontAwesome)

Use inline SVG stars matching product-rating design:
- **Filled**: `#EEAE0B`
- **Empty**: `#E2E8F0`
- **ViewBox**: `0 0 17 16` (17×16)

```html
<div class="star-picker" data-rating="0">
  @for($i = 1; $i <= 5; $i++)
  <svg class="star" data-value="{{ $i }}" width="17" height="16" viewBox="0 0 17 16">
    <path d="M8.5 0.5L10.4084 6.37336L16.584 6.37336L11.5878 10.0033L13.4962 15.8766L8.5 12.2467L3.50383 15.8766L5.41219 10.0033L0.416019 6.37336L6.59163 6.37336L8.5 0.5Z"
      fill="#E2E8F0"/>
  </svg>
  @endfor
</div>
```

```javascript
// Fill-based hover/click (NOT class-based)
$('.star-picker .star').on('mouseenter', function() {
  const val = $(this).data('value');
  $(this).parent().find('.star').each(function(i) {
    $(this).find('path').attr('fill', i < val ? '#EEAE0B' : '#E2E8F0');
  });
});
$('.star-picker .star').on('click', function() {
  const val = $(this).data('value');
  $(this).closest('.star-picker').data('rating', val);
  $(this).parent().find('.star').each(function(i) {
    $(this).find('path').attr('fill', i < val ? '#EEAE0B' : '#E2E8F0');
  });
});
```

## Modal (Contact Seller Style)

Follows steveautoparts.com pattern:
```html
<div class="modal fade" id="reviewModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-dialog-zoom">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Write a Review</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body gry-bg px-3 pt-3">
        <!-- Star picker + textarea + image upload -->
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary rounded-0 fw-600" data-bs-dismiss="modal">Close</button>
        <button class="btn btn-primary rounded-0 fw-600">Submit</button>
      </div>
    </div>
  </div>
</div>
```

## Image Upload with Preview

```html
<input type="file" name="images[]" multiple accept="image/*" id="reviewImages">
<div id="imagePreview" class="d-flex gap-2 mt-2"></div>
```

```javascript
$('#reviewImages').on('change', function() {
  const files = Array.from(this.files).slice(0, 5); // max 5
  $('#imagePreview').empty();
  files.forEach(file => {
    if (file.size > 2 * 1024 * 1024) return; // 2MB max
    const reader = new FileReader();
    reader.onload = function(e) {
      $('#imagePreview').append(`<div class="position-relative">
        <img src="${e.target.result}" width="80" height="80" style="object-fit:cover;border-radius:8px">
        <button type="button" class="btn-close position-absolute" style="top:-5px;right:-5px" data-dismiss="preview"></button>
      </div>`);
    };
    reader.readAsDataURL(file);
  });
});
```

## Lightbox Gallery

```javascript
function openLightbox(images, index) {
  let current = index;
  const total = images.length;

  const html = `<div class="lightbox-overlay" style="position:fixed;inset:0;z-index:9999;background:rgba(0,0,0,0.9);display:flex;align-items:center;justify-content:center">
    <button class="lightbox-close" style="position:absolute;top:20px;right:20px;color:#fff;font-size:30px">&times;</button>
    <button class="lightbox-prev" style="position:absolute;left:20px;color:#fff;font-size:30px">&#10094;</button>
    <img src="${images[current]}" style="max-width:80%;max-height:80vh;border-radius:8px">
    <button class="lightbox-next" style="position:absolute;right:20px;color:#fff;font-size:30px">&#10095;</button>
    <div style="position:absolute;bottom:20px;color:#fff">${current + 1}/${total}</div>
  </div>`;

  $('body').append(html);

  // Keyboard nav: Escape=close, Left/Right=prev/next
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape') $('.lightbox-overlay').remove();
    if (e.key === 'ArrowLeft') { current = (current - 1 + total) % total; updateLightbox(images, current, total); }
    if (e.key === 'ArrowRight') { current = (current + 1) % total; updateLightbox(images, current, total); }
  });
}
```

## Dashboard Reviews Page (My Reviews)

- Uses `LengthAwarePaginator` for server-side pagination
- Server-side search by product name or review text
- Status filter (all/pending/reviewed)
- 4-column table: Product | Review | Status | Action
- Status badges: pending (warning), reviewed (success)

## Product Page Reviews Tab

- Review form always visible (server validates eligibility on submit)
- 401 → "Please login" with link
- 403 → "You must purchase this product"
- 200 → success + form reset
- AJAX submission via `FormData` (for image upload)

## Common Pitfalls

- Reviews are JSON — no Eloquent model, use `$product->update(['reviews_data' => $reviews])`
- Image directory needs write permissions: `sudo chown -R www-data:www-data public/assets/images/reviews`
- One review per user per product — check before insert
- Star picker uses fill-based JS (not class toggling) — SVG `fill` attribute changes
- Modal follows Contact Seller style from steveautoparts.com
