<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MomentController extends Controller
{
    public function index()
    {
        // Tampilkan hanya momen milik freelancer yang sedang login
        $moments = Portfolio::where('user_id', Auth::id())->latest()->paginate(10);
        return view('freelancer.moments.index', compact('moments'));
    }

    public function create()
    {
        return view('freelancer.moments.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'cover_image' => 'required|url', // Wajib berupa Link
            'category' => 'required|string|max:255',
            'event_date' => 'required|date',
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'quote' => 'nullable|string|max:255',
            'gallery_links' => 'nullable|string', // Kita terima dari textarea (tiap baris 1 link)
        ]);

        // 1. Convert link untuk Cover Image
        $coverImageDirect = $this->convertToDirectLink($validated['cover_image']);

        // 2. Pecah teks dari textarea menjadi array dan convert setiap link
        $galleryArray = [];
        if (!empty($validated['gallery_links'])) {
            $links = explode("\n", str_replace("\r", "", $validated['gallery_links']));
            foreach ($links as $link) {
                if (trim($link) !== '') {
                    $galleryArray[] = $this->convertToDirectLink(trim($link));
                }
            }
        }

        // 3. Simpan ke database
        Portfolio::create([
            'user_id' => Auth::id(),
            'cover_image' => $coverImageDirect,
            'category' => $validated['category'],
            'event_date' => $validated['event_date'],
            'title' => $validated['title'],
            'client_name' => $validated['client_name'],
            'quote' => $validated['quote'],
            'gallery_links' => $galleryArray,
        ]);

        return redirect()->route('freelancer.moments.index')->with('success', 'Moment successfully published!');
    }

    public function edit(Portfolio $portfolio)
    {
        // Pastikan hanya pemiliknya (atau admin) yang bisa edit
        if ($portfolio->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }
        
        // Ubah array gallery_links kembali jadi string (dipisah enter) buat dimasukin ke textarea
        $galleryString = '';
        if (is_array($portfolio->gallery_links)) {
            $galleryString = implode("\n", $portfolio->gallery_links);
        }

        return view('freelancer.moments.edit', compact('portfolio', 'galleryString'));
    }

    public function update(Request $request, Portfolio $portfolio)
    {
        if ($portfolio->user_id !== Auth::id() && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'cover_image' => 'required|url',
            'category' => 'required|string|max:255',
            'event_date' => 'required|date',
            'title' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'quote' => 'nullable|string|max:255',
            'gallery_links' => 'nullable|string',
        ]);

        $coverImageDirect = $this->convertToDirectLink($validated['cover_image']);

        $galleryArray = [];
        if (!empty($validated['gallery_links'])) {
            $links = explode("\n", str_replace("\r", "", $validated['gallery_links']));
            foreach ($links as $link) {
                if (trim($link) !== '') {
                    $galleryArray[] = $this->convertToDirectLink(trim($link));
                }
            }
        }

        $portfolio->update([
            'cover_image' => $coverImageDirect,
            'category' => $validated['category'],
            'event_date' => $validated['event_date'],
            'title' => $validated['title'],
            'client_name' => $validated['client_name'],
            'quote' => $validated['quote'],
            'gallery_links' => $galleryArray,
        ]);

        return redirect()->route('freelancer.moments.index')->with('success', 'Moment successfully updated!');
    }

    public function destroy(Portfolio $portfolio)
    {
        // Pastikan freelancer hanya bisa hapus karyanya sendiri
        if ($portfolio->user_id !== Auth::id()) {
            abort(403);
        }
        $portfolio->delete();
        return redirect()->route('freelancer.moments.index')->with('success', 'Moment successfully deleted!');
    }

    /**
     * Fungsi Helper untuk menukar link GDrive biasa menjadi Direct Link
     */
    private function convertToDirectLink($url)
    {
        // Cari ID unik dari link Google Drive menggunakan Regex
        if (preg_match('/\/d\/([a-zA-Z0-9_-]+)/', $url, $matches)) {
            // UPDATE: Gunakan endpoint lh3.googleusercontent.com yang diizinkan Google untuk tag <img>
            return 'https://lh3.googleusercontent.com/d/' . $matches[1];
        }
        
        // Jika format lain (seperti uc?id= yang sudah manual), ubah juga ke lh3
        if (preg_match('/uc\?id=([a-zA-Z0-9_-]+)/', $url, $matches)) {
            return 'https://lh3.googleusercontent.com/d/' . $matches[1];
        }
        
        return $url;
    }
}