<?php

namespace App\Console\Commands;

use App\Services\StockFeedSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncProductStock extends Command
{
    protected $signature = 'stocks:sync
                            {--dry-run : Parse the feed and report matches without writing stock}
                            {--force : Run even when the sync toggle is off}';

    protected $description = 'Sync product stock from the configured supplier feed';

    public function handle(StockFeedSyncService $service): int
    {
        try {
            $result = $service->run(
                (bool) $this->option('dry-run'),
                (bool) $this->option('force')
            );
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->line($result['message']);

        return self::SUCCESS;
    }
}
