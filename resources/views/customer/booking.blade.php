@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<style>
    .leaflet-container { z-index: 10 !important; }
</style>

<div class="min-h-screen bg-[#FDFBF7] pt-28 pb-20 px-4 sm:px-6 lg:px-8 font-sans-custom">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-10">
            <h2 class="font-script text-5xl text-gray-900 mb-2">Book Your Session</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Secure Your Date with Everlast</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-6 mb-8 rounded-sm shadow-sm">
                <p class="text-xs font-bold mb-2 tracking-widest uppercase"><i class="fas fa-exclamation-triangle mr-2"></i> Periksa kembali isian Anda:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-8 md:p-12 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] rounded-sm">
            <form action="{{ route('customer.booking.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    
                    <div class="space-y-6">
                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-3 mb-6">Paket & Pasangan</h3>
                        
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Anda</label>
                            <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm text-sm text-gray-500 cursor-not-allowed italic">
                        </div>

                        <div>
                            <label for="partner_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Pasangan <span class="text-red-500">*</span></label>
                            <input type="text" name="partner_name" id="partner_name" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm" placeholder="Contoh: Jane Doe">
                        </div>

                        <div>
                            <label for="package_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Paket <span class="text-red-500">*</span></label>
                            <select name="package_id" id="package_id" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700 cursor-pointer">
                                <option value="" disabled selected>Pilih layanan...</option>
                                @foreach($packages as $package)
                                    <option value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="couple_address" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Alamat Rumah <span class="text-red-500">*</span></label>
                            <textarea name="couple_address" id="couple_address" rows="3" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Alamat lengkap untuk koordinasi tim..."></textarea>
                            
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 mt-4">Pin Lokasi Rumah</p>
                            <div id="coupleMap" class="h-48 w-full rounded-sm border border-gray-200 mb-1 z-10"></div>
                            <p class="text-[9px] text-gray-400 italic">Geser pin (marker) ke lokasi yang tepat.</p>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-3 mb-6">Detail Acara</h3>

                        <div>
                            <label for="booking_date" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Tanggal Acara <span class="text-red-500">*</span></label>
                            <input type="date" name="booking_date" id="booking_date" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700 cursor-pointer">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jam Mulai <span class="text-red-500">*</span></label>
                                <input type="time" name="start_time" id="start_time" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700 cursor-pointer">
                            </div>
                            <div>
                                <label for="end_time" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Jam Selesai</label>
                                <input type="time" name="end_time" id="end_time" required readonly class="w-full px-4 py-3 bg-gray-100 border border-gray-200 rounded-sm focus:outline-none text-sm text-gray-500 cursor-not-allowed" title="Jam selesai otomatis dihitung berdasarkan paket">
                            </div>
                        </div>

                        <div>
                            <label for="event_location" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lokasi Acara <span class="text-red-500">*</span></label>
                            <textarea name="event_location" id="event_location" rows="2" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Nama gedung, hotel, atau detail venue..."></textarea>
                            
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 mt-4">Pin Lokasi Acara</p>
                            <div id="eventMap" class="h-48 w-full rounded-sm border border-gray-200 mb-1 z-10"></div>
                        </div>

                        <div id="location2_container" class="hidden pt-4 border-t border-gray-100 mt-4">
                            <label for="event_location_2" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Lokasi Acara Ke-2 (Prewedding)</label>
                            <textarea name="event_location_2" id="event_location_2" rows="2" class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Alamat lokasi kedua..."></textarea>

                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-2 mt-4">Pin Lokasi Ke-2</p>
                            <div id="eventMap2" class="h-48 w-full rounded-sm border border-gray-200 mb-1 z-10"></div>
                            
                            <input type="hidden" name="event_lat_2" id="event_lat_2" value="-6.200000">
                            <input type="hidden" name="event_lng_2" id="event_lng_2" value="106.816666">
                        </div>
                        
                        <input type="hidden" name="couple_lat" id="couple_lat" value="-6.200000">
                        <input type="hidden" name="couple_lng" id="couple_lng" value="106.816666">
                        <input type="hidden" name="event_lat" id="event_lat" value="-6.200000">
                        <input type="hidden" name="event_lng" id="event_lng" value="106.816666">
                    </div>
                </div>

                <div class="mt-12 pt-8 border-t border-gray-100">
                    <button type="submit" class="w-full bg-black text-white px-8 py-5 text-[11px] font-bold tracking-[0.3em] uppercase rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                        Lanjut ke Pembayaran
                    </button>
                    <p class="text-center text-[10px] text-gray-400 mt-4 font-serif-custom italic">Anda akan diarahkan ke halaman pembayaran yang aman.</p>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const packagesData = @json($packages);
    let currentDuration = 0;
    let map2Initialized = false;
    let eventMap2, eventMarker2;

    document.addEventListener('DOMContentLoaded', function() {
        const defaultLat = -6.200000; // Jakarta default
        const defaultLng = 106.816666;

        const packageSelect = document.getElementById('package_id');
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const loc2Container = document.getElementById('location2_container');

        // LOGIKA AUTO KALKULASI JAM
        packageSelect.addEventListener('change', function() {
            const selectedPkg = packagesData.find(p => p.id == this.value);
            if(selectedPkg) {
                currentDuration = selectedPkg.duration_hours;
                calculateEndTime();

                if(selectedPkg.total_locations > 1) {
                    loc2Container.classList.remove('hidden');
                    initMap2();
                } else {
                    loc2Container.classList.add('hidden');
                }
            }
        });

        startTimeInput.addEventListener('input', calculateEndTime);

        function calculateEndTime() {
            if(startTimeInput.value && currentDuration > 0) {
                let [hours, minutes] = startTimeInput.value.split(':');
                let endHours = parseInt(hours) + currentDuration;
                if(endHours >= 24) endHours -= 24;
                endTimeInput.value = endHours.toString().padStart(2, '0') + ':' + minutes;
            }
        }

        // SETUP MAPS
        const coupleMap = L.map('coupleMap').setView([defaultLat, defaultLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(coupleMap);
        const coupleMarker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(coupleMap);
        coupleMap.on('click', e => { coupleMarker.setLatLng(e.latlng); document.getElementById('couple_lat').value = e.latlng.lat; document.getElementById('couple_lng').value = e.latlng.lng; });
        coupleMarker.on('dragend', () => { const ll = coupleMarker.getLatLng(); document.getElementById('couple_lat').value = ll.lat; document.getElementById('couple_lng').value = ll.lng; });

        const eventMap = L.map('eventMap').setView([defaultLat, defaultLng], 12);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(eventMap);
        const eventMarker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap);
        eventMap.on('click', e => { eventMarker.setLatLng(e.latlng); document.getElementById('event_lat').value = e.latlng.lat; document.getElementById('event_lng').value = e.latlng.lng; });
        eventMarker.on('dragend', () => { const ll = eventMarker.getLatLng(); document.getElementById('event_lat').value = ll.lat; document.getElementById('event_lng').value = ll.lng; });

        function initMap2() {
            if(!map2Initialized) {
                setTimeout(() => {
                    eventMap2 = L.map('eventMap2').setView([defaultLat, defaultLng], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(eventMap2);
                    eventMarker2 = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap2);
                    
                    eventMap2.on('click', e => { eventMarker2.setLatLng(e.latlng); document.getElementById('event_lat_2').value = e.latlng.lat; document.getElementById('event_lng_2').value = e.latlng.lng; });
                    eventMarker2.on('dragend', () => { const ll = eventMarker2.getLatLng(); document.getElementById('event_lat_2').value = ll.lat; document.getElementById('event_lng_2').value = ll.lng; });
                    
                    map2Initialized = true;
                }, 100);
            }
        }

        setTimeout(function(){ coupleMap.invalidateSize(); eventMap.invalidateSize(); }, 500);
    });
</script>
@endsection