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
        // Validasi pindah ke sini (Tambahan rule buat jam dan prewed)
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
            'event_location_3' => 'nullable|string',
            'event_lat_3' => 'nullable',
            'event_lng_3' => 'nullable',
            // ==========================================
            // TAMBAHAN VALIDASI DOUBLE DATE (PREWEDDING)
            // ==========================================
            'prewed_date' => 'nullable|date|after_or_equal:today',
            'prewed_start_time' => 'nullable|date_format:H:i|required_with:prewed_date',
            'prewed_end_time' => 'nullable|date_format:H:i|after:prewed_start_time|required_with:prewed_date',
        ]);

        // ==========================================
        // CEK BENTROK JADWAL 1: TANGGAL UTAMA (WEDDING/SINGLE EVENT)
        // ==========================================
        $mainConflict = Booking::whereNotIn('status', ['cancelled'])
            ->where(function ($q) use ($request) {
                // Cek bentrok dengan Acara Utama klien lain
                $q->where(function ($q2) use ($request) {
                    $q2->where('booking_date', $request->booking_date)
                       ->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>', $request->start_time);
                })
                // Cek bentrok dengan Acara Prewed klien lain (karena fotografernya sama)
                ->orWhere(function ($q3) use ($request) {
                    $q3->whereNotNull('prewed_date')
                       ->where('prewed_date', $request->booking_date)
                       ->where('prewed_start_time', '<', $request->end_time)
                       ->where('prewed_end_time', '>', $request->start_time);
                });
            })->exists();

        if ($mainConflict) {
            return back()->withErrors(['start_time' => 'Maaf, jadwal pada tanggal acara utama tersebut sudah terisi. Silakan geser jam atau pilih hari lain.'])->withInput();
        }

        // ==========================================
        // CEK BENTROK JADWAL 2: TANGGAL PREWEDDING (JIKA ADA)
        // ==========================================
        if ($request->filled('prewed_date')) {
            $prewedConflict = Booking::whereNotIn('status', ['cancelled'])
                ->where(function ($q) use ($request) {
                    // Cek bentrok dengan Acara Utama klien lain
                    $q->where(function ($q2) use ($request) {
                        $q2->where('booking_date', $request->prewed_date)
                           ->where('start_time', '<', $request->prewed_end_time)
                           ->where('end_time', '>', $request->prewed_start_time);
                    })
                    // Cek bentrok dengan Acara Prewed klien lain
                    ->orWhere(function ($q3) use ($request) {
                        $q3->whereNotNull('prewed_date')
                           ->where('prewed_date', $request->prewed_date)
                           ->where('prewed_start_time', '<', $request->prewed_end_time)
                           ->where('prewed_end_time', '>', $request->prewed_start_time);
                    });
                })->exists();

            if ($prewedConflict) {
                return back()->withErrors(['prewed_start_time' => 'Maaf, jadwal pada tanggal Prewedding tersebut sudah terisi. Silakan geser jam atau pilih hari lain.'])->withInput();
            }
        }

        // Tambahkan data otomatis
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        // Simpan ke database
        $booking = Booking::create($validated);

        // Lempar ke halaman pembayaran
        return redirect()->route('customer.checkout', $booking->id);
    }

    public function cancelForm($id)
    {
        $booking = \App\Models\Booking::with('payments')
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'dp_paid'])) {
            abort(403, 'Booking ini tidak bisa dibatalkan.');
        }

        return view('customer.bookings.cancel', compact('booking'));
    }

    public function cancelStore(Request $request, $id)
    {
        $booking = \App\Models\Booking::with(['payments', 'user', 'package'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        if (!in_array($booking->status, ['pending', 'dp_paid'])) {
            return back()->with('error', 'Booking ini tidak bisa dibatalkan.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'bank_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:50',
            'account_holder' => 'required|string|max:100',
        ]);

        $successPayments = $booking->payments->where('status', 'success');
        $totalPaid = $successPayments->sum('amount');
        $dpProof = $successPayments->firstWhere('proof_image', '!=', null);

        foreach ($successPayments as $payment) {
            \App\Models\CashFlow::where('reference_id', 'payment_' . $payment->id)->delete();
        }

        \App\Models\Payment::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'success'])
            ->update(['status' => 'failed']);

        $assignments = \App\Models\Assignment::where('booking_id', $booking->id)->get();
        foreach ($assignments as $assignment) {
            \App\Models\CashFlow::where('reference_id', 'assignment_' . $assignment->id)->delete();
            $assignment->update(['status' => 'rejected']);
        }

        if ($booking->google_calendar_id) {
            try {
                $event = \Spatie\GoogleCalendar\Event::find($booking->google_calendar_id);
                $event->delete();
            } catch (\Exception $e) {}
        }

        $booking->update(['status' => 'cancelled', 'google_calendar_id' => null]);

        $proofText = $dpProof
            ? asset('storage/' . $dpProof->proof_image)
            : 'Tidak ada bukti manual (pembayaran via Midtrans / belum ada payment)';

        $message = "*PEMBATALAN BOOKING*\n"
            . "Client: {$booking->user->name}\n"
            . "Partner: {$booking->partner_name}\n"
            . "Paket: " . ($booking->package->name ?? '-') . "\n"
            . "Tanggal Acara: " . \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') . "\n\n"
            . "*Alasan Pembatalan:*\n{$validated['reason']}\n\n"
            . "Total Sudah Dibayar: Rp " . number_format($totalPaid, 0, ',', '.') . "\n"
            . "Bukti Pembayaran: {$proofText}\n\n"
            . "*Data Rekening Pengembalian:*\n"
            . "Bank: {$validated['bank_name']}\n"
            . "No. Rek: {$validated['account_number']}\n"
            . "A/N: {$validated['account_holder']}";

        $adminPhone = config('services.admin_whatsapp.number');
        $waLink = "https://wa.me/{$adminPhone}?text=" . urlencode($message);

        return redirect()->route('customer.pesanan')
            ->with('success', 'Booking berhasil dibatalkan. Konfirmasi pengembalian dana akan kami proses via WhatsApp.')
            ->with('wa_link', $waLink);
    }
}