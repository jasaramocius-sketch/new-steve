<?php

namespace App\Console\Commands;

class FallbackMissingImages extends CleanupDuplicateImages
{
    protected $signature = 'images:fallback-missing {--dry-run : Show what would be done without changing anything}';

    protected $description = 'Point referenced-but-missing images at an existing _250.webp/_500.webp thumbnail variant';

    /** @var array<string,string> missing path -> fallback variant path */
    private array $noFallback = [];

    public function handle(): int
    {
        $this->info('Building reference set...');
        $this->collectReferences();

        $this->info('Scanning referenced-but-missing image files...');
        $remap = [];
        foreach ($this->refs as $path => $count) {
            if ($this->exists($path)) {
                continue;
            }
            if (preg_match('/_(250|500)\.webp$/', $path)) {
                continue;
            }
            $fb = $this->findFallback($path);
            if ($fb) {
                $remap[$path] = $fb;
            } else {
                $this->noFallback[$path] = $count;
            }
        }
        uksort($remap, fn ($a, $b) => strcmp($a, $b));
        $this->remap = $remap;

        $this->table(['property', 'count'], [
            ['broken references to remap', count($this->remap)],
            ['broken references without any fallback', count($this->noFallback)],
        ]);

        if (empty($this->remap)) {
            $this->info('Nothing to fix.');

            return 0;
        }

        $this->newLine();
        $this->info('Sample fallbacks:');
        $i = 0;
        foreach ($this->remap as $old => $new) {
            if (++$i > 8) {
                break;
            }
            $this->line("  $old");
            $this->line("      -> $new  (exists)");
        }

        if ($this->option('dry-run')) {
            $preview = $this->previewRewriteCounts();
            if (count($preview)) {
                $this->newLine();
                $this->info('Reference rewrites that would apply:');
                $this->table(['source', 'rows'], collect($preview)->map(fn ($c, $k) => [$k, $c])->values());
            }
            $this->newLine();
            $this->line('Without any fallback (skipped, need manual upload):');
            foreach (array_slice(array_keys($this->noFallback), 0, 30) as $p) {
                $this->line("  $p");
            }
            if (count($this->noFallback) > 30) {
                $this->line('  ... and '.(count($this->noFallback) - 30).' more');
            }
            $this->warn('DRY RUN — no changes were made.');

            return 0;
        }

        $this->backupDatabase();
        $this->rewriteReferences();

        $this->newLine();
        $this->info('=== Fallback Summary ===');
        $this->info('References rewritten:   '.array_sum($this->rewriteStats));
        $this->info('Image paths remapped:    '.count($this->remap));

        $this->newLine();
        $this->line('Referenced non-variant images still missing after fix: '.count($this->noFallback));
        foreach (array_slice(array_keys($this->noFallback), 0, 30) as $p) {
            $this->line("  $p");
        }
        if (count($this->noFallback) > 30) {
            $this->line('  ... and '.(count($this->noFallback) - 30).' more');
        }

        return 0;
    }

    private function exists(string $path): bool
    {
        return file_exists(storage_path('app/public/'.$path)) || file_exists(public_path($path));
    }

    private function findFallback(string $path): ?string
    {
        $dir = dirname($path);
        $base = pathinfo($path, PATHINFO_FILENAME);
        foreach (['_500.webp', '_250.webp'] as $suffix) {
            $candidate = $dir.'/'.$base.$suffix;
            if ($this->exists($candidate)) {
                return $candidate;
            }
        }

        return null;
    }
}
