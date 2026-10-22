<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Summernote submits "<p><br></p>" / "<br>" when a rich text field is left
 * empty. This middleware strips such empty-editor artifacts from every
 * submitted input value across the whole application.
 *
 * Values that contain no meaningful text (after stripping tags) are replaced
 * with an empty string, unless they contain media (img/iframe/video/table/link)
 * or inline classes/styles.
 */
class NormalizeEditorContent
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH'], true)) {
            $this->normalizeInput($request);
        }

        return $next($request);
    }

    protected function normalizeInput(Request $request): void
    {
        $input = $request->all();

        array_walk_recursive($input, function (&$value) {
            if (is_string($value) && $value !== '' && str_contains($value, '<')) {
                $cleaned = $this->clean($value);
                if ($cleaned !== $value) {
                    $value = $cleaned;
                }
            }
        });

        $request->replace($input);
    }

    protected function clean(string $html): string
    {
        $text = trim(html_entity_decode(strip_tags($html)));

        if ($text !== '') {
            return $html;
        }

        $hasMedia = (bool) preg_match('/<img\b|<iframe\b|<video\b|<table\b|<a\b|class\s*=|style\s*=/i', $html);

        return $hasMedia ? $html : '';
    }
}
