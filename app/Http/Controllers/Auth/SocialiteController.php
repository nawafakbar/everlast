<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirectToGoogle() {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback() {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->getEmail())->first();
            
            if (!$user) {
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'role' => 'customer',
                    
                    // 1. OTOMATIS VERIFIKASI EMAIL
                    'email_verified_at' => now(), 
                    
                    // 2. PASSWORD ACAK (24 Karakter biar super aman)
                    'password' => Hash::make(\Illuminate\Support\Str::random(24)), 
                ]);
            } else {
                $user->update(['google_id' => $googleUser->getId()]);
            }
            
            // Login user-nya
            Auth::login($user);

            // ==========================================
            // LOGIKA REDIRECT BERDASARKAN ROLE
            // ==========================================
            if ($user->role === 'admin') {
                return redirect()->route('admin.dashboard');
            } elseif ($user->role === 'freelancer') {
                return redirect()->route('freelancer.schedules.index');
            } else {
                // Default ke Landing Page (buat customer)
                return redirect()->intended('/'); 
            }
            
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['Terjadi kesalahan saat login dengan Google.']);
            //dd($e->getMessage());
        }
    }
}