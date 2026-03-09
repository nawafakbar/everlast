<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // Tangkap inputan search
        $search = $request->input('search');

        // Query dengan pencarian dan paginasi (10 data per halaman)
        $users = User::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.users.index', compact('users', 'search'));
    }

    public function edit(string $id)
    {
        $user = User::findOrFail($id);
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, string $id)
    {
        // Cuma boleh validasi dan update ROLE
        $request->validate([
            'role' => 'required|in:admin,customer,freelancer',
        ]);

        $user = User::findOrFail($id);
        $user->update(['role' => $request->role]);

        return redirect()->route('admin.users.index')->with('success', 'Role akses user berhasil diperbarui.');
    }

    public function destroy(string $id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    // Fungsi sakti buat Hapus Massal (Checkbox)
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:users,id'
        ]);

        User::whereIn('id', $request->ids)->delete();

        return back()->with('success', count($request->ids) . ' user berhasil dihapus.');
    }
}