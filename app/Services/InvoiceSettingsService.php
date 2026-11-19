<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Storage;

class InvoiceSettingsService
{
    public function forOrder(Order $order): array
    {
        $storedSettings = Setting::getAllAsArray();
        $invoiceSettings = [];

        foreach (config('invoice.settings', []) as $key => $default) {
            $invoiceSettings[$key] = $storedSettings[$key] ?? $default;
        }

        foreach (config('invoice.visible_sections', []) as $key) {
            $invoiceSettings[$key] = ($storedSettings[$key] ?? '1') === '1';
        }

        if (! preg_match('/^#[0-9A-Fa-f]{6}$/', (string) $invoiceSettings['invoice_primary_color'])) {
            $invoiceSettings['invoice_primary_color'] = config('invoice.settings.invoice_primary_color');
        }

        $configuredLogo = $storedSettings['invoice_logo'] ?? null;
        $logoPath = $configuredLogo === null
            ? public_path(config('invoice.logo'))
            : ($configuredLogo !== '' && str_starts_with($configuredLogo, 'invoices/')
                ? Storage::disk('public')->path($configuredLogo)
                : public_path(ltrim($configuredLogo, '/')));

        $invoiceLogo = null;
        if (is_file($logoPath) && is_readable($logoPath)) {
            $logoContents = file_get_contents($logoPath);
            if ($logoContents === false) {
                throw new \RuntimeException('The invoice logo could not be read.');
            }

            $invoiceLogo = 'data:'.(mime_content_type($logoPath) ?: 'image/png').';base64,'.base64_encode($logoContents);
        }

        return [
            'invoiceLogo' => $invoiceLogo,
            'invoiceSettings' => $invoiceSettings,
            'invoiceNumber' => (string) $invoiceSettings['invoice_prefix'].($order->order_number ?? $order->id),
        ];
    }
}
