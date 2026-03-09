<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $packages = \App\Models\Package::when($search, function ($query, $search) {
            return $query->where('name', 'like', "%{$search}%")
                         ->orWhere('category', 'like', "%{$search}%");
        })->orderBy('created_at', 'desc')->paginate(10); // Paginasi 10 data

        return view('admin.packages.index', compact('packages', 'search'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);
        \App\Models\Package::whereIn('id', $request->ids)->delete();
        return back()->with('success', count($request->ids) . ' paket berhasil dihapus.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.packages.create');
    }

    public function store(Request $request)
    {
        // 1. Validasi inputan form biar nggak ada yang ngasal
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:All In,Wedding,Prewedding,Akad,Engagement,Other',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'total_locations' => 'required|integer|min:1',
            'description' => 'required|string',
            'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // 2. Cek apakah admin mengunggah gambar thumbnail
        if ($request->hasFile('thumbnail_path')) {
            // Simpan gambar ke folder 'storage/app/public/packages'
            $validated['thumbnail_path'] = $request->file('thumbnail_path')->store('packages', 'public');
        }

        // 3. Simpan semua data ke database
        \App\Models\Package::create($validated);

        // 4. Balikin admin ke halaman tabel index
        return redirect()->route('admin.packages.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $package = \App\Models\Package::findOrFail($id);
        return view('admin.packages.edit', compact('package'));
    }

    public function update(Request $request, string $id)
    {
        $package = \App\Models\Package::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:All In,Wedding,Prewedding,Akad,Engagement,Other',
            'price' => 'required|numeric|min:0',
            'duration_hours' => 'required|integer|min:1',
            'total_locations' => 'required|integer|min:1',
            'description' => 'required|string',
            'thumbnail_path' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        // Cek apakah ada gambar baru yang diunggah
        if ($request->hasFile('thumbnail_path')) {
            // Hapus gambar lama kalau ada
            if ($package->thumbnail_path) {
                Storage::disk('public')->delete($package->thumbnail_path);
            }
            // Simpan gambar baru
            $validated['thumbnail_path'] = $request->file('thumbnail_path')->store('packages', 'public');
        }

        $package->update($validated);

        return redirect()->route('admin.packages.index');
    }

    public function destroy(string $id)
    {
        $package = \App\Models\Package::findOrFail($id);

        // Hapus gambar dari penyimpanan kalau ada
        if ($package->thumbnail_path) {
            Storage::disk('public')->delete($package->thumbnail_path);
        }

        $package->delete();

        return redirect()->route('admin.packages.index');
    }
}
