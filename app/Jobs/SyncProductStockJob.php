<?php

namespace App\Jobs;

use App\Services\StockFeedSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600;

    public $tries = 1;

    public function __construct(private readonly bool $dryRun = false) {}

    public function handle(StockFeedSyncService $service): void
    {
        $service->run($this->dryRun);
    }
}
