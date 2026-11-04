<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\Rule;

class CurrencySettingsController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $currencies = Currency::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('code', 'like', '%'.$search.'%');
            }))
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();
        $activeCurrencies = Currency::where('is_active', true)->orderBy('name')->get();
        $settings = [
            'system_default_currency' => Setting::get('system_default_currency', 'USD'),
            'symbol_format' => Setting::get('symbol_format', '1'),
            'decimal_separator' => Setting::get('decimal_separator', '1'),
            'no_of_decimals' => Setting::get('no_of_decimals', '2'),
        ];

        return view('admin.settings.currency.index', compact('currencies', 'activeCurrencies', 'settings', 'search'));
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'system_default_currency' => 'required|string|exists:currencies,code',
            'symbol_format' => 'required|in:1,2,3,4',
            'decimal_separator' => 'required|in:1,2',
            'no_of_decimals' => 'required|in:0,1,2,3',
        ]);
        $currency = Currency::where('code', $validated['system_default_currency'])->firstOrFail();
        if (! $currency->is_active) {
            return back()->withErrors(['system_default_currency' => 'The system default currency must be active.'])->withInput();
        }

        foreach ($validated as $key => $value) {
            Setting::set($key, (string) $value);
        }
        Cache::forget('currency_format_settings');
        Cache::forget('system_default_currency');

        return redirect()->route('admin.settings.currency.index')->with('success', 'Currency settings updated.');
    }

    public function create()
    {
        return view('admin.settings.currency.form', ['currency' => new Currency(), 'isEditing' => false]);
    }

    public function store(Request $request)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|size:3|alpha|unique:currencies,code',
            'symbol' => 'required|string|max:12',
            'rate' => 'required|numeric|gt:0|max:100000000',
        ]);
        Currency::create($validated + ['is_active' => $request->boolean('is_active')]);

        return redirect()->route('admin.settings.currency.index')->with('success', 'Currency added.');
    }

    public function edit(Currency $currency)
    {
        return view('admin.settings.currency.form', ['currency' => $currency, 'isEditing' => true]);
    }

    public function update(Request $request, Currency $currency)
    {
        $request->merge(['code' => strtoupper(trim((string) $request->input('code')))]);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'code' => ['required', 'string', 'size:3', 'alpha', Rule::unique('currencies', 'code')->ignore($currency->id)],
            'symbol' => 'required|string|max:12',
            'rate' => 'required|numeric|gt:0|max:100000000',
        ]);
        $oldCode = $currency->code;
        $currency->update($validated);
        if ($oldCode !== $currency->code && Setting::get('system_default_currency', 'USD') === $oldCode) {
            Setting::set('system_default_currency', $currency->code);
            Cache::forget('system_default_currency');
        }

        return redirect()->route('admin.settings.currency.index')->with('success', 'Currency updated.');
    }

    public function toggleStatus(Currency $currency)
    {
        if ($currency->is_active && Setting::get('system_default_currency', 'USD') === $currency->code) {
            return response()->json(['message' => 'The system default currency cannot be disabled.'], 422);
        }
        if ($currency->is_active && Currency::where('is_active', true)->count() <= 1) {
            return response()->json(['message' => 'At least one currency must remain active.'], 422);
        }

        $currency->update(['is_active' => ! $currency->is_active]);

        return response()->json(['success' => true, 'is_active' => $currency->is_active]);
    }

    public function syncRates()
    {
        try {
            $response = Http::timeout(10)->withoutVerifying()->get('https://api.unirateapi.com/api/widget/v1/rates', ['base' => 'USD']);
            if (! $response->successful() || ! is_array($response->json('rates'))) {
                return back()->with('error', 'Live exchange rates are temporarily unavailable.');
            }

            $rates = $response->json('rates');
            foreach (Currency::query()->get() as $currency) {
                if (isset($rates[$currency->code]) && is_numeric($rates[$currency->code]) && (float) $rates[$currency->code] > 0) {
                    $currency->update(['rate' => $rates[$currency->code]]);
                }
            }
        } catch (\Throwable $exception) {
            logger()->warning('Currency rate sync failed: '.$exception->getMessage());

            return back()->with('error', 'Live exchange rates are temporarily unavailable.');
        }

        Cache::forget('dynamic_currency_rates');

        return redirect()->route('admin.settings.currency.index')->with('success', 'Exchange rates updated.');
    }
}
