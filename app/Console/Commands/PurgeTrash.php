<?php

namespace App\Console\Commands;

use App\Models\FileRevision;
use App\Models\Revision;
use Illuminate\Console\Command;

class PurgeTrash extends Command
{
    protected $signature = 'trash:purge {--days=15}';

    protected $description = 'Force-delete Revision/FileRevision trash older than N days and purge trashed log files';

    public function handle(): int
    {
        $days = (int) $this->option('days') ?: 15;
        $cutoff = now()->subDays($days);

        // 1) Revisions: force-delete soft-deleted rows older than cutoff.
        $revCount = Revision::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->forceDelete();

        // 2) File Revisions: force-delete rows + their backup files.
        $fileRevs = FileRevision::onlyTrashed()
            ->where('deleted_at', '<', $cutoff)
            ->get();

        $fileCount = 0;
        foreach ($fileRevs as $rev) {
            if ($rev->backup_path) {
                $path = storage_path('file-backups/archive/'.$rev->backup_path);
                if (is_file($path)) {
                    @unlink($path);
                }
            }
            $rev->forceDelete();
            $fileCount++;
        }

        // 3) Log files: delete trashed .log files in the trash dir older than cutoff.
        $logDir = storage_path('logs/site-changes-trash');
        $logCount = 0;
        if (is_dir($logDir)) {
            foreach (scandir($logDir) as $file) {
                if (! preg_match('/\.log$/', $file)) {
                    continue;
                }
                $path = $logDir.DIRECTORY_SEPARATOR.$file;
                if (filemtime($path) < $cutoff->getTimestamp()) {
                    @unlink($path);
                    $logCount++;
                }
            }
        }

        $this->info("Purged trash older than {$days} days.");
        $this->info("Revisions: {$revCount}");
        $this->info("File revisions (+ backups): {$fileCount}");
        $this->info("Log files: {$logCount}");

        return self::SUCCESS;
    }
}
