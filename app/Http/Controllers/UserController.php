<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'status', 'city', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $customers = User::where('role', 'customer')
            ->with('followedSellers.seller')
            ->orderBy($sortBy, $sortDir)->paginate($perPage);
        $customers->appends($request->query())->onEachSide(1);

        return view('admin.customers.index', compact('customers', 'sortBy', 'sortDir'));
    }

    public function edit($id)
    {
        $customer = User::findOrFail($id);

        if (Auth::user()->role !== 'master_admin' && $customer->role === 'master_admin') {
            return redirect()->route('admin.customers.index')->with('error', 'You cannot edit master admin users.');
        }

        return view('admin.customers.edit', compact('customer'));
    }

    public function show(Request $request, $id)
    {
        $customer = User::with(['addresses'])->whereIn('role', ['customer', 'user'])->findOrFail($id);

        $orders = Order::with(['items.product', 'user'])
            ->where('user_id', $customer->id)
            ->orderBy('created_at', 'desc')
            ->paginate($request->integer('per_page', 10))
            ->withQueryString();

        $stats = [
            'total_orders' => $orders->total(),
            'total_spent' => (float) Order::where('user_id', $customer->id)
                ->where('status', '!=', 'cancelled')
                ->sum('total_amount'),
            'pending_orders' => Order::where('user_id', $customer->id)
                ->whereIn('status', ['pending', 'processing'])
                ->count(),
        ];

        return view('admin.customers.show', compact('customer', 'orders', 'stats'));
    }

    public function profile()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to access your profile.');
        }

        $user = Auth::user();
        $profile = $user;
        $addresses = $user->addresses()->orderBy('set_default', 'desc')->latest()->get();

        return view('user.profile', compact('profile', 'addresses'));
    }

    public function editProfile()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'Please login to edit your profile.');
        }

        $profile = Auth::user();

        return view('user.profile-edit', compact('profile'));
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (Auth::user()->role !== 'master_admin' && $user->role === 'master_admin') {
            return redirect()->route('admin.customers.index')->with('error', 'You cannot delete master admin users.');
        }

        $user->delete();

        return redirect()->route('admin.customers.index');
    }

    public function dashboard()
    {
        return view('user.dashboard');
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $rules = [
            'full_name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
            'phone' => 'required|string|max:255|regex:/^[0-9+\-\s()]+$/',
            'address' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'required|string|max:255',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];

        if ($request->filled('current_password') || $request->filled('new_password') || $request->filled('new_password_confirmation')) {
            $rules['current_password'] = 'required';
            $rules['new_password'] = 'required|min:8|confirmed';
        }

        $request->validate($rules);

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                deleteImageFiles($user->avatar);
            }
            $user->avatar = saveImageWithWebp($request->file('avatar'));
        }

        if ($request->filled('current_password') || $request->filled('new_password')) {
            if (! Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Incorrect current password']);
            }

            $user->password = Hash::make($request->new_password);
        }

        $user->update([
            'name' => $request->full_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'avatar' => $user->avatar,
            'password' => $user->password,
            'postal_code' => $request->postal_code,
        ]);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (! Hash::check($request->current_password, auth()->user()->password)) {
            return back()->withErrors(['current_password' => 'Incorrect current password']);
        }

        auth()->user()->update([
            'password' => Hash::make($request->new_password),
        ]);

        return back()->with('success', 'Password changed successfully.');
    }

    public function customers(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'status', 'city', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $users = User::where('role', 'customer')->orderBy($sortBy, $sortDir)->paginate($perPage);
        $users->appends($request->query())->onEachSide(1);

        return view('admin.customers.index', compact('users', 'sortBy', 'sortDir'));
    }

    public function staff(Request $request)
    {
        $sortBy = in_array($request->sort_by, ['id', 'name', 'email', 'role', 'created_at']) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';
        $perPage = in_array((int) $request->per_page, [10, 20, 50, 100]) ? (int) $request->per_page : 10;

        $users = User::whereIn('role', ['master_admin', 'admin', 'staff'])->orderBy($sortBy, $sortDir)->paginate($perPage);
        $users->appends($request->query())->onEachSide(1);

        return view('admin.staff.index', compact('users', 'sortBy', 'sortDir'));
    }

    public function createCustomer()
    {
        return view('admin.customers.create');
    }

    public function storeCustomer(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20|regex:/^[A-Za-z0-9\-\s]{3,20}$/',
            'status' => 'nullable|in:active,inactive',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'role' => 'customer',
            'status' => $request->status ?? 'active',
        ]);

        return redirect()->route('admin.customers.index')->with('success', 'Customer created successfully.');
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        if (Auth::user()->role !== 'master_admin' && $user->role === 'master_admin') {
            return redirect()->route('admin.customers.index')->with('error', 'You cannot update master admin users.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'country' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20|regex:/^[A-Za-z0-9\-\s]{3,20}$/',
            'status' => 'nullable|in:active,inactive',
        ]);

        $data = $request->only(['name', 'email', 'phone', 'address', 'city', 'state', 'country', 'postal_code', 'status']);
        if (! isset($data['status'])) {
            $data['status'] = 'active';
        }
        $user->update($data);

        return redirect()->route('admin.customers.index')->with('success', 'Customer updated successfully.');
    }

    public function createStaff()
    {
        return view('admin.staff.create');
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'role' => 'required|in:admin,staff',
        ]);

        if (in_array($request->role, ['admin', 'master_admin']) && Auth::user()->role !== 'master_admin') {
            return back()->withErrors(['role' => 'Only master admin can create admin/master admin accounts.']);
        }

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role' => $request->role,
        ]);

        $label = ucfirst($request->role);

        return redirect()->route('admin.staff.index')->with('success', "{$label} created successfully.");
    }

    public function editStaff($id)
    {
        $user = User::findOrFail($id);

        return view('admin.staff.edit', compact('user'));
    }

    public function updateStaff(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:255|regex:/^[0-9+\-\s()]*$/',
            'password' => 'nullable|string|min:6',
            'role' => 'required|in:admin,staff',
        ]);

        if ($user->role === 'master_admin') {
            return back()->withErrors(['role' => 'Cannot modify master admin accounts.']);
        }

        if (in_array($request->role, ['admin', 'master_admin']) && Auth::user()->role !== 'master_admin') {
            return back()->withErrors(['role' => 'Only master admin can assign admin/master admin role.']);
        }

        $data = $request->only(['name', 'email', 'phone', 'role']);
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('admin.staff.index')->with('success', 'User updated successfully.');
    }

    public function destroyStaff($id)
    {
        User::findOrFail($id)->delete();

        return redirect()->route('admin.staff.index')->with('success', 'Staff deleted successfully.');
    }

    public function toggleStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->role === 'master_admin') {
            return back()->with('error', 'Master admin status cannot be changed.');
        }

        $user->status = $user->status === 'active' ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'Customer status updated successfully.');
    }
}
