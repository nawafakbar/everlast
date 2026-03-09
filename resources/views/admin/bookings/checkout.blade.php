@extends('layouts.admin')

@section('content')
<div class="mb-8 border-b border-gray-200 pb-4 mt-2 flex justify-between items-center">
    <div>
        <h2 class="text-xl font-semibold text-gray-900 tracking-tight">Test Payment System</h2>
        <p class="text-gray-500 text-xs mt-1">Simulasi halaman checkout klien untuk booking <strong>{{ $booking->user->name }}</strong>.</p>
    </div>
    <a href="{{ route('admin.bookings.index') }}" class="text-xs text-gray-500 hover:text-black transition-colors uppercase tracking-wider font-medium">
        <i class="fas fa-arrow-left mr-2"></i> Back
    </a>
</div>

@if (session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm text-sm font-medium">
        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
    </div>
@endif

@if ($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-sm text-sm font-medium">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
@endif

@if($isFullyPaid)
    <div class="bg-white border border-gray-200 p-10 rounded-sm text-center max-w-2xl mx-auto mt-10 shadow-sm">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-green-100 mb-4">
            <i class="fas fa-check text-2xl text-green-600"></i>
        </div>
        <h3 class="text-xl font-bold text-gray-900 uppercase tracking-tight mb-2">Payment Completed</h3>
        <p class="text-sm text-gray-500">Booking ini telah dibayar lunas sepenuhnya sebesar <br> <span class="font-bold text-gray-900">Rp {{ number_format($fullPrice, 0, ',', '.') }}</span>.</p>
    </div>
@else
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-10">
    <div class="lg:col-span-2 space-y-6">
        <form id="paymentForm" action="{{ route('admin.bookings.processCheckout', $booking->id) }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-200 p-6 rounded-sm">
            @csrf
            
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">1. Payment Type</h3>
            <div class="grid grid-cols-2 gap-4 mb-8">
                
                @if(!$hasPaidDP)
                <label class="relative border border-gray-200 rounded-sm p-4 flex cursor-pointer hover:bg-gray-50 focus-within:ring-1 focus-within:ring-black">
                    <input type="radio" name="payment_type" value="dp" class="peer sr-only" onchange="updatePriceUI()" checked>
                    <div class="w-full">
                        <div class="flex justify-between items-center"><span class="text-xs font-bold uppercase">DP 50%</span><i class="fas fa-check-circle text-black opacity-0 peer-checked:opacity-100"></i></div>
                    </div>
                    <div class="absolute inset-0 border-2 border-transparent peer-checked:border-black rounded-sm pointer-events-none"></div>
                </label>
                @endif

                <label class="relative rounded-sm p-4 flex cursor-pointer hover:bg-gray-50 focus-within:ring-1 focus-within:ring-black">
                    <input type="radio" name="payment_type" value="pelunasan" class="peer sr-only" onchange="updatePriceUI()" {{ $hasPaidDP ? 'checked' : '' }}>
                    <div class="w-full">
                        <div class="flex justify-between items-center">
                            <span class="text-xs font-bold uppercase">{{ $hasPaidDP ? 'Pelunasan (Sisa)' : 'Lunas' }}</span>
                            <i class="fas fa-check-circle text-black opacity-0 peer-checked:opacity-100"></i>
                        </div>
                        @if($hasPaidDP)
                            <p class="text-[10px] text-gray-400 mt-1">Sisa yang harus dibayar</p>
                        @endif
                    </div>
                    <div class="absolute inset-0 border-2 border-transparent rounded-sm pointer-events-none"></div>
                </label>
            </div>

            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">2. Method</h3>
            <div class="space-y-3 mb-8">
                <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50 text-sm"><input type="radio" name="payment_method" value="midtrans" class="mr-3" onchange="toggleManual()" checked> Midtrans (Snap Pop-up)</label>
                <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50 text-sm"><input type="radio" name="payment_method" value="manual_transfer" class="mr-3" onchange="toggleManual()"> Manual Transfer (BCA/Mandiri)</label>
                <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50 text-sm"><input type="radio" name="payment_method" value="manual_qris" class="mr-3" onchange="toggleManual()"> Manual QRIS</label>
            </div>

            <div id="manualForm" class="hidden space-y-4 bg-gray-50 p-4 border border-gray-200 rounded-sm mb-8">
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase mb-2">Upload Struk Bukti</label>
                    <input type="file" name="proof_image" id="proof_image" accept="image/*" class="w-full text-xs bg-white border border-gray-200 p-2 rounded-sm cursor-pointer">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 uppercase mb-2">Notes</label>
                    <input type="text" name="notes" placeholder="Catatan test..." class="w-full px-3 py-2 border border-gray-200 text-sm rounded-sm outline-none focus:ring-1 focus:ring-black">
                </div>
            </div>

            <button type="submit" id="payBtn" class="w-full bg-black text-white py-3 text-xs font-bold uppercase tracking-wider rounded-sm hover:bg-gray-800 flex justify-center items-center">
                Simulate Payment
            </button>
        </form>
    </div>

    <div class="bg-gray-50 border border-gray-200 p-6 rounded-sm h-fit">
        <h3 class="text-xs font-bold uppercase border-b border-gray-200 pb-2 mb-4">Summary</h3>
        <div class="flex justify-between text-sm mb-2"><span>Package</span><span class="font-medium">{{ $booking->package->name }}</span></div>
        <div class="flex justify-between text-sm mb-2"><span>Total Price</span><span>Rp {{ number_format($fullPrice, 0, ',', '.') }}</span></div>
        <div class="flex justify-between text-sm mb-4 pb-4 border-b border-gray-200"><span class="text-green-600">Total Paid</span><span class="text-green-600">- Rp {{ number_format($totalPaid, 0, ',', '.') }}</span></div>
        
        <div class="flex justify-between font-bold text-lg"><span>Pay Amount</span><span id="displayPrice">Rp 0</span></div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>
<script>
    // Tarik data dinamis dari backend Laravel ke Javascript
    const full = {{ $fullPrice }};
    const remaining = {{ $remainingAmount }};
    const form = document.getElementById('paymentForm');

    function updatePriceUI() {
        if(!form) return;
        const typeInput = document.querySelector('input[name="payment_type"]:checked');
        if(!typeInput) return;
        
        const type = typeInput.value;
        const displayVal = type === 'dp' ? (full / 2) : remaining; // Ambil sisa dinamis!
        
        document.getElementById('displayPrice').innerText = 'Rp ' + displayVal.toLocaleString('id-ID');
    }

    function toggleManual() {
        if(!form) return;
        const m = document.querySelector('input[name="payment_method"]:checked').value;
        const mf = document.getElementById('manualForm');
        const pi = document.getElementById('proof_image');
        if (m === 'midtrans') { mf.classList.add('hidden'); pi.removeAttribute('required'); } 
        else { mf.classList.remove('hidden'); pi.setAttribute('required', 'required'); }
    }

    updatePriceUI(); toggleManual();

    if(form) {
        form.addEventListener('submit', function(e) {
            if (document.querySelector('input[name="payment_method"]:checked').value === 'midtrans') {
                e.preventDefault(); 
                const btn = document.getElementById('payBtn');
                const ori = btn.innerHTML;
                btn.innerHTML = 'Loading...'; btn.disabled = true;

                fetch(form.action, { method: 'POST', body: new FormData(form), headers: {'X-Requested-With': 'XMLHttpRequest'} })
                .then(res => res.json())
                .then(data => {
                    btn.innerHTML = ori; btn.disabled = false;
                    if (data.snap_token) {
                        window.snap.pay(data.snap_token, {
                            onSuccess: function(r){ window.location.href = "{{ route('admin.bookings.paymentSuccess', $booking->id) }}"; },
                            onPending: function(r){ alert("Pending!"); },
                            onError: function(r){ alert("Error!"); },
                            onClose: function(){ alert("Pop-up ditutup!"); }
                        });
                    }
                }).catch(err => { btn.innerHTML = ori; btn.disabled = false; alert('Error koneksi!'); });
            }
        });
    }
</script>
@endif
@endsection