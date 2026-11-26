<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

final class BulkOperation
{
    /** 64 MB, expressed in kilobytes for Laravel's `max` validation rule. */
    public const MAX_UPLOAD_KB = 65536;

    public const RECOMMENDED_UPLOAD = '64M';
    public const RECOMMENDED_POST = '80M';
    public const RECOMMENDED_EXECUTION = 300;

    public const STATE_TTL_MINUTES = 1440;

    public static function progressKey(string $id): string
    {
        return 'bulk-operation:'.$id;
    }

    public static function init(string $id, array $state): array
    {
        $state = array_merge([
            'id' => $id,
            'type' => null,
            'status' => 'queued',
            'file_name' => null,
            'processed' => 0,
            'total' => 0,
            'message' => 'Queued. Waiting for a queue worker...',
            'errors' => [],
            'error_count' => 0,
            'result' => [],
            'export_path' => null,
            'download_url' => null,
            'created_at' => now()->toIso8601String(),
            'updated_at' => now()->toIso8601String(),
        ], $state, ['id' => $id]);

        Cache::put(self::progressKey($id), $state, now()->addMinutes(self::STATE_TTL_MINUTES));

        return $state;
    }

    public static function get(string $id): ?array
    {
        return Cache::get(self::progressKey($id));
    }

    public static function update(string $id, array $changes): array
    {
        $state = self::get($id) ?? self::init($id, []);
        $state = array_merge($state, $changes, ['updated_at' => now()->toIso8601String()]);

        Cache::put(self::progressKey($id), $state, now()->addMinutes(self::STATE_TTL_MINUTES));

        return $state;
    }

    /**
     * Warnings shown on Server Status when PHP is too constrained for bulk work.
     */
    public static function limitWarnings(): array
    {
        $warnings = [];

        if (self::parseSize(ini_get('upload_max_filesize')) < self::parseSize(self::RECOMMENDED_UPLOAD)) {
            $warnings[] = 'upload_max_filesize is below '.self::RECOMMENDED_UPLOAD.'. The bulk import upload limit is '.self::RECOMMENDED_UPLOAD.'.';
        }

        if (self::parseSize(ini_get('post_max_size')) < self::parseSize(self::RECOMMENDED_POST)) {
            $warnings[] = 'post_max_size is below '.self::RECOMMENDED_POST.' and will reject large CSV uploads before Laravel sees them.';
        }

        $execution = (int) ini_get('max_execution_time');
        if ($execution > 0 && $execution < self::RECOMMENDED_EXECUTION) {
            $warnings[] = 'max_execution_time is '.$execution.'s. Bulk imports/exports are queued, but set it to at least '.self::RECOMMENDED_EXECUTION.'s for the request that starts them.';
        }

        return $warnings;
    }

    public static function parseSize(mixed $value): int
    {
        $value = strtolower(trim((string) $value));
        if ($value === '' || $value === '-1' || $value === '0') {
            return PHP_INT_MAX;
        }

        if (! preg_match('/^([0-9]+(?:\.[0-9]+)?)\s*([kmgt]?)b?$/', $value, $matches)) {
            return 0;
        }

        $multipliers = [
            '' => 1,
            'k' => 1024,
            'm' => 1024 ** 2,
            'g' => 1024 ** 3,
            't' => 1024 ** 4,
        ];

        return (int) ((float) $matches[1] * $multipliers[$matches[2]]);
    }
}
