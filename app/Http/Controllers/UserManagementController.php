<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::withTrashed();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deleted_at')->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where(function ($q) {
                    $q->whereNotNull('deleted_at')->orWhere('is_active', false);
                });
            }
        }

        $users = $query->latest()->paginate(15);

        return view('direktur.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('direktur.users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => ['required', Rule::in([UserRole::ADMIN_KEUANGAN->value, UserRole::MANAJER_OPERASIONAL->value])],
            'phone_number' => 'nullable|string|max:20',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'is_active' => true,
        ]);

        return redirect()->route('direktur.users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);
        return view('direktur.users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, string $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        $allowedRoles = [UserRole::ADMIN_KEUANGAN->value, UserRole::MANAJER_OPERASIONAL->value];
        if ($user->role === UserRole::DIREKTUR) {
            $allowedRoles[] = UserRole::DIREKTUR->value;
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in($allowedRoles)],
            'phone_number' => 'nullable|string|max:20',
            'is_active' => 'required|boolean',
        ]);

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'phone_number' => $request->phone_number,
            'is_active' => $request->is_active,
        ]);

        if ($request->filled('password')) {
            $user->update(['password' => $request->password]);
        }

        // Restore if soft-deleted and being reactivated
        if ($user->trashed() && $request->is_active) {
            $user->restore();
        }

        return redirect()->route('direktur.users.index')
            ->with('success', 'User berhasil diperbarui!');
    }

    /**
     * Toggle user active status.
     */
    public function toggleActive(string $id)
    {
        $user = User::withTrashed()->findOrFail($id);

        // Don't allow deactivating yourself
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        if ($user->is_active) {
            $user->update(['is_active' => false]);
            return back()->with('success', "User {$user->name} dinonaktifkan.");
        } else {
            $user->update(['is_active' => true]);
            if ($user->trashed()) {
                $user->restore();
            }
            return back()->with('success', "User {$user->name} diaktifkan kembali.");
        }
    }

    public function destroy(string $id)
    {
        // Cari user yang akan dihapus
        $user = User::withTrashed()->findOrFail($id);

        // Cek apakah user mencoba menghapus dirinya sendiri
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        // Cek apakah user yang dihapus adalah direktur
        if ($user->role === UserRole::DIREKTUR) {
            return back()->with('error', 'Akun Direktur tidak dapat dihapus.');
        }

        // Eksekusi hapus permanen dari database
        $user->forceDelete();

        return back()->with('success', "User {$user->name} berhasil dihapus permanen.");
    }
}
