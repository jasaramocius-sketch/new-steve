# Home Page Admin Management System

## Overview

A complete admin dashboard system for managing all homepage content without requiring code changes. Administrators can now control:
- **Hero Section**: Banner image, title, subtitle, description, and CTA button
- **Banner Sections**: Multiple banners with images and links
- **Homepage Content**: Full management of promotional sections

## Features

✅ **Database-Driven Content**: All homepage sections stored in `home_page_sections` table
✅ **Image Upload**: Support for JPEG, PNG, JPG, GIF, WebP formats (max 5MB)
✅ **Section Ordering**: Manage the order of sections on the homepage
✅ **Active/Inactive Toggle**: Enable/disable sections without deletion
✅ **Admin Dashboard Link**: Quick access from the main admin dashboard
✅ **Responsive Management UI**: Bootstrap-based admin forms for easy editing

## File Structure

### Database
- **Migration**: `database/migrations/2026_06_22_000000_create_home_page_sections_table.php`
  - Creates `home_page_sections` table with all necessary fields
  - Supports flexible data structure with JSON storage for extra_data

### Models
- **HomePageSection**: `app/Models/HomePageSection.php`
  - Eloquent model with JSON casting for extra_data
  - Manages all homepage section data

### Controllers
- **HomePageController**: `app/Http/Controllers/Admin/HomePageController.php`
  - `index()`: Display all homepage sections
  - `edit($id)`: Edit a specific section
  - `update($id)`: Save changes with image upload handling
  - `reorder()`: Change section ordering

- **HomeController**: `app/Http/Controllers/HomeController.php` (Updated)
  - Fetches hero section and banners from database
  - Falls back to default content if sections not configured

### Views
- **Admin Index**: `resources/views/admin/home-page/index.blade.php`
  - Lists all homepage sections with status
  - Links to edit each section

- **Admin Edit**: `resources/views/admin/home-page/edit.blade.php`
  - Form to edit section details
  - Image upload with preview
  - Section metadata display

- **Homepage**: `resources/views/home.blade.php` (Updated)
  - Uses database content for hero and banners
  - Maintains fallback for default content
  - Dynamic banner layout (responsive columns)

### Routes
Added to `routes/web.php`:
```php
Route::prefix('admin')
    ->middleware(['auth','role:admin,staff'])
    ->group(function () {
        Route::get('/home-page', [HomePageController::class, 'index'])
            ->name('admin.home-page.index');
        Route::get('/home-page/{id}/edit', [HomePageController::class, 'edit'])
            ->name('admin.home-page.edit');
        Route::put('/home-page/{id}', [HomePageController::class, 'update'])
            ->name('admin.home-page.update');
        Route::post('/home-page/reorder', [HomePageController::class, 'reorder'])
            ->name('admin.home-page.reorder');
    });
```

### Seeders
- **HomePageSectionSeeder**: `database/seeders/HomePageSectionSeeder.php`
  - Seeds default homepage sections:
    - Hero section with default content
    - 3 Banner sections
    - Offers section

## Database Schema

```sql
CREATE TABLE home_page_sections (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(255) UNIQUE NOT NULL,
    title TEXT,
    subtitle TEXT,
    description TEXT,
    image VARCHAR(255),
    button_text VARCHAR(100),
    button_url VARCHAR(255),
    `order` INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    extra_data JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Usage

### Admin Dashboard
1. Go to `/admin` dashboard
2. Click "Home Page" card to manage sections
3. Click "Edit" on any section to modify it

### Editing a Section
1. Update title, subtitle, description, button text, and URL
2. Upload a new image (optional)
3. Toggle active/inactive status
4. Click "Save Changes"

### Uploading Images
- Supported formats: JPEG, PNG, JPG, GIF, WebP
- Maximum file size: 5MB
- Images stored in: `public/assets/images/home/`
- Old images automatically deleted when replaced

### Directory Structure
```
public/
  assets/
    images/
      home/           <- Homepage section images stored here
        [timestamp]_[unique_id].ext
```

## Initial Data

The seeder creates 5 default sections:
1. **hero** - Main hero section with call-to-action
2. **banner_1** - First promotional banner
3. **banner_2** - Second promotional banner
4. **banner_3** - Third promotional banner
5. **offers** - Offers section header

All sections are active by default with order starting from 1.

## Integration with Frontend

The homepage automatically displays:
1. **Hero section** (if active) from database, otherwise fallback
2. **Categories** (already database-driven)
3. **Banners** (from `banner_*` sections in database)
4. **Products** (database-driven)

### Frontend Updates
- Hero section now pulls from `$heroSection` variable
- Banners now pulled from `$bannerSections` collection
- Responsive banner layout auto-adjusts based on number of banners

## Admin Dashboard Update

The admin dashboard now includes a "Management Tools" section with quick-access cards:
- **Home Page** - New link to manage homepage sections
- **Categories** - Manage product categories
- **Products** - Manage all products
- **Orders** - Manage customer orders

## File Upload Handling

The system includes robust image handling:
- Validates file type (image/jpeg, image/png, etc.)
- Limits file size to 5MB
- Automatically deletes old image when new one uploaded
- Generates unique filenames to avoid conflicts
- Stores relative path in database

## Validation

Update endpoint validates:
- `title`: Optional, max 255 characters
- `subtitle`: Optional, max 255 characters
- `description`: Optional, string
- `button_text`: Optional, max 100 characters
- `button_url`: Optional, max 255 characters
- `is_active`: Optional, boolean
- `image`: Optional, image file, max 5MB, jpg/png/gif/webp

## Error Handling

- Invalid file type: Error message displayed to user
- File too large: Error message and size limit shown
- Database errors: Graceful fallback to default content
- Missing sections: Homepage still displays with defaults

## Migration Path

If migrating from hardcoded content:
1. Create migrations (already created)
2. Run `php artisan migrate`
3. Run seeder: `php artisan db:seed --class=HomePageSectionSeeder`
4. Log in to admin dashboard
5. Go to "Home Page" section
6. Edit each section as needed
7. Upload images for each section
8. Verify homepage displays correctly

## Security

- Admin-only access (middleware: `auth`, `role:admin,staff`)
- Image upload validation
- CSRF protection on forms
- File permissions properly set (755)

## Future Enhancements

Possible improvements:
- Drag-and-drop banner reordering
- Image cropping/resizing in admin
- Preview of homepage before publish
- Version history/rollback
- Schedule section activation dates
- A/B testing different hero content
- Analytics on banner clicks

## Troubleshooting

**Homepage not showing new hero section?**
- Verify `is_active` is checked
- Check browser cache

**Images not uploading?**
- Verify `public/assets/images/home/` directory exists
- Check directory permissions (should be 755)
- Verify file size under 5MB
- Supported formats: jpg, png, gif, webp

**Admin dashboard not showing Home Page link?**
- Verify you're logged in as admin or staff
- Clear browser cache
- Check route is registered in `routes/web.php`

**Sections not appearing on homepage?**
- Check `is_active` flag on sections
- Verify migration ran: `php artisan migrate`
- Check database has records: `HomePageSection::all()`
