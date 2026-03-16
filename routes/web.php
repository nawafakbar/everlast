<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\CalendarController;
use App\Http\Controllers\Admin\FinancialReportController;
use App\Http\Controllers\Admin\CashFlowController;
use App\Http\Controllers\Freelancer\MomentController;
use App\Http\Controllers\Freelancer\AssignmentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerBookingController;
use App\Models\Booking;
use App\Models\Portfolio;
use Carbon\Carbon;

// ==========================================
// 1. PUBLIC ROUTES (Bisa diakses siapa saja)
// ==========================================
Route::get('/', function () {
    $today = Carbon::today()->toDateString();

    // 1. Ambil semua pesanan yang tanggal utama ATAU tanggal prewed-nya belum lewat
    $bookings = Booking::with('package', 'user')
        ->where(function($query) use ($today) {
            $query->whereDate('booking_date', '>=', $today)
                  ->orWhereDate('prewed_date', '>=', $today);
        })
        ->whereIn('status', ['dp_paid', 'paid_in_full', 'completed']) // Opsional: Biar cuma jadwal fix yang tampil
        ->get();

    $scheduleList = collect();

    // 2. Pecah dan kloning datanya biar jadwal Prewed & Utama jadi kotak terpisah
    foreach ($bookings as $b) {
        
        // Cek dan masukkan Acara Utama (Main Event)
        if ($b->booking_date >= $today) {
            $mainEvent = clone $b;
            $mainEvent->display_date = $b->booking_date;
            $mainEvent->display_start = $b->start_time;
            $mainEvent->display_end = $b->end_time;
            $mainEvent->display_location = $b->event_location ?? 'TBA';
            $mainEvent->event_label = 'MAIN EVENT'; // Label untuk UI
            
            $scheduleList->push($mainEvent);
        }

        // Cek dan masukkan Acara Prewedding (Jika ada)
        if ($b->prewed_date && $b->prewed_date >= $today) {
            $prewedEvent = clone $b;
            $prewedEvent->display_date = $b->prewed_date;
            $prewedEvent->display_start = $b->prewed_start_time;
            $prewedEvent->display_end = $b->prewed_end_time;
            $prewedEvent->display_location = $b->event_location_2 ?? ($b->event_location_3 ?? 'TBA');
            $prewedEvent->event_label = 'PREWEDDING SESSION'; // Label untuk UI
            
            $scheduleList->push($prewedEvent);
        }
    }

    // 3. Urutkan gabungan jadwal tersebut dari yang terdekat, lalu ambil 4 saja
    $schedules = $scheduleList->sortBy('display_date')->take(4)->values();

    $moments = Portfolio::latest()->take(10)->get();
    
    return view('welcome', compact('schedules', 'moments'));
})->name('home');

// TAMBAHAN: Route Halaman Detail Publik (Gallery Feel)
Route::get('/moment/{id}', function ($id) {
    $moment = \App\Models\Portfolio::findOrFail($id);
    return view('front.moment-detail', compact('moment'));
})->name('front.moment.show');

Route::get('/portfolio', function () {
    $moments = \App\Models\Portfolio::latest()->paginate(16);
    return view('front.portfolio', compact('moments'));
})->name('front.portfolio.index');

// Google Socialite
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('google.login');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback']);

// Webhook Midtrans (Pastikan URL ini masuk pengecualian di VerifyCsrfToken!)
Route::post('/api/midtrans/callback', [CheckoutController::class, 'callback']);

// ==========================================
// 2. AUTHENTICATED ROUTES (Semua role masuk ke sini dulu)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    
    // REDIRECTION HUB: Mengarahkan user yang login ke dashboard masing-masing
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        if ($role === 'admin') return redirect()->route('admin.dashboard');
        if ($role === 'freelancer') return redirect()->route('freelancer.dashboard');
        return redirect('/'); // Customer diarahkan kembali ke halaman utama
    })->name('dashboard');

    // Profile (Bawaan Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/pesanan', function () {
        // Ambil data booking milik user yang sedang login beserta relasi paketnya
        $bookings = Booking::with('package')
                    ->where('user_id', auth()->id())
                    ->latest() // Urutkan dari yang terbaru
                    ->get();
                    
        return view('customer.pesanan', compact('bookings'));
    })->name('customer.pesanan');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Rute Form Booking Customer
    Route::get('/booking', [CustomerBookingController::class, 'create'])->name('customer.booking');
    Route::post('/booking', [CustomerBookingController::class, 'store'])->name('customer.booking.store');

    // Rute Checkout
    Route::get('/checkout/{id}', [CheckoutController::class, 'show'])->name('customer.checkout');
    Route::post('/checkout/{id}', [CheckoutController::class, 'process'])->name('customer.checkout.process'); // <--- TAMBAHIN INI

    // Route Cetak Nota Pembayaran (Customer / Admin)
    Route::get('/payment/{booking}/invoice', [CheckoutController::class, 'downloadInvoice'])->name('booking.invoice');

    // Calender Everlast
    Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar');
    Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('admin.calendar.events');
    Route::get('/customer/calendar-events', [CalendarController::class, 'getAvailableDates'])->name('customer.calendar.events');

    // ROUTE KHUSUS CUSTOMER (Bisa diakses Admin & Freelancer juga)
    // Jika ada route seperti 'Pesanan Saya', bungkus di sini pakai:
    // Route::middleware(['role:admin,freelancer,customer'])->group(function() { ... });
});

// ==========================================
// 3. FREELANCER AREA (Bisa diakses Admin & Freelancer)
// ==========================================
Route::prefix('freelance')
    ->name('freelancer.')
    ->middleware(['auth', 'role:freelancer']) // Admin ditambahkan agar bisa akses
    ->group(function () {
        
        Route::get('/moments', [MomentController::class, 'index'])->name('moments.index');
        Route::get('/moments/create', [MomentController::class, 'create'])->name('moments.create');
        Route::post('/moments', [MomentController::class, 'store'])->name('moments.store');
        Route::delete('/moments/{portfolio}', [MomentController::class, 'destroy'])->name('moments.destroy');
        Route::get('/moments/{portfolio}/edit', [MomentController::class, 'edit'])->name('moments.edit');
        Route::put('/moments/{portfolio}', [MomentController::class, 'update'])->name('moments.update');
        Route::get('/schedules', [AssignmentController::class, 'index'])->name('schedules.index');
        Route::patch('/schedules/{assignment}/status', [AssignmentController::class, 'updateStatus'])->name('schedules.status');
        
});

// ==========================================
// 4. ADMIN AREA (Hanya mutlak untuk Admin)
// ==========================================
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        // Resource Routes (Otomatis bikin route index, create, store, dll)
        Route::resource('packages', PackageController::class);
        Route::resource('bookings', BookingController::class);
        Route::resource('users', UserController::class);
        Route::post('/admin/bookings/{booking}/assign', [BookingController::class, 'assignFreelancer'])->name('bookings.assign');
        Route::get('/admin/assignments/{assignment}/edit', [BookingController::class, 'editAssignment'])->name('assignments.edit');
        Route::put('/admin/assignments/{assignment}', [BookingController::class, 'updateAssignment'])->name('assignments.update');
        Route::delete('/admin/assignments/{assignment}', [BookingController::class, 'deleteAssignment'])->name('assignments.destroy');
        
        // Bulk Delete
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');
        Route::post('packages/bulk-delete', [PackageController::class, 'bulkDelete'])->name('packages.bulkDelete');
        Route::post('bookings/bulk-delete', [BookingController::class, 'bulkDelete'])->name('bookings.bulkDelete');
        
        // Checkout Testing
        Route::get('bookings/{id}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
        Route::post('bookings/{id}/checkout', [BookingController::class, 'processCheckout'])->name('bookings.processCheckout');
        Route::get('bookings/{id}/payment-success', [BookingController::class, 'paymentSuccess'])->name('bookings.paymentSuccess');

        // Financial Report
        Route::get('/finance', [FinancialReportController::class, 'index'])->name('finance');
        Route::get('/admin/finance/pdf', [FinancialReportController::class, 'exportPdf'])->name('finance.pdf');
        Route::get('/admin/finance/excel', [FinancialReportController::class, 'exportExcel'])->name('finance.excel');

        // Cash Flow Routes
        Route::get('/cash-flows', [CashFlowController::class, 'index'])->name('cash_flows.index');
        Route::post('/cash-flows', [CashFlowController::class, 'store'])->name('cash_flows.store');
        Route::delete('/cash-flows/{cashFlow}', [CashFlowController::class, 'destroy'])->name('cash_flows.destroy');
        Route::get('/cash-flows/export-pdf', [CashFlowController::class, 'exportPdf'])->name('cash_flows.export_pdf');
});

require __DIR__.'/auth.php';