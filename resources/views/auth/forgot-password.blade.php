@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] flex items-center justify-center py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)]">
        
        <div class="text-center mb-8">
            <h2 class="font-script text-5xl text-gray-900 mb-2">Recover Account</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Reset your password</p>
        </div>

        <div class="mb-8 text-sm text-gray-500 font-serif-custom italic text-center leading-relaxed">
            Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-green-50 text-green-600 text-xs text-center border border-green-100">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 text-red-600 text-xs text-center border border-red-100">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
            @csrf
            
            <div>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus placeholder="Email Address" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <button type="submit" class="w-full mt-8 bg-black text-white text-[10px] font-bold tracking-[0.3em] uppercase py-4 hover:bg-gray-800 transition-colors">
                Email Password Reset Link
            </button>
        </form>

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="text-[10px] uppercase tracking-widest font-bold text-gray-400 hover:text-black transition-colors border-b border-transparent hover:border-black pb-1">
                <i class="fas fa-long-arrow-alt-left mr-2"></i> Back to Login
            </a>
        </div>
    </div>
</div>
@endsection