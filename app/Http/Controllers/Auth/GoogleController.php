<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            
            // Cek apakah user dengan ID Google ini sudah ada
            $findUser = User::where('google_id', $googleUser->id)->first();
            
            if ($findUser) {
                Auth::login($findUser);
                return redirect()->intended('dashboard');
            } else {
                // Cek apakah emailnya sudah pernah daftar manual
                $existingUser = User::where('email', $googleUser->email)->first();
                
                if($existingUser) {
                    // Kalau ada, update data user tersebut dengan ID Google
                    $existingUser->update([
                        'google_id' => $googleUser->id,
                    ]);
                    Auth::login($existingUser);
                } else {
                    // Kalau belum ada sama sekali, bikin user baru
                    $newUser = User::create([
                        'name' => $googleUser->name,
                        'email' => $googleUser->email,
                        'google_id' => $googleUser->id,
                        'password' => null, // Kosongkan karena login via Google
                        'email_verified_at' => now(), // Otomatis terverifikasi
                    ]);
                    Auth::login($newUser);
                }
                return redirect()->intended('dashboard');
            }
        } catch (Exception $e) {
            return redirect('/login')->withErrors(['email' => 'Gagal login pakai Google. Silakan coba lagi.']);
        }
    }
}