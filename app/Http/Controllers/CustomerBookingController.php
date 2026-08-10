<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use App\Mail\AdminBookingCancelledMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;

class CustomerBookingController extends Controller
{
    // 1. Nampilin Halaman Form
    public function create()
    {
        $user = auth()->user();

        // Cek apakah nomor telepon masih kosong (null atau string kosong)
        if (empty($user->phone)) {
            // Lempar balik ke halaman profil dengan pesan error
            return redirect()->route('profile.edit')
                ->with('error_profile', 'Lengkapi Nomor Telepon Anda terlebih dahulu sebelum melakukan booking jadwal.');
        }
        $packages = Package::all();
        return view('customer.booking', compact('packages'));
    }

    public function packages()
    {
        $packages = \App\Models\Package::withAvg('reviews', 'rating')
            ->withCount('reviews')
            ->latest()
            ->get();
        return view('customer.packages', compact('packages'));
    }

    // 2. Proses Validasi & Simpan Data
    public function store(Request $request)
    {
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
            'prewed_date' => 'nullable|date|after_or_equal:today',
            'prewed_start_time' => 'nullable|date_format:H:i|required_with:prewed_date',
            'prewed_end_time' => 'nullable|date_format:H:i|after:prewed_start_time|required_with:prewed_date',
        ]);

        //cek bentrok jadwal
        $mainConflict = Booking::whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])
            ->where(function ($q) use ($request) {
                $q->where(function ($q2) use ($request) {
                    $q2->where('booking_date', $request->booking_date)
                    ->where('start_time', '<', $request->end_time)
                    ->where('end_time', '>', $request->start_time);
                })
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

        if ($request->filled('prewed_date')) {
            $prewedConflict = Booking::whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])
                ->where(function ($q) use ($request) {
                    $q->where(function ($q2) use ($request) {
                        $q2->where('booking_date', $request->prewed_date)
                        ->where('start_time', '<', $request->prewed_end_time)
                        ->where('end_time', '>', $request->prewed_start_time);
                    })
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

        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending';

        $booking = Booking::create($validated);

        return redirect()->route('customer.checkout', $booking->id);
    }

    // list jam tersedia di hari tertentu (dipakai di form booking)
    public function availableSlots(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->query('date');

        $workStart = '08:00';
        $workEnd = '17:00';

        $bookings = Booking::whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])
            ->where(function ($q) use ($date) {
                $q->whereDate('booking_date', $date)
                  ->orWhereDate('prewed_date', $date);
            })
            ->get();

        $busy = [];

        foreach ($bookings as $booking) {
            if ($booking->booking_date && \Carbon\Carbon::parse($booking->booking_date)->format('Y-m-d') === $date) {
                $busy[] = [
                    'start' => substr($booking->start_time, 0, 5),
                    'end' => substr($booking->end_time, 0, 5),
                ];
            }

            if ($booking->prewed_date && \Carbon\Carbon::parse($booking->prewed_date)->format('Y-m-d') === $date) {
                $busy[] = [
                    'start' => substr($booking->prewed_start_time, 0, 5),
                    'end' => substr($booking->prewed_end_time, 0, 5),
                ];
            }
        }

        usort($busy, fn ($a, $b) => strcmp($a['start'], $b['start']));

        $mergedBusy = [];
        foreach ($busy as $slot) {
            $last = count($mergedBusy) - 1;
            if ($last >= 0 && $slot['start'] <= $mergedBusy[$last]['end']) {
                $mergedBusy[$last]['end'] = max($mergedBusy[$last]['end'], $slot['end']);
            } else {
                $mergedBusy[] = $slot;
            }
        }

        $free = [];
        $cursor = $workStart;

        foreach ($mergedBusy as $slot) {
            if ($slot['start'] > $cursor) {
                $free[] = ['start' => $cursor, 'end' => $slot['start']];
            }
            if ($slot['end'] > $cursor) {
                $cursor = $slot['end'];
            }
        }

        if ($cursor < $workEnd) {
            $free[] = ['start' => $cursor, 'end' => $workEnd];
        }

        return response()->json([
            'date' => $date,
            'busy' => $mergedBusy,
            'free' => $free,
            'work_start' => $workStart,
            'work_end' => $workEnd,
        ]);
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

        // HANYA simpan data cancel. Status TIDAK diubah — masih pending/dp_paid.
        // Ini jadi "sinyal" bahwa ada request cancel yang nunggu konfirmasi admin.
        $booking->update([
            'cancel_reason' => $validated['reason'],
            'cancel_bank_name' => $validated['bank_name'],
            'cancel_account_number' => $validated['account_number'],
            'cancel_account_holder' => $validated['account_holder'],
        ]);

        $proofText = $dpProof
            ? asset('storage/' . $dpProof->proof_image)
            : 'Tidak ada bukti manual (pembayaran via Midtrans / belum ada payment)';

        $message = "*PERMINTAAN PEMBATALAN BOOKING*\n"
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
            . "A/N: {$validated['account_holder']}\n\n"
            . "⚠️ Mohon konfirmasi pembatalan ini di panel admin.";

        $admin = User::where('role', 'admin')->first();
        if ($admin) {
            Mail::to($admin->email)->send(new AdminBookingCancelledMail(
                $booking,
                $validated['reason'],
                $totalPaid,
                $validated['bank_name'],
                $validated['account_number'],
                $validated['account_holder'],
                $dpProof ? asset('storage/' . $dpProof->proof_image) : null
            ));
        }

        $adminPhone = config('services.admin_whatsapp.number');
        $waLink = "https://wa.me/{$adminPhone}?text=" . urlencode($message);

        return redirect()->route('customer.pesanan')
            ->with('success', 'Permintaan pembatalan booking terkirim. Admin akan mengkonfirmasi dalam waktu 1x24 jam.')
            ->with('wa_link', $waLink);
    }
}