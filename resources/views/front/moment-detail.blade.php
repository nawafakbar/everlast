<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $moment->title }} - Everlast</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: { colors: { cream: '#FFFFFF' }, fontFamily: { 'sans-custom': ['Montserrat', 'sans-serif'], 'serif-custom': ['"Playfair Display"', 'serif'] } }
            }
        }
    </script>
</head>
<body class="font-sans-custom bg-cream text-gray-900 antialiased overflow-x-hidden">

    <div class="fixed top-0 left-0 w-full p-6 flex justify-between items-center z-50 bg-gradient-to-b from-cream/80 to-transparent backdrop-blur-sm">
        <a href="/#moments" class="text-xs font-bold tracking-[0.2em] uppercase text-gray-600 hover:text-black transition-colors">
            <i class="fas fa-arrow-left mr-2"></i> Back to Home
        </a>
        
        @auth
            @if(Auth::id() == $moment->user_id || Auth::user()->role == 'admin')
                <a href="{{ route('freelancer.moments.edit', $moment->id) }}" class="bg-black text-white px-6 py-2.5 text-[10px] font-bold uppercase tracking-widest rounded-sm hover:bg-gray-800 transition-colors shadow-sm">
                    <i class="fas fa-edit mr-2"></i> Edit Moment
                </a>
            @endif
        @endauth
    </div>

    <main class="pt-32 pb-24">
        <div class="max-w-5xl mx-auto px-6">
            
            <div class="text-center mb-16 md:mb-24">
                <p class="text-[10px] font-bold tracking-[0.3em] text-[#C9A66B] uppercase mb-6">{{ $moment->category }}</p>
                <h1 class="text-4xl md:text-5xl lg:text-6xl font-serif-custom text-gray-900 mb-6 leading-tight">{{ $moment->title }}</h1>
                <p class="text-xs font-bold tracking-[0.2em] uppercase text-gray-500 mb-6">{{ $moment->client_name }}</p>
                <div class="flex items-center justify-center space-x-4 text-xs tracking-widest text-gray-400 uppercase">
                    <span>{{ \Carbon\Carbon::parse($moment->event_date)->format('d M Y') }}</span>
                    <span>•</span>
                    <span>By {{ $moment->user->name ?? 'Everlast Talent' }}</span>
                </div>
            </div>

            <div class="w-full mb-20 shadow-xl rounded-sm overflow-hidden">
                <img src="{{ $moment->cover_image }}" alt="Cover" class="w-full h-auto object-cover" referrerpolicy="no-referrer">
            </div>

            @if($moment->quote)
            <div class="text-center mb-20 max-w-3xl mx-auto">
                <p class="text-xl md:text-2xl tracking-wide text-gray-600 italic font-serif-custom leading-relaxed">"{{ $moment->quote }}"</p>
            </div>
            @endif

            @if(is_array($moment->gallery_links) && count($moment->gallery_links) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-start mb-20">
                    @foreach($moment->gallery_links as $link)
                        <div class="w-full rounded-sm overflow-hidden transform hover:scale-[1.02] transition-transform duration-500">
                            <img src="{{ $link }}" alt="Gallery Image" class="w-full h-auto object-cover aspect-[4/5]" referrerpolicy="no-referrer">
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-20 border-t border-b border-gray-200 mb-20">
                    <p class="text-xs text-gray-400 tracking-[0.2em] uppercase">End of Exhibition</p>
                </div>
            @endif

        </div>
    </main>

</body>
</html>