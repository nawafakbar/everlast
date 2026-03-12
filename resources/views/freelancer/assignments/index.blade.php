@extends('layouts.freelancer') 

@section('content')
<div class="mb-8 border-b border-gray-200 pb-4 mt-2">
    <h2 class="text-xl font-semibold text-gray-900 tracking-tight">My Schedules</h2>
    <p class="text-gray-500 text-xs mt-1">Manage your upcoming events and job assignments.</p>
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
            
            $eventDate = $isPrewed ? $assign->booking->prewed_date : $assign->booking->booking_date;
            $eventStart = $isPrewed ? $assign->booking->prewed_start_time : $assign->booking->start_time;
            $eventEnd = $isPrewed ? $assign->booking->prewed_end_time : $assign->booking->end_time;
            
            $eventLoc = $assign->booking->event_location;
            if ($isPrewed) {
                $eventLoc = $assign->booking->event_location_3 ?? ($assign->booking->event_location_2 ?? 'Lokasi belum ditentukan');
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
                        {{ $assign->event_type }}
                    </span>
                <h3 class="mt-3 text-lg font-serif-custom text-gray-900 leading-tight mb-2">
                    {{ $assign->booking->user->name }} & {{ $assign->booking->partner_name }}
                </h3>
                
                <div class="text-xs text-gray-500 mb-6 space-y-1">
                    <p><i class="fas fa-map-marker-alt w-4 text-center text-gray-400"></i> {{ $eventLoc }}</p>
                    <p><i class="far fa-clock w-4 text-center text-gray-400"></i> {{ \Carbon\Carbon::parse($eventStart)->format('H:i') }} - {{ \Carbon\Carbon::parse($eventEnd)->format('H:i') }}</p>
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
@endsection