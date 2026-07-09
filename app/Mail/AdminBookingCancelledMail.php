<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminBookingCancelledMail extends Mailable
{
    use Queueable, SerializesModels;

    public Booking $booking;
    public string $reason;
    public float $totalPaid;
    public string $bankName;
    public string $accountNumber;
    public string $accountHolder;
    public ?string $proofUrl;

    public function __construct(Booking $booking, string $reason, float $totalPaid, string $bankName, string $accountNumber, string $accountHolder, ?string $proofUrl)
    {
        $this->booking = $booking;
        $this->reason = $reason;
        $this->totalPaid = $totalPaid;
        $this->bankName = $bankName;
        $this->accountNumber = $accountNumber;
        $this->accountHolder = $accountHolder;
        $this->proofUrl = $proofUrl;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Client Membatalkan Booking - Everlast',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin-booking-cancelled',
        );
    }
}