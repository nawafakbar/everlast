@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] flex items-center justify-center py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white p-10 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)]">
        
        <div class="text-center mb-10">
            <h2 class="font-script text-5xl text-gray-900 mb-2">New Password</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Secure your account</p>
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

        <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
            @csrf
            
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <div>
                <input type="email" name="email" value="{{ old('email', $request->email) }}" required readonly 
                       class="w-full bg-gray-50 border-0 border-b border-gray-200 px-3 py-3 text-sm text-gray-500 focus:ring-0 outline-none cursor-not-allowed" title="Email tidak dapat diubah">
            </div>

            <div>
                <input type="password" name="password" required autofocus placeholder="New Password (Min. 8, Uppercase, Number, Symbol)" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <div>
                <input type="password" name="password_confirmation" required placeholder="Confirm New Password" 
                       class="w-full bg-transparent border-0 border-b border-gray-300 px-0 py-3 text-sm text-gray-900 focus:ring-0 focus:border-black transition-colors placeholder-gray-400 font-serif-custom italic outline-none">
            </div>

            <button type="submit" class="w-full mt-8 bg-black text-white text-[10px] font-bold tracking-[0.3em] uppercase py-4 hover:bg-gray-800 transition-colors">
                Reset Password
            </button>
        </form>

    </div>
</div>
@endsection