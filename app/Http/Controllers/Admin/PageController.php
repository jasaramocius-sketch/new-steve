<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Image;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PageController extends Controller
{
    /**
     * Summernote submits <p><br></p> / <br> when the editor is left empty.
     * Treat content with no meaningful text as empty (null).
     */
    protected static function normalizeContent(?string $html): ?string
    {
        $html = trim((string) $html);
        if ($html === '') {
            return null;
        }

        $text = trim(html_entity_decode(strip_tags($html)));
        if ($text === '') {
            $hasMedia = (bool) preg_match('/<img\b|<iframe\b|<video\b|<table\b|<a\b|class\s*=|style\s*=/i', $html);

            return $hasMedia ? $html : null;
        }

        return $html;
    }

    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'title', 'slug', 'status', 'updated_at', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $query = Page::query();
        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%");
        }
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }
        $pages = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        $pages->appends($request->query())->onEachSide(1);

        return view('admin.pages.index', compact('pages', 'sortBy', 'sortDir'));
    }

    public function restore($id)
    {
        Page::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.pages.index')->with('success', 'Page restored successfully.');
    }

    public function forceDelete($id)
    {
        Page::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.pages.index')->with('success', 'Page permanently deleted.');
    }

    public function create()
    {
        return view('admin.pages.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->only(['title', 'short_description', 'content', 'meta_title', 'meta_description']);

        $slug = Str::slug($request->input('slug') ?: $request->title);
        if (Page::where('slug', $slug)->exists()) {
            return back()->withErrors(['slug' => 'This slug is already in use. Please choose another.'])->withInput();
        }
        $data['slug'] = $slug;

        $data['short_description'] = $request->input('short_description') ?: null;
        $data['content'] = static::normalizeContent($request->input('content'));
        $data['image'] = $request->filled('image_from_manager') ? 'storage/'.ltrim($request->input('image_from_manager'), '/') : null;
        $data['status'] = $request->boolean('status');
        $data['show_title'] = $request->boolean('show_title');

        $page = Page::create($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->input('image_from_manager'), $page);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page created successfully.');
    }

    public function edit($id)
    {
        $page = Page::findOrFail($id);

        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, $id)
    {
        $page = Page::findOrFail($id);

        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'short_description' => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = $request->only(['title', 'short_description', 'content', 'meta_title', 'meta_description']);

        $slug = Str::slug($request->input('slug') ?: $request->title);
        if (Page::where('slug', $slug)->where('id', '!=', $page->id)->exists()) {
            return back()->withErrors(['slug' => 'This slug is already in use. Please choose another.'])->withInput();
        }
        $data['slug'] = $slug;

        $data['short_description'] = $request->input('short_description') ?: null;
        $data['content'] = static::normalizeContent($request->input('content'));
        if ($request->boolean('remove_section_image')) {
            $data['image'] = null;
        } elseif ($request->filled('image_from_manager')) {
            $data['image'] = 'storage/'.ltrim($request->input('image_from_manager'), '/');
        }
        $data['status'] = $request->boolean('status');
        $data['show_title'] = $request->boolean('show_title');

        $page->update($data);

        if ($request->filled('image_from_manager')) {
            Image::markUsed($request->input('image_from_manager'), $page);
        }

        return redirect()->route('admin.pages.index')->with('success', 'Page updated successfully.');
    }

    public function toggleStatus($id)
    {
        $page = Page::findOrFail($id);
        $page->status = ! $page->status;
        $page->save();

        $status = $page->status ? 'active' : 'inactive';

        return back()->with('success', "Page status was successfully set to {$status}.");
    }

    public function destroy($id)
    {
        Page::findOrFail($id)->delete();

        return redirect()->route('admin.pages.index')->with('success', 'Page deleted successfully.');
    }
}
