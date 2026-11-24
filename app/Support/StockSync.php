<?php

namespace App\Support;

use App\Models\Setting;
use Throwable;

final class StockSync
{
    public const FORMATS = ['json', 'csv'];
    public const KEY_FIELDS = ['part_number', 'id', 'slug'];
    public const FREQUENCIES = ['manual', 'hourly', 'daily'];

    public static function defaults(): array
    {
        return [
            'stock_feed_url' => '',
            'stock_feed_token' => '',
            'stock_feed_format' => 'json',
            'stock_feed_items_path' => '',
            'stock_feed_key_field' => 'part_number',
            'stock_feed_stock_field' => 'stock',
            'stock_sync_enabled' => false,
            'stock_sync_frequency' => 'manual',
            'low_stock_threshold' => 5,
            'stock_sync_last_run_at' => '',
            'stock_sync_last_result' => '',
        ];
    }

    public static function settings(): array
    {
        try {
            $raw = Setting::getAllAsArray();
        } catch (Throwable) {
            $raw = [];
        }

        $settings = self::defaults();
        foreach (array_keys($settings) as $key) {
            if (array_key_exists($key, $raw)) {
                $settings[$key] = $raw[$key];
            }
        }

        $settings['stock_sync_enabled'] = filter_var($settings['stock_sync_enabled'], FILTER_VALIDATE_BOOLEAN);
        $settings['low_stock_threshold'] = max(0, (int) $settings['low_stock_threshold']);
        $settings['stock_feed_format'] = in_array($settings['stock_feed_format'], self::FORMATS, true)
            ? $settings['stock_feed_format']
            : 'json';
        $settings['stock_feed_key_field'] = in_array($settings['stock_feed_key_field'], self::KEY_FIELDS, true)
            ? $settings['stock_feed_key_field']
            : 'part_number';
        $settings['stock_sync_frequency'] = in_array($settings['stock_sync_frequency'], self::FREQUENCIES, true)
            ? $settings['stock_sync_frequency']
            : 'manual';

        return $settings;
    }

    public static function configured(array $settings): bool
    {
        return (bool) $settings['stock_sync_enabled'] && trim((string) $settings['stock_feed_url']) !== '';
    }

    public static function recordResult(string $message): void
    {
        try {
            Setting::set('stock_sync_last_run_at', now()->toDateTimeString());
            Setting::set('stock_sync_last_result', mb_substr($message, 0, 1000));
        } catch (Throwable) {
            // Status bookkeeping must never break a sync.
        }
    }
}
