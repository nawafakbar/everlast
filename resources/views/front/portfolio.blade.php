<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Portfolio - Everlast</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { cream: '#FDFBF7' }, fontFamily: { 'sans-custom': ['Montserrat', 'sans-serif'], 'serif-custom': ['"Playfair Display"', 'serif'] } }
            }
        }
    </script>
</head>
<body class="font-sans-custom bg-cream text-gray-900 antialiased overflow-x-hidden">

    <div class="w-full p-6 flex justify-between items-center bg-transparent absolute top-0 z-50">
        <a href="/" class="text-xs font-bold tracking-[0.2em] uppercase text-gray-600 hover:text-black transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
        </a>
    </div>

    <main class="pt-32 pb-24">
        <div class="max-w-[90rem] mx-auto px-6"> <div class="text-center mb-20">
                <h1 class="text-4xl md:text-5xl font-serif-custom text-gray-900 mb-4">Our Masterpieces</h1>
                <p class="text-xs font-sans-custom uppercase tracking-[0.3em] text-gray-400">The complete collection of everlasting moments</p>
                <div class="w-16 h-[1px] bg-gray-300 mx-auto mt-8"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-16">
                
                @forelse($moments as $moment)
                <a href="{{ route('front.moment.show', $moment->id) }}" class="flex flex-col items-center group cursor-pointer block">
                    
                    <div class="aspect-[4/3] w-full overflow-hidden mb-6 rounded-sm shadow-sm">
                        <img src="{{ $moment->cover_image }}" alt="Cover" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-out" referrerpolicy="no-referrer">
                    </div>

                    <div class="w-full text-center">
                        <p class="text-[9px] font-bold tracking-[0.2em] text-[#C9A66B] uppercase mb-5">{{ $moment->category }}</p>

                        <div class="flex flex-row justify-center items-start gap-4 px-2">
                            <div class="flex flex-col items-center pt-1 min-w-[50px]">
                                <div class="w-5 h-[1px] bg-gray-300 mb-2"></div>
                                <span class="text-2xl font-serif-custom text-gray-800 leading-none">{{ \Carbon\Carbon::parse($moment->event_date)->format('d') }}</span>
                                <span class="text-[7px] font-bold tracking-[0.2em] text-gray-400 uppercase mt-1">{{ \Carbon\Carbon::parse($moment->event_date)->format('M Y') }}</span>
                            </div>

                            <div class="text-left flex-1">
                                <h3 class="text-xs font-medium tracking-widest uppercase text-gray-900 mb-1.5 leading-relaxed">{{ $moment->title }}</h3>
                                <p class="text-[8px] font-bold tracking-widest uppercase text-gray-400 mb-4">{{ $moment->client_name }}</p>
                                <p class="text-[9px] tracking-[0.1em] uppercase text-gray-500 italic font-serif-custom line-clamp-2">{{ $moment->quote }}</p>
                            </div>
                        </div>
                    </div>
                </a>
                @empty
                <div class="col-span-full text-center py-20 text-gray-400 text-xs tracking-widest uppercase">
                    Belum ada portofolio yang diunggah.
                </div>
                @endforelse

            </div>

            <div class="mt-20 flex justify-center">
                {{ $moments->links() }}
            </div>

        </div>
    </main>

</body>
</html>