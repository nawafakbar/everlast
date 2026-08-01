<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id', 
        'payment_method', 
        'midtrans_transaction_id', 
        'snap_token',     
        'amount', 
        'payment_type', 
        'status', 
        'proof_image',    
        'notes'           
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}