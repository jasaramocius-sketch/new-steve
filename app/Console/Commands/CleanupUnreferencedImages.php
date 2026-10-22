<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CleanupUnreferencedImages extends CleanupDuplicateImages
{
    protected $signature = 'images:cleanup-unreferenced {--dry-run : Preview which files would be moved to trash}';

    protected $description = 'Move uploads/ files that have no DB reference anywhere into cleanup_trash (reversible)';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function handle(): int
    {
        $this->info('Building full reference set from DB (incl. JSON blobs)...');
        $this->collectReferences();
        $this->protectDoubleEncodedBanners();

        $this->info('Scanning uploads/ for unreferenced files...');
        $candidates = $this->scanDisk();

        if (empty($candidates)) {
            $this->info('No unreferenced files found.');

            return 0;
        }

        $bytes = array_sum(array_column($candidates, 'size'));
        $this->newLine();
        $this->table(['property', 'value'], [
            ['Unreferenced files', count($candidates)],
            ['Size', $this->formatBytes($bytes)],
            ['Total files referenced (refs set)', count($this->refs)],
        ]);

        if ($this->option('dry-run')) {
            foreach (array_slice($candidates, 0, 30) as $c) {
                $this->line('  move  '.$c['path'].'  ('.$this->formatBytes($c['size']).')');
            }
            if (count($candidates) > 30) {
                $this->line('  ... and '.(count($candidates) - 30).' more');
            }
            $this->warn('DRY RUN — no changes were made.');

            return 0;
        }

        $this->backupDatabase();

        $trash = storage_path('app/cleanup_trash/unreferenced_'.now()->format('Ymd_His'));
        $moves = [];
        foreach ($candidates as $c) {
            $this->moveToTrash(storage_path('app/public/'.$c['path']), $trash, $moves);
        }
        $this->writeManifest([
            'trash' => $trash,
            'moves' => $moves,
            'type' => 'unreferenced-files',
        ]);

        $this->newLine();
        $this->info('Done: '.$this->fileCount.' unreferenced files moved to trash.');
        $this->info('Size moved: '.$this->formatBytes($this->fileBytes));

        $this->call('cache:clear');

        return 0;
    }

    /** home_page_sections.extra_data is double-encoded JSON with escaped slashes — add those refs too. */
    private function protectDoubleEncodedBanners(): void
    {
        foreach (DB::table('home_page_sections')->whereNotNull('extra_data')->where('extra_data', '!=', '[]')->pluck('extra_data') as $raw) {
            if (! is_string($raw)) {
                continue;
            }
            $inner = json_decode($raw, true);
            if (is_string($inner)) {
                $inner = json_decode($inner, true);
            }
            if ($inner !== null) {
                $this->scanJsonRefs($inner);
            }
        }
    }

    /** @return array<int,array{path:string,size:int}> */
    private function scanDisk(): array
    {
        $base = storage_path('app/public/uploads');
        $candidates = [];
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS)
        );
        $count = 0;
        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, self::EXTENSIONS, true)) {
                continue;
            }
            $rel = 'uploads/'.str_replace($base.'/', '', $file->getPathname());
            $count++;
            if (! isset($this->refs[$rel])) {
                $candidates[] = ['path' => $rel, 'size' => $file->getSize()];
            }
        }
        $this->line("  Total files scanned: {$count}");

        return $candidates;
    }
}
