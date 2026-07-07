@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Kirim Karya ke Client</h2>
            <p class="text-gray-500 text-xs mt-1">Kirim link Google Drive hasil foto/video kepada client via WhatsApp.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="text-gray-500 hover:text-black transition-colors text-xs font-medium uppercase tracking-wider">
            <i class="fas fa-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    @if ($errors->any())
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-sm text-xs font-bold uppercase tracking-wider max-w-2xl">
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 p-4 mb-6 rounded-sm text-xs font-bold uppercase tracking-wider max-w-2xl">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white border border-gray-200 rounded-sm shadow-sm p-8 max-w-2xl">
        <div class="mb-6 pb-4 border-b border-gray-100">
            <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Client</p>
            <p class="text-sm font-semibold text-gray-900">{{ $booking->user->name }} & {{ $booking->partner_name }}</p>
            <p class="text-[10px] text-gray-400 mt-2 uppercase tracking-wider">Status saat ini:
                <span class="font-bold text-gray-600">{{ strtoupper(str_replace('_',' ',$booking->status)) }}</span>
            </p>
        </div>

        <form action="{{ route('admin.bookings.delivery.store', $booking->id) }}" method="POST">
            @csrf
            <div class="mb-6">
                <label class="block text-[10px] font-bold text-gray-700 uppercase tracking-wider mb-2">
                    Link Google Drive <span class="text-red-500">*</span>
                </label>
                <input type="url" name="delivery_link" value="{{ old('delivery_link') }}"
                    placeholder="https://drive.google.com/drive/folders/..."
                    required
                    class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-xs transition-colors">
                <p class="text-[9px] text-gray-400 mt-2 uppercase tracking-wide">
                    Pastikan akses Google Drive diset "Anyone with the link can view". Link ini tidak disimpan di sistem, hanya dipakai untuk pesan WhatsApp.
                </p>
            </div>

            <div class="flex justify-end gap-4 border-t border-gray-100 pt-6">
                <button type="submit" class="bg-black text-white px-8 py-3 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                    <i class="fab fa-whatsapp mr-1"></i> Tandai Selesai & Kirim
                </button>
            </div>
        </form>
    </div>
@endsection