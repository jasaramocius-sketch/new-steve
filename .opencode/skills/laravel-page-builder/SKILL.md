---
name: laravel-page-builder
description: Use when building, editing, or fixing the Laravel Page Builder package — including block editor UI, drag-drop with SortableJS, AJAX save, image upload via Image Manager, repeater fields, Elementor-style per-element styling, or frontend block rendering. Package is at ../laravel-page-builder/.
---

# Laravel Page Builder

## Package Location

`/var/www/html/laravel-page-builder/` (separate repo, connected via composer path repository)

## Architecture

- **Service Provider**: `SteveStore\PageBuilder\PageBuilderServiceProvider`
- **Facade**: `SteveStore\PageBuilder\PageBuilder` (resolves to `BlockRegistry`)
- **Models with HasBlocks trait**: `Page`, `Blog`
- **Content stored in**: `content_blocks` JSON column (nullable)

## Block Structure

Each block extends `SteveStore\PageBuilder\Blocks\Block`:
```php
namespace SteveStore\PageBuilder\Blocks\BuiltIn;

class HeroBanner extends Block {
    public string $name = 'hero';
    public string $label = 'Hero Banner';
    public string $icon = 'fas fa-image';
    public string $group = 'content';
    public bool $singleton = true;

    public function fields(): array {
        return [
            'title' => ['type' => 'text', 'label' => 'Title'],
            'subtitle' => ['type' => 'textarea', 'label' => 'Subtitle'],
            'bg_image' => ['type' => 'image', 'label' => 'Background Image'],
            'btn_text' => ['type' => 'text', 'label' => 'Button Text'],
            'btn_url' => ['type' => 'text', 'label' => 'Button URL'],
        ];
    }
}
```

## Editor UI

- File: `resources/views/admin/page-builder/editor.blade.php`
- JS: `resources/js/page-builder.js` (loaded via `@push('page-builder-js')`)
- CSS: `resources/css/page-builder.css`

### Key JS Behaviors
- **Drag-drop**: SortableJS on `.pb-block-list`
- **AJAX save**: `$.post(route('page-builder.update'), { id, content_blocks: serializeBlocks() })`
- **Block add**: Fetches form via AJAX, appends to editor
- **Image upload**: Uses Image Manager picker (NOT native file input)
- **Repeater**: `__INDEX__` / `__NUM__` placeholders for templates
- **Dirty tracking**: `beforeunload` warning on unsaved changes

### Block Serialization
```javascript
function serializeBlocks() {
  const blocks = [];
  document.querySelectorAll('.pb-block-item').forEach(el => {
    const data = {};
    el.querySelectorAll('[name]').forEach(input => {
      const name = input.getAttribute('name');
      const match = name.match(/\[data\]\[(.+?)\]/);
      if (match) data[match[1]] = input.value;
    });
    blocks.push({ type: el.dataset.blockType, data });
  });
  return blocks;
}
```

## Frontend Rendering

```blade
<!-- In pages/show.blade.php or blog/show.blade.php -->
<x-page-blocks :model="$page" />
```

### Frontend Block Views
Located in: `resources/views/frontend/blocks/`
- `hero.blade.php`, `text.blade.php`, `gallery.blade.php`
- `features.blade.php`, `testimonials.blade.php`, `cta.blade.php`

## Elementor-Style Per-Element Styling

### StyleHelper
File: `src/Helpers/StyleHelper.php`

```php
use SteveStore\PageBuilder\Helpers\StyleHelper;

// In frontend block view
{!! StyleHelper::build($data, 'title') !!}
{!! StyleHelper::spacing($data, 'section') !!}
```

### Style Fields Partial
File: `resources/views/editor/partials/style-fields.blade.php`

```blade
@include('editor.partials.style-fields', [
  'prefix' => 'title',
  'label' => 'Title Style',
  'data' => $block['data'] ?? [],
  'show' => ['typography', 'colors', 'spacing', 'border']
])
```

### Available Style Properties
| Category | Properties |
|----------|-----------|
| Typography | font_family, font_size, font_weight, line_height, letter_spacing, text_transform, text-decoration |
| Colors | color, background_color |
| Spacing | padding_top/right/bottom/left, margin_top/right/bottom/left |
| Border | border_radius, border_width, border_style, border_color |
| Size | width, height |

## Install/Uninstall Commands

```bash
# Install (adds content_blocks column to pages + blogs tables)
php artisan page-builder:install

# Uninstall (removes column, disables package)
php artisan page-builder:uninstall
```

## Config

File: `config/page-builder.php`
```php
return [
    'enabled' => env('PAGE_BUILDER_ENABLED', true),
    'models' => [
        'page' => \App\Models\Page::class,
        'blog' => \App\Models\Blog::class,
    ],
];
```

## Common Pitfalls

- **CSRF token**: Must have `<meta name="csrf-token">` in admin layout `<head>`
- **jQuery load order**: Page Builder JS must load AFTER jQuery (use `@push('page-builder-js')`)
- **Component registration**: Use `Blade::component('page-builder', Editor::class)` — NOT namespace registration
- **Facade import**: Use `SteveStore\PageBuilder\PageBuilder` — NOT `Facades\PageBuilder`
- **Content blocks JSON**: Repeater/gallery arrays may be double-encoded as strings — use `json_decode()` if `is_string()`
- **Slug uniqueness**: Auto-appends `-1`, `-2` etc. if slug exists
- **Bootstrap providers**: Must be in `bootstrap/providers.php`
