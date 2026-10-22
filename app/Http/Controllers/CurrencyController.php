<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function rates()
    {
        try {
            $response = Http::timeout(5)->withoutVerifying()->get(config('services.currency.url'));

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            logger()->warning('Currency rates endpoint failed: '.$e->getMessage());
        }

        return response()->json([
            'base' => 'USD',
            'rates' => collect(config('currencies', []))
                ->map(fn ($info) => $info['rate'] ?? null)
                ->filter()
                ->all(),
        ]);
    }

    public function convertCurrency($amount, $from, $to)
    {
        try {
            $response = Http::timeout(5)->withoutVerifying()->get(
                'https://api.unirateapi.com/api/widget/v1/convert',
                [
                    'amount' => $amount,
                    'from' => strtoupper($from),
                    'to' => strtoupper($to),
                ]
            );

            if ($response->successful()) {
                return $response->json();
            }
        } catch (\Throwable $e) {
            logger()->warning('Currency convert endpoint failed: '.$e->getMessage());
        }

        return response()->json([
            'success' => false,
        ]);
    }

    public function convert($amount, $from, $to)
    {
        try {
            $response = Http::timeout(5)->withoutVerifying()->get(
                'https://api.unirateapi.com/api/widget/v1/convert',
                [
                    'amount' => $amount,
                    'from' => strtoupper($from),
                    'to' => strtoupper($to),
                ]
            );

            return response()->json($response->json(), $response->status());
        } catch (\Throwable $e) {
            logger()->warning('Currency convert endpoint failed: '.$e->getMessage());

            return response()->json(['success' => false], 502);
        }
    }
}
