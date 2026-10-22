<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'slug', 'status', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $query = BlogCategory::withCount('blogs')->with('parent');
        if ($search = $request->query('search')) {
            $query->where('name', 'like', "%{$search}%");
        }
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }
        $categories = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        $categories->appends($request->query())->onEachSide(1);

        return view('admin.blog-categories.index', compact('categories', 'sortBy', 'sortDir'));
    }

    public function restore($id)
    {
        BlogCategory::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category restored successfully.');
    }

    public function forceDelete($id)
    {
        $category = BlogCategory::onlyTrashed()->findOrFail($id);
        BlogCategory::where('parent_id', $id)->update(['parent_id' => null]);
        $category->forceDelete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category permanently deleted.');
    }

    public function create()
    {
        $parents = BlogCategory::whereNull('parent_id')->where('status', 'active')->get();

        return view('admin.blog-categories.create', compact('parents'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'parent_id' => 'nullable|exists:blog_categories,id',
        ]);

        $data = $request->only(['name', 'status', 'parent_id']);
        $data['slug'] = Str::slug($request->name).'-'.time();

        BlogCategory::create($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category created successfully.');
    }

    public function edit($id)
    {
        $category = BlogCategory::findOrFail($id);
        $parents = BlogCategory::whereNull('parent_id')->where('status', 'active')
            ->where('id', '!=', $id)
            ->get();

        return view('admin.blog-categories.edit', compact('category', 'parents'));
    }

    public function update(Request $request, $id)
    {
        $category = BlogCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'status' => 'required|in:active,inactive',
            'parent_id' => 'nullable|exists:blog_categories,id',
        ]);

        $data = $request->only(['name', 'status', 'parent_id']);
        $data['slug'] = Str::slug($request->name).'-'.time();

        $category->update($data);

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category updated successfully.');
    }

    public function destroy($id)
    {
        $category = BlogCategory::findOrFail($id);
        BlogCategory::where('parent_id', $id)->update(['parent_id' => null]);
        $category->delete();

        return redirect()->route('admin.blog-categories.index')->with('success', 'Blog category deleted successfully.');
    }
}
