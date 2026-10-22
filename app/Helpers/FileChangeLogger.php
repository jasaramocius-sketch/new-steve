<?php

namespace App\Helpers;

class FileChangeLogger
{
    public static function log(string $event, string $message, array $details = []): void
    {
        $date = now()->format('Y-m-d');
        $directory = storage_path('logs/file-changes');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filePath = $directory.DIRECTORY_SEPARATOR.$date.'.log';
        $entry = [
            'timestamp' => now()->toIso8601String(),
            'event' => $event,
            'message' => $message,
            'details' => $details,
        ];

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE).PHP_EOL;

        file_put_contents($filePath, $line, FILE_APPEND | LOCK_EX);
    }
}
