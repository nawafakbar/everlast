@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
        /* Biar petanya nggak tumpang tindih sama UI Tailwind */
        .leaflet-container { z-index: 10 !important; }
    </style>
    <div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Manual Booking</h2>
            <p class="text-gray-500 text-xs mt-1">Create a new reservation for a client manually.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-sm max-w-4xl mb-10">
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-8 rounded-sm">
                <p class="text-sm font-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Oops, maaf tanggal sudah full booking:</p>
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <form action="{{ route('admin.bookings.store') }}" method="POST" class="p-8">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">Client & Package Info</h3>
                    
                    <div>
                        <label for="user_id" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Select Client</label>
                        <select name="user_id" id="user_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                            <option value="" disabled selected>Choose a registered client...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->email }})</option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase">Client must be registered first.</p>
                    </div>

                    <div>
                        <label for="partner_name" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Partner's Name</label>
                        <input type="text" name="partner_name" id="partner_name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm" placeholder="e.g. Jane Doe">
                    </div>

                    <div>
                        <label for="package_id" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Select Package</label>
                        <select name="package_id" id="package_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                            <option value="" disabled selected>Choose a package...</option>
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Payment Status</label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                            <option value="pending" selected>Pending</option>
                            <option value="dp_paid">DP Paid</option>
                            <option value="paid_in_full">Paid in Full</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">Event Details</h3>

                    <div>
                        <label for="booking_date" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Event Date</label>
                        <input type="date" name="booking_date" id="booking_date" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Start Time</label>
                            <input type="time" name="start_time" id="start_time" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700">
                        </div>
                        <div>
                            <label for="end_time" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">End Time (Auto)</label>
                            <input type="time" name="end_time" id="end_time" required readonly class="w-full px-4 py-2.5 bg-gray-200 border border-gray-300 rounded-sm focus:outline-none text-sm text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label for="couple_address" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Couple's Home Address</label>
                        <textarea name="couple_address" id="couple_address" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Full address for coordination..."></textarea>
                        
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Location (Couple's Home)</p>
                        <div id="coupleMap" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        <p class="text-[10px] text-gray-400 italic">Drag the marker to pinpoint the exact location.</p>
                    </div>

                    <div class="mt-6">
                        <label for="event_location" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Event Venue / Location</label>
                        <textarea name="event_location" id="event_location" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Name of building, hotel, or specific venue location..."></textarea>
                        
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Location (Event Venue)</p>
                        <div id="eventMap" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        <p class="text-[10px] text-gray-400 italic">Drag the marker to pinpoint the exact location.</p>
                    </div>

                    <div id="location2_container" class="mt-6 hidden p-4 bg-gray-50 border border-gray-200 rounded-sm">
                        <label for="event_location_2" class="block text-xs font-medium text-blue-700 uppercase tracking-wider mb-2">2nd Venue Location (Prewedding)</label>
                        <textarea name="event_location_2" id="event_location_2" rows="2" class="w-full px-4 py-3 bg-white border border-gray-200 rounded-sm focus:ring-1 focus:ring-black transition-colors text-sm mb-2" placeholder="Second location address..."></textarea>

                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Location 2</p>
                        <div id="eventMap2" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>

                        <input type="hidden" name="event_lat_2" id="event_lat_2" value="-6.858333">
                        <input type="hidden" name="event_lng_2" id="event_lng_2" value="107.920000">
                    </div>
                    
                    <input type="hidden" name="couple_lat" id="couple_lat" value="-6.200000">
                    <input type="hidden" name="couple_lng" id="couple_lng" value="106.816666">
                    <input type="hidden" name="event_lat" id="event_lat" value="-6.200000">
                    <input type="hidden" name="event_lng" id="event_lng" value="106.816666">
                </div>

            </div>

            <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-3 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
                    Save Booking
                </button>
            </div>
        </form>
    </div>

    <script>
        const packagesData = @json($packages);
        let currentDuration = 0;
        let map2Initialized = false;
        let eventMap2, eventMarker2;
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const defaultLat = -6.858333; // Sumedang
            const defaultLng = 107.920000;

            // --- 1. LOGIKA AUTO-CALCULATE JAM & SHOW/HIDE LOKASI ---
            const packageSelect = document.getElementById('package_id');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            const loc2Container = document.getElementById('location2_container');

            packageSelect.addEventListener('change', function() {
                // Cari paket yang dipilih dari JSON
                const selectedPkg = packagesData.find(p => p.id == this.value);
                if(selectedPkg) {
                    currentDuration = selectedPkg.duration_hours; // Tarik durasi jam
                    calculateEndTime();

                    // Tampilkan lokasi ke-2 kalau paketnya butuh lebih dari 1 lokasi
                    if(selectedPkg.total_locations > 1) {
                        loc2Container.classList.remove('hidden');
                        initMap2(); // Nyalain peta ke-2 kalau belum nyala
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
                    
                    if(endHours >= 24) endHours -= 24; // Mencegah jam 25:00
                    
                    // Format jadi 2 digit (HH:MM)
                    endTimeInput.value = endHours.toString().padStart(2, '0') + ':' + minutes;
                }
            }

            // --- 2. SETUP LEAFLET MAPS (SAMA KAYAK SEBELUMNYA) ---
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

            // Fungsi Nyalain Peta ke-2
            function initMap2() {
                if(!map2Initialized) {
                    setTimeout(() => {
                        eventMap2 = L.map('eventMap2').setView([defaultLat, defaultLng], 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(eventMap2);
                        eventMarker2 = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap2);
                        
                        eventMap2.on('click', e => { eventMarker2.setLatLng(e.latlng); document.getElementById('event_lat_2').value = e.latlng.lat; document.getElementById('event_lng_2').value = e.latlng.lng; });
                        eventMarker2.on('dragend', () => { const ll = eventMarker2.getLatLng(); document.getElementById('event_lat_2').value = ll.lat; document.getElementById('event_lng_2').value = ll.lng; });
                        
                        map2Initialized = true;
                    }, 100); // Jeda dikit biar div-nya nongol dulu
                }
            }

            setTimeout(function(){ coupleMap.invalidateSize(); eventMap.invalidateSize(); }, 500);
        });
    </script>

    <!-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Titik Awal Default (Diset ke area Sumedang / Jawa Barat biar dekat basecamp)
            const defaultLat = -6.858333;
            const defaultLng = 107.920000;

            // --- PETA 1: COUPLE ADDRESS ---
            const coupleMap = L.map('coupleMap').setView([defaultLat, defaultLng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(coupleMap);

            // Bikin marker
            const coupleMarker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(coupleMap);
            
            // Event 1: Kalau marker di-drag
            coupleMarker.on('dragend', function (e) {
                const latLng = coupleMarker.getLatLng();
                updateCoupleInputs(latLng);
            });

            // Event 2: Kalau area peta di-klik
            coupleMap.on('click', function(e) {
                coupleMarker.setLatLng(e.latlng); // Pindah posisi pin
                updateCoupleInputs(e.latlng);
            });

            function updateCoupleInputs(latLng) {
                document.getElementById('couple_lat').value = latLng.lat.toFixed(8);
                document.getElementById('couple_lng').value = latLng.lng.toFixed(8);
            }

            // --- PETA 2: EVENT VENUE ---
            const eventMap = L.map('eventMap').setView([defaultLat, defaultLng], 12);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(eventMap);

            const eventMarker = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap);
            
            // Event 1: Kalau marker di-drag
            eventMarker.on('dragend', function (e) {
                const latLng = eventMarker.getLatLng();
                updateEventInputs(latLng);
            });

            // Event 2: Kalau area peta di-klik
            eventMap.on('click', function(e) {
                eventMarker.setLatLng(e.latlng); // Pindah posisi pin
                updateEventInputs(e.latlng);
            });

            function updateEventInputs(latLng) {
                document.getElementById('event_lat').value = latLng.lat.toFixed(8);
                document.getElementById('event_lng').value = latLng.lng.toFixed(8);
            }

            // Fix masalah map nggak render full
            setTimeout(function(){ 
                coupleMap.invalidateSize(); 
                eventMap.invalidateSize();
            }, 500);
        });
    </script> -->
@endsection