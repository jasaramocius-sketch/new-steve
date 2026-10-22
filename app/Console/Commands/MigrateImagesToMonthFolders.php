<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomePageSection;
use App\Models\Image;
use App\Models\Page;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MigrateImagesToMonthFolders extends Command
{
    protected $signature = 'images:migrate-to-month-folders {--dry-run : Show what would be done without making changes} {--skip-backup : Skip database backup}';

    protected $description = 'Migrate all images to WordPress-style uploads/Y/m folder structure and update DB paths';

    private $stats = [
        'moved' => 0,
        'skipped' => 0,
        'errors' => 0,
        'updated_db' => 0,
    ];

    private $fileMap = []; // source physical path -> target relative path

    public function handle()
    {
        if (! $this->option('dry-run') && ! $this->option('skip-backup')) {
            $this->info('Creating database backup...');
            $this->backupDatabase();
        }

        $this->info('Starting image migration to uploads/Y/m structure...');
        $this->newLine();

        // Process each image source
        $this->migrateImagesTable();
        $this->migrateProducts();
        $this->migrateCategories();
        $this->migrateBrands();
        $this->migrateBlogs();
        $this->migratePages();
        $this->migrateUsers();
        $this->migrateHomePageSections();
        $this->migrateSettings();
        $this->migrateReviewImages();

        $this->newLine();
        $this->displaySummary();

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN COMPLETE - No changes made');
        }
    }

    private function backupDatabase()
    {
        $filename = 'database/stautoparts_migration_backup_'.now()->format('Ymd_His').'.sql';
        exec("mysqldump -u root stautoparts > $filename 2>&1", $output, $returnVar);
        if ($returnVar === 0) {
            $this->info("Backup saved to $filename");
        } else {
            $this->error('Backup failed: '.implode("\n", $output));
        }
    }

    private function getFileMtime($physicalPath)
    {
        return file_exists($physicalPath) ? filemtime($physicalPath) : time();
    }

    private function getTargetPath($physicalPath, $filename)
    {
        $mtime = $this->getFileMtime($physicalPath);
        $year = date('Y', $mtime);
        $month = date('m', $mtime);

        return "uploads/$year/$month/$filename";
    }

    private function findPhysicalFile($relativePath, $baseDirs = [])
    {
        // If relative path already has directory structure (contains /)
        if (str_contains($relativePath, '/')) {
            $candidates = [
                public_path($relativePath),
                storage_path('app/public/'.$relativePath),
            ];
            foreach ($candidates as $c) {
                if (file_exists($c)) {
                    return $c;
                }
            }

            return null;
        }

        // Bare filename - check base dirs
        foreach ($baseDirs as $dir) {
            $candidates = [
                public_path($dir.$relativePath),
                storage_path('app/public/'.$dir.$relativePath),
            ];
            foreach ($candidates as $c) {
                if (file_exists($c)) {
                    return $c;
                }
            }
        }

        return null;
    }

    private function moveFile($sourcePath, $targetRelativePath)
    {
        if (isset($this->fileMap[$sourcePath])) {
            return $this->fileMap[$sourcePath]; // Already processed
        }

        $filename = basename($sourcePath);
        $targetPath = storage_path('app/public/'.$targetRelativePath);

        if (! $this->option('dry-run')) {
            File::ensureDirectoryExists(dirname($targetPath));
            File::copy($sourcePath, $targetPath);

            // Also move .webp sibling if exists
            $webpSource = dirname($sourcePath).'/'.pathinfo($filename, PATHINFO_FILENAME).'.webp';
            if (file_exists($webpSource)) {
                $webpTarget = dirname($targetPath).'/'.pathinfo($filename, PATHINFO_FILENAME).'.webp';
                File::copy($webpSource, $webpTarget);
            }
        }

        $this->fileMap[$sourcePath] = $targetRelativePath;
        $this->stats['moved']++;

        return $targetRelativePath;
    }

    private function updateDB($table, $column, $oldValue, $newValue, $where = [])
    {
        if ($oldValue === $newValue) {
            return false;
        }

        if (! $this->option('dry-run')) {
            $query = DB::table($table)->where($column, $oldValue);
            foreach ($where as $k => $v) {
                $query->where($k, $v);
            }
            $updated = $query->update([$column => $newValue]);
            if ($updated) {
                $this->stats['updated_db'] += $updated;
            }

            return $updated > 0;
        }
        $this->stats['updated_db']++;

        return true;
    }

    private function migrateImagesTable()
    {
        $this->info('Migrating images table...');
        $images = Image::whereNotNull('path')->where('path', '!=', '')->get();

        foreach ($images as $img) {
            $oldPath = $img->path;
            $sourcePath = $this->findPhysicalFile($oldPath);
            if (! $sourcePath) {
                $this->warn("  File not found for: $oldPath");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldPath));
            if ($oldPath === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);

            if (! $this->option('dry-run')) {
                $img->path = $newPath;
                $img->url = 'storage/'.$newPath;
                $img->save();
            }
            $this->stats['updated_db']++;
        }
        $this->info("  Processed {$images->count()} images table rows");
    }

    private function migrateProducts()
    {
        $this->info('Migrating products.image...');
        $products = Product::whereNotNull('image')->where('image', '!=', '')->get();
        $baseDir = 'assets/images/thumbnails/';

        foreach ($products as $product) {
            $oldValue = $product->image;
            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for product {$product->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('products', 'image', $oldValue, $newPath, ['id' => $product->id]);
        }
        $this->info("  Processed {$products->count()} products");
    }

    private function migrateCategories()
    {
        $this->info('Migrating categories.image...');
        $items = Category::whereNotNull('image')->where('image', '!=', '')->get();
        $baseDir = 'assets/images/categories/';

        foreach ($items as $item) {
            $oldValue = $item->image;
            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for category {$item->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('categories', 'image', $oldValue, $newPath, ['id' => $item->id]);
        }
        $this->info("  Processed {$items->count()} categories");
    }

    private function migrateBrands()
    {
        $this->info('Migrating brands.image...');
        $items = Brand::whereNotNull('image')->where('image', '!=', '')->get();
        $baseDir = 'assets/images/brands/';

        foreach ($items as $item) {
            $oldValue = $item->image;
            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for brand {$item->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('brands', 'image', $oldValue, $newPath, ['id' => $item->id]);
        }
        $this->info("  Processed {$items->count()} brands");
    }

    private function migrateBlogs()
    {
        $this->info('Migrating blogs.image...');
        $items = Blog::whereNotNull('image')->where('image', '!=', '')->get();
        $baseDir = 'assets/images/blogs/';

        foreach ($items as $item) {
            $oldValue = $item->image;
            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for blog {$item->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('blogs', 'image', $oldValue, $newPath, ['id' => $item->id]);
        }
        $this->info("  Processed {$items->count()} blogs");
    }

    private function migratePages()
    {
        $this->info('Migrating pages.image...');
        $items = Page::whereNotNull('image')->where('image', '!=', '')->get();
        $baseDir = 'assets/images/pages/';

        foreach ($items as $item) {
            $oldValue = $item->image;
            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for page {$item->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('pages', 'image', $oldValue, $newPath, ['id' => $item->id]);
        }
        $this->info("  Processed {$items->count()} pages");
    }

    private function migrateUsers()
    {
        $this->info('Migrating users.avatar...');
        $users = User::whereNotNull('avatar')->where('avatar', '!=', '')->get();

        foreach ($users as $user) {
            $oldValue = $user->avatar;
            $sourcePath = $this->findPhysicalFile($oldValue);
            if (! $sourcePath) {
                $this->warn("  File not found for user {$user->id}: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue === $targetPath) {
                $this->stats['skipped']++;

                continue;
            }

            $newPath = $this->moveFile($sourcePath, $targetPath);
            $this->updateDB('users', 'avatar', $oldValue, $newPath, ['id' => $user->id]);
        }
        $this->info("  Processed {$users->count()} users");
    }

    private function migrateHomePageSections()
    {
        $this->info('Migrating home_page_sections.image and extra_data banners...');
        $sections = HomePageSection::all();

        // Main image column
        foreach ($sections as $section) {
            if (! empty($section->image)) {
                $oldValue = $section->image;
                $sourcePath = $this->findPhysicalFile($oldValue, ['assets/images/home/', 'assets/images/']);
                if (! $sourcePath) {
                    $this->warn("  File not found for home_section {$section->id}: $oldValue");
                    $this->stats['errors']++;

                    continue;
                }

                $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
                if ($oldValue !== $targetPath) {
                    $newPath = $this->moveFile($sourcePath, $targetPath);
                    $this->updateDB('home_page_sections', 'image', $oldValue, $newPath, ['id' => $section->id]);
                } else {
                    $this->stats['skipped']++;
                }
            }
        }

        // extra_data banners - search multiple possible locations
        $bannerBaseDirs = ['assets/images/home/', 'assets/images/categories/', 'assets/images/brands/', 'assets/images/blogs/', 'assets/images/pages/', 'assets/images/thumbnails/', 'assets/images/'];
        foreach ($sections as $section) {
            if (! empty($section->extra_data)) {
                $data = is_string($section->extra_data) ? json_decode($section->extra_data, true) : $section->extra_data;
                if (isset($data['banners']) && is_array($data['banners'])) {
                    $modified = false;
                    foreach ($data['banners'] as &$banner) {
                        if (! empty($banner['image'])) {
                            $oldValue = $banner['image'];
                            $sourcePath = $this->findPhysicalFile($oldValue, $bannerBaseDirs);
                            if (! $sourcePath) {
                                $this->warn("  Banner file not found for section {$section->id}: $oldValue");
                                $this->stats['errors']++;

                                continue;
                            }

                            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
                            if ($oldValue !== $targetPath) {
                                $newPath = $this->moveFile($sourcePath, $targetPath);
                                $banner['image'] = $newPath;
                                $modified = true;
                            } else {
                                $this->stats['skipped']++;
                            }
                        }
                    }
                    if ($modified && ! $this->option('dry-run')) {
                        $section->extra_data = json_encode($data);
                        $section->save();
                        $this->stats['updated_db']++;
                    }
                }
            }
        }
        $this->info("  Processed {$sections->count()} home page sections");
    }

    private function migrateSettings()
    {
        $this->info('Migrating settings logos/favicon...');
        $keys = ['header_logo', 'mobile_logo', 'footer_logo', 'header_favicon'];
        $baseDir = 'assets/images/';

        foreach ($keys as $key) {
            $oldValue = Setting::get($key);
            if (empty($oldValue)) {
                continue;
            }

            $sourcePath = $this->findPhysicalFile($oldValue, [$baseDir]);
            if (! $sourcePath) {
                $this->warn("  File not found for setting $key: $oldValue");
                $this->stats['errors']++;

                continue;
            }

            $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
            if ($oldValue !== $targetPath) {
                $newPath = $this->moveFile($sourcePath, $targetPath);
                if (! $this->option('dry-run')) {
                    Setting::set($key, $newPath);
                }
                $this->stats['updated_db']++;
            } else {
                $this->stats['skipped']++;
            }
        }
        $this->info('  Processed '.count($keys).' settings');
    }

    private function migrateReviewImages()
    {
        $this->info('Migrating review images in products.reviews_data...');
        $products = Product::whereNotNull('reviews_data')->where('reviews_data', '!=', '')->get();
        $modified = 0;

        foreach ($products as $product) {
            $data = is_string($product->reviews_data) ? json_decode($product->reviews_data, true) : $product->reviews_data;
            $productModified = false;

            foreach ($data as &$review) {
                if (! empty($review['images']) && is_array($review['images'])) {
                    foreach ($review['images'] as &$img) {
                        $oldValue = $img;
                        $sourcePath = $this->findPhysicalFile($oldValue);
                        if (! $sourcePath) {
                            $this->warn("  Review image file not found: $oldValue");
                            $this->stats['errors']++;

                            continue;
                        }

                        $targetPath = $this->getTargetPath($sourcePath, basename($oldValue));
                        if ($oldValue !== $targetPath) {
                            $newPath = $this->moveFile($sourcePath, $targetPath);
                            $img = $newPath;
                            $productModified = true;
                        } else {
                            $this->stats['skipped']++;
                        }
                    }
                }
            }

            if ($productModified) {
                if (! $this->option('dry-run')) {
                    $product->reviews_data = json_encode($data);
                    $product->save();
                }
                $this->stats['updated_db']++;
                $modified++;
            }
        }
        $this->info("  Modified {$modified} products' review data");
    }

    private function displaySummary()
    {
        $this->newLine();
        $this->info('=== Migration Summary ===');
        $this->info("Files moved:       {$this->stats['moved']}");
        $this->info("Files skipped:     {$this->stats['skipped']} (already in target location)");
        $this->info("DB rows updated:   {$this->stats['updated_db']}");
        $this->info("Errors:            {$this->stats['errors']}");

        if ($this->stats['errors'] > 0) {
            $this->warn('Some files were not found - check warnings above');
        }
    }
}
