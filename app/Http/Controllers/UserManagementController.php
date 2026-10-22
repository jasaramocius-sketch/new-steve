<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'role', 'status', 'city', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $users = User::whereIn('role', ['staff', 'admin'])->orderBy($sortBy, $sortDir)->paginate($perPage);
        $users->appends($request->query())->onEachSide(1);

        return view('admin.users.index', compact('users', 'sortBy', 'sortDir'));
    }

    public function create()
    {
        return view('admin.users.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:master_admin,staff,customer',
            'status' => 'nullable|in:active,inactive',
            'phone' => 'nullable|string|max:50|regex:/^[0-9+\-\s()]*$/',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'status' => $request->status ?? 'active',
            'phone' => $request->phone,
            'city' => $request->city,
            'country' => $request->country,
            'address' => $request->address,
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:master_admin,staff,customer',
            'status' => 'nullable|in:active,inactive',
            'phone' => 'nullable|string|max:50|regex:/^[0-9+\-\s()]*$/',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
        ]);

        if ($user->role === 'master_admin' && $request->role !== 'master_admin') {
            return back()->withErrors(['role' => 'Master admin role cannot be changed.']);
        }

        if ($user->role === 'master_admin') {
            $data['status'] = 'active';
        } else {
            $data['status'] = $request->status ?? 'active';
        }
        $data += $request->only(['name', 'email', 'role', 'phone', 'city', 'country', 'address']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'master_admin') {
            return back()->with('error', 'Master admin status cannot be changed.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'User status updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'master_admin') {
            return redirect()->route('admin.users.index')->with('error', 'Master admin users cannot be deleted.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User deleted successfully.');
    }
}
