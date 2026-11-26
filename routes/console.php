<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('file:audit --truncate-diffs --max-archive-days=90')
    ->dailyAt('03:00')
    ->withoutOverlapping();

Schedule::command('trash:purge --days=15')
    ->dailyAt('03:30')
    ->withoutOverlapping();

Schedule::call(fn () => App\Models\Blog::autoPublishDueScheduled())
    ->name('blog:auto-publish-due-scheduled')
    ->everyMinute()
    ->withoutOverlapping();

$stockSync = App\Support\StockSync::settings();
if ($stockSync['stock_sync_enabled'] && in_array($stockSync['stock_sync_frequency'], ['hourly', 'daily'], true)) {
    $stockSchedule = Schedule::command('stocks:sync')->withoutOverlapping();

    if ($stockSync['stock_sync_frequency'] === 'hourly') {
        $stockSchedule->hourly();
    } else {
        $stockSchedule->dailyAt('04:00');
    }
}
