<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use Illuminate\Http\Request;
use Spatie\GoogleCalendar\Event;
use Carbon\Carbon;

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

        // Aturan Bisnis: CUMA hapus yang statusnya cancelled
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
        // 1. Validasi inputan form
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'partner_name' => 'required|string|max:255',
            'couple_address' => 'required|string',
            'event_location' => 'required|string',
            'event_location_2' => 'nullable|string', 
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:pending,dp_paid,paid_in_full,completed,cancelled',
            'couple_lat' => 'nullable|numeric',
            'couple_lng' => 'nullable|numeric',
            'event_lat' => 'nullable|numeric',
            'event_lng' => 'nullable|numeric',
            'event_lat_2' => 'nullable|numeric',
            'event_lng_2' => 'nullable|numeric',
        ]);

        // 2. CEK DOUBLE BOOKING
        // Cari apakah ada booking di tanggal yang sama, selain yang statusnya dibatalkan
        $existingBooking = \App\Models\Booking::where('booking_date', $request->booking_date)
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existingBooking) {
            // Kalau ketemu, lempar kembali ke form bawa pesan error
            return back()->withErrors(['booking_date' => 'Tanggal ini sudah di-booking! Silakan pilih tanggal lain.'])->withInput();
        }

        // 3. LOGIKA GOOGLE CALENDAR (Hanya jalan jika status DP atau Lunas)
        if (in_array($request->status, ['dp_paid', 'paid_in_full'])) {
            try {
                $customer = \App\Models\User::find($request->user_id);
                $package = \App\Models\Package::find($request->package_id);

                $event = new \Spatie\GoogleCalendar\Event;
                $event->name = "Everlast: " . $customer->name . " & " . $request->partner_name;
                $event->description = "Paket: " . $package->name . "\nLokasi 1: " . $request->couple_address . "\nLokasi 2: " . $request->event_location;

                // Set waktu pakai zona waktu Jakarta biar akurat
                $event->startDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->start_time, 'Asia/Jakarta');
                $event->endDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->end_time, 'Asia/Jakarta');

                $savedEvent = $event->save();
                $validated['google_calendar_id'] = $savedEvent->id;

            } catch (\Exception $e) {
                // NYALAKAN RADAR ERROR SEMENTARA!
                dd("Error dari Google: " . $e->getMessage()); 
            }
        }

        // 4. Simpan ke database MySQL
        \App\Models\Booking::create($validated);

        return redirect()->route('admin.bookings.index');
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

        // 1. Validasi inputan form
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'package_id' => 'required|exists:packages,id',
            'partner_name' => 'required|string|max:255',
            'couple_address' => 'required|string',
            'event_location' => 'required|string',
            'event_location_2' => 'nullable|string', 
            'booking_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'status' => 'required|in:pending,dp_paid,paid_in_full,completed,cancelled',
            'couple_lat' => 'nullable|numeric',
            'couple_lng' => 'nullable|numeric',
            'event_lat' => 'nullable|numeric',
            'event_lng' => 'nullable|numeric',
            'event_lat_2' => 'nullable|numeric',
            'event_lng_2' => 'nullable|numeric',
        ]);

        // 2. CEK DOUBLE BOOKING (Kecuali booking ini sendiri)
        $existingBooking = \App\Models\Booking::where('booking_date', $request->booking_date)
            ->where('id', '!=', $id) // Abaikan ID yang sedang di-edit
            ->whereNotIn('status', ['cancelled'])
            ->first();

        if ($existingBooking) {
            return back()->withErrors(['booking_date' => 'Tanggal ini sudah di-booking oleh klien lain!'])->withInput();
        }

        // 3. LOGIKA GOOGLE CALENDAR
        if ($request->status === 'cancelled') {
            // Jika status dibatalkan, hapus event dari Google Calendar (jika ada)
            if ($booking->google_calendar_id) {
                try {
                    $event = \Spatie\GoogleCalendar\Event::find($booking->google_calendar_id);
                    $event->delete();
                    $validated['google_calendar_id'] = null; // Kosongkan ID di database lokal
                } catch (\Exception $e) {
                    // Abaikan jika event di Google Calendar sudah terhapus manual atau tidak ditemukan
                }
            }
        } elseif (in_array($request->status, ['dp_paid', 'paid_in_full'])) {
            // BONUS: Jika dari pending berubah jadi DP/Lunas, dan belum ada kalender, kita buatkan
            if (!$booking->google_calendar_id) {
                try {
                    $customer = \App\Models\User::find($request->user_id);
                    $package = \App\Models\Package::find($request->package_id);

                    $event = new \Spatie\GoogleCalendar\Event;
                    $event->name = "Everlast: " . $customer->name . " & " . $request->partner_name;
                    $event->description = "Paket: " . $package->name . "\nLokasi 1: " . $request->couple_address;
                    $event->startDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->start_time, 'Asia/Jakarta');
                    $event->endDateTime = \Carbon\Carbon::parse($request->booking_date . ' ' . $request->end_time, 'Asia/Jakarta');

                    $savedEvent = $event->save();
                    $validated['google_calendar_id'] = $savedEvent->id;
                } catch (\Exception $e) {}
            }
        }

        // ==========================================
        // 4. LOGIKA SINKRONISASI STATUS PEMBAYARAN
        // ==========================================
        if ($request->status === 'dp_paid') {
            // Ubah pembayaran DP yang masih pending menjadi success
            \App\Models\Payment::where('booking_id', $booking->id)
                ->where('payment_type', 'dp')
                ->where('status', 'pending')
                ->update(['status' => 'success']);
        } 
        elseif (in_array($request->status, ['paid_in_full', 'completed'])) {
            // Jika lunas atau selesai, ubah SEMUA pembayaran pending menjadi success
            \App\Models\Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->update(['status' => 'success']);
        } 
        elseif ($request->status === 'cancelled') {
            // Jika pesanan batal, batalkan juga tagihan yang masih gantung
            \App\Models\Payment::where('booking_id', $booking->id)
                ->where('status', 'pending')
                ->update(['status' => 'failed']);
        }

        // 5. Simpan perubahan ke database MySQL
        $booking->update($validated);

        return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil diupdate.');
    }

    public function destroy(string $id)
    {
        $booking = \App\Models\Booking::findOrFail($id);

        // ATURAN BISNIS: Harus Cancelled dulu
        if ($booking->status !== 'cancelled') {
            // Kembalikan ke halaman sebelumnya dengan pesan error bawaan (kita tangkap di Javascript/Blade nanti)
            return back()->with('error', 'Gagal menghapus! Ubah status booking menjadi Cancelled terlebih dahulu.');
        }

        // Hapus dari database (Event GCal sudah otomatis terhapus saat update ke cancelled)
        $booking->delete();

        return redirect()->route('admin.bookings.index')->with('success', 'Booking berhasil dihapus permanen.');
    }

    // --- FUNGSI TESTING CHECKOUT UNTUK ADMIN ---
    public function checkout(string $id)
    {
        // Pastikan load relasi 'payments' biar bisa kita hitung
        $booking = \App\Models\Booking::with(['package', 'user', 'payments'])->findOrFail($id);
        
        // 1. Hitung total uang yang SUDAH MASUK (status = success)
        $totalPaid = $booking->payments->where('status', 'success')->sum('amount');
        $fullPrice = $booking->package->price;
        
        // 2. Hitung Sisa Tagihan
        $remainingAmount = $fullPrice - $totalPaid;
        
        // 3. Tentukan Status
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

        // JIKA TESTING MIDTRANS
        if ($request->payment_method === 'midtrans') {
            \Midtrans\Config::$serverKey = env('MIDTRANS_SERVER_KEY');
            \Midtrans\Config::$isProduction = env('MIDTRANS_IS_PRODUCTION', false);
            \Midtrans\Config::$isSanitized = true;
            \Midtrans\Config::$is3ds = true;

            $params = [
                'transaction_details' => [
                    'order_id' => $orderId,
                    'gross_amount' => $amount, // <-- Akan pakai harga dinamis
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
        
        // JIKA TESTING MANUAL / QRIS
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
        ]);

        $currentBooking = \App\Models\Booking::findOrFail($bookingId);

        // CEK BENTROK JADWAL MENGGUNAKAN 'booking_date'
        $isConflict = \App\Models\Assignment::where('user_id', $validated['user_id'])
            ->where('status', '!=', 'rejected')
            ->whereHas('booking', function ($query) use ($currentBooking) {
                $query->whereDate('booking_date', $currentBooking->booking_date); 
            })
            ->exists();

        if ($isConflict) {
            return back()->with('error', 'Penugasan gagal! Freelancer ini sudah memiliki jadwal di tanggal tersebut.');
        }

        \App\Models\Assignment::create([
            'booking_id' => $currentBooking->id,
            'user_id' => $validated['user_id'],
            'task' => $validated['task'],
            'fee' => $validated['fee'],
            'status' => 'pending', // Status awal nunggu di-acc freelancer
        ]);

        return back()->with('success', 'Freelancer berhasil ditugaskan! Menunggu konfirmasi.');
    }

    // Nampilin form edit penugasan
    public function editAssignment(\App\Models\Assignment $assignment)
    {
        // Ambil semua freelancer untuk opsi dropdown
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
        ]);

        // Cek bentrok jadwal HANYA JIKA admin mengganti orangnya (user_id berubah)
        if ($assignment->user_id != $validated['user_id']) {
            $isConflict = \App\Models\Assignment::where('user_id', $validated['user_id'])
                ->where('id', '!=', $assignment->id) // Abaikan tugas yang sedang di-edit ini
                ->where('status', '!=', 'rejected')
                ->whereHas('booking', function ($query) use ($assignment) {
                    $query->whereDate('booking_date', $assignment->booking->booking_date); 
                })
                ->exists();

            if ($isConflict) {
                return back()->with('error', 'Update gagal! Freelancer pengganti sudah memiliki jadwal di tanggal tersebut.');
            }
        }

        // Update data
        $assignment->update([
            'user_id' => $validated['user_id'],
            'task' => $validated['task'],
            'fee' => $validated['fee'],
            // Note: Status TIDAK kita ubah ke pending lagi agar tidak merepotkan freelancer kalau cuma ganti Fee/Task
        ]);

        return redirect()->route('admin.bookings.show', $assignment->booking_id)->with('success', 'Detail penugasan tim berhasil diupdate!');
    }

    // Proses hapus penugasan
    public function deleteAssignment(\App\Models\Assignment $assignment)
    {
        $bookingId = $assignment->booking_id; // Simpan ID booking untuk keperluan redirect
        $assignment->delete();
        
        return redirect()->route('admin.bookings.show', $bookingId)->with('success', 'Freelancer berhasil dihapus dari tim acara ini!');
    }
}
