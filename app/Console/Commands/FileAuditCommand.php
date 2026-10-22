<?php

namespace App\Console\Commands;

use App\Models\FileRevision;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class FileAuditCommand extends Command
{
    protected $signature = 'file:audit
        {--init : Initialize the mirror and state file}
        {--watch : Keep watching in a loop}
        {--interval=60 : Polling interval in seconds (default 60)}
        {--max-archive-days=90 : Delete archives older than this many days}
        {--truncate-diffs : Truncate old diff data to save DB space}
        {--truncate-diffs-days=90 : Truncate diffs older than this many days}
        {--per-file-limit=50 : Keep only last N revisions per file, truncate the rest}';

    protected $description = 'Track file changes with backup snapshots';

    protected array $watchDirs = [
        'app', 'config', 'database', 'resources', 'routes', 'public/assets',
    ];

    protected array $excludePatterns = [
        '/vendor/', '/node_modules/', '/storage/', '/.git/', '/.idea/',
        '/.vscode/', '/.nova/', '/.cursor/', '/.zed/', '/.codex/',
        '/bootstrap/cache/', '*.log', '.DS_Store',
    ];

    private string $basePath;

    private string $backupRoot;

    private string $mirrorDir;

    private string $archiveDir;

    private string $stateFile;

    public function __construct()
    {
        parent::__construct();
        $this->basePath = base_path();
        $this->backupRoot = storage_path('file-backups');
        $this->mirrorDir = $this->backupRoot.'/mirror';
        $this->archiveDir = $this->backupRoot.'/archive';
        $this->stateFile = $this->backupRoot.'/.state.json';
    }

    public function handle(): int
    {
        if ($this->option('init')) {
            return $this->initMirror();
        }

        if ($this->option('max-archive-days')) {
            $this->purgeOldArchives((int) $this->option('max-archive-days'));
        }

        if ($this->option('truncate-diffs')) {
            $this->truncateOldDiffs((int) $this->option('truncate-diffs-days'));
        }

        if ($this->option('per-file-limit')) {
            $this->truncatePerFileLimit((int) $this->option('per-file-limit'));
        }

        if ($this->option('watch')) {
            return $this->watchLoop();
        }

        return $this->scanOnce();
    }

    protected function initMirror(): int
    {
        $this->info('Initializing file audit mirror...');

        $files = $this->collectFiles();

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $state = [];
        foreach ($files as $relative) {
            $full = $this->basePath.'/'.$relative;
            $hash = md5_file($full);
            $state[$relative] = $hash;

            $mirrorPath = $this->mirrorDir.'/'.$relative;
            $mirrorDir = dirname($mirrorPath);
            if (! is_dir($mirrorDir)) {
                mkdir($mirrorDir, 0755, true);
            }
            copy($full, $mirrorPath);

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        $stateDir = dirname($this->stateFile);
        if (! is_dir($stateDir)) {
            mkdir($stateDir, 0755, true);
        }
        file_put_contents($this->stateFile, json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info('Done. Tracked '.count($state).' files.');

        return self::SUCCESS;
    }

    protected function watchLoop(): int
    {
        $interval = max(2, (int) $this->option('interval'));
        $this->info("Watching files every {$interval}s. Press Ctrl+C to stop.");

        if (! file_exists($this->stateFile)) {
            $this->warn('State file not found. Running --init first...');
            $this->initMirror();
        }

        while (true) {
            $this->scanChanges();
            sleep($interval);
        }

        return self::SUCCESS;
    }

    protected function scanOnce(): int
    {
        if (! file_exists($this->stateFile)) {
            $this->warn('State file not found. Run with --init first.');

            return self::FAILURE;
        }

        $this->scanChanges();

        return self::SUCCESS;
    }

    protected function scanChanges(): void
    {
        $state = json_decode(file_get_contents($this->stateFile), true) ?? [];
        $current = $this->collectFiles();
        $currentMap = [];

        foreach ($current as $relative) {
            $full = $this->basePath.'/'.$relative;
            $hash = md5_file($full);
            $currentMap[$relative] = $hash;
        }

        $added = array_diff_key($currentMap, $state);
        $removed = array_diff_key($state, $currentMap);

        $modified = [];
        foreach ($currentMap as $relative => $hash) {
            if (isset($state[$relative]) && $state[$relative] !== $hash) {
                $modified[$relative] = $hash;
            }
        }

        $userId = $this->detectUserId();

        foreach ($added as $relative => $hash) {
            $this->handleCreated($relative, $hash, $userId);
        }

        foreach ($removed as $relative => $hash) {
            $this->handleDeleted($relative, $hash, $userId);
        }

        foreach ($modified as $relative => $hash) {
            $this->handleUpdated($relative, $hash, $state[$relative], $userId);
        }

        // Update state file
        $newState = $currentMap;
        file_put_contents($this->stateFile, json_encode($newState, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    protected function handleCreated(string $relative, string $hash, ?int $userId): void
    {
        $full = $this->basePath.'/'.$relative;
        $mirrorPath = $this->mirrorDir.'/'.$relative;
        $mirrorDir = dirname($mirrorPath);

        if (! is_dir($mirrorDir)) {
            mkdir($mirrorDir, 0755, true);
        }
        copy($full, $mirrorPath);
        $this->archiveBackup($relative, 'created');

        FileRevision::create([
            'file_path' => $relative,
            'event' => 'created',
            'content_hash' => $hash,
            'backup_path' => null,
            'user_id' => $userId,
        ]);

        $this->line("[created] {$relative}");
    }

    protected function handleUpdated(string $relative, string $newHash, string $oldHash, ?int $userId): void
    {
        $full = $this->basePath.'/'.$relative;
        $mirrorPath = $this->mirrorDir.'/'.$relative;

        // Archive the OLD mirror content (before change)
        $backupRel = $this->archiveBackup($relative, 'updated');

        // Compute diff between old mirror and new file
        $diff = null;
        if (file_exists($mirrorPath)) {
            $oldContent = file_get_contents($mirrorPath);
            $newContent = file_get_contents($full);
            $diff = $this->computeDiff($oldContent, $newContent, $relative);
        }

        // Update mirror with new content
        $mirrorDir = dirname($mirrorPath);
        if (! is_dir($mirrorDir)) {
            mkdir($mirrorDir, 0755, true);
        }
        copy($full, $mirrorPath);

        FileRevision::create([
            'file_path' => $relative,
            'event' => 'updated',
            'content_hash' => $newHash,
            'backup_path' => $backupRel,
            'diff' => $diff,
            'user_id' => $userId,
        ]);

        $this->line("[updated] {$relative}");
    }

    protected function handleDeleted(string $relative, string $hash, ?int $userId): void
    {
        $mirrorPath = $this->mirrorDir.'/'.$relative;

        // Archive the mirror copy (last known good version)
        $backupRel = $this->archiveBackup($relative, 'deleted');

        // Remove mirror
        if (file_exists($mirrorPath)) {
            unlink($mirrorPath);
        }

        FileRevision::create([
            'file_path' => $relative,
            'event' => 'deleted',
            'content_hash' => $hash,
            'backup_path' => $backupRel,
            'user_id' => $userId,
        ]);

        $this->line("[deleted] {$relative}");
    }

    protected function archiveBackup(string $relative, string $event): ?string
    {
        $mirrorPath = $this->mirrorDir.'/'.$relative;

        if (! file_exists($mirrorPath)) {
            return null;
        }

        $timestamp = now()->format('Y-m-d_H-i-s');
        $backupRel = $timestamp.'/'.$relative.'.bak';
        $backupFull = $this->archiveDir.'/'.$backupRel;
        $backupDir = dirname($backupFull);

        if (! is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        copy($mirrorPath, $backupFull);

        return $backupRel;
    }

    protected function detectUserId(): ?int
    {
        // Try to detect from current HTTP request
        if (Auth::check()) {
            return Auth::id();
        }

        // Try to read last logged-in user from session (if available via CLI context)
        try {
            if (session_status() === PHP_SESSION_NONE && headers_sent() === false) {
                @session_start();
            }
            $userId = $_SESSION['user_profile']['id'] ?? null;
            if ($userId) {
                return (int) $userId;
            }
        } catch (\Throwable $e) {
            // silent
        }

        return null;
    }

    protected function collectFiles(): array
    {
        $files = [];

        foreach ($this->watchDirs as $dir) {
            $fullDir = $this->basePath.'/'.$dir;
            if (! is_dir($fullDir)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($fullDir, \RecursiveDirectoryIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::SELF_FIRST
            );

            foreach ($iterator as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $relative = str_replace($this->basePath.'/', '', $file->getRealPath());
                $relative = str_replace(DIRECTORY_SEPARATOR, '/', $relative);

                if (! $this->shouldExclude($relative)) {
                    $files[] = $relative;
                }
            }
        }

        sort($files);

        return $files;
    }

    protected function shouldExclude(string $relative): bool
    {
        foreach ($this->excludePatterns as $pattern) {
            if (str_starts_with($pattern, '*')) {
                if (str_ends_with($relative, ltrim($pattern, '*'))) {
                    return true;
                }
            } elseif (str_contains($relative, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function computeDiff(string $old, string $new, string $relative): string
    {
        $oldLines = explode("\n", str_replace("\r\n", "\n", $old));
        $newLines = explode("\n", str_replace("\r\n", "\n", $new));

        $oldLen = count($oldLines);
        $newLen = count($newLines);
        $maxLen = max($oldLen, $newLen);

        $output = "--- a/{$relative}\n+++ b/{$relative}\n";
        $i = 0;
        $j = 0;
        $chunk = [];

        while ($i < $oldLen || $j < $newLen) {
            if ($i < $oldLen && $j < $newLen && $oldLines[$i] === $newLines[$j]) {
                if (! empty($chunk)) {
                    $output .= $this->formatChunk($chunk, $i - count($chunk) + 1, $j - count($chunk) + 1);
                    $chunk = [];
                }
                $i++;
                $j++;
            } else {
                $chunk[] = ['old' => $i < $oldLen ? $oldLines[$i] : null, 'new' => $j < $newLen ? $newLines[$j] : null];
                if ($i < $oldLen) {
                    $i++;
                }
                if ($j < $newLen) {
                    $j++;
                }
            }
        }

        if (! empty($chunk)) {
            $output .= $this->formatChunk($chunk, $i - count($chunk) + 1, $j - count($chunk) + 1);
        }

        // If diff is empty (binary or identical), mark it
        if ($output === "--- a/{$relative}\n+++ b/{$relative}\n") {
            $output .= "(no visible changes)\n";
        }

        return $output;
    }

    protected function formatChunk(array $chunk, int $oldStart, int $newStart): string
    {
        $output = "@@ -{$oldStart},{$newStart} @@\n";
        foreach ($chunk as $line) {
            if ($line['old'] !== null && $line['new'] !== null) {
                $output .= "-{$line['old']}\n+{$line['new']}\n";
            } elseif ($line['old'] !== null) {
                $output .= "-{$line['old']}\n";
            } elseif ($line['new'] !== null) {
                $output .= "+{$line['new']}\n";
            }
        }

        return $output;
    }

    protected function truncateOldDiffs(int $olderThanDays): void
    {
        $count = FileRevision::truncateDiffs($olderThanDays);

        if ($count > 0) {
            $this->line("[truncated] {$count} old diff(s) saved to summary");
        }
    }

    protected function truncatePerFileLimit(int $keepPerFile): void
    {
        FileRevision::truncatePerFileLimit($keepPerFile);
        $this->line("[per-file-limit] Kept last {$keepPerFile} revisions per file, truncated older diffs");
    }

    protected function purgeOldArchives(int $maxDays): void
    {
        if (! is_dir($this->archiveDir)) {
            return;
        }

        $cutoff = now()->subDays($maxDays)->timestamp;
        $count = 0;

        $dirs = glob($this->archiveDir.'/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $name = basename($dir);
            $ts = strtotime(str_replace('_', ' ', $name));
            if ($ts && $ts < $cutoff) {
                File::deleteDirectory($dir);
                $count++;
            }
        }

        if ($count > 0) {
            $this->line("[purged] {$count} old archive(s)");
        }
    }
}
