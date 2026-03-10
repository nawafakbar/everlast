<?php
namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail; // WAJIB DI-IMPORT
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

// WAJIB IMPLEMENTS MustVerifyEmail
class User extends Authenticatable implements MustVerifyEmail 
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'google_id', // Tambahkan ini
        'role',      // Tambahkan ini
        'email_verified_at',
        'phone',
        'avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}