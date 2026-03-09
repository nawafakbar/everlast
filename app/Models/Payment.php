<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 
        'payment_method', // Baru
        'midtrans_transaction_id', 
        'snap_token',     // Baru
        'amount', 
        'payment_type', 
        'status', 
        'proof_image',    // Baru
        'notes'           // Baru
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}