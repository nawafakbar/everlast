@extends('layouts.customer')

@section('content')
<div class="max-w-5xl relative">
    
    <div class="mb-8 border-b border-gray-200 pb-4 flex flex-col sm:flex-row justify-between items-start sm:items-end gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 mb-1">Daftar Pesanan</h2>
            <p class="text-xs text-gray-500">Pantau status booking, jadwal foto, dan riwayat pesanan Anda di sini.</p>
        </div>
        <a href="{{ route('customer.booking') }}" class="bg-black text-white px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition-colors whitespace-nowrap">
            + Booking Baru
        </a>
    </div>

    @if($bookings->isEmpty())
        <div class="bg-white p-12 rounded-md shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-box-open text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Belum Ada Pesanan</h3>
            <p class="text-xs text-gray-500 mb-6 max-w-md">Anda belum melakukan booking layanan apapun. Yuk, abadikan momen spesialmu bersama Everlast Project!</p>
            <a href="/#packages" class="border border-gray-300 text-gray-900 px-6 py-3 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-50 transition-colors">
                Lihat Paket Kami
            </a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($bookings as $booking)
                @php
                    $statusClass = match(strtolower($booking->status)) {
                        'pending' => 'bg-yellow-50 text-yellow-700 border border-yellow-200',
                        'paid', 'dp_paid' => 'bg-green-50 text-green-700 border border-green-200',
                        'completed' => 'bg-blue-50 text-blue-700 border border-blue-200',
                        'cancelled' => 'bg-red-50 text-red-700 border border-red-200',
                        default => 'bg-gray-50 text-gray-700 border border-gray-200'
                    };
                @endphp

                <div class="bg-white p-6 rounded-md shadow-sm border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-6 hover:shadow-md transition-shadow">
                    
                    <div class="flex-1">
                        <div class="flex items-center gap-3 mb-3">
                            <h3 class="text-lg font-bold text-gray-900">{{ $booking->package->name ?? 'Paket Kustom' }}</h3>
                            <span class="px-3 py-1 text-[9px] font-bold tracking-wider uppercase rounded-full {{ $statusClass }}">
                                {{ $booking->status }}
                            </span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-y-2 gap-x-6 text-xs text-gray-500">
                            <p class="flex items-center"><i class="far fa-calendar-alt w-5 text-gray-400"></i> 
                                {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}
                            </p>
                            <p class="flex items-center"><i class="far fa-clock w-5 text-gray-400"></i> 
                                {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WIB
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-col items-start md:items-end gap-3 w-full md:w-auto pt-4 md:pt-0 border-t border-gray-100 md:border-t-0">
                        <button onclick="openModal('modal-{{ $booking->id }}')" class="w-full md:w-auto text-center border border-gray-300 text-gray-900 px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-50 transition-colors whitespace-nowrap mt-2">
                            Detail Pesanan
                        </button>
                    </div>
                </div>

                <div id="modal-{{ $booking->id }}" class="fixed inset-0 z-50 hidden">
                    <div class="absolute inset-0 backdrop-blur-sm transition-opacity" onclick="closeModal('modal-{{ $booking->id }}')"></div>
                    
                    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
                        <div class="relative bg-white rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-2xl w-full pointer-events-auto border border-gray-100">
                            
                            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                                <h3 class="text-lg leading-6 font-bold text-gray-900">
                                    Detail Booking <span class="text-gray-400 font-normal text-sm ml-2">#EVL-{{ $booking->id }}</span>
                                </h3>
                                <button onclick="closeModal('modal-{{ $booking->id }}')" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                                    <i class="fas fa-times text-xl"></i>
                                </button>
                            </div>

                            <div class="px-6 py-6 space-y-6">
                                
                                <div class="flex justify-between items-center bg-[#FDFBF7] p-4 rounded-sm border border-[#EBE6DD]">
                                    <div>
                                        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1">Paket Pilihan</p>
                                        <p class="text-md font-bold text-gray-900">{{ $booking->package->name ?? 'Paket Kustom' }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase tracking-widest text-gray-500 font-bold mb-1">Status</p>
                                        <span class="px-3 py-1 text-[9px] font-bold tracking-wider uppercase rounded-full {{ $statusClass }}">
                                            {{ $booking->status }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <h4 class="text-[10px] font-bold tracking-[0.2em] uppercase text-gray-900 mb-3 border-b border-gray-100 pb-2">Informasi Pasangan</h4>
                                        <div class="space-y-3 text-sm text-gray-600">
                                            <p><strong class="font-semibold text-gray-800">Nama:</strong> {{ Auth::user()->name }} & {{ $booking->partner_name }}</p>
                                            <p><strong class="font-semibold text-gray-800">Alamat:</strong> {{ $booking->couple_address ?? '-' }}</p>
                                        </div>
                                    </div>

                                    <div>
                                        <h4 class="text-[10px] font-bold tracking-[0.2em] uppercase text-gray-900 mb-3 border-b border-gray-100 pb-2">Jadwal & Lokasi</h4>
                                        <div class="space-y-3 text-sm text-gray-600">
                                            <p><strong class="font-semibold text-gray-800">Tanggal:</strong> {{ \Carbon\Carbon::parse($booking->booking_date)->translatedFormat('d F Y') }}</p>
                                            <p><strong class="font-semibold text-gray-800">Waktu:</strong> {{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }} WIB</p>
                                            <p><strong class="font-semibold text-gray-800">Lokasi Utama:</strong> {{ $booking->event_location ?? '-' }}</p>
                                            @if($booking->event_location_2)
                                                <p><strong class="font-semibold text-gray-800">Lokasi Kedua:</strong> {{ $booking->event_location_2 }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
                                <button onclick="closeModal('modal-{{ $booking->id }}')" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-100 transition-colors text-center">
                                    Tutup
                                </button>
                                
                                @if(in_array(strtolower($booking->status), ['pending', 'dp_paid']))
                                    <a href="{{ route('customer.checkout', $booking->id) }}" class="bg-black text-white px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition-colors shadow-md text-center">
                                        Lanjutkan Pembayaran
                                    </a>
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
                @endforeach
        </div>
    @endif

</div>

<script>
    function openModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); // Cegah background scroll
    }

    function closeModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden'); // Kembalikan scroll
    }
</script>
@endsection