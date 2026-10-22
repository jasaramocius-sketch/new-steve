<?php

namespace Tests\Feature;

use App\Helpers\SiteChangeLogger;
use Tests\TestCase;

class SiteChangeLoggerTest extends TestCase
{
    public function test_it_writes_change_entry_to_daily_log_file(): void
    {
        $logFile = storage_path('logs/site-changes/'.now()->format('Y-m-d').'.log');

        if (file_exists($logFile)) {
            unlink($logFile);
        }

        SiteChangeLogger::log('info', 'Test change', ['page' => 'home']);

        $this->assertFileExists($logFile);
        $contents = file_get_contents($logFile);
        $this->assertStringContainsString('Test change', $contents);
        $this->assertStringContainsString('"type":"info"', $contents);
    }
}
