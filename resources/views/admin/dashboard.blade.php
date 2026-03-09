@extends('layouts.admin')

@section('content')
    <div class="mb-8 border-b border-gray-200 pb-4 mt-2">
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Overview</h2>
        <p class="text-gray-500 text-xs mt-1">Real-time statistics and recent activities.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white border border-gray-200 p-6 rounded-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Revenue</p>
                <h3 class="text-2xl font-light text-gray-900">Rp {{ number_format($revenue, 0, ',', '.') }}</h3>
            </div>
            <div class="w-10 h-10 bg-gray-50 flex items-center justify-center rounded-sm border border-gray-100">
                <i class="fas fa-wallet text-gray-400"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-200 p-6 rounded-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Active Bookings</p>
                <h3 class="text-2xl font-light text-gray-900">{{ $totalBookings }} <span class="text-sm text-gray-400">Events</span></h3>
            </div>
            <div class="w-10 h-10 bg-gray-50 flex items-center justify-center rounded-sm border border-gray-100">
                <i class="fas fa-calendar-check text-gray-400"></i>
            </div>
        </div>

        <div class="bg-white border border-gray-200 p-6 rounded-sm flex items-center justify-between">
            <div>
                <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Total Clients</p>
                <h3 class="text-2xl font-light text-gray-900">{{ $totalClients }} <span class="text-sm text-gray-400">Users</span></h3>
            </div>
            <div class="w-10 h-10 bg-gray-50 flex items-center justify-center rounded-sm border border-gray-100">
                <i class="fas fa-users text-gray-400"></i>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <div class="bg-white border border-gray-200 rounded-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Upcoming Events</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($upcomingEvents as $event)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $event->partner_name }} & {{ $event->user->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ \Carbon\Carbon::parse($event->booking_date)->format('d M Y') }} • {{ \Carbon\Carbon::parse($event->start_time)->format('H:i') }}</p>
                        </div>
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-[10px] uppercase tracking-wider rounded-sm">{{ $event->package->name }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-xs italic">No upcoming events.</div>
                @endforelse
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm">
            <div class="px-6 py-4 border-b border-gray-100 flex justify-between items-center">
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Recently Added</h3>
                <a href="{{ route('admin.bookings.index') }}" class="text-[10px] text-gray-400 hover:text-black uppercase tracking-wider font-medium">View All</a>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($recentBookings as $booking)
                    <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 transition-colors">
                        <div>
                            <p class="text-sm font-medium text-gray-900">{{ $booking->user->name }}</p>
                            <p class="text-xs text-gray-500 mt-1">Booked for: {{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</p>
                        </div>
                        @php
                            $statusColor = match($booking->status) {
                                'pending' => 'text-yellow-600',
                                'dp_paid' => 'text-blue-600',
                                'paid_in_full', 'completed' => 'text-green-600',
                                'cancelled' => 'text-red-600',
                                default => 'text-gray-600',
                            };
                        @endphp
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $statusColor }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-gray-400 text-xs italic">No bookings yet.</div>
                @endforelse
            </div>
        </div>
    </div>
@endsection