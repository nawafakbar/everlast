<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Assignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'user_id',
        'task',
        'fee',
        'status',
        'event_type',
    ];

    // Relasi balik ke Booking
    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    // Relasi balik ke Freelancer (User)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}