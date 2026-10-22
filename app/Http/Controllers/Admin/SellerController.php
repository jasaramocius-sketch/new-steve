<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FollowedSeller;
use App\Models\Image;
use App\Models\Seller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'status', 'created_at', 'followers']) ? $request->sort_by : 'id';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $sellers = Seller::withCount('followedBy as followers_count')
            ->orderBy($sortBy === 'followers' ? 'followers_count' : $sortBy, $sortDir)
            ->paginate($perPage);
        $sellers->appends($request->query())->onEachSide(1);

        return view('admin.sellers.index', compact('sellers', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.sellers.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only(['name', 'location', 'description']);
        $data['slug'] = Str::slug($request->name).'-'.time();
        $data['status'] = $request->boolean('status');

        if ($request->filled('image_from_manager')) {
            $data['image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        } elseif ($request->hasFile('image')) {
            $data['image'] = saveImageWithWebp($request->file('image'));
        }

        $seller = Seller::create($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $seller);
        }

        return redirect()->route('admin.sellers.index')->with('success', 'Seller created successfully.');
    }

    public function edit($id)
    {
        $seller = Seller::findOrFail($id);

        return view('admin.sellers.edit', compact('seller'));
    }

    public function update(Request $request, $id)
    {
        $seller = Seller::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = $request->only(['name', 'location', 'description']);
        $data['slug'] = Str::slug($request->name).'-'.$seller->id;
        $data['status'] = $request->boolean('status');

        if ($request->filled('image_from_manager')) {
            $data['image'] = 'storage/'.ltrim($request->image_from_manager, '/');
        } elseif ($request->hasFile('image')) {
            $data['image'] = saveImageWithWebp($request->file('image'));
        }

        $seller->update($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $seller);
        }

        return redirect()->route('admin.sellers.index')->with('success', 'Seller updated successfully.');
    }

    public function followers($id)
    {
        $seller = Seller::findOrFail($id);

        $followers = FollowedSeller::with('user')
            ->where('seller_id', $seller->id)
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.sellers.followers', compact('seller', 'followers'));
    }

    public function toggleStatus($id)
    {
        $seller = Seller::findOrFail($id);
        $seller->status = ! $seller->status;
        $seller->save();

        return back()->with('success', 'Seller status updated successfully.');
    }

    public function destroy($id)
    {
        $seller = Seller::findOrFail($id);
        $seller->delete();

        return redirect()->route('admin.sellers.index')->with('success', 'Seller deleted successfully.');
    }
}
