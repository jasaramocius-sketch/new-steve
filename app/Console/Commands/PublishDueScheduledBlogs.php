<?php

namespace App\Console\Commands;

use App\Models\Blog;
use Illuminate\Console\Command;

class PublishDueScheduledBlogs extends Command
{
    protected $signature = 'blog:publish-due-scheduled';

    protected $description = 'Sirf wo scheduled blogs publish kare jinke is due date/time nikal chuki (published_at <= now)';

    public function handle(): int
    {
        $count = Blog::autoPublishDueScheduled();
        $this->info("Published {$count} due scheduled blog(s).");
        return self::FAILURE === 0 ? self::SUCCESS : self::SUCCESS;
    }
}
