<?php

namespace App\Http\Controllers\Freelancer;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssignmentController extends Controller
{
    // 1. Nampilin daftar tugas si freelancer
    public function index()
    {
        $assignments = Assignment::with('booking')
            ->where('user_id', Auth::id())
            // Mengurutkan agar yang berstatus 'pending' (butuh aksi) muncul paling atas
            ->orderByRaw("FIELD(status, 'pending', 'accepted', 'completed', 'rejected')")
            ->latest()
            ->paginate(10);

        return view('freelancer.assignments.index', compact('assignments'));
    }

    // 2. Fungsi buat ngubah status (Terima/Tolak/Selesai)
    public function updateStatus(Request $request, Assignment $assignment)
    {
        // Validasi keamanan: Pastikan hanya yang ditugaskan yang bisa ngubah status
        if ($assignment->user_id !== Auth::id()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'status' => 'required|in:accepted,rejected,completed'
        ]);

        $assignment->update([
            'status' => $validated['status']
        ]);

        // Pesan sukses dinamis berdasarkan aksi yang dipilih
        $message = 'Status penugasan diperbarui!';
        if ($validated['status'] == 'accepted') {
            $message = 'Tugas diterima! Jangan lupa dicatat di kalendermu bro.';
        } elseif ($validated['status'] == 'rejected') {
            $message = 'Tugas ditolak. Admin akan segera mencari penggantimu.';
        } elseif ($validated['status'] == 'completed') {
            $message = 'Kerja bagus! Status acara telah ditandai selesai.';
        }

        return back()->with('success', $message);
    }
}