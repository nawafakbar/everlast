@extends('layouts.admin')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <style>
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
                                <option value="{{ $package->id }}" data-category="{{ strtolower($package->category) }}">{{ $package->name }} - Rp {{ number_format($package->price, 0, ',', '.') }}</option>
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

                    <div>
                        <label for="couple_address" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Couple's Home Address</label>
                        <textarea name="couple_address" id="couple_address" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Full address for coordination..."></textarea>
                        
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Location (Couple's Home)</p>
                        <div id="coupleMap" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        <p class="text-[10px] text-gray-400 italic">Drag the marker to pinpoint the exact location.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">Main Event Details</h3>

                    <div>
                        <label for="booking_date" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Main Event Date</label>
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

                    <div class="mt-6">
                        <label for="event_location" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Main Venue Location</label>
                        <textarea name="event_location" id="event_location" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm mb-2" placeholder="Name of building, hotel, or specific venue location..."></textarea>
                        
                        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Location (Main Venue)</p>
                        <div id="eventMap" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        <p class="text-[10px] text-gray-400 italic">Drag the marker to pinpoint the exact location.</p>
                    </div>

                    <div id="prewed_container" class="hidden space-y-4 pt-4 border-t-2 border-dashed border-[#C9A66B]">
                        <h3 class="text-xs font-bold text-[#C9A66B] uppercase tracking-wider border-b border-gray-100 pb-2">Prewedding Session (Eksklusif)</h3>
                        
                        <div>
                            <label for="prewed_date" class="block text-xs font-medium text-[#C9A66B] uppercase tracking-wider mb-2">Prewedding Date</label>
                            <input type="date" name="prewed_date" id="prewed_date" class="w-full px-4 py-2.5 bg-gray-50 border border-[#EBE6DD] rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#C9A66B] focus:border-[#C9A66B] transition-colors text-sm text-gray-700">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="prewed_start_time" class="block text-xs font-medium text-[#C9A66B] uppercase tracking-wider mb-2">Start Time</label>
                                <input type="time" name="prewed_start_time" id="prewed_start_time" class="w-full px-4 py-2.5 bg-gray-50 border border-[#EBE6DD] rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#C9A66B] transition-colors text-sm text-gray-700">
                            </div>
                            <div>
                                <label for="prewed_end_time" class="block text-xs font-medium text-[#C9A66B] uppercase tracking-wider mb-2">End Time</label>
                                <input type="time" name="prewed_end_time" id="prewed_end_time" class="w-full px-4 py-2.5 bg-gray-50 border border-[#EBE6DD] rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-[#C9A66B] transition-colors text-sm text-gray-700">
                            </div>
                        </div>

                        <div>
                            <label for="event_location_2" class="block text-xs font-medium text-[#C9A66B] uppercase tracking-wider mb-2">Prewed Location 1</label>
                            <textarea name="event_location_2" id="event_location_2" rows="2" class="w-full px-4 py-3 bg-white border border-[#EBE6DD] rounded-sm focus:ring-1 focus:ring-[#C9A66B] transition-colors text-sm mb-2" placeholder="Second location address..."></textarea>
                            
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Prewed Location 1</p>
                            <div id="eventMap2" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        </div>

                        <div id="location3_container" class="mt-4">
                            <label for="event_location_3" class="block text-xs font-medium text-[#C9A66B] uppercase tracking-wider mb-2">Prewed Location 2 (Optional)</label>
                            <textarea name="event_location_3" id="event_location_3" rows="2" class="w-full px-4 py-3 bg-white border border-[#EBE6DD] rounded-sm focus:ring-1 focus:ring-[#C9A66B] transition-colors text-sm mb-2" placeholder="Third location address..."></textarea>
                            
                            <p class="text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1 mt-3">Pin Prewed Location 2</p>
                            <div id="eventMap3" class="h-48 w-full rounded-sm border border-gray-200 mb-2"></div>
                        </div>
                    </div>
                    
                    <input type="hidden" name="couple_lat" id="couple_lat" value="-6.200000">
                    <input type="hidden" name="couple_lng" id="couple_lng" value="106.816666">
                    <input type="hidden" name="event_lat" id="event_lat" value="-6.200000">
                    <input type="hidden" name="event_lng" id="event_lng" value="106.816666">
                    <input type="hidden" name="event_lat_2" id="event_lat_2" value="-6.858333">
                    <input type="hidden" name="event_lng_2" id="event_lng_2" value="107.920000">
                    <input type="hidden" name="event_lat_3" id="event_lat_3" value="-6.858333">
                    <input type="hidden" name="event_lng_3" id="event_lng_3" value="107.920000">
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
        let prewedMapsInitialized = false; // Ganti nama biar universal
        let eventMap2, eventMarker2;
        let eventMap3, eventMarker3; // Tambahan Map 3
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const defaultLat = -6.858333; 
            const defaultLng = 107.920000;

            const packageSelect = document.getElementById('package_id');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');
            
            // Elemen-elemen khusus All In
            const prewedContainer = document.getElementById('prewed_container');
            const prewedDateInput = document.getElementById('prewed_date');
            const prewedStartInput = document.getElementById('prewed_start_time');
            const prewedEndInput = document.getElementById('prewed_end_time');
            const eventLoc2Input = document.getElementById('event_location_2');

            packageSelect.addEventListener('change', function() {
                const selectedPkg = packagesData.find(p => p.id == this.value);
                const selectedOption = this.options[this.selectedIndex];
                const category = selectedOption.getAttribute('data-category');

                if(selectedPkg) {
                    currentDuration = selectedPkg.duration_hours;
                    calculateEndTime();

                    // LOGIKA ALL IN
                    if(category === 'all in') {
                        prewedContainer.classList.remove('hidden');
                        
                        // Set required biar admin nggak lupa ngisi prewed
                        prewedDateInput.setAttribute('required', 'required');
                        prewedStartInput.setAttribute('required', 'required');
                        prewedEndInput.setAttribute('required', 'required');
                        eventLoc2Input.setAttribute('required', 'required');

                        initPrewedMaps();
                    } else {
                        prewedContainer.classList.add('hidden');
                        
                        // Cabut required kalau bukan All In
                        prewedDateInput.removeAttribute('required');
                        prewedStartInput.removeAttribute('required');
                        prewedEndInput.removeAttribute('required');
                        eventLoc2Input.removeAttribute('required');
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

            // SETUP MAPS (SAMA)
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

            function initPrewedMaps() {
                if(!prewedMapsInitialized) {
                    setTimeout(() => {
                        // RENDER MAP 2 (Prewed 1)
                        eventMap2 = L.map('eventMap2').setView([defaultLat, defaultLng], 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(eventMap2);
                        eventMarker2 = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap2);
                        
                        eventMap2.on('click', e => { eventMarker2.setLatLng(e.latlng); document.getElementById('event_lat_2').value = e.latlng.lat; document.getElementById('event_lng_2').value = e.latlng.lng; });
                        eventMarker2.on('dragend', () => { const ll = eventMarker2.getLatLng(); document.getElementById('event_lat_2').value = ll.lat; document.getElementById('event_lng_2').value = ll.lng; });
                        
                        // RENDER MAP 3 (Prewed 2)
                        eventMap3 = L.map('eventMap3').setView([defaultLat, defaultLng], 12);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(eventMap3);
                        eventMarker3 = L.marker([defaultLat, defaultLng], {draggable: true}).addTo(eventMap3);
                        
                        eventMap3.on('click', e => { eventMarker3.setLatLng(e.latlng); document.getElementById('event_lat_3').value = e.latlng.lat; document.getElementById('event_lng_3').value = e.latlng.lng; });
                        eventMarker3.on('dragend', () => { const ll = eventMarker3.getLatLng(); document.getElementById('event_lat_3').value = ll.lat; document.getElementById('event_lng_3').value = ll.lng; });

                        prewedMapsInitialized = true;
                    }, 100);
                }
            }

            setTimeout(function(){ coupleMap.invalidateSize(); eventMap.invalidateSize(); }, 500);
        });
    </script>
@endsection