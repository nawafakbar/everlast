<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Client - Everlast</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { 'sans-custom': ['Montserrat', 'sans-serif'] }
                }
            }
        }
    </script>
</head>
<body class="bg-[#F5F6F8] font-sans-custom text-gray-900 antialiased">

    <div class="md:hidden bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between fixed top-0 w-full z-40 shadow-sm">
        <div class="font-bold text-lg tracking-widest uppercase">Everlast</div>
        <button id="mobile-menu-btn" class="text-gray-900 focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>

    <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 hidden md:hidden transition-opacity"></div>

    <div class="flex min-h-screen pt-16 md:pt-0">
        
        <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 flex flex-col fixed inset-y-0 left-0 z-50 transform -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out h-full shadow-lg md:shadow-none">
            
            <div class="md:hidden absolute top-4 right-4">
                <button id="close-sidebar-btn" class="text-gray-400 hover:text-red-500 focus:outline-none transition-colors">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div class="p-8 flex flex-col items-center border-b border-gray-100 mt-6 md:mt-0">
                <div class="w-20 h-20 rounded-full overflow-hidden border border-gray-200 mb-4 shadow-sm">
                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(Auth::user()->name) . '&background=random' }}" class="w-full h-full object-cover">
                </div>
                <h3 class="font-bold text-gray-900 text-center text-sm">{{ Auth::user()->name }}</h3>
                <p class="text-[10px] text-gray-500 text-center mt-1 break-all">{{ Auth::user()->email }}</p>
            </div>

            <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto">
                <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-3 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('profile.edit') ? 'bg-gray-100 text-black font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-black' }}">
                    <i class="far fa-user w-5 text-center"></i> <span>My Profil</span>
                </a>
                
                <a href="{{ route('customer.pesanan') }}" class="flex items-center space-x-3 px-4 py-3 rounded-md text-xs font-medium transition-colors {{ request()->routeIs('customer.pesanan') ? 'bg-gray-100 text-black font-bold' : 'text-gray-500 hover:bg-gray-50 hover:text-black' }}">
                    <i class="fas fa-receipt w-5 text-center"></i> <span>My Booking</span>
                </a>
                
                <a href="/" class="flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-gray-50 hover:text-black rounded-md font-medium text-xs transition-colors">
                    <i class="fas fa-home w-5 text-center"></i> <span>Home</span>
                </a>
                
                <form method="POST" action="{{ route('logout') }}" class="m-0 p-0 w-full mt-2 border-t border-gray-100 pt-2">
                    @csrf
                    <button type="submit" class="w-full flex items-center space-x-3 px-4 py-3 text-gray-500 hover:bg-red-50 hover:text-red-600 rounded-md font-medium text-xs transition-colors">
                        <i class="fas fa-sign-out-alt w-5 text-center"></i> <span>Log Out</span>
                    </button>
                </form>
            </nav>
        </aside>

        <main class="flex-1 w-full md:ml-64 p-6 sm:p-8 md:p-12 overflow-x-hidden">
            @yield('content')
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const mobileBtn = document.getElementById('mobile-menu-btn');
            const closeBtn = document.getElementById('close-sidebar-btn');

            function openSidebar() {
                sidebar.classList.remove('-translate-x-full');
                overlay.classList.remove('hidden');
                document.body.classList.add('overflow-hidden'); // Cegah background scroll
            }

            function closeSidebar() {
                sidebar.classList.add('-translate-x-full');
                overlay.classList.add('hidden');
                document.body.classList.remove('overflow-hidden'); // Kembalikan scroll
            }

            mobileBtn.addEventListener('click', openSidebar);
            closeBtn.addEventListener('click', closeSidebar);
            overlay.addEventListener('click', closeSidebar); // Klik area gelap buat nutup
        });
    </script>
</body>
</html>