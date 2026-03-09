@extends('layouts.admin')

@section('content')
    <div class="flex items-center justify-between mb-8 border-b border-gray-200 pb-4 mt-2">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Bookings</h2>
            <p class="text-gray-500 text-xs mt-1">Manage client reservations and event schedules.</p>
        </div>
        <a href="{{ route('admin.bookings.create') }}" class="bg-black text-white px-4 py-2 text-xs font-medium uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors">
            + Manual Booking
        </a>
    </div>

    @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-sm">
            <p class="text-sm font-bold"><i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}</p>
        </div>
    @endif
    
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm">
            <p class="text-sm font-bold"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</p>
        </div>
    @endif
    <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-6 gap-4">
        <form action="{{ route('admin.bookings.index') }}" method="GET" class="relative w-full sm:w-64">
            <input type="text" name="search" value="{{ $search }}" placeholder="Search client name..." class="w-full pl-9 pr-4 py-2 bg-white border border-gray-200 rounded-sm focus:outline-none focus:ring-1 focus:ring-black text-sm">
            <i class="fas fa-search absolute left-3 top-2.5 text-gray-400 text-sm"></i>
        </form>
    </div>

    <!-- @if (session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-sm text-sm font-medium"><i class="fas fa-exclamation-triangle mr-2"></i> {{ session('error') }}</div>
    @endif
    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm text-sm font-medium"><i class="fas fa-check-circle mr-2"></i> {{ session('success') }}</div>
    @endif -->

    <form id="bulkDeleteForm" action="{{ route('admin.bookings.bulkDelete') }}" method="POST" class="hidden">
        @csrf
    </form>
    <div>
        <div id="bulkActionContainer" class="hidden bg-gray-50 border border-gray-200 p-3 mb-4 rounded-sm flex items-center justify-between">
            <span class="text-xs font-semibold text-gray-700"><span id="selectedCount">0</span> selected</span>
            <button type="button" onclick="confirmBulkDelete()" class="bg-red-500 text-white px-4 py-1.5 text-[10px] font-medium uppercase tracking-wider rounded-sm hover:bg-red-600">
                <i class="fas fa-trash mr-1"></i> Delete Selected
            </button>
        </div>

        <div class="bg-white border border-gray-200 rounded-sm overflow-hidden mb-4">
            <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse whitespace-nowrap">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200 text-xs text-gray-500 uppercase tracking-wider">
                        <th class="px-6 py-4 w-10"><input type="checkbox" id="selectAll" form="bulkDeleteForm" class="w-3.5 h-3.5 text-black bg-gray-100 border-gray-300 rounded-sm cursor-pointer"></th>
                        <th class="px-6 py-4 font-medium">Event Date</th>
                        <th class="px-6 py-4 font-medium">Client</th>
                        <th class="px-6 py-4 font-medium">Status</th>
                        <th class="px-6 py-4 font-medium text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @forelse ($bookings as $booking)
                        <tr class="hover:bg-gray-50 transition-colors group cursor-pointer" onclick="openBookingModal(this)" data-client="{{ $booking->user->name }}" data-partner="{{ $booking->partner_name }}" data-package="{{ $booking->package->name }}" data-date="{{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}" data-time="{{ \Carbon\Carbon::parse($booking->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($booking->end_time)->format('H:i') }}" data-address="{{ $booking->couple_address }}" data-location="{{ $booking->event_location }}" data-status="{{ strtoupper(str_replace('_', ' ', $booking->status)) }}">
                            <td class="px-6 py-4" onclick="event.stopPropagation()">
                                <input type="checkbox" name="ids[]" value="{{ $booking->id }}" form="bulkDeleteForm" class="row-checkbox w-3.5 h-3.5 text-black bg-gray-100 border-gray-300 rounded-sm cursor-pointer">
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-gray-700">{{ $booking->user->name }}</td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColor = match($booking->status) {
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'dp_paid' => 'bg-blue-100 text-blue-700',
                                        'paid_in_full', 'completed' => 'bg-green-100 text-green-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 text-[10px] font-semibold uppercase tracking-wider rounded-sm {{ $statusColor }}">{{ str_replace('_', ' ', $booking->status) }}</span>
                            </td>
                            <td class="px-6 py-4 text-right" onclick="event.stopPropagation()">
                                <div class="flex justify-end space-x-3">
                                    <a href="{{ route('admin.bookings.checkout', $booking->id) }}" class="text-gray-400 hover:text-green-500 transition-colors" title="Test Payment">
                                        <i class="fas fa-credit-card"></i>
                                    </a>
                                    <a href="{{ route('admin.bookings.edit', $booking->id) }}" class="text-gray-400 hover:text-black transition-colors" title="Edit"><i class="fas fa-edit"></i></a>
                                    <form action="{{ route('admin.bookings.destroy', $booking->id) }}" method="POST" onsubmit="return confirm('Hapus booking ini?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors" title="Delete"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-6 py-8 text-center text-gray-400 text-xs italic">No bookings found.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
        </div>

    <div class="mt-4">{{ $bookings->links() }}</div>

    <div id="bookingModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40 backdrop-blur-sm transition-opacity">
        <div class="bg-white rounded-sm shadow-2xl w-full max-w-lg p-8 relative transform transition-all">
            <button onclick="closeBookingModal()" class="absolute top-5 right-5 text-gray-400 hover:text-black transition-colors">
                <i class="fas fa-times fa-lg"></i>
            </button>
            
            <div class="mb-6 border-b border-gray-100 pb-4">
                <span id="bModalStatus" class="px-2 py-1 bg-gray-100 text-gray-500 text-[10px] uppercase tracking-wider rounded-sm mb-3 inline-block"></span>
                <h3 id="bModalCouple" class="text-xl font-semibold text-gray-900 tracking-tight leading-none mb-2"></h3>
                <p id="bModalPackage" class="text-sm font-medium text-gray-500"></p>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Event Schedule</p>
                    <p class="text-sm text-gray-800"><i class="far fa-calendar-alt w-5 text-gray-400"></i> <span id="bModalDate"></span></p>
                    <p class="text-sm text-gray-800 mt-1"><i class="far fa-clock w-5 text-gray-400"></i> <span id="bModalTime"></span></p>
                </div>
                
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-wider mb-1">Locations</p>
                    <p class="text-sm text-gray-800"><span class="font-medium">Couple Address:</span> <span id="bModalAddress" class="text-gray-600"></span></p>
                    <p class="text-sm text-gray-800 mt-1"><span class="font-medium">Venue:</span> <span id="bModalLocation" class="text-gray-600"></span></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        const selectAll = document.getElementById('selectAll');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkActionContainer = document.getElementById('bulkActionContainer');
        const selectedCountText = document.getElementById('selectedCount');
        const bulkForm = document.getElementById('bulkDeleteForm');

        function updateBulkActionUI() {
            const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
            selectedCountText.innerText = checkedCount;
            if (checkedCount > 0) { bulkActionContainer.classList.remove('hidden'); } 
            else { bulkActionContainer.classList.add('hidden'); selectAll.checked = false; }
        }

        selectAll.addEventListener('change', function() { rowCheckboxes.forEach(cb => cb.checked = this.checked); updateBulkActionUI(); });
        rowCheckboxes.forEach(cb => { cb.addEventListener('change', updateBulkActionUI); });
        function confirmBulkDelete() { if (confirm('Delete selected bookings? Pastikan statusnya Cancelled!')) { bulkForm.submit(); } }
    </script>

    <script>
        function openBookingModal(row) {
            document.getElementById('bModalCouple').innerText = row.dataset.client + ' & ' + row.dataset.partner;
            document.getElementById('bModalPackage').innerText = row.dataset.package;
            document.getElementById('bModalDate').innerText = row.dataset.date;
            document.getElementById('bModalTime').innerText = row.dataset.time;
            document.getElementById('bModalAddress').innerText = row.dataset.address;
            document.getElementById('bModalLocation').innerText = row.dataset.location;
            document.getElementById('bModalStatus').innerText = row.dataset.status;

            const modal = document.getElementById('bookingModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeBookingModal() {
            const modal = document.getElementById('bookingModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('bookingModal');
            if (event.target == modal) {
                closeBookingModal();
            }
        }
    </script>
@endsection