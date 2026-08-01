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
use App\Http\Controllers\Freelancer\CashFlowController as FreelancerCashFlowController;
use App\Models\Booking;
use App\Models\Portfolio;
use Carbon\Carbon;

// public route untuk halaman utama (welcome) dan halaman detail momen
Route::get('/', function () {
    $today = Carbon::today()->toDateString();

    $bookings = Booking::with('package', 'user')
        ->where(function($query) use ($today) {
            $query->whereDate('booking_date', '>=', $today)
                  ->orWhereDate('prewed_date', '>=', $today);
        })
        ->whereIn('status', ['dp_paid', 'paid_in_full', 'completed']) 
        ->get();

    $scheduleList = collect();

    foreach ($bookings as $b) {
        
        // Cek dan masukkan Acara Utama (Main Event)
        if ($b->booking_date >= $today) {
            $mainEvent = clone $b;
            $mainEvent->display_date = $b->booking_date;
            $mainEvent->display_start = $b->start_time;
            $mainEvent->display_end = $b->end_time;
            $mainEvent->display_location = $b->event_location ?? 'TBA';
            $mainEvent->event_label = 'MAIN EVENT'; 
            
            $scheduleList->push($mainEvent);
        }

        // Cek dan masukkan Acara Prewedding (Jika ada)
        if ($b->prewed_date && $b->prewed_date >= $today) {
            $prewedEvent = clone $b;
            $prewedEvent->display_date = $b->prewed_date;
            $prewedEvent->display_start = $b->prewed_start_time;
            $prewedEvent->display_end = $b->prewed_end_time;
            $prewedEvent->display_location = $b->event_location_2 ?? ($b->event_location_3 ?? 'TBA');
            $prewedEvent->event_label = 'PREWEDDING SESSION'; 
            
            $scheduleList->push($prewedEvent);
        }
    }

    // Urutkan gabungan jadwal tersebut dari yang terdekat, 4 data
    $schedules = $scheduleList->sortBy('display_date')->take(4)->values();

    $moments = Portfolio::latest()->take(10)->get();
    
    return view('welcome', compact('schedules', 'moments'));
})->name('home');

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

// Webhook Midtrans
Route::post('/api/midtrans/callback', [CheckoutController::class, 'callback']);

// Calender Everlast
Route::get('/calendar', [CalendarController::class, 'index'])->name('admin.calendar');
Route::get('/calendar/events', [CalendarController::class, 'getEvents'])->name('admin.calendar.events');
Route::get('/customer/calendar-events', [CalendarController::class, 'getAvailableDates'])->name('customer.calendar.events');

// authenticated route untuk semua user yang sudah login (admin, freelancer, customer)
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
        
        $bookings = Booking::with('package')
                    ->where('user_id', auth()->id())
                    ->latest() 
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
    Route::post('payments/{id}/reject', [BookingController::class, 'rejectPayment'])->name('payments.reject');
    Route::get('/booking/{id}/cancel', [CustomerBookingController::class, 'cancelForm'])->name('customer.bookings.cancel');
    Route::post('/booking/{id}/cancel', [CustomerBookingController::class, 'cancelStore'])->name('customer.bookings.cancel.store');
    Route::get('/booking/available-slots', [CustomerBookingController::class, 'availableSlots'])->name('customer.booking.available-slots');

    // Route list packages
    Route::get('/packages', [CustomerBookingController::class, 'packages'])->name('customer.packages');
});

// freelance area
Route::prefix('freelance')
    ->name('freelancer.')
    ->middleware(['auth', 'role:freelancer'])
    ->group(function () {
        
        Route::get('/moments', [MomentController::class, 'index'])->name('moments.index');
        Route::get('/moments/create', [MomentController::class, 'create'])->name('moments.create');
        Route::post('/moments', [MomentController::class, 'store'])->name('moments.store');
        Route::delete('/moments/{portfolio}', [MomentController::class, 'destroy'])->name('moments.destroy');
        Route::get('/moments/{portfolio}/edit', [MomentController::class, 'edit'])->name('moments.edit');
        Route::put('/moments/{portfolio}', [MomentController::class, 'update'])->name('moments.update');
        Route::get('/schedules', [AssignmentController::class, 'index'])->name('schedules.index');
        Route::patch('/schedules/{assignment}/status', [AssignmentController::class, 'updateStatus'])->name('schedules.status');
        Route::get('/cash-flows', [FreelancerCashFlowController::class, 'index'])->name('cash_flows.index');
        Route::post('/cash-flows', [FreelancerCashFlowController::class, 'store'])->name('cash_flows.store');
        Route::delete('/cash-flows/{cashFlow}', [FreelancerCashFlowController::class, 'destroy'])->name('cash_flows.destroy');
        
});

// admin area
Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'role:admin'])
    ->group(function () {
        
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        
        Route::resource('packages', PackageController::class);
        Route::resource('bookings', BookingController::class);
        Route::resource('users', UserController::class);
        Route::post('/admin/bookings/{booking}/assign', [BookingController::class, 'assignFreelancer'])->name('bookings.assign');
        Route::get('/admin/assignments/{assignment}/edit', [BookingController::class, 'editAssignment'])->name('assignments.edit');
        Route::put('/admin/assignments/{assignment}', [BookingController::class, 'updateAssignment'])->name('assignments.update');
        Route::delete('/admin/assignments/{assignment}', [BookingController::class, 'deleteAssignment'])->name('assignments.destroy');
        Route::post('payments/{id}/reject', [BookingController::class, 'rejectPayment'])->name('payments.reject');
        
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
        Route::get('/finance/pdf', [FinancialReportController::class, 'exportPdf'])->name('finance.pdf');
        Route::get('/admin/finance/excel', [FinancialReportController::class, 'exportExcel'])->name('finance.excel');

        // Cash Flow Routes
        Route::get('/cash-flows', [CashFlowController::class, 'index'])->name('cash_flows.index');
        Route::post('/cash-flows', [CashFlowController::class, 'store'])->name('cash_flows.store');
        Route::delete('/cash-flows/{cashFlow}', [CashFlowController::class, 'destroy'])->name('cash_flows.destroy');
        Route::get('/cash-flows/export-pdf', [CashFlowController::class, 'exportPdf'])->name('cash_flows.export_pdf');

        //Send Delivery Result
        Route::get('bookings/{id}/delivery', [BookingController::class, 'showDelivery'])->name('bookings.delivery');
        Route::post('bookings/{id}/delivery', [BookingController::class, 'sendDelivery'])->name('bookings.delivery.store');

        //Mark refund
        Route::post('/bookings/{id}/mark-refunded', [BookingController::class, 'markRefunded'])->name('bookings.mark_refunded');
        Route::post('/bookings/{id}/confirm-cancel', [BookingController::class, 'confirmCancel'])->name('bookings.confirm_cancel');
        Route::post('/bookings/{id}/reject-cancel', [BookingController::class, 'rejectCancel'])->name('bookings.reject_cancel');
});

require __DIR__.'/auth.php';