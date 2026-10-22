<?php

namespace App\Http\Controllers\Admin;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'role', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        if ($request->has('trashed')) {
            $staffs = Staff::onlyTrashed()->orderBy($sortBy, $sortDir)->paginate($perPage);
            $showTrashed = true;
        } else {
            $staffs = Staff::orderBy($sortBy, $sortDir)->paginate($perPage);
            $showTrashed = false;
        }
        $staffs->appends($request->query())->onEachSide(1);

        return view('admin.staff.index', compact('staffs', 'showTrashed', 'sortBy', 'sortDir'));
    }

    public function restore($id)
    {
        Staff::onlyTrashed()->findOrFail($id)->restore();

        return redirect()->route('admin.staff.index')->with('success', 'Staff restored successfully.');
    }

    public function forceDelete($id)
    {
        $staff = Staff::onlyTrashed()->findOrFail($id);
        $staff->forceDelete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff permanently deleted.');
    }
}
