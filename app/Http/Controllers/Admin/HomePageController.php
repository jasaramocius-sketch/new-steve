<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomePageSection;
use App\Models\Image;
use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['section_name', 'title', 'status', 'order', 'created_at']) ? $request->sort_by : 'order';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $sections = HomePageSection::orderBy($sortBy, $sortDir)->paginate($perPage);
        $sections->appends($request->query())->onEachSide(1);

        return view('admin.home-page.index', compact('sections', 'sortBy', 'sortDir'));
    }

    public function edit($id)
    {
        $section = HomePageSection::findOrFail($id);

        return view('admin.home-page.edit', compact('section'));
    }

    public function update(Request $request, $id)
    {
        $section = HomePageSection::findOrFail($id);

        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'button_text' => 'nullable|string|max:100',
            'button_url' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'tabs' => 'nullable|array',
            'tabs.*.label' => 'nullable|string|max:255',
            'tabs.*.product_ids' => 'nullable|array',
            'tabs.*.product_ids.*' => 'integer|exists:products,id',
        ]);

        $validated['status'] = $request->has('status') ? 1 : 0;

        $pickedPaths = [];

        if ($request->hasFile('image')) {
            $validated['image'] = saveImageWithWebp($request->file('image'));
        } elseif ($request->filled('image_from_manager')) {
            $validated['image'] = 'storage/'.ltrim($request->image_from_manager, '/');
            $pickedPaths[] = $request->image_from_manager;
        }

        if ($request->has('remove_section_image') && $request->boolean('remove_section_image') && ! isset($validated['image'])) {
            $validated['image'] = null;
        }

        if ($request->has('tabs')) {
            $tabs = $request->input('tabs');
            // Clean up empty tabs
            $tabs = array_values(array_filter($tabs, fn ($t) => ! empty(trim($t['label'] ?? ''))));
            $validated['extra_data'] = ['tabs' => $tabs];
        }

        $existing = $section->extra_data ?? [];

        if ($request->filled('brands_limit')) {
            $existing['brands_limit'] = $request->brands_limit;
        }

        if ($request->has('brand_ids')) {
            $existing['brand_ids'] = array_values(array_filter(array_map('intval', (array) $request->input('brand_ids', []))));
        }

        if ($request->filled('posts_count')) {
            $existing['posts_count'] = (int) $request->posts_count;
        }

        if ($request->has('post_ids')) {
            $ids = array_values(array_filter(array_map('intval', (array) $request->input('post_ids', []))));
            if ($request->filled('posts_count')) {
                $ids = array_slice($ids, 0, (int) $request->posts_count);
            }
            $existing['post_ids'] = $ids;
        }

        if ($request->has('category_ids')) {
            $existing['category_ids'] = array_values(array_filter(array_map('intval', (array) $request->input('category_ids', []))));
        }

        if ($request->filled('countdown')) {
            $existing['countdown'] = $request->countdown;
        }

        if ($section->section_name === 'deal_of_day') {
            if ($request->filled('deal_bg_image_from_manager')) {
                $existing['deal_image'] = 'storage/'.ltrim($request->deal_bg_image_from_manager, '/');
                $pickedPaths[] = $request->deal_bg_image_from_manager;
            }

            if ($request->has('remove_deal_bg_image') && $request->boolean('remove_deal_bg_image') && ! $request->filled('deal_bg_image_from_manager')) {
                $existing['deal_image'] = null;
            }
        }

        if ($section->section_name === 'offers' && $request->has('banners')) {
            $savedBanners = [];
            foreach ((array) $request->input('banners') as $row) {
                if (! is_array($row)) {
                    continue;
                }

                $imageFromManager = $row['image_from_manager'] ?? null;
                $existingImage = $row['existing_image'] ?? null;
                $newImage = null;

                if ($imageFromManager) {
                    $newImage = 'storage/'.ltrim($imageFromManager, '/');
                    $pickedPaths[] = $imageFromManager;
                }

                if (! $newImage && $existingImage) {
                    $newImage = $existingImage;
                }

                $savedBanners[] = [
                    'image' => $newImage,
                    'title' => $row['title'] ?? '',
                    'subtitle' => $row['subtitle'] ?? '',
                    'button_text' => $row['button_text'] ?? '',
                    'button_url' => $row['button_url'] ?? '',
                ];
            }

            $savedBanners = array_values(array_filter($savedBanners, function ($b) {
                return $b['image'] || $b['title'] || $b['subtitle'] || $b['button_text'] || $b['button_url'];
            }));

            $existing['banners'] = $savedBanners;
        }

        $extraChanged = $request->filled('brands_limit')
            || $request->has('brand_ids')
            || $request->filled('posts_count')
            || $request->has('post_ids')
            || $request->has('category_ids')
            || $request->filled('countdown')
            || ($section->section_name === 'offers' && $request->has('banners'))
            || ($section->section_name === 'deal_of_day' && ($request->filled('deal_bg_image_from_manager') || $request->has('remove_deal_bg_image')));

        if ($extraChanged) {
            $validated['extra_data'] = $existing;
        }

        $section->update($validated);

        foreach ($pickedPaths as $pickedPath) {
            Image::markUsed($pickedPath, $section);
        }

        return redirect()->route('admin.home-page.index')
            ->with('success', 'Section updated successfully!');
    }

    public function toggleStatus($id)
    {
        $section = HomePageSection::findOrFail($id);
        $section->status = ! $section->status;
        $section->save();

        return back()->with('success', 'Section status updated successfully.');
    }

    public function reorder(Request $request)
    {
        $order = $request->get('order', []);

        foreach ($order as $position => $id) {
            HomePageSection::where('id', $id)->update(['order' => $position]);
        }

        return response()->json(['success' => true]);
    }
}
