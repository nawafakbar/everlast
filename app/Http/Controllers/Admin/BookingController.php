<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;
use App\Mail\PaymentSuccessMail;
use App\Mail\NewAssignmentMail;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $bookings = \App\Models\Booking::with(['user', 'package'])
            ->when($search, function ($query, $search) {
                $query->whereHas('user', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('partner_name', 'like', "%{$search}%");
            })->orderBy('booking_date', 'desc')->paginate(10);

        return view('admin.bookings.index', compact('bookings', 'search'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate(['ids' => 'required|array']);

        // Aturan Bisnis: cuma hapus yang statusnya cancelled
        $deletedCount = \App\Models\Booking::whereIn('id', $request->ids)
                            ->where('status', 'cancelled')
                            ->delete();
        
        if ($deletedCount < count($request->ids)) {
            return back()->with('error', $deletedCount . ' booking dihapus. Sisanya diabaikan karena status belum Cancelled!');
        }

        return back()->with('success', $deletedCount . ' booking berhasil dihapus permanen.');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Tarik data user yang role-nya customer, dan semua data paket
        $customers = \App\Models\User::where('role', 'customer')->get();
        $packages = \App\Models\Package::all();
        
        return view('admin.bookings.create', compact('customers', 'packages'));
    }

    public function store(Request $request)
    {
        // 1. Validasi inputan form (update: Tambah kolom Prewed & Lokasi 3)
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'partner_name' => 'required|string|max:255',
            'couple_address' => 'required|string',
            'event_location' => 'required|string',
            'event_location_2' => 'nullable|string', 
            'event_location_3' => 'nullable|string',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'prewed_date' => 'nullable|date',
            'prewed_start_time' => 'nullable|date_format:H:i',
            'prewed_end_time' => 'nullable|date_format:H:i|after:prewed_start_time',
            'status' => 'required|in:pending,dp_paid,paid_in_full,completed,cancelled',
            'couple_lat' => 'nullable|numeric',
            'couple_lng' => 'nullable|numeric',
            'event_lat' => 'nullable|numeric',
            'event_lng' => 'nullable|numeric',
            'event_lat_2' => 'nullable|numeric',
            'event_lng_2' => 'nullable|numeric',
            'event_lat_3' => 'nullable|numeric',
            'event_lng_3' => 'nullable|numeric',
        ]);

        // 2. cek double booking (BERDASARKAN JAM / OVERLAP WAKTU)
        // A. Cek Bentrok Acara Utama
        $conflictMain = \App\Models\Booking::whereNotIn('status', ['cancelled'])
            ->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->whereDate('booking_date', $request->booking_date)
                       ->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>', $request->start_time);
                })->orWhere(function($q2) use ($request) {
                    $q2->whereDate('prewed_date', $request->booking_date)
                       ->where('prewed_start_time', '<', $request->end_time)
                       ->where('prewed_end_time', '>', $request->start_time);
                });
            })->first();

        if ($conflictMain) {
            return back()->withErrors(['booking_date' => 'Jadwal Acara Utama bentrok dengan pesanan klien lain di rentang jam tersebut!'])->withInput();
        }

        // B. Cek Bentrok Acara Prewedding (Jika ada)
        if ($request->prewed_date) {
            $conflictPrewed = \App\Models\Booking::whereNotIn('status', ['cancelled'])
                ->where(function($q) use ($request) {
                    $q->where(function($q2) use ($request) {
                        $q2->whereDate('booking_date', $request->prewed_date)
                           ->where('start_time', '<', $request->prewed_end_time)
                           ->where('end_time', '>', $request->prewed_start_time);
                    })->orWhere(function($q2) use ($request) {
                        $q2->whereDate('prewed_date', $request->prewed_date)
                           ->where('prewed_start_time', '<', $request->prewed_end_time)
                           ->where('prewed_end_time', '>', $request->prewed_start_time);
                    });
                })->first();

            if ($conflictPrewed) {
                return back()->withErrors(['prewed_date' => 'Jadwal Prewedding bentrok dengan pesanan klien lain di rentang jam tersebut!'])->withInput();
            }
        }

        // 3. logika google caledar (Hanya jalan jika status DP atau Lunas)
        if (in_array($request->status, ['dp_paid', 'paid_in_full'])) {
            try {
                $customer = \App\Models\User::find($request->user_id);
                $package = \App\Models\Package::find($request->package_id);

                // A. Simpan Event Acara Utama ke Google Calendar
                $event = new \Spatie\GoogleCalendar\Event;
                $event->name = "Everlast (Utama): " . $customer->name . " & " . $request->partner_name;
                $event->description = "Paket: " . $package->name . "\nLokasi: " . $request->event_location;
                $event->startDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->start_time, 'Asia/Jakarta');
                $event->endDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->end_time, 'Asia/Jakarta');
                $savedEvent = $event->save();
                
                $validated['google_calendar_id'] = $savedEvent->id;

                // B. Simpan Event Prewedding ke Google Calendar (Jika Ada)
                if ($request->prewed_date) {
                    $prewedEvent = new \Spatie\GoogleCalendar\Event;
                    $prewedEvent->name = "Everlast (Prewed): " . $customer->name . " & " . $request->partner_name;
                    $prewedEvent->description = "Sesi Prewedding Paket All In\nLokasi: " . ($request->event_location_2 ?? 'Belum ditentukan');
                    $prewedEvent->startDateTime = \Carbon\Carbon::parse($request->prewed_date . ' ' . $request->prewed_start_time, 'Asia/Jakarta');
                    $prewedEvent->endDateTime = \Carbon\Carbon::parse($request->prewed_date . ' ' . $request->prewed_end_time, 'Asia/Jakarta');
                    $prewedEvent->save();
                }

            } catch (\Exception $e) {
                // nyalakan error sementara!
                dd("Error dari Google: " . $e->getMessage()); 
            }
        }

        // 4. Simpan ke database MySQL
        $booking = \App\Models\Booking::create($validated);

        return redirect()->route('admin.bookings.index')->with('success', 'Pesanan manual berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $booking = \App\Models\Booking::with('assignments.user')->findOrFail($id);
        
        // Ambil semua user yang rolenya freelancer
        $freelancers = \App\Models\User::where('role', 'freelancer')->get();
        
        return view('admin.bookings.show', compact('booking', 'freelancers'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        $customers = \App\Models\User::where('role', 'customer')->get();
        $packages = \App\Models\Package::all();
        
        return view('admin.bookings.edit', compact('booking', 'customers', 'packages'));
    }

    public function update(Request $request, string $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);
        $oldStatus = $booking->status;

        // 1. Validasi inputan form
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'partner_name' => 'required|string|max:255',
            'couple_address' => 'required|string',
            'event_location' => 'required|string',
            'event_location_2' => 'nullable|string', 
            'event_location_3' => 'nullable|string',
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'prewed_date' => 'nullable|date',
            'prewed_start_time' => 'nullable|date_format:H:i',
            'prewed_end_time' => 'nullable|date_format:H:i|after:prewed_start_time',
            'status' => 'required|in:pending,dp_paid,paid_in_full,completed,cancelled',
            'couple_lat' => 'nullable|numeric',
            'couple_lng' => 'nullable|numeric',
            'event_lat' => 'nullable|numeric',
            'event_lng' => 'nullable|numeric',
            'event_lat_2' => 'nullable|numeric',
            'event_lng_2' => 'nullable|numeric',
            'event_lat_3' => 'nullable|numeric',
            'event_lng_3' => 'nullable|numeric',
        ]);

        // 2. cek jadwal bentrok untuk edit (Lebih kompleks karena harus abaikan ID sendiri)
        $conflictMain = \App\Models\Booking::where('id', '!=', $booking->id)
            ->whereNotIn('status', ['cancelled'])
            ->where(function($q) use ($request) {
                $q->where(function($q2) use ($request) {
                    $q2->whereDate('booking_date', $request->booking_date)
                       ->where('start_time', '<', $request->end_time)
                       ->where('end_time', '>', $request->start_time);
                })->orWhere(function($q2) use ($request) {
                    $q2->whereDate('prewed_date', $request->booking_date)
                       ->where('prewed_start_time', '<', $request->end_time)
                       ->where('prewed_end_time', '>', $request->start_time);
                });
            })->first();

        if ($conflictMain) {
            return back()->withErrors(['booking_date' => 'Jadwal Acara Utama bentrok dengan pesanan klien lain di rentang jam tersebut!'])->withInput();
        }

        if ($request->prewed_date) {
            $conflictPrewed = \App\Models\Booking::where('id', '!=', $booking->id)
                ->whereNotIn('status', ['cancelled'])
                ->where(function($q) use ($request) {
                    $q->where(function($q2) use ($request) {
                        $q2->whereDate('booking_date', $request->prewed_date)
                           ->where('start_time', '<', $request->prewed_end_time)
                           ->where('end_time', '>', $request->prewed_start_time);
                    })->orWhere(function($q2) use ($request) {
                        $q2->whereDate('prewed_date', $request->prewed_date)
                           ->where('prewed_start_time', '<', $request->prewed_end_time)
                           ->where('prewed_end_time', '>', $request->prewed_start_time);
                    });
                })->first();

            if ($conflictPrewed) {
                return back()->withErrors(['prewed_date' => 'Jadwal Prewedding bentrok dengan pesanan klien lain di rentang jam tersebut!'])->withInput();
            }
        }

        // 3. logika google calendar
        // jadwal prewedd di paket all in kalau di hapus harus manual
        if ($request->status === 'cancelled') {
            if ($booking->google_calendar_id) {
                try {
                    $event = \Spatie\GoogleCalendar\Event::find($booking->google_calendar_id);
                    $event->delete();
                    $validated['google_calendar_id'] = null; 
                } catch (\Exception $e) { }
            }
        } elseif (in_array($request->status, ['dp_paid', 'paid_in_full'])) {
            if (!$booking->google_calendar_id) {
                try {
                    $customer = \App\Models\User::find($booking->user_id);
                    $package = \App\Models\Package::find($request->package_id);

                    // main event
                    $event = new \Spatie\GoogleCalendar\Event;
                    $event->name = "Everlast Booking: " . $customer->name . " & " . $request->partner_name;
                    $event->description = "Paket: " . $package->name . "\nLokasi 1: " . $request->event_location;
                    $event->startDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->start_time, 'Asia/Jakarta');
                    $event->endDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->end_time, 'Asia/Jakarta');

                    $savedEvent = $event->save();
                    $validated['google_calendar_id'] = $savedEvent->id;

                    // preweeding event
                    if ($request->prewed_date && $request->prewed_start_time && $request->prewed_end_time) {
                        $prewedEvent = new \Spatie\GoogleCalendar\Event;
                        $prewedEvent->name = "[PREWED] Everlast: " . $customer->name . " & " . $request->partner_name;
                        $lokasiPrewed = $request->event_location_3 ?? $request->event_location_2 ?? 'Lokasi belum ditentukan';
                        
                        $prewedEvent->description = "Paket: " . $package->name . "\nLokasi Prewed: " . $lokasiPrewed;
                        $prewedEvent->startDateTime = \Carbon\Carbon::parse($request->prewed_date . ' ' . $request->prewed_start_time, 'Asia/Jakarta');
                        $prewedEvent->endDateTime = \Carbon\Carbon::parse($request->prewed_date . ' ' . $request->prewed_end_time, 'Asia/Jakarta');
                        
                        $prewedEvent->save();
                    }

                } catch (\Exception $e) { }
            }
        }

        // 4. logika auto create payment & sinkronisasi email (khusus manual edit)
        $package = \App\Models\Package::find($request->package_id);
        $packagePrice = $package->price ?? 0;
        $dpAmount = $packagePrice * 0.3; // Hitung 30%

        // Cek jika status diubah dari Pending -> DP Paid
        if ($oldStatus === 'pending' && $request->status === 'dp_paid') {
            $existingPayment = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'dp')
                ->where('status', '!=', 'failed')
                ->first();
            
            if(!$existingPayment) {
                // Tangkap hasil create ke variabel
                $paymentToRecord = \App\Models\Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $dpAmount,
                    'payment_method' => 'manual_admin', 
                    'status' => 'success',
                    'payment_type' => 'dp',
                    'notes' => 'Telah dikonfirmasi secara manual oleh Admin'
                ]);
            } else {
                $existingPayment->update(['status' => 'success']);
                $dpAmount = $existingPayment->amount; 
                $paymentToRecord = $existingPayment;
            }

            // [injector 2a] otomatis catat pemasukan DP manual
            \App\Models\CashFlow::firstOrCreate(
                ['reference_id' => 'payment_' . $paymentToRecord->id],
                [
                    'date' => now()->toDateString(),
                    'type' => 'income',
                    'category' => 'booking_payment',
                    'amount' => $dpAmount,
                    'description' => 'Pembayaran DP dari ' . $booking->user->name . ' (Manual Admin)'
                ]
            );

            \Illuminate\Support\Facades\Mail::to($booking->user->email)->send(new \App\Mail\PaymentSuccessMail($booking, 'Down Payment (DP)', $dpAmount));
        } 
        
        // Cek jika status diubah jadi LUNAS (Entah dari DP atau langsung dari Pending)
        elseif (in_array($oldStatus, ['pending', 'dp_paid']) && in_array($request->status, ['paid_in_full', 'completed'])) {
            $totalPaidSoFar = \App\Models\Payment::where('booking_id', $booking->id)->where('status', 'success')->sum('amount');
            $pelunasanAmount = max(0, $packagePrice - $totalPaidSoFar);
            $existingPayment = \App\Models\Payment::where('booking_id', $booking->id)->where('status', 'pending')->first();

            if(!$existingPayment && $pelunasanAmount > 0) {
                $paymentToRecord = \App\Models\Payment::create([
                    'booking_id' => $booking->id,
                    'amount' => $pelunasanAmount,
                    'payment_method' => 'manual_admin',
                    'status' => 'success',
                    'payment_type' => 'pelunasan',
                    'notes' => 'Telah dikonfirmasi lunas secara manual oleh Admin'
                ]);
            } elseif ($existingPayment) {
                $existingPayment->update(['status' => 'success']);
                $pelunasanAmount = $existingPayment->amount;
                $paymentToRecord = $existingPayment;
            }

            // === [INJECTOR 2B] OTOMATIS CATAT PEMASUKAN PELUNASAN MANUAL ===
            if (isset($paymentToRecord)) {
                \App\Models\CashFlow::firstOrCreate(
                    ['reference_id' => 'payment_' . $paymentToRecord->id],
                    [
                        'date' => now()->toDateString(),
                        'type' => 'income',
                        'category' => 'booking_payment',
                        'amount' => $pelunasanAmount,
                        'description' => 'Pelunasan dari ' . $booking->user->name . ' (Manual Admin)'
                    ]
                );
            }
            // ===============================================================

            \Illuminate\Support\Facades\Mail::to($booking->user->email)->send(new \App\Mail\PaymentSuccessMail($booking, 'Pelunasan', $pelunasanAmount));
        }
                
        // Cek jika di-cancel
        // elseif ($oldStatus !== 'cancelled' && $request->status === 'cancelled') {
        //     \App\Models\Payment::where('booking_id', $booking->id)
        //         ->where('status', 'pending')
        //         ->update(['status' => 'failed']);
        // }
        // Cek jika di-cancel
        elseif ($oldStatus !== 'cancelled' && $request->status === 'cancelled') {
            // 1. Hapus catatan pemasukan (income) di CashFlow dari payment yang sudah success
            $successPayments = \App\Models\Payment::where('booking_id', $booking->id)
                ->where('status', 'success')
                ->get();

            foreach ($successPayments as $payment) {
                \App\Models\CashFlow::where('reference_id', 'payment_' . $payment->id)->delete();
            }

            // 2. Tandai semua payment (pending & success) jadi failed, supaya tidak terhitung revenue
            \App\Models\Payment::where('booking_id', $booking->id)
                ->whereIn('status', ['pending', 'success'])
                ->update(['status' => 'failed']);

            // 3. Hapus catatan pengeluaran (expense) fee kru di CashFlow
            $assignments = \App\Models\Assignment::where('booking_id', $booking->id)->get();

            foreach ($assignments as $assignment) {
                \App\Models\CashFlow::where('reference_id', 'assignment_' . $assignment->id)->delete();
                $assignment->update(['status' => 'rejected']);
            }
        }

        // 5. Simpan perubahan ke database MySQL
        $booking->update($validated);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil diupdate & status pembayaran disinkronisasi.');
    }

    public function destroy(string $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        if ($booking->status !== 'cancelled') {
            return back()->with('error', 'Gagal menghapus! Ubah status booking menjadi Cancelled terlebih dahulu.');
        }

        // [injector 4] Sapu bersih arus kas terkait pesanan ini
        // 1. Tarik kembali uang masuk
        $payments = \App\Models\Payment::where('booking_id', $booking->id)->get();
        foreach ($payments as $payment) {
            \App\Models\CashFlow::where('reference_id', 'payment_' . $payment->id)->delete();
        }

        // 2. Tarik kembali uang keluar (fee kru)
        if (class_exists(\App\Models\Assignment::class)) {
            $assignments = \App\Models\Assignment::where('booking_id', $booking->id)->get();
            foreach ($assignments as $assignment) {
                \App\Models\CashFlow::where('reference_id', 'assignment_' . $assignment->id)->delete();
            }
        }

        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil dihapus permanen.');
    }

    // fungsi testing checkout untuk admin
    public function checkout(string $id)
    {
        $booking = \App\Models\Booking::with(['package', 'user', 'payments'])->findOrFail($id);
        
        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $fullPrice = $booking->package->price;
        
        $remainingAmount = $fullPrice - $totalPaid;
        
        $isFullyPaid = $remainingAmount <= 0;
        $hasPaidDP = $totalPaid > 0 && $totalPaid < $fullPrice;

        return view('admin.bookings.checkout', compact('booking', 'totalPaid', 'remainingAmount', 'isFullyPaid', 'hasPaidDP', 'fullPrice'));
    }

    public function processCheckout(Request $request, string $id)
    {
        $booking = \App\Models\Booking::with(['package', 'user', 'payments'])->findOrFail($id);

        // Hitung ulang sisa tagihan untuk keamanan di backend
        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $fullPrice = $booking->package->price;
        $remainingAmount = $fullPrice - $totalPaid;

        // Cegah kalau udah lunas tapi iseng mau bayar lagi
        if ($remainingAmount <= 0) {
            return redirect()->back()->withErrors(['Pesanan ini sudah lunas sepenuhnya.']);
        }

        $request->validate([
            'payment_type' => 'required|in:dp,pelunasan',
            'payment_method' => 'required|in:midtrans,manual_transfer,manual_qris',
        ]);

        // Cegah klien nakal milih DP padahal udah pernah DP
        if ($totalPaid > 0 && $request->payment_type === 'dp') {
            return redirect()->back()->withErrors(['Anda sudah membayar DP. Silakan pilih Pelunasan.']);
        }

        // Tentukan nominal tagihan! (Ini kuncinya)
        $amount = $request->payment_type === 'dp' ? ($fullPrice / 2) : $remainingAmount;

        $orderId = 'EVR-' . $booking->id . '-' . time();

        // jika testing midtrans
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
                    'first_name' => $booking->user->name,
                    'email' => $booking->user->email,
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            \App\Models\Payment::create([
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
        
        // jika testing manual transfer / qris
        else {
            $request->validate([
                'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
                'notes' => 'nullable|string|max:255'
            ]);

            $imagePath = $request->file('proof_image')->store('payment_proofs', 'public');

            \App\Models\Payment::create([
                'booking_id' => $booking->id,
                'payment_method' => $request->payment_method,
                'payment_type' => $request->payment_type,
                'amount' => $amount,
                'status' => 'pending',
                'proof_image' => $imagePath,
                'notes' => $request->notes,
            ]);

            return redirect()->back()->with('success', 'Testing Upload Bukti Pembayaran Berhasil!');
        }
    }

    public function paymentSuccess(string $id)
    {
        $booking = \App\Models\Booking::with(['package', 'user', 'payments'])->findOrFail($id);
        
        // Ambil data pembayaran terakhir untuk booking ini
        $payment = $booking->payments()->latest()->first();

        return view('admin.bookings.payment_success', compact('booking', 'payment'));
    }

    public function assignFreelancer(Request $request, $bookingId)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'event_type' => 'required|string|max:50', 
        ]);

        $currentBooking = \App\Models\Booking::findOrFail($bookingId);

        // Tentukan TANGGAL dan JAM mana yang mau dicek
        if ($validated['event_type'] === 'all_in_prewedding') {
            $targetDate = $currentBooking->prewed_date;
            $targetStart = $currentBooking->prewed_start_time;
            $targetEnd = $currentBooking->prewed_end_time;
        } else {
            $targetDate = $currentBooking->booking_date;
            $targetStart = $currentBooking->start_time;
            $targetEnd = $currentBooking->end_time;
        }

        if (!$targetDate || !$targetStart || !$targetEnd) {
            return back()->with('error', 'Penugasan gagal! Tanggal dan jam untuk sesi ini belum lengkap diisi oleh klien.');
        }

        // cek bentrok jadwal freelancer dengan assignment lain yang udah ada di database
        $existingAssignments = \App\Models\Assignment::with('booking')
            ->where('user_id', $validated['user_id'])
            ->where('status', '!=', 'rejected')
            ->get();

        $isConflict = false;

        foreach ($existingAssignments as $existing) {
            // Tentukan waktu tugas si freelancer yang udah ada di database
            $exDate = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_date : $existing->booking->booking_date;
            $exStart = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_start_time : $existing->booking->start_time;
            $exEnd = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_end_time : $existing->booking->end_time;

            // Jika di hari yang sama, cek apakah jamnya tabrakan (overlap)
            if ($exDate === $targetDate) {
                // Rumus Overlap: mulai dan selesai
                if ($exStart < $targetEnd && $exEnd > $targetStart) {
                    $isConflict = true;
                    break; // Langsung stop pencarian kalau udah ketemu 1 bentrok
                }
            }
        }

        if ($isConflict) {
            return back()->with('error', 'Penugasan gagal! Freelancer ini sudah memiliki jadwal pada rentang jam tersebut di hari yang sama.');
        }

        $assignment = \App\Models\Assignment::create([
            'booking_id' => $currentBooking->id,
            'user_id' => $validated['user_id'],
            'task' => $validated['task'],
            'fee' => $validated['fee'],
            'event_type' => $validated['event_type'], 
            'status' => 'pending', 
        ]);

        $assignment->load(['user', 'booking.user']);
        \Illuminate\Support\Facades\Mail::to($assignment->user->email)->send(new \App\Mail\NewAssignmentMail($assignment));

        return back()->with('success', 'Freelancer berhasil ditugaskan untuk sesi ' . ucfirst($validated['event_type']) . '! Email notifikasi telah dikirim.');
    }

    // Nampilin form edit penugasan
    public function editAssignment(\App\Models\Assignment $assignment)
    {
        $freelancers = \App\Models\User::where('role', 'freelancer')->get();
        return view('admin.bookings.edit-assignment', compact('assignment', 'freelancers'));
    }

    // Proses simpan hasil edit
    public function updateAssignment(Request $request, \App\Models\Assignment $assignment)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'task' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'event_type' => 'required|string|max:50', 
        ]);

        $isNewFreelancer = false; 

        // Tentukan tanggal dan jam untuk update
        if ($validated['event_type'] === 'all_in_prewedding') {
            $targetDate = $assignment->booking->prewed_date;
            $targetStart = $assignment->booking->prewed_start_time;
            $targetEnd = $assignment->booking->prewed_end_time;
        } else {
            $targetDate = $assignment->booking->booking_date;
            $targetStart = $assignment->booking->start_time;
            $targetEnd = $assignment->booking->end_time;
        }

        if ($assignment->user_id != $validated['user_id'] || $assignment->event_type != $validated['event_type']) {
            
            $existingAssignments = \App\Models\Assignment::with('booking')
                ->where('user_id', $validated['user_id'])
                ->where('id', '!=', $assignment->id)
                ->where('status', '!=', 'rejected')
                ->get();

            $isConflict = false;

            foreach ($existingAssignments as $existing) {
                $exDate = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_date : $existing->booking->booking_date;
                $exStart = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_start_time : $existing->booking->start_time;
                $exEnd = $existing->event_type === 'all_in_prewedding' ? $existing->booking->prewed_end_time : $existing->booking->end_time;

                if ($exDate === $targetDate) {
                    if ($exStart < $targetEnd && $exEnd > $targetStart) {
                        $isConflict = true;
                        break;
                    }
                }
            }

            if ($isConflict) {
                return back()->with('error', 'Update gagal! Freelancer tersebut sudah memiliki jadwal pada rentang jam tersebut.');
            }
            
            if ($assignment->user_id != $validated['user_id']) {
                $isNewFreelancer = true; 
            }
        }

        $assignment->update([
            'user_id' => $validated['user_id'],
            'task' => $validated['task'],
            'fee' => $validated['fee'],
            'event_type' => $validated['event_type'], 
        ]);

        if ($isNewFreelancer) {
            $assignment->load(['user', 'booking.user']);
            \Illuminate\Support\Facades\Mail::to($assignment->user->email)->send(new \App\Mail\NewAssignmentMail($assignment));
        }

        return redirect()->route('admin.bookings.show', $assignment->booking_id)->with('success', 'Detail penugasan tim berhasil diupdate!');
    }

    // Proses hapus penugasan
    public function deleteAssignment(\App\Models\Assignment $assignment)
    {
        $bookingId = $assignment->booking_id; // Simpan ID booking untuk keperluan redirect
        $assignment->delete();
        
        return redirect()->route('admin.bookings.show', $bookingId)->with('success', 'Freelancer berhasil dihapus dari tim acara ini!');
    }

    // Tampilkan form kirim karya
    public function showDelivery(string $id)
    {
        $booking = \App\Models\Booking::with('user')->findOrFail($id);
        return view('admin.bookings.send-delivery', compact('booking'));
    }

    // Proses: update status jadi completed + generate link WhatsApp (tanpa simpan link ke DB)
    public function sendDelivery(Request $request, string $id)
    {
        $booking = \App\Models\Booking::with('user')->findOrFail($id);

        // Aturan bisnis: cegah kirim kalau belum lunas
        if (!in_array($booking->status, ['paid_in_full', 'completed'])) {
            return back()->with('error', 'Booking ini belum lunas. Karya hanya bisa dikirim setelah status Paid In Full.');
        }

        $validated = $request->validate([
            'delivery_link' => ['required', 'url', 'regex:/drive\.google\.com/'],
        ], [
            'delivery_link.regex' => 'Link harus berupa link Google Drive yang valid.',
        ]);

        // Cukup ubah status, tidak ada kolom baru yang disentuh
        $booking->update(['status' => 'completed']);

        // Susun pesan WhatsApp
        $clientName = $booking->user->name;
        $message = "Halo {$clientName}!\n"
            . "Karya foto/video acara Anda bersama {$booking->partner_name} sudah selesai kami proses.\n"
            . "Silakan cek hasilnya di link berikut:\n{$validated['delivery_link']}\n"
            . "Terima kasih telah mempercayakan momen Anda kepada Everlast.";

        // Bersihkan nomor HP (format 62xxx)
        $phone = preg_replace('/\D/', '', $booking->user->phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);

        return redirect()->route('admin.bookings.index')
            ->with('success', 'Status booking otomatis menjadi Completed! Silakan lanjutkan kirim pesan di WhatsApp.')
            ->with('wa_link', $waLink);
    }

    public function rejectPayment(Request $request, string $id)
    {
        $payment = \App\Models\Payment::with('booking.user')->findOrFail($id);

        // Cegah reject ulang kalau sudah bukan pending
        if ($payment->status !== 'pending') {
            return back()->with('error', 'Pembayaran ini sudah diverifikasi sebelumnya.');
        }

        $payment->update(['status' => 'failed']);

        $booking = $payment->booking;
        $clientName = $booking->user->name;

        $message = "Halo {$clientName},\n\n"
            . "Mohon maaf, bukti pembayaran ({$payment->payment_type}) sebesar Rp "
            . number_format($payment->amount, 0, ',', '.') . " yang Anda kirimkan "
            . "tidak dapat kami verifikasi / tidak valid.\n"
            . "Mohon kirimkan ulang bukti transfer yang jelas dan sesuai, atau hubungi kami jika ada pertanyaan.\n"
            . "Terima kasih,\nTim Everlast";

        $phone = preg_replace('/\D/', '', $booking->user->phone ?? '');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        if (empty($phone) || strlen($phone) < 10) {
            return back()->with('error', 'Nomor HP client tidak valid, tidak bisa kirim WA. Silakan hubungi manual.');
        }

        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);

        return back()
            ->with('success', 'Pembayaran ditandai tidak valid & pesan siap dikirim ke client.')
            ->with('wa_link', $waLink);
    }

    public function markRefunded($id)
    {
        $booking = \App\Models\Booking::with(['user', 'package', 'payments'])
            ->where('status', 'cancelled')
            ->findOrFail($id);

        if ($booking->refund_status !== 'pending' && $booking->refund_status !== 'processing') {
            return back()->with('error', 'Refund untuk booking ini sudah selesai atau tidak berlaku.');
        }

        $booking->update([
            'refund_status' => 'completed',
            'refunded_at' => now(),
        ]);

        $clientName = $booking->user->name;
        $totalRefunded = $booking->payments->where('status', 'success')->sum('amount');

        $message = "Halo {$clientName},\n\n"
            . "Refund untuk booking Anda (Paket: " . ($booking->package->name ?? '-') . ") telah *selesai kami proses* ke rekening berikut:\n"
            . "Bank: " . ($booking->cancel_bank_name ?? '-') . "\n"
            . "No. Rek: " . ($booking->cancel_account_number ?? '-') . "\n"
            . "A/N: " . ($booking->cancel_account_holder ?? '-') . "\n"
            . "Jumlah: Rp " . number_format($totalRefunded, 0, ',', '.') . "\n\n"
            . "Mohon ditunggu, bukti transfer akan kami kirimkan menyusul di chat ini ya.\n\n"
            . "Terima kasih,\nTim Everlast";

        // Format nomor HP client (pola sama kayak rejectPayment)
        $phone = preg_replace('/\D/', '', $booking->user->phone ?? '');

        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        } elseif (!str_starts_with($phone, '62')) {
            $phone = '62' . $phone;
        }

        if (empty($phone) || strlen($phone) < 10) {
            return back()->with('success', 'Refund berhasil ditandai selesai, tapi nomor HP client tidak valid — kirim konfirmasi manual ya.');
        }

        $waLink = "https://wa.me/{$phone}?text=" . urlencode($message);

        return back()
            ->with('success', 'Refund berhasil ditandai selesai. Anda akan diarahkan ke WhatsApp untuk kirim konfirmasi ke client.')
            ->with('wa_link', $waLink);
    }

    public function confirmCancel(Request $request, $id)
    {
        $booking = \App\Models\Booking::with(['payments', 'assignments'])
            ->whereNotNull('cancel_reason')
            ->where('status', '!=', 'cancelled')
            ->findOrFail($id);

        $successPayments = $booking->payments->where('status', 'success');
        $totalPaid = $successPayments->sum('amount');

        foreach ($successPayments as $payment) {
            \App\Models\CashFlow::where('reference_id', 'payment_' . $payment->id)->delete();
        }

        \App\Models\Payment::where('booking_id', $booking->id)
            ->whereIn('status', ['pending', 'success'])
            ->update(['status' => 'failed']);

        foreach ($booking->assignments as $assignment) {
            \App\Models\CashFlow::where('reference_id', 'assignment_' . $assignment->id)->delete();
            $assignment->delete();  
        }

        if ($booking->google_calendar_id) {
            try {
                $event = \Spatie\GoogleCalendar\Event::find($booking->google_calendar_id);
                $event->delete();
            } catch (\Exception $e) {}
        }

        $alreadyRefunded = $request->boolean('mark_refunded');

        $booking->update([
            'status' => 'cancelled',
            'google_calendar_id' => null,
            'cancelled_at' => now(),
            'refund_status' => $totalPaid > 0
                ? ($alreadyRefunded ? 'completed' : 'pending')
                : null,
            'refunded_at' => ($totalPaid > 0 && $alreadyRefunded) ? now() : null,
        ]);

        return back()->with('success', 'Pembatalan booking berhasil dikonfirmasi.');
    }

    public function rejectCancel($id)
    {
        $booking = \App\Models\Booking::whereNotNull('cancel_reason')
            ->where('status', '!=', 'cancelled')
            ->findOrFail($id);

        $booking->update([
            'cancel_reason' => null,
            'cancel_bank_name' => null,
            'cancel_account_number' => null,
            'cancel_account_holder' => null,
        ]);

        return back()->with('success', 'Permintaan pembatalan ditolak.');
    }
}
