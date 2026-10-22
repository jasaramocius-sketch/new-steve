<?php

namespace App\Helpers;

class SiteChangeLogger
{
    public static function log(string $type, string $message, array $context = []): void
    {
        $date = now()->format('Y-m-d');
        $directory = storage_path('logs/site-changes');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.DIRECTORY_SEPARATOR.$date.'.log';
        $entry = [
            'timestamp' => now()->toIso8601String(),
            'type' => $type,
            'message' => $message,
            'context' => $context,
        ];

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;

        try {
            file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
            @chmod($filePath, 0664);
        } catch (\Throwable $e) {
            // Logging must never break a request.
        }
    }
}
