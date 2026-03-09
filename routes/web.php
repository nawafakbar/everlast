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
use App\Http\Controllers\Freelancer\MomentController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerBookingController;
use App\Models\Booking;
use App\Models\Portfolio;

// ==========================================
// 1. PUBLIC ROUTES (Bisa diakses siapa saja)
// ==========================================
Route::get('/', function () {
    $schedules = Booking::with('package')
    ->where('booking_date', '>=', \Carbon\Carbon::today()->toDateString()) // Cuma ambil jadwal hari ini dan ke depannya
    ->orderBy('booking_date', 'asc') // Urutkan dari tanggal yang paling dekat (ter-awal)
    ->take(4)
    ->get();
    $moments = Portfolio::latest()->take(10)->get();
    return view('welcome', compact('schedules','moments'));
})->name('home');

// TAMBAHAN: Route Halaman Detail Publik (Gallery Feel)
Route::get('/moment/{id}', function ($id) {
    $moment = \App\Models\Portfolio::findOrFail($id);
    return view('front.moment-detail', compact('moment'));
})->name('front.moment.show');

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
        
        // Bulk Delete
        Route::post('users/bulk-delete', [UserController::class, 'bulkDelete'])->name('users.bulkDelete');
        Route::post('packages/bulk-delete', [PackageController::class, 'bulkDelete'])->name('packages.bulkDelete');
        Route::post('bookings/bulk-delete', [BookingController::class, 'bulkDelete'])->name('bookings.bulkDelete');
        
        // Checkout Testing
        Route::get('bookings/{id}/checkout', [BookingController::class, 'checkout'])->name('bookings.checkout');
        Route::post('bookings/{id}/checkout', [BookingController::class, 'processCheckout'])->name('bookings.processCheckout');
        Route::get('bookings/{id}/payment-success', [BookingController::class, 'paymentSuccess'])->name('bookings.paymentSuccess');

        // Calender Everlast
        Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
        Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('calendar.events');

        // Financial Report
        Route::get('/finance', [FinancialReportController::class, 'index'])->name('finance');
});

require __DIR__.'/auth.php';