<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Package extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'category',
        'price',
        'description',
        'duration_hours',
        'total_locations',
        'thumbnail_path',
    ];
}
