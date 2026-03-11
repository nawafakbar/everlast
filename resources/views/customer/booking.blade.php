@extends('layouts.app')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>

<style>
    .leaflet-container { z-index: 10 !important; }
    
    /* Styling Kustom Kalender (Di-copy dari Admin) */
    .fc-theme-standard .fc-scrollgrid { border-color: #f3f4f6; }
    .fc-theme-standard th { border-color: #f3f4f6; padding: 10px 0; background-color: #f9fafb; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #6b7280; }
    .fc-theme-standard td { border-color: #f3f4f6; }
    .fc-daygrid-day-number { font-size: 12px; font-weight: 600; color: #374151; padding: 8px !important; }
    .fc-event { border-radius: 2px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 5px; border: none; }
    
    .fc .fc-button-primary { background-color: #000000; border-color: #000000; color: #ffffff; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; border-radius: 2px; padding: 8px 16px; transition: all 0.2s; }
    .fc .fc-button-primary:hover { background-color: #374151; border-color: #374151; }
    .fc .fc-button-primary:disabled { background-color: #9ca3af; border-color: #9ca3af; }
    .fc .fc-button-active { background-color: #374151 !important; border-color: #374151 !important; }
    .fc-toolbar-title { font-family: 'Montserrat', sans-serif; font-size: 1.1rem !important; font-weight: 700; color: #111827; }

    /* Tanggal yang bisa diklik (interaktif) */
    .fc-day:not(.fc-day-past) { cursor: pointer; transition: background-color 0.2s; }
    .fc-day:not(.fc-day-past):hover { background-color: #fdfbf7; }

    /* ==========================================
       RESPONSIVE FIX KHUSUS MOBILE (Layar kecil)
       ========================================== */
    @media (max-width: 640px) {
        .fc-toolbar.fc-header-toolbar {
            flex-direction: column; /* Bikin elemen numpuk ke bawah */
            gap: 12px; /* Jarak antar elemen */
        }
        .fc-toolbar-chunk {
            display: flex;
            justify-content: center; /* Posisikan tombol di tengah */
            width: 100%;
        }
        .fc .fc-button-primary {
            padding: 6px 10px; /* Perkecil ukuran padding tombol */
            font-size: 9px;    /* Perkecil font tombol */
        }
        .fc-toolbar-title {
            font-size: 1.1rem !important; /* Perkecil font judul bulan */
        }
    }
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
            <div id="rules_container" class="hidden mb-8 bg-[#FDFBF7] border border-[#C9A66B] p-6 rounded-sm shadow-sm transition-all duration-300">
                <div class="flex items-center gap-3 mb-4 border-b border-[#EBE6DD] pb-3">
                    <i class="fas fa-clipboard-list text-[#C9A66B] text-xl"></i>
                    <h3 id="rules_title" class="text-xs font-bold text-gray-900 uppercase tracking-[0.2em]">RULES : </h3>
                </div>
                <div id="rules_content" class="text-[11px] text-gray-600 space-y-2 leading-relaxed font-medium">
                    </div>
            </div>
            <form action="{{ route('customer.booking.store') }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-12">
                    
                    <div class="space-y-6">
                        <h3 class="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] border-b border-gray-100 pb-3 mb-6">Paket & Pasangan</h3>
                        
                        <div>
                            <label for="category" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Kategori Acara <span class="text-red-500">*</span></label>
                            <select name="category" id="category" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700 cursor-pointer">
                                <option value="" disabled selected>Pilih kategori...</option>
                                @php $categories = $packages->pluck('category')->unique(); @endphp
                                @foreach($categories as $category)
                                    <option value="{{ $category }}">{{ $category }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="package_id" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Pilih Paket <span class="text-red-500">*</span></label>
                            <div class="flex gap-2">
                                <select name="package_id" id="package_id" required disabled class="flex-1 px-2 py-3 bg-gray-50 border border-gray-200 rounded-sm focus:outline-none text-sm text-gray-400 cursor-not-allowed transition-colors">
                                    <option value="" disabled selected>Pilih kategori</option>
                                </select>
                                
                                <a href="https://drive.google.com/file/d/1eEpPnmcJbQoKZBDhdpyDIvtrJouLE-F6/view?usp=drive_link" target="_blank" class="mr-4 px-4 py-3 bg-gray-100 border border-gray-300 text-gray-900 rounded-sm text-[10px] font-bold uppercase tracking-widest hover:bg-gray-200 transition-colors whitespace-nowrap shadow-sm flex items-center">
                                    <i class="fas fa-book-open mr-2"></i> Katalog
                                </a>
                            </div>
                            <p class="text-[9px] text-gray-400 italic mt-1">Cek katalog untuk melihat detail benefit dari masing-masing paket.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Anda</label>
                            <input type="text" value="{{ Auth::user()->name }}" disabled class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm text-sm text-gray-500 cursor-not-allowed italic">
                        </div>

                        <div>
                            <label for="partner_name" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Nama Pasangan <span class="text-red-500">*</span></label>
                            <input type="text" name="partner_name" id="partner_name" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm" placeholder="Contoh: Jane Doe">
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
                            <div class="flex gap-2">
                                <input type="date" name="booking_date" min="{{ \Carbon\Carbon::today()->toDateString() }}" id="booking_date" required class="flex-1 px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm text-gray-700 cursor-pointer">
                                <button type="button" onclick="openCalendarModal()" class="px-6 py-3 bg-gray-100 border border-gray-300 text-gray-900 rounded-sm text-[10px] font-bold uppercase tracking-widest hover:bg-gray-200 transition-colors whitespace-nowrap shadow-sm">
                                    <i class="far fa-calendar-alt mr-1"></i> Cek Jadwal
                                </button>
                            </div>
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

<div id="calendarModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/40 backdrop-blur-sm transition-opacity" onclick="closeCalendarModal()"></div>
    
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0 pointer-events-none">
        <div class="relative bg-white rounded-md text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:max-w-4xl w-full pointer-events-auto border border-gray-100 flex flex-col max-h-[90vh]">
            
            <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-bold text-gray-900">Ketersediaan Jadwal</h3>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider mt-1">Klik pada tanggal kosong untuk memilih</p>
                </div>
                <button onclick="closeCalendarModal()" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <div class="px-6 py-6 overflow-y-auto flex-1">
                <div class="flex gap-4 mb-4 justify-end">
                    <div class="flex items-center gap-2"><div class="w-3 h-3 bg-black rounded-full"></div><span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Booked</span></div>
                </div>
                <div id='customerCalendar'></div>
            </div>
        </div>
    </div>
</div>

<script>
    const packagesData = @json($packages);
    let currentDuration = 0;
    let map2Initialized = false;
    let eventMap2, eventMarker2;

    document.addEventListener('DOMContentLoaded', function() {
        const defaultLat = -6.858333; // Sumedang default
        const defaultLng = 107.920000;

        const packageSelect = document.getElementById('package_id');
        const categorySelect = document.getElementById('category'); // Tangkap elemen Kategori
        
        // ==========================================
        // LOGIKA FILTER KATEGORI -> BUKA KUNCI PAKET
        // ==========================================
        categorySelect.addEventListener('change', function() {
            const selectedCategory = this.value;

            // 1. Ubah styling input paket jadi bisa diklik (aktif)
            packageSelect.disabled = false;
            packageSelect.classList.remove('bg-gray-50', 'border-gray-200', 'text-gray-400', 'cursor-not-allowed');
            packageSelect.classList.add('bg-transparent', 'border-gray-300', 'focus:bg-white', 'focus:ring-1', 'focus:ring-black', 'focus:border-black', 'text-gray-700', 'cursor-pointer');

            // 2. Kosongkan opsi paket yang lama
            packageSelect.innerHTML = '<option value="" disabled selected>Pilih layanan...</option>';

            // 3. Filter data paket berdasarkan kategori yang dipilih
            const filteredPackages = packagesData.filter(p => p.category === selectedCategory);

            // 4. Masukkan opsi paket yang baru ke dalam dropdown
            filteredPackages.forEach(pkg => {
                const option = document.createElement('option');
                option.value = pkg.id;
                // Format angka Rupiah
                const formattedPrice = new Intl.NumberFormat('id-ID').format(pkg.price);
                option.textContent = `${pkg.name} - Rp ${formattedPrice}`;
                packageSelect.appendChild(option);
            });

            // 5. Sembunyikan ulang Rules & reset jam karena paketnya berubah
            document.getElementById('rules_container').classList.add('hidden');
            loc2Container.classList.add('hidden');
            currentDuration = 0;
            endTimeInput.value = '';
        });
        const startTimeInput = document.getElementById('start_time');
        const endTimeInput = document.getElementById('end_time');
        const loc2Container = document.getElementById('location2_container');

        // LOGIKA AUTO KALKULASI JAM & DYNAMIC RULES
        packageSelect.addEventListener('change', function() {
            const selectedPkg = packagesData.find(p => p.id == this.value);
            
            if(selectedPkg) {
                // 1. Kalkulasi Jam
                currentDuration = selectedPkg.duration_hours;
                calculateEndTime();

                if(selectedPkg.total_locations > 1) {
                    loc2Container.classList.remove('hidden');
                    initMap2();
                } else {
                    loc2Container.classList.add('hidden');
                }

                // ==========================================
                // 2. LOGIKA DYNAMIC RULES BERDASARKAN PAKET
                // ==========================================
                const rulesContainer = document.getElementById('rules_container');
                const rulesTitle = document.getElementById('rules_title');
                const rulesContent = document.getElementById('rules_content');
                
                // Ambil nama paket dan ubah ke huruf kecil biar gampang dicek
                const pkgName = selectedPkg.name.toLowerCase();
                const pkgCategory = selectedPkg.category ? selectedPkg.category.toLowerCase() : '';

                let htmlRules = '';

                // Cek apakah paket Prewedding
                if (pkgName.includes('prewedding') || pkgCategory.includes('prewedding')) {
                    rulesTitle.innerText = "RULES : PREWEDDING";
                    htmlRules = `
                        <ul class="list-none space-y-1.5">
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Booking Minimal 30% dari harga paket.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pelunasan maksimal di H-1 atau di Hari H.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Direkomendasikan meeting untuk pembahasan waktu, lokasi, konsep, dll.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Paket belum termasuk transport dan charge Lokasi.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pengerjaan file edit maksimal 1 minggu, video 20 hari.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Cetak foto pilihan client.</li>
                        </ul>
                    `;
                } 
                // Cek apakah paket Wedding
                else if (pkgName.includes('wedding') || pkgCategory.includes('wedding')) {
                    rulesTitle.innerText = "RULES : WEDDING";
                    htmlRules = `
                        <ul class="list-none space-y-1.5">
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Booking Minimal 30% dari harga paket.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pelunasan maksimal di H-1 atau di Hari H.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Client wajib mengisi data secara lengkap di form booking.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Crew 8 Jam kerja.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Paket belum termasuk transport.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pengerjaan file edit maksimal 2 minggu, video 30 hari, dan album 2 bulan dari hari H.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Cetak album dan pembesaran bisa dipilih client.</li>
                        </ul>
                    `;
                } 
                // Jika selain Wedding & Prewedding (Engagement & Other)
                else {
                    rulesTitle.innerText = "RULES : ENGAGEMENT & OTHER PACKAGE";
                    htmlRules = `
                        <ul class="list-none space-y-1.5">
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Booking Minimal Rp.100.000,-</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pelunasan maksimal di H-1 atau di Hari H.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Paket belum termasuk transport.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Crew 3 jam kerja dari waktu booking dan tiba 30 menit sebelumnya. Apabila melebihi waktu kerja akan dikenakan charge.</li>
                            <li><i class="fas fa-check text-[#C9A66B] mr-2"></i> Pengerjaan file edit maksimal foto 4 hari, video 20 hari.</li>
                        </ul>
                    `;
                }

                // Inject HTML ke dalam kotak dan munculkan kotaknya
                rulesContent.innerHTML = htmlRules;
                rulesContainer.classList.remove('hidden');
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

    // SCRIPT CALENDAR
    let customerCalendar;
    let calendarInitialized = false;

    function openCalendarModal() {
        const modal = document.getElementById('calendarModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        // Render kalender hanya saat modal dibuka agar ukurannya pas
        if (!calendarInitialized) {
            setTimeout(() => {
                const calendarEl = document.getElementById('customerCalendar');
                customerCalendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    headerToolbar: {
                        left: 'prev,next',
                        center: 'title',
                        right: 'today'
                    },
                    events: '{{ route("customer.calendar.events") }}', 
                    
                    // 1. TAMBAHAN TOOLTIP (Sama kayak di Admin)
                    eventDidMount: function(info) {
                        if (info.event.extendedProps.description) {
                            info.el.setAttribute('title', info.event.extendedProps.description);
                        }
                    },
                    
                    height: 'auto',
                    firstDay: 1,
                    
                    dateClick: function(info) {
                        const today = new Date();
                        today.setHours(0,0,0,0);
                        if (info.date < today) {
                            alert('Tidak dapat memilih tanggal yang sudah lewat.');
                            return;
                        }
                        
                        const allEvents = customerCalendar.getEvents();
                        
                        // Cek Full Booked (Blokir)
                        const isFullBooked = allEvents.some(event => event.startStr === info.dateStr && event.title === 'Full Booked');
                        if (isFullBooked) {
                            alert('Maaf, tim kami sudah Full Booked di tanggal ini. Silakan pilih tanggal lain.');
                            return;
                        }

                        // 2. TAMBAHAN INFO AVAILABLE (Setengah Booked)
                        const availableEvent = allEvents.find(event => event.startStr === info.dateStr && event.title === 'Available');
                        
                        if (availableEvent) {
                            // Munculkan notifikasi jam berapa yang udah keisi biar klien tau
                            alert('TIPS: Tanggal ini sudah terisi sebagian (' + availableEvent.extendedProps.description + ').\n\nPastikan Anda mengatur Jam Mulai acara yang tidak bentrok dengan sesi tersebut ya!');
                        }

                        // Isi otomatis ke form input
                        document.getElementById('booking_date').value = info.dateStr;
                        closeCalendarModal();
                    }
                });
                customerCalendar.render();
                calendarInitialized = true;
            }, 100);
        }
    }

    function closeCalendarModal() {
        const modal = document.getElementById('calendarModal');
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
    }
</script>
@endsection