@extends('layouts.app')

@section('content')

<!-- Hero section -->
<section id="home" class="relative w-full h-[100dvh] overflow-hidden z-0 bg-black flex items-center justify-center">
    
    <video autoplay loop muted playsinline class="absolute inset-0 w-full h-full object-cover -z-10">
        <source src="{{ asset('assets/video/hero.mp4') }}" type="video/mp4">
    </video>
    
    <div class="absolute inset-0 bg-black/30 -z-10"></div>
</section>

<!-- about us section -->
<section id="about" class="relative z-10 bg-[#FDFBF7] pt-32 pb-10 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center min-h-[80vh] shadow-[0_-10px_30px_rgba(0,0,0,0.1)]">
    <div class="max-w-3xl mx-auto text-center">
        <h4 class="text-xs sm:text-sm font-semibold tracking-[0.3em] uppercase text-gray-500 mb-6">
            Welcome To The Journey
        </h4>
        
        <h1 class="font-script text-5xl sm:text-7xl md:text-8xl text-gray-900 mb-8 sm:mb-10 leading-tight">
            Everlast Project
        </h1>
        
        <div class="font-serif-custom text-gray-600 text-sm sm:text-base leading-loose space-y-6 px-4">
            <p>
                We're so excited to share this special moment with you. As we begin our journey together, we'd love for you to join us in capturing your big day. Here, you'll find all the details you need—our packages, event schedules, venue information, and more.
            </p>
            <p>
                Your presence means the world to us, and we can't wait to create unforgettable memories together. Let's celebrate love, laughter, and happily ever after!
            </p>
            <p class="font-script text-3xl text-gray-800 mt-8">
                With Love,
            </p>
        </div>

        <div class="mt-12">
            <a href="{{ route('customer.booking') }}" class="inline-block border border-black text-black px-10 py-4 text-xs font-bold tracking-[0.2em] uppercase hover:bg-black hover:text-white transition-colors duration-300">
                Book Now
            </a>
        </div>
    </div>
</section>

<style>
    @keyframes slideGallery {
        0% { transform: translateX(0); }
        100% { transform: translateX(-50%); } 
    }
    .animate-slide-gallery {
        animation: slideGallery 25s linear infinite; 
    }
    .animate-slide-gallery:hover {
        animation-play-state: paused; 
    }
</style>

<!-- gallery section -->
<section id="gallery" class="relative z-10 bg-[#FDFBF7] pb-20 pt-10 overflow-hidden w-full border-t border-gray-100">
    <div class="flex w-[600%] md:w-[400%] lg:w-[200%] animate-slide-gallery">
        
        <div class="flex w-1/2 justify-around">
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/1.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/2.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/3.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/4.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/5.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/6.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
        </div>

        <div class="flex w-1/2 justify-around">
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/1.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/2.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/3.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/4.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/5.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
            <div class="w-full px-1 sm:px-2"><img src="{{ asset('assets/images/gallery/6.png') }}" class="w-full h-64 sm:h-80 md:h-96 object-cover grayscale hover:grayscale-0 transition-all duration-500"></div>
        </div>

    </div>
</section>

<!-- schedule section -->
<section id="schedule" class="bg-[#FDFBF7] py-24 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto">
        <div class="text-center mb-16">
            <h4 class="text-[10px] sm:text-xs font-bold tracking-[0.3em] uppercase text-gray-500 mb-4">Wedding Day Timeline</h4>
            <h2 class="font-script text-6xl md:text-7xl text-gray-900">Upcoming Schedules</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 items-start">
            <div class="border border-gray-200 p-8 sm:p-12 bg-[#FDFBF7] shadow-sm relative min-h-[600px]">
                <div class="absolute left-0 top-10 bottom-10 w-[1px] bg-gray-200"></div>
                
                @if($schedules->count() > 0)
                    <ul class="space-y-8">
                        @foreach($schedules as $schedule)
                        <li class="flex flex-col sm:flex-row sm:items-start border-b border-gray-200 pb-8 last:border-0 last:pb-0 relative">
                            
                            <div class="sm:w-36 flex-shrink-0 mb-3 sm:mb-0 pt-1">
                                <div class="text-xs font-bold tracking-widest text-gray-900 mb-1">
                                    {{ \Carbon\Carbon::parse($schedule->booking_date)->format('d M Y') }}
                                </div>
                                <div class="text-[10px] text-gray-500 font-sans-custom uppercase tracking-wider">
                                    {{ \Carbon\Carbon::parse($schedule->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($schedule->end_time)->format('H:i') }}
                                </div>
                            </div>

                            <div class="sm:flex-1">
                                <h4 class="text-[10px] font-bold uppercase tracking-[0.2em] text-[#C9A66B] mb-1">Main Event</h4>
                                <p class="text-lg text-gray-900 font-serif-custom mb-1">{{ $schedule->partner_name }}</p>
                                <p class="text-[11px] text-gray-600 italic mb-2">Package: {{ $schedule->package->category }}</p>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider"><i class="fas fa-map-marker-alt mr-2"></i>{{ $schedule->event_location }}</p>
                            </div>
                        </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-sm text-gray-500 italic text-center py-10">Belum ada jadwal yang terdaftar.</p>
                @endif
            </div>

            <div class="h-[600px] w-full shadow-sm sticky top-24">
                <img src="{{ asset('assets/images/gallery/3.png') }}" alt="Schedule Accent" class="w-full h-full object-cover grayscale hover:grayscale-0 transition-all duration-700">
            </div>
        </div>
    </div>
</section>

@endsection