<?php

namespace App\Console\Commands;

use App\Models\Image;
use Illuminate\Console\Command;

class BackfillImageDimensions extends Command
{
    protected $signature = 'images:backfill-dimensions {--dry-run : Show how many images would be updated without writing anything}';

    protected $description = 'Backfill NULL width/height on images from their files via getimagesize()';

    public function handle(): int
    {
        $images = Image::withTrashed()
            ->whereNull('width')
            ->whereNull('height')
            ->get();

        $this->info("Images with NULL width/height: {$images->count()}");

        $backfilled = 0;
        $missing = 0;
        $unreadable = 0;

        foreach ($images as $image) {
            $filePath = $image->file_path;
            if (! $filePath || ! is_file($filePath)) {
                $missing++;
                $this->warn("  missing file: #{$image->id} {$image->path}");

                continue;
            }

            $info = @getimagesize($filePath);
            if ($info === false || ! isset($info[0], $info[1]) || ! $info[0] || ! $info[1]) {
                $unreadable++;
                $this->warn("  unreadable: #{$image->id} {$image->path}");

                continue;
            }

            if (! $this->option('dry-run')) {
                $image->forceFill([
                    'width' => $info[0],
                    'height' => $info[1],
                ])->save();
            }
            $backfilled++;
        }

        $this->newLine();
        $this->table(['property', 'count'], [
            ['backfilled', $backfilled],
            ['file missing', $missing],
            ['unreadable', $unreadable],
        ]);

        if ($this->option('dry-run')) {
            $this->warn('DRY RUN — no changes were made.');
        }

        return 0;
    }
}