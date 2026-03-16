@extends('layouts.freelancer') 

@section('content')
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
<div class="mb-8 border-b border-gray-200 pb-4 mt-2">
    <h2 class="text-xl font-semibold text-gray-900 tracking-tight">My Schedules</h2>
    <p class="text-gray-500 text-xs mt-1">Manage your upcoming events and job assignments.</p>
    <div class="flex gap-2 mt-3">
            <button type="button" onclick="openCalendarModal('booking_date')" class="px-6 py-3 bg-gray-100 border border-gray-300 text-gray-900 rounded-sm text-[10px] font-bold uppercase tracking-widest hover:bg-gray-200 transition-colors whitespace-nowrap shadow-sm">
                     <i class="far fa-calendar-alt mr-1"></i> Calendar Everlast
             </button>
    </div>
</div>

@if (session('success'))
    <div class="bg-green-50 border border-green-200 text-green-700 p-4 mb-6 rounded-sm flex items-center shadow-sm">
        <i class="fas fa-check-circle mr-3 text-lg"></i>
        <p class="text-xs font-bold uppercase tracking-wider">{{ session('success') }}</p>
    </div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 xl:grid-cols-3 gap-6">
    @forelse($assignments as $assign)
        
        @php
            $isPrewed = $assign->event_type === 'all_in_prewedding';
            // Deteksi apakah ini paket All In (punya prewed_date)
            $isAllInPackage = !empty($assign->booking->prewed_date); 
            
            $eventDate = $isPrewed ? $assign->booking->prewed_date : $assign->booking->booking_date;
            $eventStart = $isPrewed ? $assign->booking->prewed_start_time : $assign->booking->start_time;
            $eventEnd = $isPrewed ? $assign->booking->prewed_end_time : $assign->booking->end_time;
            
            // Variabel penampung lokasi
            $loc1 = null; $lat1 = null; $lng1 = null;
            $loc2 = null; $lat2 = null; $lng2 = null;

            if ($isPrewed) {
                // SKENARIO 1: TUGAS PREWEDDING
                $loc1 = $assign->booking->event_location_2 ?? 'Lokasi Prewed 1 belum ditentukan';
                $lat1 = $assign->booking->event_lat_2;
                $lng1 = $assign->booking->event_lng_2;

                if ($assign->booking->event_location_3) {
                    $loc2 = $assign->booking->event_location_3;
                    $lat2 = $assign->booking->event_lat_3;
                    $lng2 = $assign->booking->event_lng_3;
                }
            } else {
                // SKENARIO 2: TUGAS MAIN EVENT (WEDDING)
                $loc1 = $assign->booking->event_location ?? 'Lokasi Wedding belum ditentukan';
                $lat1 = $assign->booking->event_lat;
                $lng1 = $assign->booking->event_lng;

                // Jika BUKAN paket All In, freelancer bisa lihat lokasi wedding kedua (dari event_location_2)
                if (!$isAllInPackage && $assign->booking->event_location_2) {
                    $loc2 = $assign->booking->event_location_2;
                    $lat2 = $assign->booking->event_lat_2;
                    $lng2 = $assign->booking->event_lng_2;
                }
            }
        @endphp

        <div class="bg-white border {{ $assign->status == 'pending' ? 'border-yellow-300 shadow-md' : 'border-gray-200 shadow-sm' }} rounded-sm overflow-hidden flex flex-col relative group transition-all">

            @if($assign->status == 'pending')
                <div class="absolute top-0 right-0 bg-yellow-400 text-yellow-900 text-[8px] font-bold uppercase tracking-widest px-3 py-1 m-4 rounded-sm shadow-sm">
                    Requires Action
                </div>
            @endif

            <div class="p-6 flex-1">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-[10px] font-bold tracking-widest text-gray-400 uppercase">
                        {{ \Carbon\Carbon::parse($eventDate)->format('d F Y') }}
                    </p>
                </div>
                <span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-sm text-[8px] font-bold uppercase tracking-widest">
                        {{ str_replace('_', ' ', $assign->event_type) }}
                    </span>
                <h3 class="mt-3 text-lg font-serif-custom text-gray-900 leading-tight mb-4">
                    {{ $assign->booking->user->name }} & {{ $assign->booking->partner_name }}
                </h3>
                
                <div class="text-xs text-gray-500 mb-6 space-y-4">
                    <div class="flex items-start gap-2 text-gray-700">
                        <i class="fas fa-map-marker-alt w-4 text-center mt-1 text-gray-400"></i> 
                        <div class="flex-1">
                            <p class="font-medium">Lokasi 1: {{ $loc1 }}</p>
                            @if($lat1 && $lng1)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $lat1 }},{{ $lng1 }}" target="_blank" class="inline-block mt-1 text-[10px] font-bold tracking-wider uppercase text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-location-arrow mr-1"></i> Buka Maps
                                </a>
                            @endif
                        </div>
                    </div>

                    @if($loc2)
                    <div class="flex items-start gap-2 text-gray-700 pt-2 border-t border-gray-50">
                        <i class="fas fa-map-marker-alt w-4 text-center mt-1 text-gray-400"></i> 
                        <div class="flex-1">
                            <p class="font-medium">Lokasi 2: {{ $loc2 }}</p>
                            @if($lat2 && $lng2)
                                <a href="https://www.google.com/maps/search/?api=1&query={{ $lat2 }},{{ $lng2 }}" target="_blank" class="inline-block mt-1 text-[10px] font-bold tracking-wider uppercase text-blue-600 hover:text-blue-800 transition-colors">
                                    <i class="fas fa-location-arrow mr-1"></i> Buka Maps
                                </a>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div class="flex items-center gap-2 text-gray-700 pt-2 border-t border-gray-50">
                        <i class="far fa-clock w-4 text-center text-gray-400"></i> 
                        <p class="font-medium">{{ \Carbon\Carbon::parse($eventStart)->format('H:i') }} - {{ \Carbon\Carbon::parse($eventEnd)->format('H:i') }} WIB</p>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-sm border border-gray-100 mb-2">
                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-500 mb-1">Your Role:</p>
                    <p class="text-sm font-medium text-gray-900">{{ $assign->task }}</p>

                    <p class="text-[9px] font-bold uppercase tracking-wider text-gray-500 mt-3 mb-1">Fee:</p>
                    <p class="text-sm font-medium text-[#C9A66B]">Rp {{ number_format($assign->fee, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="p-4 border-t border-gray-100 bg-gray-50">
                @if($assign->status == 'pending')
                    <div class="flex gap-2">
                        <form action="{{ route('freelancer.schedules.status', $assign->id) }}" method="POST" class="flex-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="w-full bg-black text-white px-3 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-sm" onclick="return confirm('Terima tugas ini? Pastikan jadwalmu kosong ya!');">
                                Accept
                            </button>
                        </form>
                        <form action="{{ route('freelancer.schedules.status', $assign->id) }}" method="POST" class="flex-1">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="w-full bg-white text-red-600 border border-red-200 px-3 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-red-50 transition-colors" onclick="return confirm('Yakin menolak tugas ini? Admin harus mencari pengganti.');">
                                Reject
                            </button>
                        </form>
                    </div>
                @elseif($assign->status == 'accepted')
                    <div class="flex flex-col items-center">
                        <span class="text-[10px] font-bold text-blue-600 uppercase tracking-widest mb-3"><i class="fas fa-calendar-check mr-1"></i> Accepted</span>
                        <form action="{{ route('freelancer.schedules.status', $assign->id) }}" method="POST" class="w-full">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="completed">
                            <button type="submit" class="w-full bg-white text-gray-700 border border-gray-200 px-3 py-2 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-50 transition-colors" onclick="return confirm('Tandai acara ini telah selesai?');">
                                Mark as Completed
                            </button>
                        </form>
                    </div>
                @elseif($assign->status == 'completed')
                    <div class="text-center py-2">
                        <span class="text-[10px] font-bold text-green-600 uppercase tracking-widest"><i class="fas fa-check-double mr-1 text-lg align-middle"></i> Job Completed</span>
                    </div>
                @else
                    <div class="text-center py-2">
                        <span class="text-[10px] font-bold text-red-500 uppercase tracking-widest"><i class="fas fa-times-circle mr-1 text-lg align-middle"></i> Rejected</span>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <div class="col-span-full py-20 text-center border border-dashed border-gray-300 rounded-sm bg-white">
            <i class="far fa-calendar-times text-4xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-500 text-sm font-medium">Belum ada jadwal penugasan untukmu bulan ini.</p>
        </div>
    @endforelse
</div>

<div class="mt-8">
    {{ $assignments->links() }}
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
                    <div class="flex items-center gap-2"><div class="w-3 h-3 bg-black/20 rounded-full"></div><span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Available</span></div>
                </div>
                <div id='customerCalendar'></div>
            </div>
        </div>
    </div>
</div>

<script>
    // SCRIPT CALENDAR
    let customerCalendar;
    let calendarInitialized = false;
    let currentTargetInput = ''; // Variabel buat nyimpen ID input yang mau diisi

    // Tambahin parameter targetId
    function openCalendarModal(targetId) {
        currentTargetInput = targetId; 
        const modal = document.getElementById('calendarModal');
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

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
                        
                        const isFullBooked = allEvents.some(event => event.startStr === info.dateStr && event.title === 'Full Booked');
                        if (isFullBooked) {
                            alert('Maaf, tim kami sudah Full Booked di tanggal ini. Silakan pilih tanggal lain.');
                            return;
                        }

                        const availableEvent = allEvents.find(event => event.startStr === info.dateStr && event.title === 'Available');
                        
                        if (availableEvent) {
                            alert('TIPS: Tanggal ini sudah terisi sebagian (' + availableEvent.extendedProps.description + ').\n\nPastikan Anda mengatur Jam Mulai acara yang tidak bentrok dengan sesi tersebut ya!');
                        }

                        // INJEK TANGGAL KE INPUT YANG BENAR (Bisa booking_date, bisa prewed_date)
                        document.getElementById(currentTargetInput).value = info.dateStr;
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