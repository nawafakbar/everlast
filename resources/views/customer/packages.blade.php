@extends('layouts.customer')

@section('content')
<div class="max-w-6xl mx-auto">

    <div class="mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 mb-1">Our Packages</h2>
        <p class="text-xs text-gray-500">Pilih paket yang sesuai untuk momen spesial Anda.</p>
    </div>

    @if($packages->isEmpty())
        <div class="bg-white p-12 rounded-md shadow-sm border border-gray-100 flex flex-col items-center justify-center text-center">
            <div class="w-20 h-20 bg-gray-50 rounded-full flex items-center justify-center mb-4">
                <i class="fas fa-box-open text-3xl text-gray-300"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Belum ada paket tersedia</h3>
            <p class="text-xs text-gray-500 max-w-md">Silakan cek kembali nanti.</p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach($packages as $package)
                <button onclick="openModal('package-modal-{{ $package->id }}')"
                    class="text-left bg-white rounded-md shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow group">

                    <div class="h-44 sm:h-48 w-full bg-gray-100 overflow-hidden">
                        @if($package->thumbnail_path)
                            <img src="{{ asset('storage/' . $package->thumbnail_path) }}" alt="{{ $package->name }}"
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-300">
                                <i class="fas fa-image text-3xl"></i>
                            </div>
                        @endif
                    </div>

                    <div class="p-5">
                        <span class="inline-block px-2 py-1 text-[9px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 rounded-sm mb-2">
                            {{ $package->category }}
                        </span>
                        <h3 class="text-md font-bold text-gray-900 mb-1 leading-snug">{{ $package->name }}</h3>
                        <p class="text-sm font-bold text-[#C9A66B]">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                    </div>
                </button>
            @endforeach
        </div>
    @endif

</div>

{{-- MODAL DETAIL PAKET --}}
@foreach($packages as $package)
<div id="package-modal-{{ $package->id }}" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 backdrop-blur-sm transition-opacity" onclick="closeModal('package-modal-{{ $package->id }}')"></div>

    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-lg w-full pointer-events-auto border border-gray-100">

            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-lg leading-6 font-bold text-gray-900">Detail Paket</h3>
                <button onclick="closeModal('package-modal-{{ $package->id }}')" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="max-h-[75vh] overflow-y-auto">
                <div class="h-52 sm:h-56 w-full bg-gray-100">
                    @if($package->thumbnail_path)
                        <img src="{{ asset('storage/' . $package->thumbnail_path) }}" alt="{{ $package->name }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-gray-300">
                            <i class="fas fa-image text-4xl"></i>
                        </div>
                    @endif
                </div>

                <div class="px-6 py-6 space-y-5">
                    <div>
                        <span class="inline-block px-2 py-1 text-[9px] font-bold uppercase tracking-wider bg-gray-100 text-gray-600 rounded-sm mb-2">
                            {{ $package->category }}
                        </span>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">{{ $package->name }}</h3>
                        <p class="text-lg font-bold text-[#C9A66B]">Rp {{ number_format($package->price, 0, ',', '.') }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 bg-[#FDFBF7] border border-[#EBE6DD] rounded-sm p-4">
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Durasi</p>
                            <p class="text-sm font-semibold text-gray-800">
                                <i class="far fa-clock text-gray-400 mr-1"></i> {{ $package->duration_hours }} Jam
                            </p>
                        </div>
                        <div>
                            <p class="text-[9px] font-bold text-gray-400 uppercase tracking-wider mb-1">Lokasi</p>
                            <p class="text-sm font-semibold text-gray-800">
                                <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> {{ $package->total_locations }} Lokasi
                            </p>
                        </div>
                    </div>

                    <div>
                        <p class="text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Deskripsi</p>
                        <p class="text-sm text-gray-600 leading-relaxed whitespace-pre-line">{{ $package->description }}</p>
                    </div>

                    {{-- REVIEWS --}}
                    <div>
                        <p class="text-[10px] font-bold text-gray-900 uppercase tracking-wider mb-2 border-b border-gray-100 pb-2">Ulasan</p>

                        @forelse($package->reviews as $review)
                            <div class="border-b border-gray-100 py-5 last:border-0">
                                <div class="flex items-center justify-between mb-2">
                                    <div>
                                        <p class="text-xs font-bold text-gray-900">{{ $review->user->name }}</p>
                                        <p class="text-[9px] text-gray-400 uppercase tracking-wider mt-0.5">{{ $review->created_at->format('d F Y') }}</p>
                                    </div>
                                    <div class="flex text-[#C9A66B] text-xs">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="fas fa-star {{ $i <= $review->rating ? '' : 'text-gray-300' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-xs text-gray-600 leading-relaxed">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <p class="text-xs text-gray-400 italic text-center py-8">No reviews yet for this package.</p>
                        @endforelse
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex flex-col sm:flex-row justify-end gap-3">
                <button onclick="closeModal('package-modal-{{ $package->id }}')" class="border border-gray-300 text-gray-700 px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-100 transition-colors text-center">
                    Close
                </button>
                <a href="{{ route('customer.booking', ['package_id' => $package->id]) }}" class="bg-black text-white px-6 py-2 rounded-sm text-xs font-bold tracking-widest uppercase hover:bg-gray-800 transition-colors shadow-md text-center">
                    Book This Package
                </a>
            </div>

        </div>
    </div>
</div>
@endforeach

<script>
    function openModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
    }

    function closeModal(modalID) {
        const modal = document.getElementById(modalID);
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection