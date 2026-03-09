<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Bookings (Selain yang di-cancel)
        $totalBookings = Booking::where('status', '!=', 'cancelled')->count();

        // 2. Total Revenue (Hanya dari status Lunas/Completed)
        // Kita loop dari relasi package untuk menjumlahkan harganya
        $revenue = Booking::whereIn('status', ['paid_in_full', 'completed'])
            ->with('package')->get()
            ->sum(function($booking) {
                return $booking->package->price ?? 0;
            });

        // 3. Total Clients (User yang rolenya customer)
        $totalClients = User::where('role', 'customer')->count();

        // 4. Booking Terbaru (5 data terakhir untuk tabel mini di dashboard)
        $recentBookings = Booking::with(['user', 'package'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 5. Acara Mendatang (5 acara terdekat dari hari ini)
        $upcomingEvents = Booking::with(['user', 'package'])
            ->where('booking_date', '>=', Carbon::today())
            ->whereNotIn('status', ['cancelled'])
            ->orderBy('booking_date', 'asc')
            ->take(5)
            ->get();

        return view('admin.dashboard', compact(
            'totalBookings', 'revenue', 'totalClients', 'recentBookings', 'upcomingEvents'
        ));
    }
}