<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminManualPaymentMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $payment;

    public function __construct($booking, $payment)
    {
        $this->booking = $booking;
        $this->payment = $payment;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '⚠️ Cek Bukti Transfer: ' . $this->booking->user->name . ' (#EVL-' . $this->booking->id . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_manual_payment',
        );
    }
}