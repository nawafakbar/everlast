@extends('layouts.customer')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Batalkan Booking</h2>
        <p class="text-xs text-gray-500">Mohon isi data berikut dengan lengkap agar proses pengembalian dana berjalan lancar.</p>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-sm text-xs">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white p-6 rounded-md shadow-sm border border-gray-100 mb-6">
        <p class="text-[10px] uppercase tracking-widest text-gray-400 font-bold mb-1">Booking</p>
        <p class="text-md font-bold text-gray-900">{{ $booking->package->name ?? 'Paket Kustom' }} — {{ $booking->partner_name }}</p>
        <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</p>
    </div>

    <form action="{{ route('customer.bookings.cancel.store', $booking->id) }}" method="POST" class="bg-white p-6 rounded-md shadow-sm border border-gray-100 space-y-5">
        @csrf

        <div>
            <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">Alasan Pembatalan <span class="text-red-500">*</span></label>
            <textarea name="reason" rows="3" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black">{{ old('reason') }}</textarea>
        </div>

        <div class="pt-4 border-t border-gray-100">
            <p class="text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-3">Data Rekening Pengembalian Dana</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase tracking-wider mb-1">Nama Bank <span class="text-red-500">*</span></label>
                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA / Mandiri / BRI, dst" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase tracking-wider mb-1">Nomor Rekening <span class="text-red-500">*</span></label>
                    <input type="text" name="account_number" value="{{ old('account_number') }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-[10px] font-medium text-gray-600 uppercase tracking-wider mb-1">Atas Nama <span class="text-red-500">*</span></label>
                    <input type="text" name="account_holder" value="{{ old('account_holder') }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm text-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black">
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3 pt-4 border-t border-gray-100">
            <a href="{{ route('customer.pesanan') }}" class="border border-gray-300 text-gray-700 px-6 py-2.5 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-gray-50 transition-colors">
                Batal
            </a>
            <button type="submit"
                onclick="return confirm('Yakin ingin membatalkan booking ini? Tindakan ini tidak bisa dibatalkan.');"
                class="bg-red-500 text-white px-6 py-2.5 rounded-sm text-xs font-bold uppercase tracking-widest hover:bg-red-600 transition-colors">
                Konfirmasi Pembatalan
            </button>
        </div>
    </form>
</div>
@endsection