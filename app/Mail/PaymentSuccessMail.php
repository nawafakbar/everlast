<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentSuccessMail extends Mailable
{
    use Queueable, SerializesModels;

    public $booking;
    public $type;
    public $amount;

    public function __construct($booking, $type, $amount)
    {
        $this->booking = $booking;
        $this->type = $type; // 'DP' atau 'Lunas'
        $this->amount = $amount;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Payment Received - Everlast Moments (#EVL-' . $this->booking->id . ')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment_success',
        );
    }
}