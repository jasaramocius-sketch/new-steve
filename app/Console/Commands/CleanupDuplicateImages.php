<?php

namespace App\Console\Commands;

use FilesystemIterator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class CleanupDuplicateImages extends Command
{
    protected $signature = 'images:cleanup-duplicates {--dry-run : Show what would be done without changing anything}';

    protected $description = 'Point every attachment at one real (canonical) image copy, then move duplicate copies to trash';

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    /** @var array<string,string> normalized path -> count of references */
    protected array $refs = [];

    /** @var array<string,string> old normalized path -> canonical normalized path */
    protected array $remap = [];

    protected int $fileCount = 0;

    protected int $fileBytes = 0;

    protected array $rewriteStats = [];

    public function handle(): int
    {
        $this->info('Building reference set...');
        $this->collectReferences();

        $this->newLine();
        $this->info('Scanning uploads/ for duplicate copies...');
        $groups = $this->findDuplicateGroups();

        $this->info('Duplicate filename groups: '.count($groups));
        $this->newLine();

        // Decide canonical copy per group and build the remap table
        foreach ($groups as $name => $copies) {
            $canonical = $this->chooseCanonical($copies);
            foreach ($copies as $dir => $rel) {
                if ($rel === $canonical || $rel === '') {
                    continue;
                }
                $this->remap[$rel] = $canonical;
            }
        }

        $this->table(['property', 'count'], [
            ['reference remaps to apply', count($this->remap)],
            ['files to move to trash', $this->countFilesToMove($groups)],
            ['approx size to free', $this->formatBytes($this->sizeToFree($groups))],
        ]);

        if (empty($this->remap) && ! $this->hasAnyMoves($groups)) {
            $this->info('No duplicates found — nothing to do.');

            return 0;
        }

        if ($this->option('dry-run')) {
            $preview = $this->previewRewriteCounts();
            if (count($preview)) {
                $this->newLine();
                $this->info('Reference rewrites that would apply:');
                $this->table(['source', 'rows'], collect($preview)->map(fn ($c, $k) => [$k, $c])->values());
            }
            $this->showSampleGroups($groups);
            $this->warn('DRY RUN — no changes were made.');

            return 0;
        }

        // Backup DB before rewriting references
        $this->backupDatabase();

        // 1) Rewrite every reference to the canonical copy
        $this->rewriteReferences();

        // 2) Move duplicate copies (+ their variant siblings) to trash
        $manifest = $this->moveDuplicateFiles($groups);

        $this->writeManifest($manifest);

        $this->newLine();
        $this->info('=== Cleanup Summary ===');
        $this->info('References rewritten:   '.array_sum($this->rewriteStats));
        $this->info('Duplicate files moved:   '.$this->fileCount);
        $this->info('Size freed:              '.$this->formatBytes($this->fileBytes));

        $this->verify();

        return 0;
    }

    protected function collectReferences(): void
    {
        $add = function ($path): void {
            if ($path === null || trim((string) $path) === '') {
                return;
            }
            $norm = $this->normalize($path);
            if ($norm && str_starts_with($norm, 'uploads/')) {
                $this->refs[$norm] = ($this->refs[$norm] ?? 0) + 1;
            }
        };
        $decode = function ($blob) {
            return is_string($blob) ? json_decode($blob, true) : $blob;
        };

        foreach (DB::table('products')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('products')->whereNotNull('reviews_data')->pluck('reviews_data') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('brands')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('categories')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('blogs')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('blogs')->whereNotNull('content_blocks')->pluck('content_blocks') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('blogs')->whereNotNull('details')->pluck('details') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('pages')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('pages')->whereNotNull('content')->pluck('content') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('pages')->whereNotNull('content_blocks')->pluck('content_blocks') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('home_page_sections')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('home_page_sections')->whereNotNull('extra_data')->where('extra_data', '!=', '[]')->pluck('extra_data') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('users')->whereNotNull('avatar')->pluck('avatar') as $v) {
            $add($v);
        }
        foreach (DB::table('users')->whereNotNull('avatar_original')->pluck('avatar_original') as $v) {
            $add($v);
        }
        foreach (DB::table('sellers')->whereNotNull('image')->where('image', '!=', '')->pluck('image') as $v) {
            $add($v);
        }
        foreach (DB::table('settings')->pluck('value') as $v) {
            if (is_string($v) && ! str_contains($v, 'uploads/')) {
                continue;
            }
            $add($v);
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('images')->whereNotNull('path')->pluck('path') as $v) {
            $add($v);
        }
        foreach (DB::table('revisions')->whereNotNull('old_values')->pluck('old_values') as $v) {
            $this->scanJsonRefs($decode($v));
        }
        foreach (DB::table('revisions')->whereNotNull('new_values')->pluck('new_values') as $v) {
            $this->scanJsonRefs($decode($v));
        }
    }

    protected function scanJsonRefs($blob): void
    {
        if (is_array($blob)) {
            foreach ($blob as $v) {
                $this->scanJsonRefs($v);
            }

            return;
        }
        if (! is_string($blob)) {
            return;
        }
        $clean = str_replace('\\/', '/', $blob);
        if (preg_match_all('#(?:storage/)?uploads/20\d{2}/\d{2}/[A-Za-z0-9_\-\.]+\.(?:webp|jpg|jpeg|png|gif)#i', $clean, $m)) {
            foreach ($m[0] as $p) {
                $norm = $this->normalize($p);
                if ($norm) {
                    $this->refs[$norm] = ($this->refs[$norm] ?? 0) + 1;
                }
            }
        }
    }

    protected function normalize(string $path): string
    {
        $path = trim($path);
        $path = preg_replace('#^/+#', '', $path);
        $path = preg_replace('#^(storage/|public/)#', '', $path);

        return $path;
    }

    /** @return array<string,array<string,string>> filename -> [dir => relative path] */
    private function findDuplicateGroups(): array
    {
        $base = storage_path('app/public/uploads');
        if (! is_dir($base)) {
            return [];
        }

        $groups = [];
        $it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, FilesystemIterator::SKIP_DOTS));
        foreach ($it as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, self::EXTENSIONS, true)) {
                continue;
            }
            $dir = str_replace($base.'/', '', dirname($file->getPathname()));
            $groups[$file->getFilename()][$dir] = 'uploads/'.$dir.'/'.$file->getFilename();
        }

        return array_filter($groups, fn ($g) => count($g) > 1);
    }

    /** @return array<string,string> dir -> relative path */
    private function chooseCanonical(array $copies): string
    {
        // Prefer a referenced copy; tie-break to the most recent month folder.
        $best = null;
        $bestScore = -1;
        foreach ($copies as $dir => $rel) {
            $refCount = $this->refs[$rel] ?? 0;
            $month = (int) substr($dir, -2, 2);
            $score = $refCount * 100 + ($month === 0 ? 0 : $month);
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $rel;
            }
        }

        return $best;
    }

    private function countFilesToMove(array $groups): int
    {
        $count = 0;
        foreach ($groups as $name => $copies) {
            $canonical = $this->chooseCanonical($copies);
            foreach ($copies as $dir => $rel) {
                if ($rel === $canonical) {
                    continue;
                }
                $count++;
            }
        }

        return $count;
    }

    private function sizeToFree(array $groups): int
    {
        $base = storage_path('app/public/uploads');
        $bytes = 0;
        foreach ($groups as $name => $copies) {
            $canonical = $this->chooseCanonical($copies);
            foreach ($copies as $dir => $rel) {
                if ($rel === $canonical) {
                    continue;
                }
                $bytes += filesize($base.'/'.$dir.'/'.$name) ?: 0;
            }
        }

        return $bytes;
    }

    private function hasAnyMoves(array $groups): bool
    {
        foreach ($groups as $name => $copies) {
            if (count($copies) > 1) {
                return true;
            }
        }

        return false;
    }

    protected function previewRewriteCounts(): array
    {
        if (empty($this->remap)) {
            return [];
        }
        $normMap = [];
        foreach ($this->remap as $old => $new) {
            $normMap[$this->normalize($old)] = $new;
        }
        $counts = [];

        $pairs = [
            ['products', 'image'], ['brands', 'image'], ['categories', 'image'],
            ['blogs', 'image'], ['pages', 'image'], ['home_page_sections', 'image'],
            ['users', 'avatar'], ['users', 'avatar_original'], ['sellers', 'image'],
        ];
        foreach ($pairs as [$table, $column]) {
            $c = 0;
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column) as $raw) {
                $norm = $this->normalize((string) $raw);
                if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                    $c++;
                }
            }
            if ($c) {
                $counts["$table.$column"] = $c;
            }
        }

        $s = 0;
        foreach (DB::table('settings')->pluck('value') as $raw) {
            if (! is_string($raw)) {
                continue;
            }
            $norm = $this->normalize($raw);
            if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                $s++;
            }
        }
        if ($s) {
            $counts['settings.value'] = $s;
        }

        $i = 0;
        foreach (DB::table('images')->whereNotNull('path')->pluck('path') as $raw) {
            $norm = $this->normalize((string) $raw);
            if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                $i++;
            }
        }
        if ($i) {
            $counts['images.path'] = $i;
        }

        $jsonCols = [
            ['products', 'reviews_data'], ['blogs', 'content_blocks'], ['blogs', 'details'],
            ['pages', 'content'], ['pages', 'content_blocks'],
            ['home_page_sections', 'extra_data'], ['revisions', 'old_values'], ['revisions', 'new_values'],
        ];
        foreach ($jsonCols as [$table, $column]) {
            $c = 0;
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column) as $raw) {
                if (! is_string($raw)) {
                    continue;
                }
                if ($this->replaceInBlob($raw, $normMap) !== $raw) {
                    $c++;
                }
            }
            if ($c) {
                $counts["$table.$column"] = $c;
            }
        }

        return $counts;
    }

    private function showSampleGroups(array $groups): void
    {
        $this->newLine();
        $this->info('Sample duplicate groups:');
        $i = 0;
        foreach ($groups as $name => $copies) {
            if (++$i > 6) {
                break;
            }
            $canonical = $this->chooseCanonical($copies);
            $referenced = array_filter($copies, fn ($rel) => isset($this->refs[$rel]));
            $this->line("  $name  [referenced: ".(count($referenced) ? implode(',', $referenced) : 'none').']');
            foreach ($copies as $dir => $rel) {
                $this->line('      '.($rel === $canonical ? 'KEEP ' : 'MOVE ').$rel);
            }
        }
    }

    protected function backupDatabase(): void
    {
        $this->info('Creating database backup...');
        $filename = 'database/stautoparts_dupcleanup_backup_'.now()->format('Ymd_His').'.sql';
        exec('cd '.base_path()." && mysqldump -u root stautoparts > $filename 2>&1", $output, $ret);
        if ($ret === 0) {
            $this->info("Backup saved to $filename");
        } else {
            $this->warn('DB backup failed (continuing — trash is reversible): '.implode("\n", $output));
        }
    }

    protected function rewriteReferences(): void
    {
        if (empty($this->remap)) {
            return;
        }

        $normMap = [];
        foreach ($this->remap as $old => $new) {
            $normMap[$this->normalize($old)] = $new;
        }

        $write = function (string $table, string $column, string $old, string $new) {
            DB::table($table)->where($column, $old)->update([$column => $new]);
        };

        // Simple path columns
        $pairs = [
            ['products', 'image'],
            ['brands', 'image'],
            ['categories', 'image'],
            ['blogs', 'image'],
            ['pages', 'image'],
            ['home_page_sections', 'image'],
            ['users', 'avatar'],
            ['users', 'avatar_original'],
            ['sellers', 'image'],
        ];
        foreach ($pairs as [$table, $column]) {
            $count = 0;
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column, 'id') as $id => $raw) {
                $norm = $this->normalize((string) $raw);
                if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                    $new = str_starts_with((string) $raw, 'storage/') ? 'storage/'.$normMap[$norm] : $normMap[$norm];
                    $write($table, $column, (string) $raw, $new);
                    $count++;
                }
            }
            if ($count) {
                $this->rewriteStats["$table.$column"] = ($this->rewriteStats["$table.$column"] ?? 0) + $count;
            }
        }

        // settings
        $sCount = 0;
        foreach (DB::table('settings')->pluck('value', 'id') as $id => $raw) {
            if (! is_string($raw)) {
                continue;
            }
            $norm = $this->normalize($raw);
            if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                $new = str_starts_with($raw, 'storage/') ? 'storage/'.$normMap[$norm] : $normMap[$norm];
                DB::table('settings')->where('id', $id)->update(['value' => $new]);
                $sCount++;
            }
        }
        if ($sCount) {
            $this->rewriteStats['settings.value'] = $sCount;
        }

        // images table: path + url
        $iCount = 0;
        foreach (DB::table('images')->whereNotNull('path')->pluck('path', 'id') as $id => $raw) {
            $norm = $this->normalize((string) $raw);
            if (isset($normMap[$norm]) && $normMap[$norm] !== $norm) {
                $new = $normMap[$norm];
                DB::table('images')->where('id', $id)->update(['path' => $new, 'url' => 'storage/'.$new]);
                $iCount++;
            }
        }
        if ($iCount) {
            $this->rewriteStats['images.path'] = $iCount;
        }

        // JSON blobs (deep replace)
        $jsonColumns = [
            ['products', 'reviews_data'],
            ['blogs', 'content_blocks'],
            ['blogs', 'details'],
            ['pages', 'content'],
            ['pages', 'content_blocks'],
            ['home_page_sections', 'extra_data'],
            ['revisions', 'old_values'],
            ['revisions', 'new_values'],
        ];
        foreach ($jsonColumns as [$table, $column]) {
            $count = 0;
            foreach (DB::table($table)->whereNotNull($column)->where($column, '!=', '')->pluck($column, 'id') as $id => $raw) {
                $new = $this->replaceInBlob($raw, $normMap);
                if ($new !== $raw) {
                    DB::table($table)->where('id', $id)->update([$column => $new]);
                    $count++;
                }
            }
            if ($count) {
                $this->rewriteStats["$table.$column"] = ($this->rewriteStats["$table.$column"] ?? 0) + $count;
            }
        }
    }

    /** Deep-replace old image paths inside a JSON/HTML blob. Returns updated string. */
    protected function replaceInBlob($raw, array $normMap): string
    {
        if (! is_string($raw)) {
            return (string) $raw;
        }
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            $updated = $this->deepReplace($decoded, $normMap);
            $encoded = json_encode($updated, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

            return $encoded === false ? $raw : $encoded;
        }

        return $this->stringReplace($raw, $normMap);
    }

    protected function deepReplace($blob, array $normMap)
    {
        if (is_array($blob)) {
            foreach ($blob as $k => $v) {
                $blob[$k] = $this->deepReplace($v, $normMap);
            }

            return $blob;
        }
        if (is_string($blob)) {
            return $this->stringReplace($blob, $normMap);
        }

        return $blob;
    }

    protected function stringReplace(string $text, array $normMap): string
    {
        $clean = str_replace('\\/', '/', $text);
        $updated = $text;
        if (preg_match_all('#(?:storage/)?uploads/20\d{2}/\d{2}/[A-Za-z0-9_\-\.]+\.(?:webp|jpg|jpeg|png|gif)#i', $clean, $m)) {
            $used = [];
            foreach ($m[0] as $p) {
                $norm = $this->normalize($p);
                if (isset($normMap[$norm]) && $normMap[$norm] !== $norm && ! isset($used[$p])) {
                    if ($text !== $clean) {
                        // escaped-separator form: swap \/ back
                        $esc = str_replace('/', '\\/', $normMap[$norm]);
                        $escOld = str_replace('/', '\\/', $norm);
                        $updated = str_replace($escOld, $esc, $updated);
                    } else {
                        $updated = str_replace($norm, $normMap[$norm], $updated);
                    }
                    $used[$p] = true;
                }
            }
        }

        return $updated;
    }

    /** @return array{trash:string, moves:array, remap:array} */
    private function moveDuplicateFiles(array $groups): array
    {
        $base = storage_path('app/public/uploads');
        $trash = storage_path('app/cleanup_trash/dup_cleanup_'.now()->format('Ymd_His'));
        $moves = [];

        foreach ($groups as $name => $copies) {
            $canonical = $this->chooseCanonical($copies);
            foreach ($copies as $dir => $rel) {
                if ($rel === $canonical) {
                    continue;
                }
                $source = $base.'/'.$dir.'/'.$name;
                $this->moveToTrash($source, $trash, $moves);
                // variant siblings of the moved original
                foreach (glob($base.'/'.$dir.'/'.pathinfo($name, PATHINFO_FILENAME).'_*.webp') as $variant) {
                    $this->moveToTrash($variant, $trash, $moves);
                }
            }
        }

        return ['trash' => $trash, 'moves' => $moves, 'remap' => $this->remap];
    }

    protected function moveToTrash(string $source, string $trash, array &$moves): void
    {
        if (! file_exists($source)) {
            return;
        }
        $rel = str_replace(storage_path('app/public/'), '', $source);
        $dest = $trash.'/'.dirname(str_replace(storage_path('app/public/uploads/'), '', $source));
        File::ensureDirectoryExists($dest);
        if (! @rename($source, $dest.'/'.basename($source))) {
            if (! @copy($source, $dest.'/'.basename($source))) {
                $this->warn("Could not move $source");

                return;
            }
            @unlink($source);
        }
        $this->fileCount++;
        $size = filesize($dest.'/'.basename($source)) ?: 0;
        $this->fileBytes += $size;
        $moves[] = ['from' => $rel, 'to' => str_replace(storage_path('app/public/'), '', $dest.'/'.basename($source)), 'bytes' => $size];
    }

    protected function writeManifest(array $manifest): void
    {
        $trash = $manifest['trash'];
        $dir = dirname($trash);
        if (! is_dir($dir)) {
            File::ensureDirectoryExists($dir);
        }
        $manifest['refs_total'] = count($this->refs);
        $manifest['rewrite_stats'] = $this->rewriteStats;
        File::put($trash.'/MANIFEST.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->info("Manifest: $trash/MANIFEST.json");
    }

    private function verify(): void
    {
        $this->newLine();
        $this->info('Verifying...');
        $groups = $this->findDuplicateGroups();
        $this->line('Duplicate filename groups remaining: '.count($groups));

        $missing = 0;
        foreach ($this->refs as $path => $count) {
            if (! file_exists(public_path($path)) && ! file_exists(storage_path('app/public/'.$path))) {
                // variants regenerate on demand; only flag non-variant originals
                if (! preg_match('/_\d{3}\.webp$/', $path)) {
                    $missing++;
                }
            }
        }
        $this->line('Referenced paths with missing file: '.$missing);
    }

    protected function formatBytes(int $bytes): string
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
