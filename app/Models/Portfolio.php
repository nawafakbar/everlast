<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Portfolio extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'cover_image',
        'category',
        'event_date',
        'title',
        'client_name',
        'quote',
        'gallery_links',
    ];

    protected $casts = [
        'event_date' => 'date',
        'gallery_links' => 'array', // Otomatis ubah JSON ke Array
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}