@extends('layouts.admin')

@section('content')
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
<style>
    /* Styling Kustom Tema Monokrom Everlast */
    .fc-theme-standard .fc-scrollgrid { border-color: #f3f4f6; }
    .fc-theme-standard th { border-color: #f3f4f6; padding: 10px 0; background-color: #f9fafb; text-transform: uppercase; font-size: 10px; letter-spacing: 0.1em; color: #6b7280; }
    .fc-theme-standard td { border-color: #f3f4f6; }
    .fc-daygrid-day-number { font-size: 12px; font-weight: 600; color: #374151; padding: 8px !important; }
    .fc-event { border-radius: 2px; font-size: 10px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 3px 5px; cursor: pointer; border: none; }
    
    /* Styling Tombol */
    .fc .fc-button-primary { background-color: #000000; border-color: #000000; color: #ffffff; text-transform: uppercase; font-size: 10px; font-weight: 700; letter-spacing: 0.1em; border-radius: 2px; padding: 8px 16px; transition: all 0.2s; }
    .fc .fc-button-primary:hover { background-color: #374151; border-color: #374151; }
    .fc .fc-button-primary:disabled { background-color: #9ca3af; border-color: #9ca3af; }
    .fc .fc-button-active { background-color: #374151 !important; border-color: #374151 !important; }
    .fc-toolbar-title { font-family: 'Montserrat', sans-serif; font-size: 1.25rem !important; font-weight: 700; color: #111827; }

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

<div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-end">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Calendar Everlast</h2>
        <p class="text-gray-500 text-xs mt-1">Monitor the availability of the team's daily schedule and agenda.
</p>
    </div>
    <div class="flex gap-4">
        <div class="flex items-center gap-2"><div class="w-3 h-3 bg-black rounded-full"></div><span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Full Booked</span></div>
        <div class="flex items-center gap-2"><div class="w-3 h-3 bg-gray-400 rounded-full"></div><span class="text-[10px] font-bold uppercase tracking-wider text-gray-500">Available</span></div>
    </div>
</div>

<div class="bg-white border border-gray-200 rounded-sm p-6 shadow-sm">
    <div id='calendar'></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek'
            },
            events: '{{ route("admin.calendar.events") }}', // Nembak data JSON dari Controller
            eventDidMount: function(info) {
                // Kasih Tooltip pas kursor diarahkan ke event-nya
                if (info.event.extendedProps.description) {
                    info.el.setAttribute('title', info.event.extendedProps.description);
                }
            },
            height: 'auto',
            firstDay: 1, // Mulai dari hari Senin
        });
        calendar.render();
    });
</script>
@endsection