<?php

namespace App\Console\Commands;

use App\Models\Blog;
use App\Models\Brand;
use App\Models\Category;
use App\Models\HomePageSection;
use App\Models\Image;
use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class SyncImagesToManager extends Command
{
    protected $signature = 'images:sync {--dry-run : Show what would be done without inserting}';

    protected $description = 'Scan all image directories and sync into the images table';

    protected array $sources = [
        ['dir' => 'thumbnails',  'model' => Product::class,         'columns' => ['main_image', 'image']],
        ['dir' => 'categories',  'model' => Category::class,        'columns' => ['image']],
        ['dir' => 'blogs',       'model' => Blog::class,             'columns' => ['image']],
        ['dir' => 'brands',      'model' => Brand::class,            'columns' => ['logo', 'image']],
        ['dir' => 'home',        'model' => HomePageSection::class,  'columns' => ['image']],
    ];

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $publicPath = public_path('assets/images');
        $inserted = 0;
        $skipped = 0;

        foreach ($this->sources as $source) {
            $dir = $publicPath.'/'.$source['dir'];
            if (! is_dir($dir)) {
                $this->warn("Directory not found: {$dir}");

                continue;
            }

            $column = $this->imageColumn($source['model'], $source['columns']);
            if ($column === null) {
                $this->warn("No image column found for {$source['model']}");

                continue;
            }

            $files = File::files($dir);
            $this->info("Scanning [{$source['dir']}] — {$source['model']} — {$column} — {$this->count($files)} files");

            foreach ($files as $file) {
                $filename = $file->getFilename();
                $ext = strtolower($file->getExtension());

                if (! in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp'])) {
                    continue;
                }

                $existing = Image::where('filename', $filename)->first();
                if ($existing) {
                    $this->comment("SKIP (already exists): {$filename}");
                    $skipped++;

                    continue;
                }

                $parent = $source['model']::where($column, $filename)->first();

                if ($dryRun) {
                    $this->line("  WOULD INSERT: {$filename} → ".($parent ? get_class($parent).' #'.$parent->id : 'UNUSED'));
                    $inserted++;

                    continue;
                }

                $image = new Image;
                $image->original_name = $filename;
                $image->filename = $filename;
                $image->path = 'assets/images/'.$source['dir'].'/'.$filename;
                $image->url = 'assets/images/'.$source['dir'].'/'.$filename;
                $image->mime_type = mime_content_type($file->getPathname()) ?: 'image/'.$ext;
                $image->size = $file->getSize();
                $image->alt_text = null;
                $image->title = null;

                $dimensions = @getimagesize($file->getPathname());
                if ($dimensions) {
                    $image->width = $dimensions[0];
                    $image->height = $dimensions[1];
                }

                if ($parent) {
                    $image->attachable_type = get_class($parent);
                    $image->attachable_id = $parent->id;
                    $image->is_unused = false;
                } else {
                    $image->is_unused = true;
                }

                $image->save();
                $this->line("  INSERTED: {$filename} → ".($parent ? get_class($parent).' #'.$parent->id : 'UNUSED'));
                $inserted++;
            }
        }

        $this->newLine();
        $this->info("Done. {$inserted} inserted, {$skipped} skipped.");

        return Command::SUCCESS;
    }

    /**
     * Resolve the column that actually holds the image path of a model. The
     * catalogue schema names them differently (products.main_image, brands.logo).
     */
    private function imageColumn(string $model, array $candidates): ?string
    {
        $table = (new $model)->getTable();

        foreach ($candidates as $candidate) {
            if (Schema::hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function count(array|int $value): int
    {
        return is_array($value) ? count($value) : $value;
    }
}
