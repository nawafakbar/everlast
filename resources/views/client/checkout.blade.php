@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
    <div class="mb-8 border-b border-gray-200 pb-4">
        <h2 class="text-2xl font-bold text-gray-900 tracking-tight uppercase">Payment Checkout</h2>
        <p class="text-gray-500 text-sm mt-1">Selesaikan pembayaran untuk mengamankan jadwal Anda.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-sm text-sm font-medium">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-sm text-sm">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        <div class="md:col-span-2 space-y-6">
            <form id="paymentForm" action="{{ route('checkout.process', $booking->id) }}" method="POST" enctype="multipart/form-data" class="bg-white border border-gray-200 p-6 rounded-sm">
                @csrf
                
                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">1. Payment Type</h3>
                <div class="grid grid-cols-2 gap-4 mb-8">
                    <label class="relative border border-gray-200 rounded-sm p-4 flex cursor-pointer hover:bg-gray-50 focus-within:ring-1 focus-within:ring-black transition-colors">
                        <input type="radio" name="payment_type" value="dp" class="peer sr-only" onchange="updatePriceUI()" checked>
                        <div class="w-full">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-900 uppercase tracking-wider">Down Payment (DP) 50%</span>
                                <i class="fas fa-check-circle text-black opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Bayar setengah harga untuk booking jadwal.</p>
                        </div>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-black rounded-sm pointer-events-none"></div>
                    </label>

                    <label class="relative border border-gray-200 rounded-sm p-4 flex cursor-pointer hover:bg-gray-50 focus-within:ring-1 focus-within:ring-black transition-colors">
                        <input type="radio" name="payment_type" value="paid_in_full" class="peer sr-only" onchange="updatePriceUI()">
                        <div class="w-full">
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-bold text-gray-900 uppercase tracking-wider">Bayar Lunas</span>
                                <i class="fas fa-check-circle text-black opacity-0 peer-checked:opacity-100 transition-opacity"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">Bayar penuh 100% sekarang.</p>
                        </div>
                        <div class="absolute inset-0 border-2 border-transparent peer-checked:border-black rounded-sm pointer-events-none"></div>
                    </label>
                </div>

                <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-100 pb-2 mb-4">2. Payment Method</h3>
                <div class="space-y-3 mb-8">
                    <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="midtrans" class="text-black focus:ring-black mr-3" onchange="toggleManualForm()" checked>
                        <span class="text-sm font-medium text-gray-900">Virtual Account / E-Wallet (Otomatis via Midtrans)</span>
                    </label>
                    <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="manual_transfer" class="text-black focus:ring-black mr-3" onchange="toggleManualForm()">
                        <span class="text-sm font-medium text-gray-900">Transfer Bank Manual (BCA/Mandiri)</span>
                    </label>
                    <label class="flex items-center p-3 border border-gray-200 rounded-sm cursor-pointer hover:bg-gray-50">
                        <input type="radio" name="payment_method" value="manual_qris" class="text-black focus:ring-black mr-3" onchange="toggleManualForm()">
                        <span class="text-sm font-medium text-gray-900">QRIS Manual (Scan Barcode)</span>
                    </label>
                </div>

                <div id="manualPaymentForm" class="hidden space-y-6 bg-gray-50 p-6 border border-gray-200 rounded-sm mb-8">
                    <div id="instructionTransfer" class="hidden">
                        <p class="text-xs font-bold text-gray-900 uppercase mb-2">Instruksi Transfer</p>
                        <p class="text-sm text-gray-600">BCA: <strong>1234567890</strong> a.n. Everlast Project</p>
                        <p class="text-sm text-gray-600">Mandiri: <strong>0987654321</strong> a.n. Everlast Project</p>
                    </div>
                    <div id="instructionQris" class="hidden">
                        <p class="text-xs font-bold text-gray-900 uppercase mb-2">Instruksi QRIS</p>
                        <div class="w-32 h-32 bg-gray-200 border border-gray-300 flex items-center justify-center text-xs text-gray-500 mb-2">[Gambar QRIS]</div>
                        <p class="text-xs text-gray-500">Scan menggunakan M-Banking atau E-Wallet Anda.</p>
                    </div>

                    <div>
                        <label for="proof_image" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Upload Bukti Transfer *</label>
                        <input type="file" name="proof_image" id="proof_image" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-sm file:border-0 file:text-xs file:font-semibold file:bg-black file:text-white hover:file:bg-gray-800 cursor-pointer border border-gray-200 rounded-sm p-2 bg-white">
                    </div>
                    <div>
                        <label for="notes" class="block text-xs font-medium text-gray-700 uppercase tracking-wider mb-2">Catatan Pengirim (Opsional)</label>
                        <input type="text" name="notes" id="notes" placeholder="Misal: Transfer atas nama Budi" class="w-full px-4 py-2 border border-gray-200 rounded-sm text-sm focus:ring-1 focus:ring-black outline-none">
                    </div>
                </div>

                <button type="submit" id="payButton" class="w-full bg-black text-white px-8 py-4 text-sm font-bold uppercase tracking-wider rounded-sm hover:bg-gray-800 transition-colors flex items-center justify-center">
                    <i class="fas fa-lock mr-2"></i> Pay Now
                </button>
            </form>
        </div>

        <div class="bg-gray-50 border border-gray-200 p-6 rounded-sm h-fit sticky top-6">
            <h3 class="text-xs font-bold text-gray-900 uppercase tracking-wider border-b border-gray-200 pb-2 mb-4">Order Summary</h3>
            
            <div class="space-y-3 text-sm text-gray-600 border-b border-gray-200 pb-4 mb-4">
                <div class="flex justify-between">
                    <span>Package</span>
                    <span class="font-medium text-gray-900">{{ $booking->package->name }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Event Date</span>
                    <span class="font-medium text-gray-900">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d M Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span>Location</span>
                    <span class="font-medium text-gray-900 truncate max-w-[150px] text-right" title="{{ $booking->event_location }}">{{ $booking->event_location }}</span>
                </div>
            </div>

            <div class="space-y-2 mb-4">
                <div class="flex justify-between text-sm text-gray-500">
                    <span>Full Price</span>
                    <span>Rp {{ number_format($booking->package->price, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-lg font-bold text-gray-900 pt-2 border-t border-gray-200 mt-2">
                    <span>Total to Pay</span>
                    <span id="displayPrice">Rp 0</span>
                </div>
            </div>
            <p class="text-[10px] text-gray-400 text-center uppercase tracking-wider"><i class="fas fa-shield-alt mr-1"></i> Secure Payment by Midtrans</p>
        </div>
    </div>
</div>

<script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ env('MIDTRANS_CLIENT_KEY') }}"></script>

<script>
    const fullPrice = {{ $booking->package->price }};
    const dpPrice = fullPrice / 2;
    const displayPriceEl = document.getElementById('displayPrice');
    const form = document.getElementById('paymentForm');

    // Update tampilan harga saat radio button DP/Lunas ditekan
    function updatePriceUI() {
        const type = document.querySelector('input[name="payment_type"]:checked').value;
        const amount = type === 'dp' ? dpPrice : fullPrice;
        displayPriceEl.innerText = 'Rp ' + amount.toLocaleString('id-ID');
    }

    // Muncul/hilangkan form manual upload struk
    function toggleManualForm() {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        const manualForm = document.getElementById('manualPaymentForm');
        const instTransfer = document.getElementById('instructionTransfer');
        const instQris = document.getElementById('instructionQris');
        const proofInput = document.getElementById('proof_image');

        if (method === 'midtrans') {
            manualForm.classList.add('hidden');
            proofInput.removeAttribute('required'); // Gak wajib upload
        } else {
            manualForm.classList.remove('hidden');
            proofInput.setAttribute('required', 'required'); // Wajib upload
            
            if (method === 'manual_transfer') {
                instTransfer.classList.remove('hidden');
                instQris.classList.add('hidden');
            } else {
                instTransfer.classList.add('hidden');
                instQris.classList.remove('hidden');
            }
        }
    }

    // Jalankan sekali saat halaman diload
    updatePriceUI();
    toggleManualForm();

    // INTERCEPT FORM SUBMIT KHUSUS MIDTRANS
    form.addEventListener('submit', function(e) {
        const method = document.querySelector('input[name="payment_method"]:checked').value;
        
        // Kalau Klien pilih Midtrans, kita cegah form pindah halaman, kita pakai AJAX
        if (method === 'midtrans') {
            e.preventDefault(); 
            
            // Ubah teks tombol biar klien tahu lagi loading
            const btn = document.getElementById('payButton');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...';
            btn.disabled = true;

            const formData = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Kembalikan tombol seperti semula
                btn.innerHTML = originalText;
                btn.disabled = false;

                // Kalau dapet token dari Controller, panggil Pop Up Snap!
                if (data.snap_token) {
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result){
                            // Nanti kita arahin ke halaman histori/sukses
                            alert("Pembayaran Berhasil!"); 
                            window.location.href = "/"; // Sementara lempar ke home
                        },
                        onPending: function(result){
                            alert("Menunggu Pembayaran...");
                        },
                        onError: function(result){
                            alert("Pembayaran Gagal!");
                        },
                        onClose: function(){
                            alert("Anda menutup pop-up sebelum menyelesaikan pembayaran.");
                        }
                    });
                }
            })
            .catch(error => {
                console.error("Error:", error);
                btn.innerHTML = originalText;
                btn.disabled = false;
                alert('Terjadi kesalahan koneksi ke Midtrans.');
            });
        }
        // Kalau pilih manual, biarkan form jalan normal secara default!
    });
</script>
@endsection