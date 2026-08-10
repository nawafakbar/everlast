@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-[#FDFBF7] pt-28 pb-20 px-4 sm:px-6 lg:px-8 font-sans-custom">
    <div class="max-w-2xl mx-auto">

        <div class="text-center mb-10">
            <h2 class="font-script text-5xl text-gray-900 mb-2">Write a Review</h2>
            <p class="text-[10px] font-sans-custom font-bold tracking-[0.3em] uppercase text-gray-400">Tell us about your experience</p>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 p-6 mb-8 rounded-sm shadow-sm">
                <p class="text-xs font-bold mb-2 tracking-widest uppercase"><i class="fas fa-exclamation-triangle mr-2"></i> Please check your entries:</p>
                <ul class="list-disc list-inside text-xs space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="bg-white p-8 md:p-12 border border-gray-100 shadow-[0_10px_40px_rgba(0,0,0,0.04)] rounded-sm">

            <div class="bg-[#FDFBF7] border border-[#EBE6DD] p-5 rounded-sm mb-8">
                <p class="text-[10px] font-bold text-[#C9A66B] uppercase tracking-[0.2em] mb-3">Booking Details</p>
                <div class="space-y-2">
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500">Booking ID</span>
                        <span class="font-bold text-gray-900">#EVL-{{ $booking->id }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500">Package</span>
                        <span class="font-bold text-gray-900 text-right">{{ $booking->package->name }}</span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="text-gray-500">Event Date</span>
                        <span class="font-bold text-gray-900">{{ \Carbon\Carbon::parse($booking->booking_date)->format('d F Y') }}</span>
                    </div>
                </div>
            </div>

            <form action="{{ route('customer.reviews.store', $booking->id) }}" method="POST">
                @csrf

                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-3">Your Rating <span class="text-red-500">*</span></label>

                    <input type="hidden" name="rating" id="rating" value="{{ old('rating', 0) }}">

                    <div class="flex items-center gap-3">
                        <div id="starWrap" class="flex gap-1">
                            @for ($i = 1; $i <= 5; $i++)
                                <button type="button" data-value="{{ $i }}" class="star-btn text-3xl text-gray-300 transition-colors focus:outline-none">
                                    <i class="fas fa-star"></i>
                                </button>
                            @endfor
                        </div>
                        <span id="ratingLabel" class="text-[10px] font-bold uppercase tracking-wider text-gray-400"></span>
                    </div>
                </div>

                <div class="mb-8">
                    <label for="comment" class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">Your Review <span class="text-red-500">*</span></label>
                    <textarea name="comment" id="comment" rows="5" required maxlength="1000"
                        class="w-full px-4 py-3 bg-transparent border border-gray-300 rounded-sm focus:bg-white focus:outline-none focus:ring-1 focus:ring-black focus:border-black transition-colors text-sm"
                        placeholder="How was the team, the result, and the overall experience?">{{ old('comment') }}</textarea>
                    <p class="text-[9px] text-gray-400 italic mt-1">Minimum 10 characters. Your review will be shown publicly on the package page.</p>
                </div>

                <div class="pt-6 border-t border-gray-100">
                    <button type="submit" class="w-full bg-black text-white px-8 py-4 text-[11px] font-bold tracking-[0.3em] uppercase rounded-sm hover:bg-gray-800 transition-colors shadow-md">
                        Submit Review
                    </button>
                    <a href="{{ route('customer.pesanan') }}" class="block text-center text-[10px] text-gray-400 mt-4 uppercase tracking-widest font-bold hover:text-gray-700 transition-colors">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const ratingInput = document.getElementById('rating');
    const starButtons = document.querySelectorAll('.star-btn');
    const ratingLabel = document.getElementById('ratingLabel');
    const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'];

    function paintStars(value) {
        starButtons.forEach(btn => {
            const v = parseInt(btn.dataset.value);
            if (v <= value) {
                btn.classList.remove('text-gray-300');
                btn.classList.add('text-[#C9A66B]');
            } else {
                btn.classList.remove('text-[#C9A66B]');
                btn.classList.add('text-gray-300');
            }
        });
    }

    starButtons.forEach(btn => {
        const value = parseInt(btn.dataset.value);

        // Preview saat kursor lewat
        btn.addEventListener('mouseenter', () => paintStars(value));

        // Kunci nilai saat diklik
        btn.addEventListener('click', () => {
            ratingInput.value = value;
            paintStars(value);
            ratingLabel.innerText = labels[value];
        });
    });

    // Balikin ke nilai yang tersimpan saat kursor keluar dari area bintang
    document.getElementById('starWrap').addEventListener('mouseleave', () => {
        paintStars(parseInt(ratingInput.value) || 0);
    });

    // Render awal (buat restore data lama kalau validasi gagal)
    paintStars(parseInt(ratingInput.value) || 0);
    if (parseInt(ratingInput.value) > 0) {
        ratingLabel.innerText = labels[parseInt(ratingInput.value)];
    }
</script>
@endsection