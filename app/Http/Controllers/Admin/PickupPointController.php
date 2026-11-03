<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PickupPoint;
use Illuminate\Http\Request;

class PickupPointController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('search', ''));
        $pickupPoints = PickupPoint::query()
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', '%'.$search.'%')
                    ->orWhere('manager', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%');
            }))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.settings.pickup-points.index', compact('pickupPoints', 'search'));
    }

    public function create()
    {
        return view('admin.settings.pickup-points.form', ['pickupPoint' => new PickupPoint(), 'isEditing' => false]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'manager' => 'required|string|max:150',
            'location' => 'required|string|max:255',
            'contact' => 'required|string|max:50',
            'is_open' => 'nullable|boolean',
        ]);
        $validated['is_open'] = $request->boolean('is_open');
        PickupPoint::create($validated);

        return redirect()->route('admin.settings.pickup-points.index')->with('success', 'Pickup point added.');
    }

    public function edit(PickupPoint $pickupPoint)
    {
        return view('admin.settings.pickup-points.form', compact('pickupPoint') + ['isEditing' => true]);
    }

    public function update(Request $request, PickupPoint $pickupPoint)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'manager' => 'required|string|max:150',
            'location' => 'required|string|max:255',
            'contact' => 'required|string|max:50',
            'is_open' => 'nullable|boolean',
        ]);
        $validated['is_open'] = $request->boolean('is_open');
        $pickupPoint->update($validated);

        return redirect()->route('admin.settings.pickup-points.index')->with('success', 'Pickup point updated.');
    }

    public function toggleStatus(PickupPoint $pickupPoint)
    {
        $pickupPoint->update(['is_open' => ! $pickupPoint->is_open]);

        return response()->json(['success' => true, 'is_open' => $pickupPoint->is_open]);
    }

    public function destroy(PickupPoint $pickupPoint)
    {
        $pickupPoint->delete();

        return redirect()->route('admin.settings.pickup-points.index')->with('success', 'Pickup point deleted.');
    }
}
