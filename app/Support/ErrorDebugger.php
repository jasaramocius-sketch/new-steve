<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Throwable;

class ErrorDebugger
{
    public const CHANNEL = 'debug500';

    public const LOG_FILE = 'debug500.log';

    /**
     * Write a sanitized, structured record about a 500-level exception.
     *
     * No request body, cookies, headers or query strings are stored.
     */
    public static function report(Throwable $exception, ?Request $request = null): array
    {
        $request ??= request();
        $entry = self::buildEntry($exception, $request);

        try {
            Log::channel(self::CHANNEL)->error(self::encode($entry));
        } catch (Throwable) {
            // Debug logging must never interfere with normal error handling.
        }

        return $entry;
    }

    /**
     * Read the most recent debug records, newest first.
     */
    public static function latest(int $limit = 10): array
    {
        $path = self::path();
        if (! is_file($path) || ! is_readable($path)) {
            return [];
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }

        $limit = max(1, min($limit, 50));
        $entries = [];

        foreach (array_reverse($lines) as $line) {
            $start = strpos($line, '{');
            $decoded = $start !== false ? json_decode(substr($line, $start), true) : null;

            $entries[] = is_array($decoded) ? $decoded : ['raw' => $line];

            if (count($entries) >= $limit) {
                break;
            }
        }

        return $entries;
    }

    public static function path(): string
    {
        return storage_path('logs/'.self::LOG_FILE);
    }

    protected static function buildEntry(Throwable $exception, ?Request $request): array
    {
        return [
            'id' => bin2hex(random_bytes(6)),
            'time' => now()->toIso8601String(),
            'env' => app()->environment(),
            'method' => $request?->getMethod(),
            'path' => $request?->path(),
            'route' => self::routeInfo($request, 'name'),
            'route_uri' => self::routeInfo($request, 'uri'),
            'user_id' => self::userId(),
            'exception' => get_class($exception),
            'message' => $exception->getMessage(),
            'code' => $exception->getCode(),
            'file' => self::shortPath($exception->getFile()),
            'line' => $exception->getLine(),
            'frames' => self::frames($exception),
            'memory_peak_mb' => round(memory_get_peak_usage(true) / 1048576, 2),
            'php' => PHP_VERSION,
        ];
    }

    protected static function routeInfo(?Request $request, string $key): ?string
    {
        if (! $request) {
            return null;
        }

        try {
            $route = $request->route();
            if (! $route) {
                return null;
            }

            $value = $key === 'name' ? $route->getName() : $route->uri();

            return $value ? (string) $value : null;
        } catch (Throwable) {
            return null;
        }
    }

    protected static function userId(): ?int
    {
        try {
            $id = auth()->id();

            return is_numeric($id) ? (int) $id : null;
        } catch (Throwable) {
            return null;
        }
    }

    protected static function frames(Throwable $exception): array
    {
        $frames = [];

        foreach ($exception->getTrace() as $index => $trace) {
            if ($index >= 8) {
                break;
            }

            $call = trim(
                (string) ($trace['class'] ?? '').
                (string) ($trace['type'] ?? '').
                (string) ($trace['function'] ?? '')
            );

            $frames[] = [
                'file' => self::shortPath($trace['file'] ?? null),
                'line' => $trace['line'] ?? null,
                'call' => $call !== '' ? $call : null,
            ];
        }

        return $frames;
    }

    protected static function shortPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $base = base_path().DIRECTORY_SEPARATOR;

        return str_starts_with($path, $base) ? substr($path, strlen($base)) : $path;
    }

    protected static function encode(array $entry): string
    {
        return json_encode(
            $entry,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE
        ) ?: '{}';
    }
}
