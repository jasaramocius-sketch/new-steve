<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'question', 'order', 'status', 'updated_at', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $query = Faq::query();
        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('question', 'like', "%{$search}%")
                    ->orWhere('answer', 'like', "%{$search}%");
            });
        }
        if ($request->has('trashed')) {
            $query->onlyTrashed();
        }
        $faqs = $query->orderBy($sortBy, $sortDir)->paginate($perPage);
        $faqs->appends($request->query())->onEachSide(1);

        return view('admin.faqs.index', compact('faqs', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.faqs.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        Faq::create([
            'question' => $request->question,
            'answer' => $request->answer,
            'order' => $request->order ?? 0,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ created successfully.');
    }

    public function edit($id)
    {
        $faq = Faq::findOrFail($id);

        return view('admin.faqs.edit', compact('faq'));
    }

    public function update(Request $request, $id)
    {
        $faq = Faq::findOrFail($id);

        $request->validate([
            'question' => 'required|string|max:500',
            'answer' => 'required|string',
            'order' => 'nullable|integer|min:0',
        ]);

        $faq->update([
            'question' => $request->question,
            'answer' => $request->answer,
            'order' => $request->order ?? 0,
            'status' => $request->boolean('status'),
        ]);

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ updated successfully.');
    }

    public function toggleStatus($id)
    {
        $faq = Faq::findOrFail($id);
        $faq->status = ! $faq->status;
        $faq->save();

        return back()->with('success', 'FAQ status updated successfully.');
    }

    public function destroy($id)
    {
        Faq::findOrFail($id)->delete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ deleted successfully.');
    }

    public function restore($id)
    {
        Faq::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ restored successfully.');
    }

    public function forceDelete($id)
    {
        Faq::onlyTrashed()->findOrFail($id)->forceDelete();

        return redirect()->route('admin.faqs.index')->with('success', 'FAQ permanently deleted.');
    }
}
