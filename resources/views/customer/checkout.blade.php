@extends('layouts.app')

@section('content')
<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<div class="min-h-screen bg-[#FDFBF7] pt-28 pb-20 px-4 sm:px-6 lg:px-8 font-sans-custom">
    <div class="max-w-4xl mx-auto">
        
        <div class="text-center mb-10">
            <h2 class="font-script text-5xl text-gray-900 mb-2">Checkout</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Complete Your Payment</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 text-red-700 p-4 mb-8 rounded-sm text-xs font-bold text-center border border-red-200">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="grid grid-cols-1 md:grid-cols-5 gap-8">
            
            <div class="md:col-span-2">
                <div class="bg-white p-6 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] rounded-sm">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-gray-900 border-b border-gray-100 pb-3 mb-4">Rincian Pesanan</h3>
                    
                    <div class="space-y-3 mb-6">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">ID Booking</span>
                            <span class="font-bold text-gray-900">#EVL-{{ $booking->id }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Paket</span>
                            <span class="font-bold text-gray-900 text-right">{{ $booking->package->name }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Total Harga</span>
                            <span class="font-bold text-gray-900">Rp {{ number_format($fullPrice, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="bg-gray-50 p-4 border border-gray-200 rounded-sm mb-4">
                        <div class="flex justify-between items-center text-xs mb-1">
                            <span class="text-gray-500 font-bold uppercase tracking-wider">Telah Dibayar</span>
                            <span class="font-bold text-green-600">Rp {{ number_format($totalPaid, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500 font-bold uppercase tracking-wider">Sisa Tagihan</span>
                            <span class="font-bold text-red-600">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="md:col-span-3">
                <div class="bg-white p-8 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] rounded-sm">
                    
                    <form id="payment-form" action="{{ route('customer.checkout.process', $booking->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-6">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Jenis Pembayaran</label>
                            <div class="grid grid-cols-2 gap-4">
                                @if(!$hasPaidDP)
                                    <label class="cursor-pointer">
                                        <input type="radio" name="payment_type" value="dp" checked class="peer hidden" onchange="updateAmount()">
                                        <div class="border border-gray-200 p-4 rounded-sm text-center peer-checked:border-black peer-checked:bg-gray-50 transition-colors">
                                            <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Bayar DP (50%)</p>
                                            <p class="text-sm font-bold text-gray-900">Rp {{ number_format($fullPrice / 2, 0, ',', '.') }}</p>
                                        </div>
                                    </label>
                                @endif
                                
                                <label class="cursor-pointer">
                                    <input type="radio" name="payment_type" value="pelunasan" {{ $hasPaidDP ? 'checked' : '' }} class="peer hidden" onchange="updateAmount()">
                                    <div class="border border-gray-200 p-4 rounded-sm text-center peer-checked:border-black peer-checked:bg-gray-50 transition-colors h-full flex flex-col justify-center">
                                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-500 mb-1">Pelunasan</p>
                                        <p class="text-sm font-bold text-gray-900">Rp {{ number_format($remainingAmount, 0, ',', '.') }}</p>
                                    </div>
                                </label>
                            </div>
                        </div>

                        <div class="mb-8">
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Metode Pembayaran</label>
                            <select name="payment_method" id="payment_method" required class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:ring-1 focus:ring-black outline-none text-sm text-gray-700">
                                <option value="midtrans">Otomatis (Virtual Account, Gopay, dll)</option>
                                <option value="manual_transfer">Transfer Bank Manual (BCA/Mandiri)</option>
                                <option value="manual_qris">QRIS Manual</option>
                            </select>
                        </div>

                        <div id="manual_payment_area" class="hidden space-y-6 bg-gray-50 p-6 border border-gray-200 rounded-sm mb-8">
                            <div id="info_transfer" class="hidden text-xs text-gray-600 leading-relaxed text-center">
                                Silakan transfer ke rekening berikut:<br>
                                <strong class="text-black text-sm block mt-2">BCA: 1234567890 (a.n. Everlast Project)</strong>
                            </div>
                            
                            <div id="info_qris" class="hidden text-center">
                                <img src="{{ asset('assets/images/qris_dummy.jpg') }}" alt="QRIS Everlast" class="w-40 h-40 mx-auto border border-gray-300 rounded-sm mb-2 object-cover">
                                <p class="text-[10px] uppercase tracking-wider text-gray-500 font-bold">Scan QRIS di atas</p>
                            </div>

                            <div class="border-t border-gray-200 pt-4 mt-4">
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Upload Bukti Transfer <span class="text-red-500">*</span></label>
                                <input type="file" name="proof_image" id="proof_image" accept="image/*" class="w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-bold file:bg-gray-200 file:text-gray-700 hover:file:bg-gray-300 transition-colors">
                            </div>
                        </div>

                        <button type="submit" id="btn-submit" class="w-full bg-black text-white px-8 py-4 text-[11px] font-bold tracking-[0.3em] uppercase rounded-sm hover:bg-gray-800 transition-colors shadow-md text-center">
                            Proses Pembayaran
                        </button>
                    </form>

                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const paymentMethodSelect = document.getElementById('payment_method');
    const manualArea = document.getElementById('manual_payment_area');
    const infoTransfer = document.getElementById('info_transfer');
    const infoQris = document.getElementById('info_qris');
    const proofInput = document.getElementById('proof_image');
    const form = document.getElementById('payment-form');
    const btnSubmit = document.getElementById('btn-submit');

    // LOGIKA TAMPIL/SEMBUNYI METODE BAYAR
    paymentMethodSelect.addEventListener('change', function() {
        if (this.value === 'midtrans') {
            manualArea.classList.add('hidden');
            proofInput.removeAttribute('required');
        } else {
            manualArea.classList.remove('hidden');
            proofInput.setAttribute('required', 'required');
            
            if (this.value === 'manual_transfer') {
                infoTransfer.classList.remove('hidden');
                infoQris.classList.add('hidden');
            } else {
                infoTransfer.classList.add('hidden');
                infoQris.classList.remove('hidden');
            }
        }
    });

    // LOGIKA PROSES FORM (MIDTRANS AJAX vs MANUAL SUBMIT)
    form.addEventListener('submit', function(e) {
        if (paymentMethodSelect.value === 'midtrans') {
            e.preventDefault(); // Jangan refresh halaman dulu
            btnSubmit.innerHTML = 'Memproses...';
            btnSubmit.disabled = true;

            // Tembak data ke controller pakai AJAX
            fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if(data.snap_token) {
                    snap.pay(data.snap_token, {
                        onSuccess: function(result){ window.location.href = "{{ route('customer.pesanan') }}"; },
                        onPending: function(result){ window.location.href = "{{ route('customer.pesanan') }}"; },
                        onError: function(result){ 
                            alert("Pembayaran Gagal!"); 
                            btnSubmit.innerHTML = 'Proses Pembayaran';
                            btnSubmit.disabled = false;
                        },
                        onClose: function(){
                            btnSubmit.innerHTML = 'Proses Pembayaran';
                            btnSubmit.disabled = false;
                        }
                    });
                }
            })
            .catch(error => {
                alert("Terjadi kesalahan sistem.");
                btnSubmit.innerHTML = 'Proses Pembayaran';
                btnSubmit.disabled = false;
            });
        }
        // Kalau pilih manual, biarkan form jalan normal (refresh halaman dan upload gambar)
    });

    // Jalankan sekali saat load pertama biar tampilannya bener
    paymentMethodSelect.dispatchEvent(new Event('change'));
</script>
@endsection