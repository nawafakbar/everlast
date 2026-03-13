<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payment;
use Illuminate\Http\RedirectResponse;
use App\Models\Booking;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Carbon\Carbon;
use App\Models\User;
use App\Mail\PaymentSuccessMail;
use App\Mail\AdminManualPaymentMail;
use App\Mail\AdminPaymentNotificationMail;
use Illuminate\Support\Facades\Mail;

class CheckoutController extends Controller
{
    // 1. Tampilkan Halaman Pilihan Pembayaran
    public function show($id)
    {
        $booking = Booking::with(['package', 'user', 'payments'])->findOrFail($id);

        // Keamanan: Cuma yang punya booking yang boleh buka
        if ($booking->user_id !== auth()->id()) {
            abort(403, 'Akses ditolak.');
        }

        // Hitung total bayar dan sisa tagihan
        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $fullPrice = $booking->package->price;
        $remainingAmount = $fullPrice - $totalPaid;

        // Cegah akses kalau udah lunas
        if ($remainingAmount <= 0) {
            return redirect()->route('customer.pesanan')->with('status', 'Pesanan ini sudah lunas.');
        }

        $isFullyPaid = false; // Karena udah dicek di atas
        $hasPaidDP = $totalPaid > 0 && $totalPaid < $fullPrice;

        return view('customer.checkout', compact('booking', 'totalPaid', 'remainingAmount', 'isFullyPaid', 'hasPaidDP', 'fullPrice'));
    }

    // 2. Proses Pembayaran (Sesuai Metode yang Dipilih)
    public function process(Request $request, $id)
    {
        $booking = Booking::with(['package', 'user', 'payments'])->findOrFail($id);
        
        // Cek Ulang Tagihan
        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $fullPrice = $booking->package->price;
        $remainingAmount = $fullPrice - $totalPaid;

        if ($remainingAmount <= 0) return redirect()->back()->withErrors(['Pesanan sudah lunas.']);

        $request->validate([
            'payment_type' => 'required|in:dp,pelunasan',
            'payment_method' => 'required|in:midtrans,manual_transfer,manual_qris',
        ]);

        if ($totalPaid > 0 && $request->payment_type === 'dp') {
            return redirect()->back()->withErrors(['Anda sudah membayar DP. Silakan pilih Pelunasan.']);
        }

        // Nominal yang harus dibayar
        $amount = $request->payment_type === 'dp' ? ($fullPrice / 2) : $remainingAmount;
        $orderId = 'EVL-' . $booking->id . '-' . time(); // EVL untuk klien

        // JIKA PILIH MIDTRANS
        if ($request->payment_method === 'midtrans') {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => auth()->user()->name,
                    'email' => auth()->user()->email,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => 'midtrans',
                'payment_type' => $request->payment_type,
                'amount' => $amount,
                'status' => 'pending',
                'midtrans_transaction_id' => $orderId,
                'snap_token' => $snapToken,
            ]);

            return response()->json(['snap_token' => $snapToken]);
        } 
        
        // JIKA PILIH MANUAL (Transfer Bank / QRIS)
        else {
            $request->validate([
                'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'notes' => 'nullable|string|max:255'
            ]);

            // Simpan gambar bukti transfer
            $imagePath = $request->file('proof_image')->store('payment_proofs', 'public');

            $payment = Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'amount' => $amount,
                'status' => 'pending', // Menunggu konfirmasi admin
                'proof_image' => $imagePath,
                'notes' => $request->notes,
            ]);

            // === TAMBAHAN: KIRIM EMAIL KE ADMIN ===
            // Cari user admin pertama buat dikirimin notif
            $admin = User::where('role', 'admin')->first();
            if ($admin) {
                Mail::to($admin->email)->send(new AdminManualPaymentMail($booking, $payment));
            }
            // =======================================

            return redirect()->route('customer.pesanan')->with('success', 'Bukti pembayaran berhasil diunggah. Menunggu konfirmasi admin.');
        }
    }
    
    public function callback(Request $request)
    {
        $serverKey = env('MIDTRANS_SERVER_KEY');
        
        // 1. Validasi Keamanan (Signature Key)
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        if ($hashed !== $request->signature_key) {
            return response()->json(['message' => 'Invalid signature!'], 403);
        }

        // 2. Cari data pembayaran berdasarkan ID Transaksi
        $payment = Payment::where('midtrans_transaction_id', $request->order_id)->first();
        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        // Ambil data booking
        $booking = Booking::with(['user', 'package'])->find($payment->booking_id);
        $transactionStatus = $request->transaction_status;
        
        // 3. Logika Perubahan Status
        if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
            // PEMBAYARAN SUKSES
            $payment->update(['status' => 'success']);
            
            // Update status booking jadi DP atau Lunas
            $newStatus = $payment->payment_type === 'dp' ? 'dp_paid' : 'paid_in_full';
            $booking->update(['status' => $newStatus]);

            // ==========================================
            // BUNGKUS KIRIM EMAIL PAKE TRY-CATCH BIAR GAK CRASH
            // ==========================================
            try {
                $typeString = $payment->payment_type === 'dp' ? 'Down Payment (DP)' : 'Pelunasan';
                Mail::to($booking->user->email)->send(new PaymentSuccessMail($booking, $typeString, $payment->amount));

                $admin = User::where('role', 'admin')->first();
                if ($admin) {
                    Mail::to($admin->email)->send(new AdminPaymentNotificationMail($booking, $payment));
                }
            } catch (\Exception $e) {
                // Biarin aja kalau gagal kirim email, yang penting script lanjut jalan
                \Illuminate\Support\Facades\Log::error('Gagal kirim email Midtrans: ' . $e->getMessage());
            }

            // ==========================================
            // LOGIKA OTOMATISASI GOOGLE CALENDAR
            // ==========================================
            if (!$booking->google_calendar_id) {
                try {
                    // 1. EVENT ACARA UTAMA (WEDDING / SINGLE)
                    $event = new Event;
                    $event->name = "Everlast Booking: " . $booking->user->name . " & " . $booking->partner_name;
                    $event->description = "Paket: " . $booking->package->name . "\nLokasi 1: " . $booking->event_location;
                    $event->startDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->start_time, 'Asia/Jakarta');
                    $event->endDateTime = Carbon::parse($booking->booking_date . ' ' . $booking->end_time, 'Asia/Jakarta');

                    $savedEvent = $event->save();
                    $booking->update(['google_calendar_id' => $savedEvent->id]);

                    // 2. EVENT PREWEDDING (KHUSUS ALL IN)
                    if ($booking->prewed_date && $booking->prewed_start_time && $booking->prewed_end_time) {
                        $prewedEvent = new Event;
                        $prewedEvent->name = "[PREWED] Everlast: " . $booking->user->name . " & " . $booking->partner_name;
                        
                        $lokasiPrewed = $booking->event_location_3 ?? $booking->event_location_2 ?? 'Lokasi belum ditentukan';
                        $prewedEvent->description = "Paket: " . $booking->package->name . "\nLokasi Prewed: " . $lokasiPrewed;
                        
                        $prewedEvent->startDateTime = Carbon::parse($booking->prewed_date . ' ' . $booking->prewed_start_time, 'Asia/Jakarta');
                        $prewedEvent->endDateTime = Carbon::parse($booking->prewed_date . ' ' . $booking->prewed_end_time, 'Asia/Jakarta');
                        
                        $prewedEvent->save();
                    }

                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Gagal bikin GCalendar: ' . $e->getMessage());
                }
            }

        } else if ($transactionStatus == 'cancel' || $transactionStatus == 'deny' || $transactionStatus == 'expire') {
            $payment->update(['status' => 'failed']);
        } else if ($transactionStatus == 'pending') {
            $payment->update(['status' => 'pending']);
        }

        // ==========================================
        // KODE INI SEKARANG PASTI TEREKSEKUSI!
        // ==========================================
        return response()->json(['message' => 'Callback sukses.']);
    }

    public function downloadInvoice($id)
    {
        // Tarik data Booking, sekaligus narik relasi Payment yang berstatus 'success'
        $booking = \App\Models\Booking::with(['user', 'package', 'payments' => function($query) {
            $query->where('status', 'success')->orderBy('created_at', 'asc');
        }])->findOrFail($id);

        // Validasi Keamanan
        if ($booking->user_id !== \Illuminate\Support\Facades\Auth::id() && \Illuminate\Support\Facades\Auth::user()->role !== 'admin') {
            abort(403, 'Anda tidak memiliki akses ke nota ini.');
        }

        // Hitung total uang yang udah masuk
        $totalPaid = $booking->payments->sum('amount');

        if ($totalPaid == 0) {
            abort(400, 'Belum ada pembayaran yang berhasil untuk booking ini.');
        }

        // Load view PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('customer.invoice', compact('booking', 'totalPaid'));

        // Nama file: INV-EVL-BookingID.pdf
        $fileName = 'INV-EVL-' . $booking->id . '.pdf';
        
        return $pdf->download($fileName);
    }
}
// namespace App\Http\Controllers;

// use App\Models\Booking;
// use App\Models\Payment;
// use Illuminate\Http\Request;
// use Illuminate\Support\Str;

// class CheckoutController extends Controller
// {
//     public function show(string $id)
//     {
//         $booking = Booking::with('package')->findOrFail($id);

//         // Keamanan: Cuma user yang mesen yang bisa lihat halaman bayarnya
//         if ($booking->user_id !== auth()->id()) {
//             abort(403, 'Akses ditolak. Ini bukan pesanan Anda.');
//         }

//         return view('client.checkout', compact('booking'));
//     }

//     public function process(Request $request, string $id)
//     {
//         $booking = Booking::with('package')->findOrFail($id);

//         $request->validate([
//             'payment_type' => 'required|in:dp,paid_in_full',
//             'payment_method' => 'required|in:midtrans,manual_transfer,manual_qris',
//         ]);

//         // Kalkulasi Harga: DP = 50%, Lunas = 100%
//         $amount = $request->payment_type === 'dp' 
//             ? $booking->package->price / 2 
//             : $booking->package->price;

//         $orderId = 'EVR-' . $booking->id . '-' . time(); // Bikin ID Transaksi Unik

//         // === JIKA KLIEN MILIH MIDTRANS ===
//         if ($request->payment_method === 'midtrans') {
            
//             // Konfigurasi Midtrans
//             \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
//             \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
//             \Midtrans\Config::$isSanitized = true;
//             \Midtrans\Config::$is3ds = true;

//             $params = [
//                 'transaction_details' => [
//                     'order_id' => $orderId,
//                     'gross_amount' => $amount,
//                 ],
//                 'customer_details' => [
//                     'first_name' => auth()->user()->name,
//                     'email' => auth()->user()->email,
//                 ],
//             ];

//             // Minta "Kunci Pop-Up" (Snap Token) ke server Midtrans
//             $snapToken = \Midtrans\Snap::getSnapToken($params);

//             // Simpan data pembayaran ke database dengan status pending
//             Payment::create([
//                 'booking_id' => $booking->id,
//                 'payment_method' => 'midtrans',
//                 'payment_type' => $request->payment_type,
//                 'amount' => $amount,
//                 'status' => 'pending',
//                 'midtrans_transaction_id' => $orderId,
//                 'snap_token' => $snapToken,
//             ]);

//             // Kembalikan token ke halaman biar JS bisa buka pop-up
//             return response()->json(['snap_token' => $snapToken]);
//         } 
        
//         // === JIKA KLIEN MILIH TRANSFER MANUAL / QRIS ===
//         else {
//             $request->validate([
//                 'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
//                 'notes' => 'nullable|string|max:255'
//             ], [
//                 'proof_image.required' => 'Bukti transfer wajib diupload!',
//                 'proof_image.image' => 'File harus berupa gambar (JPG/PNG).',
//                 'proof_image.max' => 'Ukuran gambar maksimal 2MB.'
//             ]);

//             // Simpan gambar ke folder storage/app/public/payment_proofs
//             $imagePath = $request->file('proof_image')->store('payment_proofs', 'public');

//             // Simpan ke database
//             Payment::create([
//                 'booking_id' => $booking->id,
//                 'payment_method' => $request->payment_method,
//                 'payment_type' => $request->payment_type,
//                 'amount' => $amount,
//                 'status' => 'pending',
//                 'proof_image' => $imagePath,
//                 'notes' => $request->notes,
//             ]);

//             // Ubah status booking jadi pending konfirmasi (opsional)
//             // $booking->update(['status' => 'pending']); 

//             return redirect()->back()->with('success', 'Bukti pembayaran berhasil diupload! Menunggu konfirmasi admin.');
//         }
//     }
// }