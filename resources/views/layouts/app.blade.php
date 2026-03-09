<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Everlast Project</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Great+Vibes&family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: { cream: '#FDFBF7' },
                    fontFamily: {
                        'sans-custom': ['Montserrat', 'sans-serif'],
                        'serif-custom': ['"Playfair Display"', 'serif'],
                        'script': ['"Great Vibes"', 'cursive'],
                    }
                }
            }
        }
    </script>
</head>
<body class="font-sans-custom bg-cream text-gray-900 antialiased overflow-x-hidden">

    <nav id="navbar" class="fixed w-full z-50 transition-all duration-500 py-4 md:py-6 px-6 md:px-8 flex justify-between items-center text-white bg-transparent">
        <div class="flex items-center z-50">
            <a href="/" id="logo-text" class="text-xl md:text-2xl font-medium tracking-widest uppercase transition-colors">
                Everlast
            </a>
        </div>

        <div class="hidden md:flex space-x-8 text-[10px] font-medium tracking-[0.2em] uppercase">
            <a href="/" class="hover:opacity-70 transition-opacity">Home</a>
            <a href="#about" class="hover:opacity-70 transition-opacity">About</a>
            <a href="#gallery" class="hover:opacity-70 transition-opacity">Gallery</a>
            <a href="#schedule" class="hover:opacity-70 transition-opacity">Schedule</a>
        </div>

        <div class="hidden md:flex items-center space-x-4 text-sm">
            <a href="#" class="hover:opacity-70 transition-opacity"><i class="fab fa-instagram"></i></a>
            <a href="#" class="hover:opacity-70 transition-opacity"><i class="fab fa-youtube"></i></a>
            
            @guest
                <a href="{{ route('login') }}" class="hover:opacity-70 transition-opacity" title="Login / Register">
                    <i class="far fa-user-circle"></i>
                </a>
            @endguest

            @auth
                <a href="{{ route('profile.edit') }}" class="hover:opacity-70 transition-opacity" title="{{ Auth::user()->name }}">
                    <i class="far fa-user-circle"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                    @csrf
                    <button type="submit" class="hover:opacity-70 transition-opacity focus:outline-none" title="Log Out">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            @endauth
        </div>

        <button id="mobile-btn" class="md:hidden z-50 text-2xl focus:outline-none transition-colors">
            <i class="fas fa-bars"></i>
        </button>
    </nav>

    <div id="mobile-menu" class="fixed inset-0 bg-[#FDFBF7] z-40 transform translate-x-full transition-transform duration-500 flex flex-col items-center justify-center opacity-0 pointer-events-none">
        <div class="flex flex-col space-y-8 text-center text-black">
            <a href="/" class="mobile-link text-sm font-bold tracking-[0.3em] uppercase hover:text-[#C9A66B] transition-colors">Home</a>
            <a href="#about" class="mobile-link text-sm font-bold tracking-[0.3em] uppercase hover:text-[#C9A66B] transition-colors">About Us</a>
            <a href="#gallery" class="mobile-link text-sm font-bold tracking-[0.3em] uppercase hover:text-[#C9A66B] transition-colors">Gallery</a>
            <a href="#schedule" class="mobile-link text-sm font-bold tracking-[0.3em] uppercase hover:text-[#C9A66B] transition-colors">Schedule</a>
            
            <div class="pt-8 flex justify-center items-center space-x-6 text-xl text-gray-400 border-t border-gray-200 w-48 mx-auto mt-4">
                <a href="#" class="hover:text-black transition-colors"><i class="fab fa-instagram"></i></a>
                <a href="#" class="hover:text-black transition-colors"><i class="fab fa-youtube"></i></a>
                
                @guest
                    <a href="{{ route('login') }}" class="hover:text-black transition-colors" title="Login / Register">
                        <i class="far fa-user-circle"></i>
                    </a>
                @endguest

                @auth
                    <a href="{{ route('profile.edit') }}" class="hover:text-black transition-colors" title="{{ Auth::user()->name }}">
                        <i class="far fa-user-circle"></i>
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline m-0 p-0">
                        @csrf
                        <button type="submit" class="hover:text-black transition-colors focus:outline-none" title="Log Out">
                            <i class="fas fa-sign-out-alt"></i>
                        </button>
                    </form>
                @endauth
            </div>
        </div>
    </div>

    <main>
        @yield('content')
    </main>

    <footer class="bg-white pt-16 md:pt-24 pb-8 md:pb-12 px-6 md:px-8 border-t border-gray-100 relative mt-10">
        <div class="max-w-6xl mx-auto">
            <div class="absolute left-1/2 -translate-x-1/2 -top-[70px] bg-white p-2 rounded-full hidden md:block">
                <div class="w-32 h-32 rounded-full border border-gray-200 flex flex-col items-center justify-center text-center bg-[#FDFBF7]">
                    <span class="text-[9px] font-bold tracking-[0.3em] uppercase text-gray-400 mb-1">From</span>
                    <span class="font-serif-custom italic text-[#C9A66B] text-xl">Instagram</span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-12 text-center md:text-left text-sm text-gray-600 font-serif-custom">
                <div class="order-2 md:order-1">
                    <h4 class="text-[10px] font-bold tracking-[0.3em] uppercase text-gray-900 mb-4 md:mb-6 font-sans-custom">Get In Touch</h4>
                    <p class="mb-2 italic">Phone / WA (+62) 812 3456 7890</p>
                    <p class="mb-2 text-xs text-gray-400 font-sans-custom uppercase tracking-wider">(by appointment only)</p>
                </div>

                <div class="order-1 md:order-2 border-b border-gray-100 md:border-0 pb-8 md:pb-0">
                    <h4 class="text-[10px] font-bold tracking-[0.3em] uppercase text-gray-900 mb-4 md:mb-6 font-sans-custom">For Inquiries</h4>
                    <p class="mb-4 md:mb-6 italic">Akbar (+62) 812 3456 7891</p>
                    <p class="italic">Team Everlast (+62) 812 3456 7892</p>
                </div>

                <div class="order-3 md:order-3 pt-4 md:pt-0">
                    <h4 class="text-[10px] font-bold tracking-[0.3em] uppercase text-gray-900 mb-4 md:mb-6 font-sans-custom">About</h4>
                    <p class="leading-relaxed mb-6 italic">
                        We're a group of passionate young talents working together to create beautiful masterpieces.
                        <br><br><span class="font-sans-custom font-bold uppercase tracking-widest text-[10px] text-gray-900">EVERLAST PROJECT</span>
                    </p>
                </div>
            </div>

            <div class="mt-16 md:mt-24 text-center text-[8px] md:text-[9px] font-bold tracking-[0.4em] uppercase text-gray-300 font-sans-custom">
                &copy; {{ date('Y') }} site by Everlast Project.
            </div>
        </div>
    </footer>

    <script>
        const navbar = document.getElementById('navbar');
        const logoText = document.getElementById('logo-text');
        const mobileBtn = document.getElementById('mobile-btn');
        const mobileMenu = document.getElementById('mobile-menu');
        const mobileLinks = document.querySelectorAll('.mobile-link');
        const icon = mobileBtn.querySelector('i');
        
        let isMenuOpen = false;
        const heroSectionHeight = window.innerHeight - 100;

        // Fungsi Ganti Warna Navbar saat di-scroll
        function updateNavbarStyle() {
            if (isMenuOpen) return; // Jangan ubah warna kalau menu lagi kebuka
            
            if (window.scrollY > heroSectionHeight) {
                navbar.classList.remove('bg-transparent', 'text-white', 'py-6');
                navbar.classList.add('bg-white', 'text-black', 'py-4', 'shadow-sm');
            } else {
                navbar.classList.add('bg-transparent', 'text-white', 'py-6');
                navbar.classList.remove('bg-white', 'text-black', 'py-4', 'shadow-sm');
            }
        }

        window.addEventListener('scroll', updateNavbarStyle);

        // Fungsi Toggle Mobile Menu
        mobileBtn.addEventListener('click', () => {
            isMenuOpen = !isMenuOpen;
            
            if(isMenuOpen) {
                // Buka Menu
                mobileMenu.classList.remove('translate-x-full', 'opacity-0', 'pointer-events-none');
                icon.classList.remove('fa-bars');
                icon.classList.add('fa-times');
                // Paksa logo & tombol jadi hitam biar kelihatan di background cream
                logoText.classList.remove('text-white');
                logoText.classList.add('text-black');
                mobileBtn.classList.remove('text-white');
                mobileBtn.classList.add('text-black');
            } else {
                // Tutup Menu
                mobileMenu.classList.add('translate-x-full', 'opacity-0', 'pointer-events-none');
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
                // Kembalikan warna sesuai posisi scroll
                logoText.classList.remove('text-black');
                mobileBtn.classList.remove('text-black');
                updateNavbarStyle();
            }
        });

        // Tutup menu otomatis kalau link diklik
        mobileLinks.forEach(link => {
            link.addEventListener('click', () => {
                mobileBtn.click();
            });
        });
    </script>
</body>
</html>
