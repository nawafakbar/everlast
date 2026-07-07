<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Booking;
use App\Mail\BookingAutoCancelledMail;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class CancelExpiredBookings extends Command
{
    protected $signature = 'bookings:cancel-expired';
    protected $description = 'Otomatis cancel booking yang masih pending lebih dari 7 hari sejak dibuat';

    public function handle()
    {
        $expiredBookings = Booking::with('user')
            ->where('status', 'pending')
            ->where('created_at', '<=', Carbon::now()->subDays(7))
            ->get();

        if ($expiredBookings->isEmpty()) {
            $this->info('Tidak ada booking yang expired hari ini.');
            return;
        }

        foreach ($expiredBookings as $booking) {

            if ($booking->google_calendar_id) {
                try {
                    $event = \Spatie\GoogleCalendar\Event::find($booking->google_calendar_id);
                    $event->delete();
                } catch (\Exception $e) {
                    // Diamkan saja
                }
            }

            $booking->update([
                'status' => 'cancelled',
                'google_calendar_id' => null,
            ]);

            // Kirim email notifikasi ke client
            Mail::to($booking->user->email)->send(new BookingAutoCancelledMail($booking));

            $this->info("Booking #{$booking->id} ({$booking->user->name} & {$booking->partner_name}) otomatis dibatalkan & email terkirim.");
        }

        $this->info($expiredBookings->count() . ' booking berhasil di-cancel otomatis.');
    }
}