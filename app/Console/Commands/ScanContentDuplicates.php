<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ScanContentDuplicates extends Command
{
    protected $signature = 'images:scan-content-duplicates';

    protected $description = 'Detect content-level duplicate images (same file hash, different filenames) and report which are referenced';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var array<string,array{path:string,size:int}> hash -> [{path,size}] */
    private array $hashGroups = [];

    /** @var array<string,true> normalized path -> referenced */
    private array $refPaths = [];

    public function handle(): int
    {
        $this->info('Scanning uploads/ and computing MD5 hashes...');
        $this->scanFiles();

        $this->info('Building reference set from DB...');
        $this->buildRefSet();

        $this->info('Finding duplicate groups...');
        $dupGroups = [];
        foreach ($this->hashGroups as $hash => $files) {
            if (count($files) < 2) {
                continue;
            }
            $dupGroups[$hash] = $files;
        }
        uksort($dupGroups, fn ($a, $b) => count($dupGroups[$b]) <=> count($dupGroups[$a]));

        $this->newLine();
        $this->info('Content Duplicates Found: '.count($dupGroups).' groups');
        $this->newLine();

        $totalReclaimable = 0;
        $totalFiles = 0;
        $totalGroups = 0;
        $unreferencedGroups = 0;

        foreach ($dupGroups as $hash => $files) {
            $totalGroups++;
            $totalFiles += count($files);

            $this->line("<info>Group #{$totalGroups}: MD5 {$hash}</info>");
            $groupReclaimable = 0;

            foreach ($files as $f) {
                $rel = $f['path'];
                $refd = $this->isReferenced($rel);
                $sizeStr = $this->formatBytes($f['size']);
                $tag = $refd ? '<info>REFERENCED</info>' : '<comment>UNREFERENCED</comment>';
                $this->line("  {$rel}    {$sizeStr}  {$tag}");
                if (! $refd) {
                    $groupReclaimable += $f['size'];
                }
            }

            $totalReclaimable += $groupReclaimable;

            if ($groupReclaimable > 0) {
                $unreferencedGroups++;
                $this->line("  <comment>Reclaimable: {$this->formatBytes($groupReclaimable)}</comment>");
            }
            $this->newLine();
        }

        $this->info('=== Summary ===');
        $this->table(['property', 'value'], [
            ['Total duplicate groups', $totalGroups],
            ['Groups with unreferenced copies', $unreferencedGroups],
            ['Total files in groups', $totalFiles],
            ['Total files referenced', $totalFiles - $this->countUnreferenced($dupGroups)],
            ['Total files unreferenced', $this->countUnreferenced($dupGroups)],
            ['Reclaimable size', $this->formatBytes($totalReclaimable)],
        ]);

        return 0;
    }

    private function scanFiles(): void
    {
        $base = storage_path('app/public/uploads');
        if (! is_dir($base)) {
            $this->error('uploads/ directory not found');

            return;
        }

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
            $hash = md5_file($file->getPathname());
            if ($hash === false) {
                continue;
            }
            $rel = 'uploads/'.str_replace($base.'/', '', $file->getPathname());
            $this->hashGroups[$hash][] = ['path' => $rel, 'size' => $file->getSize()];
            $count++;
            if ($count % 2000 === 0) {
                $this->line("  ... {$count} files hashed");
            }
        }
        $this->line("  Total files scanned: {$count}");
    }

    private function buildRefSet(): void
    {
        $add = function ($path): void {
            if (! is_string($path) || trim($path) === '') {
                return;
            }
            $norm = $this->normalize($path);
            if ($norm && str_starts_with($norm, 'uploads/')) {
                $this->refPaths[$norm] = true;
            }
        };

        $pairs = [
            ['products', 'image'], ['brands', 'image'], ['categories', 'image'],
            ['blogs', 'image'], ['pages', 'image'], ['home_page_sections', 'image'],
            ['users', 'avatar'], ['users', 'avatar_original'], ['sellers', 'image'],
        ];
        foreach ($pairs as [$table, $column]) {
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column) as $v) {
                $add($v);
            }
        }

        foreach (DB::table('settings')->pluck('value') as $v) {
            if (is_string($v)) {
                $add($v);
            }
        }

        foreach (DB::table('images')->whereNotNull('path')->pluck('path') as $v) {
            $add($v);
        }

        // JSON blobs
        $jsonCols = [
            ['products', 'reviews_data'], ['blogs', 'content_blocks'], ['blogs', 'details'],
            ['pages', 'content'], ['pages', 'content_blocks'],
            ['home_page_sections', 'extra_data'], ['revisions', 'old_values'], ['revisions', 'new_values'],
        ];
        foreach ($jsonCols as [$table, $column]) {
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column) as $raw) {
                if (! is_string($raw)) {
                    continue;
                }
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $this->scanJsonForRefs($decoded, $add);
                } else {
                    $add($raw);
                }
            }
        }
    }

    private function scanJsonForRefs($blob, callable $add): void
    {
        if (is_array($blob)) {
            foreach ($blob as $v) {
                $this->scanJsonForRefs($v, $add);
            }

            return;
        }
        if (! is_string($blob)) {
            return;
        }
        if (preg_match_all('#(?:storage/)?uploads/[0-9]{4}/[0-9]{2}/[A-Za-z0-9_\-\.]+\.(?:webp|jpg|jpeg|png|gif)#i', $blob, $m)) {
            foreach ($m[0] as $p) {
                $add($p);
            }
        }
    }

    private function normalize(string $path): string
    {
        $path = trim($path);
        $path = preg_replace('#^/+#', '', $path);
        $path = preg_replace('#^(storage/|public/)#', '', $path);

        return $path;
    }

    private function isReferenced(string $relativePath): bool
    {
        return isset($this->refPaths[$relativePath]);
    }

    private function countUnreferenced(array $groups): int
    {
        $c = 0;
        foreach ($groups as $files) {
            foreach ($files as $f) {
                if (! $this->isReferenced($f['path'])) {
                    $c++;
                }
            }
        }

        return $c;
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes >= 1024 * 1024) {
            return round($bytes / 1024 / 1024, 2).' MB';
        }
        if ($bytes >= 1024) {
            return round($bytes / 1024, 1).' KB';
        }

        return $bytes.' B';
    }
}
