@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] flex items-center justify-center py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)]">
        
        <div class="text-center mb-10">
            <h2 class="font-script text-5xl text-gray-900 mb-2">Create Account</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Join Everlast Project</p>
        </div>

        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 text-red-600 text-xs text-left border border-red-100">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf
            
            <div>
                <input type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Full Name" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <div>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email Address" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <div>
                <input type="password" name="password" required placeholder="Password (Min. 8, Uppercase, Number, Symbol)" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <div>
                <input type="password" name="password_confirmation" required placeholder="Confirm Password" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <button type="submit" class="w-full mt-8 bg-black text-white text-[10px] font-bold tracking-[0.3em] uppercase py-4 hover:bg-gray-800 transition-colors">
                Register Now
            </button>
        </form>

        <div class="mt-8 mb-6 flex items-center">
            <div class="flex-grow h-px bg-gray-200"></div>
            <span class="px-4 text-[9px] font-bold tracking-widest uppercase text-gray-400">Or sign up with</span>
            <div class="flex-grow h-px bg-gray-200"></div>
        </div>

        <a href="{{ route('google.login') }}" class="w-full flex justify-center items-center border border-gray-200 py-3 hover:bg-gray-50 transition-colors group">
            <svg class="w-4 h-4 mr-3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 15.02 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            <span class="text-[10px] font-bold tracking-[0.2em] uppercase text-gray-600 group-hover:text-black">Google</span>
        </a>

        <div class="mt-10 text-center">
            <p class="text-xs text-gray-500 font-serif-custom italic">Already have an account? 
                <a href="{{ route('login') }}" class="font-sans-custom font-bold text-[10px] tracking-widest uppercase text-black ml-1 border-b border-black pb-1 hover:text-gray-600">Sign In</a>
            </p>
        </div>
    </div>
</div>
@endsection