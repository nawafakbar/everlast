<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use Illuminate\Http\Request;

class CustomerBookingController extends Controller
{
    // 1. Nampilin Halaman Form
    public function create()
    {
        $user = auth()->user();

        // LOGIKA WAJIB PROFIL LENGKAP
        // Cek apakah nomor telepon masih kosong (null atau string kosong)
        if (empty($user->phone)) {
            // Lempar balik ke halaman profil dengan pesan error
            return redirect()->route('profile.edit')
                ->with('error_profile', 'Lengkapi Nomor Telepon Anda terlebih dahulu sebelum melakukan booking jadwal.');
        }
        $packages = Package::all();
        return view('customer.booking', compact('packages'));
    }

    // 2. Proses Validasi & Simpan Data
    public function store(Request $request)
    {
        // Validasi pindah ke sini (Tambahan rule buat jam biar valid)
        $validated = $request->validate([
            'package_id' => 'required|exists:packages,id',
            'partner_name' => 'required|string|max:255',
            'booking_date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'couple_address' => 'required|string',
            'event_location' => 'required|string',
            'couple_lat' => 'required',
            'couple_lng' => 'required',
            'event_lat' => 'required',
            'event_lng' => 'required',
            'event_location_2' => 'nullable|string',
            'event_lat_2' => 'nullable',
            'event_lng_2' => 'nullable',
        ]);

        // ==========================================
        // CEK BENTROK JADWAL (OVERLAPPING TIME LOGIC)
        // ==========================================
        $isConflict = Booking::where('booking_date', $request->booking_date)
            ->whereNotIn('status', ['cancelled']) // Abaikan pesanan klien lain yang udah dibatalkan
            ->where(function ($query) use ($request) {
                // Rumus sakti bentrok waktu: 
                // Jam Mulai baru < Jam Selesai lama DAN Jam Selesai baru > Jam Mulai lama
                $query->where('start_time', '<', $request->end_time)
                      ->where('end_time', '>', $request->start_time);
            })
            ->exists();

        // Kalau ternyata ada irisan waktu, lempar balik ke form bawa pesan error
        if ($isConflict) {
            return back()
                ->withErrors(['start_time' => 'Maaf, jadwal pada tanggal dan jam tersebut sudah terisi. Silakan geser jam acara atau pilih hari lain.'])
                ->withInput(); // withInput() biar klien nggak cape ngetik ulang formnya
        }
        // ==========================================

        // Tambahkan data otomatis
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        // Simpan ke database
        $booking = Booking::create($validated);

        // Lempar ke halaman pembayaran
        return redirect()->route('customer.checkout', $booking->id);
    }
}