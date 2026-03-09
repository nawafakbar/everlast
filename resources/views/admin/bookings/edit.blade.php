@extends('layouts.admin')

@section('content')
    <div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
        <div>
            <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Edit Booking</h2>
            <p class="text-gray-500 text-xs mt-1">Update client reservation details or cancel the booking.</p>
        </div>
        <a href="{{ route('admin.bookings.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
            <i class="fas fa-arrow-left mr-2"></i> Back
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-sm max-w-4xl mb-10">
        
        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 mx-8 mt-8 rounded-sm">
                <p class="text-sm font-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i> Oops, perhatikan hal berikut:</p>
                <ul class="list-disc list-inside text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.bookings.update', $booking->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                
                <div class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">Client & Package Info</h3>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Client Info</label>
                        
                        <input type="text" value="{{ $booking->user->name }} ({{ $booking->user->email }})" disabled 
                               class="w-full px-4 py-2.5 bg-gray-200 border border-gray-300 rounded-sm text-sm text-gray-500 cursor-not-allowed">
                        
                        <input type="hidden" name="user_id" value="{{ $booking->user_id }}">
                        
                        <p class="text-[10px] text-gray-400 mt-2 uppercase">Nama client tidak dapat diubah setelah booking dibuat.</p>
                    </div>

                    <div>
                        <label for="partner_name" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Partner's Name</label>
                        <input type="text" name="partner_name" id="partner_name" value="{{ old('partner_name', $booking->partner_name) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-sm">
                    </div>

                    <div>
                        <label for="package_id" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Select Package</label>
                        <select name="package_id" id="package_id" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-sm text-gray-700">
                            @foreach($packages as $package)
                                <option value="{{ $package->id }}" {{ $booking->package_id == $package->id ? 'selected' : '' }}>{{ $package->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="status" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Payment Status</label>
                        <select name="status" id="status" required class="w-full px-4 py-2.5 bg-red-50 border border-red-200 text-red-700 font-bold rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-sm">
                            <option value="pending" {{ $booking->status == 'pending' ? 'selected' : '' }}>Pending</option>
                            <option value="dp_paid" {{ $booking->status == 'dp_paid' ? 'selected' : '' }}>DP Paid</option>
                            <option value="paid_in_full" {{ $booking->status == 'paid_in_full' ? 'selected' : '' }}>Paid in Full</option>
                            <option value="completed" {{ $booking->status == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ $booking->status == 'cancelled' ? 'selected' : '' }}>❌ CANCELLED</option>
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1 uppercase">Warning: Changing to cancelled will delete Google Calendar event.</p>
                    </div>
                </div>

                <div class="space-y-6">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2">Event Details</h3>

                    <div>
                        <label for="booking_date" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Event Date</label>
                        <input type="date" name="booking_date" id="booking_date" value="{{ old('booking_date', $booking->booking_date) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black text-sm text-gray-700">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label for="start_time" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Start Time</label>
                            <input type="time" name="start_time" id="start_time" value="{{ old('start_time', \Carbon\Carbon::parse($booking->start_time)->format('H:i')) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-sm text-sm">
                        </div>
                        <div>
                            <label for="end_time" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">End Time</label>
                            <input type="time" name="end_time" id="end_time" value="{{ old('end_time', \Carbon\Carbon::parse($booking->end_time)->format('H:i')) }}" required readonly class="w-full px-4 py-2.5 bg-gray-200 border border-gray-300 rounded-sm text-sm text-gray-500 cursor-not-allowed">
                        </div>
                    </div>

                    <div>
                        <label for="couple_address" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Couple's Home Address</label>
                        <textarea name="couple_address" id="couple_address" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm text-sm mb-2">{{ old('couple_address', $booking->couple_address) }}</textarea>
                    </div>

                    <div class="mt-6">
                        <label for="event_location" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Event Venue / Location</label>
                        <textarea name="event_location" id="event_location" rows="2" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-sm text-sm mb-2">{{ old('event_location', $booking->event_location) }}</textarea>
                    </div>
                    
                    <input type="hidden" name="couple_lat" id="couple_lat" value="{{ old('couple_lat', $booking->couple_lat) }}">
                    <input type="hidden" name="couple_lng" id="couple_lng" value="{{ old('couple_lng', $booking->couple_lng) }}">
                    <input type="hidden" name="event_lat" id="event_lat" value="{{ old('event_lat', $booking->event_lat) }}">
                    <input type="hidden" name="event_lng" id="event_lng" value="{{ old('event_lng', $booking->event_lng) }}">
                </div>
            </div>

            @php
                // Ambil semua pembayaran untuk booking ini yang punya gambar (manual transfer/qris)
                $manualPayments = $booking->payments->whereNotNull('proof_image');
            @endphp
            
            @if($manualPayments->count() > 0)
                <div class="mt-10 pt-8 border-t border-gray-100">
                    <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider mb-4"><i class="fas fa-receipt text-gray-400 mr-2"></i> Payment Proofs (Manual Transfer / QRIS)</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                        @foreach($manualPayments as $payment)
                            <div class="border border-gray-200 bg-gray-50 rounded-sm p-4 text-center flex flex-col items-center">
                                <span class="block text-[10px] font-bold text-gray-600 uppercase tracking-wider mb-2">
                                    Tagihan: {{ $payment->payment_type }}
                                </span>
                                
                                <a href="{{ asset('storage/' . $payment->proof_image) }}" target="_blank" class="block mb-3 border border-gray-300 rounded-sm overflow-hidden hover:opacity-80 transition-opacity" title="Klik untuk memperbesar">
                                    <img src="{{ asset('storage/' . $payment->proof_image) }}" alt="Bukti Pembayaran" class="w-full h-32 object-cover">
                                </a>
                                
                                <p class="text-xs font-bold text-black mb-1">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                                
                                @if($payment->status == 'pending')
                                    <span class="px-2 py-1 text-[9px] uppercase tracking-widest bg-yellow-100 text-yellow-700 font-bold rounded-sm">Menunggu Cek</span>
                                @elseif($payment->status == 'success')
                                    <span class="px-2 py-1 text-[9px] uppercase tracking-widest bg-green-100 text-green-700 font-bold rounded-sm">Valid</span>
                                @else
                                    <span class="px-2 py-1 text-[9px] uppercase tracking-widest bg-red-100 text-red-700 font-bold rounded-sm">Ditolak</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="mt-10 pt-6 border-t border-gray-100 flex justify-end">
                <button type="submit" class="bg-black text-white px-8 py-3 text-xs font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                    Update Booking
                </button>
            </div>
        </form>
    </div>

    <script>
        const packagesData = @json($packages);
        let currentDuration = 0;

        document.addEventListener('DOMContentLoaded', function() {
            const packageSelect = document.getElementById('package_id');
            const startTimeInput = document.getElementById('start_time');
            const endTimeInput = document.getElementById('end_time');

            updateDuration();

            packageSelect.addEventListener('change', function() {
                updateDuration();
                calculateEndTime();
            });

            startTimeInput.addEventListener('input', calculateEndTime);

            function updateDuration() {
                const selectedPkg = packagesData.find(p => p.id == packageSelect.value);
                if(selectedPkg) {
                    currentDuration = selectedPkg.duration_hours || 4; 
                }
            }

            function calculateEndTime() {
                if(startTimeInput.value && currentDuration > 0) {
                    let [hours, minutes] = startTimeInput.value.split(':');
                    let endHours = parseInt(hours) + currentDuration;
                    if(endHours >= 24) endHours -= 24; 
                    endTimeInput.value = endHours.toString().padStart(2, '0') + ':' + minutes;
                }
            }
        });
    </script>
@endsection