<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Image;
use App\Models\Page;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CategoryController extends Controller
{
    // Admin / Staff Categories
    public function index(Request $request)
    {
        $search = $request->get('search', '');

        $sortBy = in_array($request->sort_by, ['id', 'name', 'products_count']) ? $request->sort_by : 'id';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $categories = $request->has('trashed')
            ? Category::onlyTrashed()->withCount('products')
            : Category::withCount('products');

        if ($search !== '') {
            $categories->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($sortBy === 'products_count') {
            $categories->orderBy('products_count', $sortDir);
        } else {
            $categories->orderBy($sortBy, $sortDir);
        }

        $categories = $categories->paginate($perPage);

        $categories->appends($request->query())->onEachSide(1);

        return view('admin.categories.index', compact('categories', 'search', 'sortBy', 'sortDir'));
    }

    public function restore($id)
    {
        Category::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category restored successfully.');
    }

    public function forceDelete($id)
    {
        Category::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category permanently deleted.');
    }

    // User Categories Page
    public function toggleStatus($id)
    {
        return back()->with('success', 'Category status is managed automatically for this catalogue.');
    }

    public function showCategories(Request $request)
    {
        $page = Page::where('slug', 'categories')->first();
        $search = $request->get('search', '');
        $sort = $request->get('sort', 'latest');

        $topCategories = Category::topLevel()
            ->withCount(['products' => fn ($q) => $q->where('status', true)])
            ->with(['children' => function ($q) {
                $q->withCount(['products' => fn ($q2) => $q2->where('status', true)])
                    ->with(['children' => function ($q2) {
                        $q2->withCount(['products' => fn ($q3) => $q3->where('status', true)]);
                    }]);
            }]);

        if ($search !== '') {
            $topCategories->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $topCategories = $topCategories->get();

        $categories = $topCategories;

        $categories = $categories->map(function ($category) {
            $descIds = $category->getAllDescendantIds();
            $category->total_products_count = Product::whereIn('category_id', $descIds)
                ->where('status', true)
                ->count();
            $category->preview_products = Product::whereIn('category_id', $descIds)
                ->where('status', true)
                ->latest()
                ->take(3)
                ->get();

            return $category;
        });

        if ($sort === 'name') {
            $categories = $categories->sortBy('name')->values();
        } elseif ($sort === 'oldest') {
            $categories = $categories->sortBy('created_at')->values();
        } else {
            $categories = $categories->sortByDesc('created_at')->values();
        }

        return view('categories.index', compact('categories', 'page'));
    }

    // Create Form
    public function create()
    {
        $parents = Category::topLevel()
            ->with('children.children.children')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parents'));
    }

    // Store Category
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_url' => 'nullable|url',
        ]);

        $category = new Category;
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->filled('parent_id') ? $request->parent_id : null;
        $category->level = $this->resolveLevel($category->parent_id);
        $category->image = $this->resolveImage($request);
        $category->save();

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $category);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category created successfully.');
    }

    // Edit Form
    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $descendantIds = $category->getAllDescendantIds();
        $descendantIds[] = $category->id;
        $excluded = array_unique($descendantIds);
        $parents = Category::topLevel()
            ->whereNotIn('id', $excluded)
            ->with('children.children.children')
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parents', 'excluded'));
    }

    // Update Category
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'image_url' => 'nullable|url',
        ]);

        $category = Category::findOrFail($id);
        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $category->parent_id = $request->filled('parent_id') ? $request->parent_id : null;
        $category->level = $this->resolveLevel($category->parent_id);

        if ($image = $this->resolveImage($request)) {
            $category->image = $image;
        }

        $category->save();

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->image_from_manager, $category);
        }

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category updated successfully');
    }

    // Delete Category
    public function destroy($id)
    {
        Category::findOrFail($id)->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Category deleted successfully');
    }

    private function resolveImage(Request $request): ?string
    {
        if ($request->filled('image_from_manager')) {
            return 'storage/'.ltrim($request->image_from_manager, '/');
        }

        if ($request->hasFile('image')) {
            return saveImageWithWebp($request->file('image'));
        }

        if ($request->filled('image_url')) {
            $path = saveImageFromUrlWithWebp($request->input('image_url'));

            if ($path === null) {
                throw ValidationException::withMessages([
                    'image_url' => 'Could not download image from the provided URL.',
                ]);
            }

            return $path;
        }

        return null;
    }

    private function resolveLevel(?int $parentId): int
    {
        if (! $parentId) {
            return 1;
        }

        $parent = Category::find($parentId);

        return $parent ? ($parent->level ?? 1) + 1 : 2;
    }
}
