<?php

if (! function_exists('currency_format')) {
    function currency_format($amount, ?string $currency = null): string
    {
        $currency = $currency ?? session('currency', 'USD');
        $currencies = config('currencies', []);
        $info = $currencies[$currency] ?? $currencies['USD'];

        $converted = ($amount ?? 0) * $info['rate'];

        return $info['symbol'].number_format($converted, 2);
    }
}
