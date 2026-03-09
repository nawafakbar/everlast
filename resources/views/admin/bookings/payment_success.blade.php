@extends('layouts.admin')

@section('content')
<div class="max-w-2xl mx-auto py-12 px-4 sm:px-6 lg:px-8 text-center mt-10">
    <div class="bg-white border border-gray-200 p-10 rounded-sm shadow-sm">
        
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6">
            <i class="fas fa-check text-4xl text-green-600"></i>
        </div>
        
        <h2 class="text-3xl font-extrabold text-gray-900 tracking-tight mb-2">Payment Successful!</h2>
        <p class="text-sm text-gray-500 mb-8">Terima kasih, simulasi pembayaran untuk booking <strong>{{ $booking->user->name }}</strong> telah berhasil dilakukan melalui Midtrans.</p>

        <div class="bg-gray-50 border border-gray-200 rounded-sm p-6 mb-8 text-left">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-200 pb-2 mb-4">Transaction Details</h3>
            
            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Order ID</span>
                    <span class="font-medium text-gray-900">{{ $payment->midtrans_transaction_id ?? '-' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Payment Type</span>
                    <span class="font-medium text-gray-900 uppercase">{{ $payment->payment_type == 'dp' ? 'Down Payment (50%)' : 'Lunas (100%)' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Amount Paid</span>
                    <span class="font-bold text-gray-900">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between items-center pt-3 border-t border-gray-200 mt-2">
                    <span class="text-gray-500">Database Status</span>
                    <span class="px-3 py-1 bg-green-100 text-green-700 text-[10px] font-bold uppercase tracking-wider rounded-sm">
                        {{ $payment->status }}
                    </span>
                </div>
            </div>
        </div>

        <a href="{{ route('admin.bookings.index') }}" class="inline-flex items-center justify-center w-full sm:w-auto px-8 py-3 border border-transparent text-xs font-bold rounded-sm text-white bg-black hover:bg-gray-800 uppercase tracking-wider transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Bookings
        </a>
    </div>
</div>
@endsection