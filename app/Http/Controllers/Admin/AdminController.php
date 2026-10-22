<?php

namespace App\Http\Controllers\Admin;

use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $query = Admin::query()->withTrashed();

        if ($request->has('trashed')) {
            $admins = $query->onlyTrashed()->latest()->get();
            $showTrashed = true;
        } else {
            $admins = $query->whereNull('deleted_at')->latest()->get();
            $showTrashed = false;
        }

        return view('admin.users.index', compact('admins', 'showTrashed'));
    }

    public function restore($id)
    {
        Admin::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.users.index')->with('success', 'Admin restored successfully.');
    }

    public function forceDelete($id)
    {
        $admin = Admin::onlyTrashed()->findOrFail($id);
        $admin->forceDelete();

        return redirect()->route('admin.users.index')->with('success', 'Admin permanently deleted.');
    }
}
