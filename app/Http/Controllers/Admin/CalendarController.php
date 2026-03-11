<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        return view('admin.calendar');
    }

    public function getEvents()
    {
        // Ambil booking yang statusnya VALID (sudah bayar DP atau Lunas)
        $bookings = Booking::with('user')->whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])->get();

        // Kelompokkan data berdasarkan tanggal acara
        $grouped = $bookings->groupBy('booking_date');

        $events = [];

        foreach ($grouped as $date => $dayBookings) {
            $maxEndTime = '00:00:00';
            $clientNames = [];

            // Cari jam selesai paling akhir di hari tersebut
            foreach ($dayBookings as $b) {
                if ($b->end_time > $maxEndTime) {
                    $maxEndTime = $b->end_time;
                }
                $clientNames[] = $b->user->name . ' (' . Carbon::parse($b->start_time)->format('H:i') . '-' . Carbon::parse($b->end_time)->format('H:i') . ')';
            }

            // LOGIKA FULL BOOK: Jika ada jadwal selesai jam 16:00 atau lebih
            $isFull = $maxEndTime >= '16:00:00';

            $events[] = [
                'title' => $isFull ? 'Full Booked' : 'Available',
                'start' => $date,
                // Tema Monokrom: Hitam pekat untuk Full, Abu-abu untuk Tersedia
                'backgroundColor' => $isFull ? '#000000' : '#9CA3AF', 
                'borderColor' => $isFull ? '#000000' : '#9CA3AF',
                'textColor' => '#ffffff',
                'description' => implode(" | ", $clientNames), // Muncul pas kursor disorot
                'allDay' => true
            ];
        }

        return response()->json($events);
    }

    // Tambahkan di dalam CustomerBookingController
    public function getAvailableDates()
    {
        // Khusus klien, nggak perlu narik relasi 'user'
        $bookings = Booking::whereIn('status', ['dp_paid', 'paid_in_full', 'completed'])->get();
        $grouped = $bookings->groupBy('booking_date');
        $events = [];

        foreach ($grouped as $date => $dayBookings) {
            $maxEndTime = '00:00:00';
            $bookedTimes = []; 

            foreach ($dayBookings as $b) {
                if ($b->end_time > $maxEndTime) {
                    $maxEndTime = $b->end_time;
                }
                $bookedTimes[] = \Carbon\Carbon::parse($b->start_time)->format('H:i') . ' - ' . \Carbon\Carbon::parse($b->end_time)->format('H:i') . ' WIB';
            }

            $isFull = $maxEndTime >= '16:00:00';

            $events[] = [
                'title' => $isFull ? 'Full Booked' : 'Available',
                'start' => $date, // Wajib ada biar muncul di kalender!
                'backgroundColor' => $isFull ? '#000000' : '#9CA3AF', 
                'borderColor' => $isFull ? '#000000' : '#9CA3AF',
                'textColor' => '#ffffff',
                'description' => 'Sesi Terisi: ' . implode(", ", $bookedTimes), 
                'allDay' => true
            ];
        }

        return response()->json($events);
    }
}