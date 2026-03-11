<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminPaymentNotificationMail extends Mailable
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
        // Pake emoji dikit biar Admin ngeh kalau ini email duit masuk
        return new Envelope(
            subject: '💰 Pembayaran Diterima: ' . $this->booking->user->name . ' (#EVL-' . $this->booking->id . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.admin_payment',
        );
    }
}