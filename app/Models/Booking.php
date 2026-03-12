<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Booking extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id', 'package_id', 'partner_name', 'couple_address',
        'couple_lat', 'couple_lng', 'event_location', 'event_lat', 'event_lng',
        'booking_date', 'start_time', 'end_time', 
        'prewed_date', 'prewed_start_time', 'prewed_end_time',
        'status', 'event_location_2', 'event_lat_2', 'event_lng_2', 
        'event_location_3', 'event_lat_3', 'event_lng_3',
        'google_calendar_id'
    ];

    // Relasi ke User (Pemesan)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Package
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    //Relasi ke Payment
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    //Relasi ke Assigment
    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}
