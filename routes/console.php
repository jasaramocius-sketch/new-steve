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
