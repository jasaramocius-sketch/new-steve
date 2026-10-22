<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Support\Facades\DB;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ConsolidateContentDuplicates extends CleanupDuplicateImages
{
    protected $signature = 'images:consolidate-content {--dry-run : Preview the consolidation without changing anything} {--verify-only : Re-scan disk and DB to check consistency after a run}';

    protected $description = 'Collapse byte-identical images (same MD5 content, different filenames) into one canonical copy per group, rewriting all references';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var array<string,array{path:string,size:int}> hash -> [{path,size}] */
    private array $contentGroups = [];

    /** @var array<string,true> paths of files already on disk (post-rewrite verification) */
    private array $diskPaths = [];

    public function handle(): int
    {
        $this->info('Building reference set from DB...');
        $this->collectReferences();

        $this->info('Scanning uploads/ by content (MD5)...');
        $this->scanContentGroups();

        $dupGroups = $this->duplicateGroups();

        if ($this->option('verify-only')) {
            $this->verify();

            return 0;
        }

        if (empty($dupGroups)) {
            $this->info('No content duplicates found.');

            return 0;
        }

        $this->buildRemap($dupGroups);
        $toMove = $this->countFilesToMove($dupGroups);
        $bytes = $this->sizeToFree($dupGroups);

        $this->newLine();
        $this->table(['property', 'value'], [
            ['Duplicate content groups', count($dupGroups)],
            ['Files to consolidate', $toMove],
            ['Estimated size freed', $this->formatBytes($bytes)],
        ]);

        if ($this->option('dry-run')) {
            $this->showSamples($dupGroups);
            $counts = $this->previewRewriteCounts();
            if ($counts) {
                $this->newLine();
                $this->info('Rewrite preview:');
                $this->table(['source', 'rows'], array_map(fn ($c, $k) => [$k, $c], $counts, array_keys($counts)));
            }
            $this->warn('DRY RUN — no changes were made.');

            return 0;
        }

        $this->backupDatabase();

        $this->info('Rewriting references...');
        $this->rewriteReferences();

        $manifest = $this->moveContentFiles($dupGroups);
        $this->writeManifest($manifest);

        $this->newLine();
        $this->info('Consolidation complete.');
        $this->info('Files moved to trash: '.$this->fileCount);
        $this->info('Size freed:           '.$this->formatBytes($this->fileBytes));

        $this->verify();

        $this->call('cache:clear');

        return 0;
    }

    private function scanContentGroups(): void
    {
        $base = storage_path('app/public/uploads');
        if (! is_dir($base)) {
            $this->error('uploads/ directory not found.');

            return;
        }
        $this->contentGroups = [];
        $count = 0;
        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS | FilesystemIterator::FOLLOW_SYMLINKS)
        );
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
            $this->contentGroups[$hash][] = ['path' => $rel, 'size' => $file->getSize()];
            $this->diskPaths[$rel] = true;
            $count++;
            if ($count % 3000 === 0) {
                $this->line("  ... {$count} files hashed");
            }
        }
        $this->line("  Total files scanned: {$count}");
    }

    /** @return array<string,array{path:string,size:int}> */
    private function duplicateGroups(): array
    {
        $groups = [];
        foreach ($this->contentGroups as $hash => $files) {
            if (count($files) < 2) {
                continue;
            }
            $groups[$hash] = $files;
        }
        uksort($groups, fn ($a, $b) => count($groups[$b]) <=> count($groups[$a]));

        return $groups;
    }

    private function buildRemap(array $groups): void
    {
        $this->remap = [];
        foreach ($groups as $files) {
            $canonical = $this->chooseContentCanonical($files);
            foreach ($files as $f) {
                if ($f['path'] === $canonical) {
                    continue;
                }
                $this->remap[$f['path']] = $canonical;
            }
        }
    }

    /**
     * Prefer the most-referenced copy; ties favour a non-variant original, then the newest month.
     * All members are byte-identical, so content quality is never lost.
     */
    private function chooseContentCanonical(array $files): string
    {
        $best = null;
        $bestScore = -1;
        foreach ($files as $f) {
            $refCount = $this->refs[$f['path']] ?? 0;
            $isVariant = (bool) preg_match('/_(?:250|500)\.webp$/i', $f['path']);
            $month = (preg_match('#/20\d{2}/(\d{2})/#', $f['path'], $m)) ? (int) $m[1] : 0;
            $score = $refCount * 10000 + ($isVariant ? 0 : 5000) + $month;
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $f['path'];
            }
        }

        return $best;
    }

    private function countFilesToMove(array $groups): int
    {
        $c = 0;
        foreach ($groups as $files) {
            $canon = $this->chooseContentCanonical($files);
            foreach ($files as $f) {
                if ($f['path'] !== $canon) {
                    $c++;
                }
            }
        }

        return $c;
    }

    private function sizeToFree(array $groups): int
    {
        $bytes = 0;
        foreach ($groups as $files) {
            $canon = $this->chooseContentCanonical($files);
            foreach ($files as $f) {
                if ($f['path'] !== $canon) {
                    $bytes += $f['size'];
                }
            }
        }

        return $bytes;
    }

    private function moveContentFiles(array $groups): array
    {
        $trash = storage_path('app/cleanup_trash/content_dedup_'.now()->format('Ymd_His'));
        $moves = [];
        foreach ($groups as $files) {
            $canon = $this->chooseContentCanonical($files);
            foreach ($files as $f) {
                if ($f['path'] === $canon) {
                    continue;
                }
                $this->moveToTrash(storage_path('app/public/'.$f['path']), $trash, $moves);
            }
        }

        return ['trash' => $trash, 'moves' => $moves, 'remap' => $this->remap];
    }

    private function showSamples(array $groups): void
    {
        $this->newLine();
        $this->info('Largest groups:');
        $shown = 0;
        foreach ($groups as $hash => $files) {
            if ($shown >= 8) {
                break;
            }
            $shown++;
            $sizes = array_sum(array_column($files, 'size'));
            $this->line("<info>Group: MD5 {$hash} — ".count($files).' files, '.$this->formatBytes($sizes).'</info>');
            $canon = $this->chooseContentCanonical($files);
            foreach ($files as $f) {
                $mark = $f['path'] === $canon ? 'CANON' : 'move →';
                $tag = isset($this->refs[$f['path']]) ? 'ref' : 'no-ref';
                $this->line("    {$mark}  {$f['path']}  ({$tag})");
            }
            $this->newLine();
        }
    }

    private function verify(): void
    {
        $this->newLine();
        $this->info('Verifying...');

        // 1) re-scan disk and rebuild reference set from the rewritten DB
        $this->contentGroups = [];
        $this->diskPaths = [];
        $this->scanContentGroups();
        $this->refs = [];
        $this->collectReferences();

        $remaining = $this->duplicateGroups();
        $this->line('Content duplicate groups remaining: '.count($remaining));

        // 2) every referenced path resolves to an actual file on disk
        $missing = [];
        foreach ($this->refs as $path => $count) {
            if (isset($this->diskPaths[$path])) {
                continue;
            }
            if (! file_exists(public_path($path)) && ! file_exists(storage_path('app/public/'.$path))) {
                $missing[$path] = $count;
            }
        }
        $this->line('Referenced paths with missing file: '.count($missing));
        if (count($missing) > 0 && count($missing) <= 40) {
            foreach ($missing as $path => $count) {
                $this->line("    missing  {$path}  (x{$count})");
            }
        }
    }
}
