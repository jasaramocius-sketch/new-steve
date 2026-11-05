<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\ShippingCarrier;
use App\Models\ShippingZone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ShippingSettingsController extends Controller
{
    public function configuration()
    {
        return view('admin.settings.shipping.configuration', [
            'homeDeliveryEnabled' => Setting::get('home_delivery_enabled', '1') === '1',
            'shippingType' => Setting::get('shipping_type', 'carrier_wise_shipping'),
            'flatRateShippingCost' => Setting::get('flat_rate_shipping_cost', '0'),
            'shippingCostAdmin' => Setting::get('shipping_cost_admin', '0'),
        ]);
    }

    public function updateConfiguration(Request $request)
    {
        $type = (string) $request->input('type');

        switch ($type) {
            case 'shipping_type':
                $validated = $request->validate([
                    'shipping_type' => ['required', Rule::in(array_keys(\App\Services\ShippingChargeService::TYPES))],
                ]);
                Setting::set('shipping_type', $validated['shipping_type']);
                $message = 'Shipping method updated.';
                break;

            case 'flat_rate_shipping_cost':
                $validated = $request->validate([
                    'flat_rate_shipping_cost' => 'required|numeric|min:0|max:99999999.99',
                ]);
                Setting::set('flat_rate_shipping_cost', $validated['flat_rate_shipping_cost']);
                $message = 'Flat rate shipping cost updated.';
                break;

            case 'shipping_cost_admin':
                $validated = $request->validate([
                    'shipping_cost_admin' => 'required|numeric|min:0|max:99999999.99',
                ]);
                Setting::set('shipping_cost_admin', $validated['shipping_cost_admin']);
                $message = 'Admin product shipping cost updated.';
                break;

            case 'home_delivery_enabled':
                Setting::set('home_delivery_enabled', $request->boolean('home_delivery_enabled') ? '1' : '0');
                $message = 'Home delivery setting updated.';
                break;

            default:
                return back()->with('error', 'Unknown shipping setting.');
        }

        return back()->with('success', $message);
    }

    public function carriers()
    {
        $carriers = ShippingCarrier::withCount('rates')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.settings.shipping.carriers.index', compact('carriers'));
    }

    public function createCarrier()
    {
        return view('admin.settings.shipping.carriers.form', [
            'carrier' => new ShippingCarrier(['is_active' => true]),
            'isEditing' => false,
        ]);
    }

    public function storeCarrier(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('shipping_carriers', 'name')->whereNull('deleted_at')],
            'delivery_estimate' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        $carrier = ShippingCarrier::withTrashed()->where('name', $validated['name'])->first();
        if ($carrier?->trashed()) {
            $carrier->restore();
            $carrier->update($validated);
        } else {
            ShippingCarrier::create($validated);
        }

        return redirect()->route('admin.settings.shipping-carriers.index')->with('success', 'Carrier created.');
    }

    public function editCarrier(ShippingCarrier $carrier)
    {
        return view('admin.settings.shipping.carriers.form', compact('carrier') + ['isEditing' => true]);
    }

    public function updateCarrier(Request $request, ShippingCarrier $carrier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('shipping_carriers', 'name')->ignore($carrier->id)],
            'delivery_estimate' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0|max:65535',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $carrier->update($validated);

        return redirect()->route('admin.settings.shipping-carriers.index')->with('success', 'Carrier updated.');
    }

    public function destroyCarrier(ShippingCarrier $carrier)
    {
        if ($carrier->rates()->exists()) {
            return back()->with('error', 'Remove this carrier from all shipping zones before deleting it.');
        }

        $carrier->delete();

        return redirect()->route('admin.settings.shipping-carriers.index')->with('success', 'Carrier deleted.');
    }

    public function zones()
    {
        $zones = ShippingZone::with('rates.carrier')->orderByDesc('priority')->orderBy('name')->paginate(20);

        return view('admin.settings.shipping.zones.index', compact('zones'));
    }

    public function createZone()
    {
        return view('admin.settings.shipping.zones.form', [
            'zone' => new ShippingZone(['country' => '*', 'is_active' => true]),
            'carriers' => ShippingCarrier::orderBy('sort_order')->orderBy('name')->get(),
            'isEditing' => false,
        ]);
    }

    public function storeZone(Request $request)
    {
        $validated = $this->validateZone($request);

        DB::transaction(function () use ($validated, $request): void {
            $zone = ShippingZone::create($validated);
            $this->saveZoneRates($zone, $request->input('rates', []));
        });

        return redirect()->route('admin.settings.shipping-zones.index')->with('success', 'Shipping zone created.');
    }

    public function editZone(ShippingZone $zone)
    {
        $zone->load('rates');

        return view('admin.settings.shipping.zones.form', [
            'zone' => $zone,
            'carriers' => ShippingCarrier::orderBy('sort_order')->orderBy('name')->get(),
            'isEditing' => true,
        ]);
    }

    public function updateZone(Request $request, ShippingZone $zone)
    {
        $validated = $this->validateZone($request);

        DB::transaction(function () use ($zone, $validated, $request): void {
            $zone->update($validated);
            $this->saveZoneRates($zone, $request->input('rates', []));
        });

        return redirect()->route('admin.settings.shipping-zones.index')->with('success', 'Shipping zone updated.');
    }

    public function destroyZone(ShippingZone $zone)
    {
        DB::transaction(function () use ($zone): void {
            $zone->rates()->delete();
            $zone->delete();
        });

        return redirect()->route('admin.settings.shipping-zones.index')->with('success', 'Shipping zone deleted.');
    }

    private function validateZone(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:150',
            'country' => 'required|string|max:150',
            'state' => 'nullable|string|max:150',
            'city' => 'nullable|string|max:150',
            'priority' => 'nullable|integer|min:0|max:65535',
            'cost' => 'nullable|numeric|min:0|max:99999999.99',
            'is_active' => 'nullable|boolean',
            'rates' => 'nullable|array',
            'rates.*' => 'nullable|numeric|min:0|max:99999999.99',
        ]) + ['is_active' => $request->boolean('is_active')];
    }

    private function saveZoneRates(ShippingZone $zone, array $rates): void
    {
        $validCarrierIds = ShippingCarrier::whereIn('id', array_keys($rates))->pluck('id')->all();
        $savedCarrierIds = [];

        foreach ($validCarrierIds as $carrierId) {
            $fee = $rates[$carrierId] ?? null;
            if ($fee === null || $fee === '') {
                continue;
            }

            $rate = $zone->rates()
                ->withTrashed()
                ->firstOrNew(['shipping_carrier_id' => $carrierId]);
            $rate->fee = $fee;
            if ($rate->trashed()) {
                $rate->restore();
            } else {
                $rate->save();
            }
            $savedCarrierIds[] = $carrierId;
        }

        $zone->rates()->whereNotIn('shipping_carrier_id', $savedCarrierIds)->delete();
    }
}
