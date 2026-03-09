@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] flex items-center justify-center py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] text-center">
        
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-gray-50 border border-gray-100 mb-6">
            <i class="far fa-envelope-open text-2xl text-black"></i>
        </div>

        <h2 class="font-script text-4xl text-gray-900 mb-4">Check Your Email</h2>
        
        <div class="font-serif-custom text-gray-600 text-sm leading-relaxed mb-8">
            <p>Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?</p>
            <p class="mt-2 text-xs italic text-gray-400">If you didn't receive the email, we will gladly send you another.</p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-8 font-medium text-xs text-green-600 bg-green-50 p-3 border border-green-100">
                A new verification link has been sent to the email address you provided during registration.
            </div>
        @endif

        <div class="flex flex-col space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="w-full bg-black text-white text-[10px] font-bold tracking-[0.3em] uppercase py-4 hover:bg-gray-800 transition-colors">
                    Resend Verification Email
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-[10px] uppercase tracking-widest font-bold text-gray-400 hover:text-black transition-colors underline pb-1">
                    Log Out
                </button>
            </form>
        </div>

    </div>
</div>
@endsection