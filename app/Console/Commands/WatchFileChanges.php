<?php

namespace App\Console\Commands;

use App\Helpers\FileChangeLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class WatchFileChanges extends Command
{
    protected $signature = 'watch:file-changes {--path=app : Path to watch relative to base path} {--interval=2 : Poll interval in seconds}';

    protected $description = 'Watch a directory for file changes and log them to daily files.';

    public function handle(): int
    {
        $watchPath = base_path($this->option('path'));
        $interval = (int) $this->option('interval');

        if (! is_dir($watchPath)) {
            $this->error("Directory not found: {$watchPath}");

            return self::FAILURE;
        }

        $this->info("Watching directory: {$watchPath}");
        $this->info("Polling every {$interval} seconds. Press Ctrl+C to stop.");

        $state = $this->snapshotDirectory($watchPath);

        while (true) {
            sleep($interval);
            $newState = $this->snapshotDirectory($watchPath);

            $added = array_diff_key($newState, $state);
            $removed = array_diff_key($state, $newState);
            $modified = array_filter($newState, fn ($hash, $path) => isset($state[$path]) && $state[$path] !== $hash, ARRAY_FILTER_USE_BOTH);

            foreach ($added as $path => $hash) {
                $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
                FileChangeLogger::log('created', 'File created', ['file' => $relative]);
                $this->line("[created] {$relative}");
            }

            foreach ($removed as $path => $hash) {
                $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
                FileChangeLogger::log('deleted', 'File deleted', ['file' => $relative]);
                $this->line("[deleted] {$relative}");
            }

            foreach ($modified as $path => $hash) {
                $relative = str_replace(base_path().DIRECTORY_SEPARATOR, '', $path);
                FileChangeLogger::log('modified', 'File modified', ['file' => $relative]);
                $this->line("[modified] {$relative}");
            }

            $state = $newState;
        }

        return self::SUCCESS;
    }

    protected function snapshotDirectory(string $dir): array
    {
        $files = File::allFiles($dir);
        $snapshot = [];

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $snapshot[$path] = md5_file($path) ?: '';
        }

        return $snapshot;
    }
}
