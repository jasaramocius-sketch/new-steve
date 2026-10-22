<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses()->latest()->get();

        return response()->json(['addresses' => $addresses]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20|regex:/^[0-9a-zA-Z\-\s]+$/',
        ]);

        $user = auth()->user();
        $data = $request->only([
            'phone',
            'address',
            'city',
            'state',
            'country',
            'zip_code',
            'set_default',
        ]);
        $data['full_name'] = $user->name;
        $address = $user->addresses()->create($data);

        if ($request->has('set_default')) {
            $user->addresses()->where('id', '!=', $address->id)->update(['set_default' => false]);
            $address->update(['set_default' => true]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return back()->with('success', 'Address added successfully!');
    }

    public function edit(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($address);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }

        $request->validate([
            'phone' => 'required|string|max:20|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'country' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20|regex:/^[0-9a-zA-Z\-\s]+$/',
        ]);

        $data = $request->only([
            'phone',
            'address',
            'city',
            'state',
            'country',
            'zip_code',
            'set_default',
        ]);
        $data['full_name'] = auth()->user()->name;

        $address->update($data);

        if ($request->has('set_default')) {
            auth()->user()->addresses()->where('id', '!=', $address->id)->update(['set_default' => false]);
            $address->update(['set_default' => true]);
        }

        if ($request->ajax()) {
            return response()->json(['success' => true, 'address' => $address]);
        }

        return back()->with('success', 'Address updated successfully!');
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }
        $address->delete();

        if (request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Address deleted successfully!');
    }

    public function setDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            abort(403);
        }
        $user = auth()->user();
        $user->addresses()->where('id', '!=', $address->id)->update(['set_default' => false]);
        $address->update(['set_default' => true]);

        return back()->with('success', 'Default address updated!');
    }
}
