<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Tampilkan form review untuk satu booking.
     */
    public function create(string $id)
    {
        $booking = Booking::with(['package', 'review'])->findOrFail($id);

        // 1. Pastikan booking ini punya user yang sedang login
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        // 2. Hanya booking yang sudah selesai yang boleh direview
        if ($booking->status !== 'completed') {
            return redirect()->route('customer.pesanan')
                ->with('error', 'You can only review a booking after it has been completed.');
        }

        // 3. Cegah review dobel
        if ($booking->review) {
            return redirect()->route('customer.pesanan')
                ->with('error', 'You have already reviewed this booking.');
        }

        return view('customer.reviews.create', compact('booking'));
    }

    /**
     * Simpan review.
     */
    public function store(Request $request, string $id)
    {
        $booking = Booking::with('review')->findOrFail($id);

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if ($booking->status !== 'completed' || $booking->review) {
            return redirect()->route('customer.pesanan')
                ->with('error', 'This booking cannot be reviewed.');
        }

        $validated = $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'required|string|min:10|max:1000',
        ], [
            'rating.required'  => 'Please select a star rating.',
            'comment.required' => 'Please write your review.',
            'comment.min'      => 'Your review must be at least 10 characters.',
        ]);

        Review::create([
            'booking_id' => $booking->id,
            'user_id'    => auth()->id(),
            'package_id' => $booking->package_id,
            'rating'     => $validated['rating'],
            'comment'    => $validated['comment'],
        ]);

        return redirect()->route('customer.pesanan')
            ->with('success', 'Thank you! Your review has been submitted.');
    }
}