<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Display a listing of the users.
     */
    public function index(Request $request)
    {
        $query = User::with('role');

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10);
        return view('pages.pengaturan.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::where('nama_role', '!=', 'Super Admin')->get();
        return view('pages.pengaturan.users.form', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:50|unique:users,username',
            'password'     => 'required|string|min:6',
            'role_id'      => 'required|exists:roles,id',
            'is_active'    => 'nullable|boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        User::create($validated);

        return redirect()->route('pengaturan.users.index')
            ->with('success', 'Pengguna baru berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::where('nama_role', '!=', 'Super Admin')->get();
        return view('pages.pengaturan.users.form', compact('user', 'roles'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'username'     => 'required|string|max:50|unique:users,username,' . $user->id,
            'password'     => 'nullable|string|min:6',
            'role_id'      => 'required|exists:roles,id',
            'is_active'    => 'nullable|boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }
        
        $validated['is_active'] = $request->has('is_active') ? true : false;

        // Cegah superadmin dari menonaktifkan dirinya sendiri
        if ($user->id === auth()->id() && !$validated['is_active']) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun yang sedang Anda gunakan.');
        }

        $user->update($validated);

        return redirect()->route('pengaturan.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    /**
     * Remove the specified user from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();

        return redirect()->route('pengaturan.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }
}
