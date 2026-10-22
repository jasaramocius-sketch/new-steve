<?php

namespace App\Http\Middleware;

use App\Models\Blog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublishDueScheduledBlogs
{
    public function handle(Request $request, Closure $next): Response
    {
        Blog::autoPublishDueScheduled();

        return $next($request);
    }
}
