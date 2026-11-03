<?php

if (! function_exists('currency_format')) {
    function currency_format($amount, ?string $currency = null): string
    {
        $defaultCurrency = cache()->remember('system_default_currency', 3600, fn () => \App\Models\Setting::get('system_default_currency', 'USD'));
        $currency = $currency ?? session('currency', $defaultCurrency);
        $currencies = config('currencies', []);
        $info = $currencies[$currency] ?? $currencies['USD'];
        $converted = (float) ($amount ?? 0) * (float) ($info['rate'] ?? 1);
        $format = cache()->remember('currency_format_settings', 3600, fn () => [
            'symbol_format' => \App\Models\Setting::get('symbol_format', '1'),
            'decimal_separator' => \App\Models\Setting::get('decimal_separator', '1'),
            'no_of_decimals' => \App\Models\Setting::get('no_of_decimals', '2'),
        ]);
        $decimals = max(0, min((int) $format['no_of_decimals'], 3));
        $decimalPoint = $format['decimal_separator'] === '2' ? ',' : '.';
        $thousandsSeparator = $format['decimal_separator'] === '2' ? '.' : ',';
        $fixed = number_format(abs($converted), $decimals, $decimalPoint, '');
        [$integer, $fraction] = array_pad(explode($decimalPoint, $fixed, 2), 2, '');
        $lastThree = substr($integer, -3);
        $leading = substr($integer, 0, -3);
        if ($leading !== '') {
            $leading = preg_replace('/\\B(?=(?:\\d{2})+$)/', $thousandsSeparator, $leading);
            $integer = $leading.$thousandsSeparator.$lastThree;
        }
        $number = ($converted < 0 ? '-' : '').$integer.($decimals > 0 ? $decimalPoint.$fraction : '');
        $symbol = $info['symbol'] ?? $currency;

        return match ($format['symbol_format']) {
            '2' => $number.$symbol,
            '3' => $symbol.' '.$number,
            '4' => $number.' '.$symbol,
            default => $symbol.$number,
        };
    }
}
