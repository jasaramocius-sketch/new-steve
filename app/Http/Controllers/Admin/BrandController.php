<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'status', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $brands = Brand::orderBy($sortBy, $sortDir)->paginate($perPage);
        $brands->appends($request->query())->onEachSide(1);

        return view('admin.brands.index', compact('brands', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.brands.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'website']);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->boolean('status');

        if ($request->filled('image_from_manager')) {
            $data['image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        } elseif ($request->hasFile('image')) {
            $data['image'] = saveImageWithWebp($request->file('image'));
        }

        $brand = Brand::create($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $brand);
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand created successfully.');
    }

    public function edit($id)
    {
        $brand = Brand::findOrFail($id);

        return view('admin.brands.edit', compact('brand'));
    }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'website' => 'nullable|url|max:255',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only(['name', 'description', 'website']);
        $data['slug'] = Str::slug($request->name);
        $data['status'] = $request->boolean('status');

        if ($request->filled('image_from_manager')) {
            $data['image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        } elseif ($request->hasFile('image')) {
            $data['image'] = saveImageWithWebp($request->file('image'));
        }

        $brand->update($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $brand);
        }

        return redirect()->route('admin.brands.index')->with('success', 'Brand updated successfully.');
    }

    public function toggleStatus($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->status = ! $brand->status;
        $brand->save();

        return back()->with('success', 'Brand status updated successfully.');
    }

    public function destroy($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return redirect()->route('admin.brands.index')->with('success', 'Brand deleted successfully.');
    }
}
