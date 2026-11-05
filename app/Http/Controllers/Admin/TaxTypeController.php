<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TaxType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaxTypeController extends Controller
{
    public function create()
    {
        return view('admin.settings.tax-types.form', [
            'taxType' => new TaxType(['is_active' => true]),
            'isEditing' => false,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150|unique:tax_types,name',
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');

        TaxType::create($validated);

        return redirect()->route('admin.settings.custom')->with('success', 'Tax type added.');
    }

    public function edit(TaxType $taxType)
    {
        return view('admin.settings.tax-types.form', compact('taxType') + ['isEditing' => true]);
    }

    public function update(Request $request, TaxType $taxType)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', Rule::unique('tax_types', 'name')->ignore($taxType->id)],
            'is_active' => 'nullable|boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active');
        $taxType->update($validated);

        return redirect()->route('admin.settings.custom')->with('success', 'Tax type updated.');
    }

    public function toggleStatus(TaxType $taxType)
    {
        $taxType->update(['is_active' => ! $taxType->is_active]);

        return back()->with('success', 'Tax type status updated.');
    }

    public function destroy(TaxType $taxType)
    {
        $taxType->delete();

        return redirect()->route('admin.settings.custom')->with('success', 'Tax type deleted.');
    }
}
