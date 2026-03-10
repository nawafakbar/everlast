<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance - everlast.project</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon.png') }}">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        body { font-family: 'Inter', sans-serif; }
        /* Transisi khusus biar smooth pas width berubah */
        #sidebar { transition: width 0.3s ease, transform 0.3s ease; }
    </style>
</head>
<body class="bg-[#f8f9fa] text-gray-800 flex h-screen overflow-hidden text-sm selection:bg-black selection:text-white">

    <div id="mobileOverlay" class="fixed inset-0 bg-black/40 backdrop-blur-sm z-30 hidden md:hidden transition-opacity" onclick="toggleMobileSidebar()"></div>

    <aside id="sidebar" class="w-64 bg-white border-r border-gray-200 flex-shrink-0 flex flex-col fixed md:relative z-40 h-full transform -translate-x-full md:translate-x-0">
        
        <div class="h-16 flex items-center justify-center border-b border-gray-100">
            <h1 id="brandLogo" class="text-black text-base font-semibold tracking-[0.2em] uppercase transition-all duration-300">everlast.</h1>
        </div>
        
        <nav class="flex-1 overflow-y-auto py-6 overflow-x-hidden">
            <ul class="space-y-1 px-4">
                
                <li>
                    <a href="{{ route('profile.edit') }}" 
                       class="flex items-center px-4 py-2.5 rounded-sm transition-all duration-300 group {{ request()->routeIs('profile.edit') ? 'bg-gray-100 text-gray-500' : 'text-gray-500 hover:text-black hover:bg-gray-50' }}"
                       title="Overview">
                        <div class="w-6 flex items-center justify-center">
                            <i class="fas fa-user text-xs transition-colors {{ request()->routeIs('profile.edit') ? 'text-gray-500' : 'text-gray-400 group-hover:text-black' }}"></i>
                        </div>
                        <span class="sidebar-text ml-3 font-medium text-xs tracking-wide uppercase whitespace-nowrap">My Profil</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('freelancer.moments.index') }}" 
                       class="flex items-center px-4 py-2.5 rounded-sm transition-all duration-300 group {{ request()->routeIs('freelancer.moments.index') ? 'bg-gray-100 text-gray-500' : 'text-gray-500 hover:text-black hover:bg-gray-50' }}"
                       title="Overview">
                        <div class="w-6 flex items-center justify-center">
                            <i class="fas fa-image text-xs transition-colors {{ request()->routeIs('freelancer.moments.index') ? 'text-gray-500' : 'text-gray-400 group-hover:text-black' }}"></i>
                        </div>
                        <span class="sidebar-text ml-3 font-medium text-xs tracking-wide uppercase whitespace-nowrap">Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="{{ route('freelancer.schedules.index') }}" 
                       class="flex items-center px-4 py-2.5 rounded-sm transition-all duration-300 group {{ request()->routeIs('freelancer.schedules.index') ? 'bg-gray-100 text-gray-500' : 'text-gray-500 hover:text-black hover:bg-gray-50' }}"
                       title="Overview">
                        <div class="w-6 flex items-center justify-center">
                            <i class="fas fa-calendar-check text-xs transition-colors {{ request()->routeIs('freelancer.schedules.index') ? 'text-gray-500' : 'text-gray-400 group-hover:text-black' }}"></i>
                        </div>
                        <span class="sidebar-text ml-3 font-medium text-xs tracking-wide uppercase whitespace-nowrap">Schedules</span>
                    </a>
                </li>
                <li>
                    <a href="/" 
                       class="flex items-center px-4 py-2.5 rounded-sm transition-all duration-300 group {{ request()->routeIs('/') ? 'bg-gray-100 text-gray-500' : 'text-gray-500 hover:text-black hover:bg-gray-50' }}"
                       title="Overview">
                        <div class="w-6 flex items-center justify-center">
                            <i class="fas fa-home text-xs transition-colors {{ request()->routeIs('/') ? 'text-gray-500' : 'text-gray-400 group-hover:text-black' }}"></i>
                        </div>
                        <span class="sidebar-text ml-3 font-medium text-xs tracking-wide uppercase whitespace-nowrap">Home</span>
                    </a>
                </li>

            </ul>
        </nav>
    </aside>

    <div class="flex-1 flex flex-col min-w-0">
        
        <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-8 z-10">
            <div class="flex items-center gap-4">
                
                <button onclick="toggleMobileSidebar()" class="md:hidden text-gray-500 hover:text-black transition-colors">
                    <i class="fas fa-bars fa-lg"></i>
                </button>

                <button onclick="toggleDesktopSidebar()" class="hidden md:block text-gray-400 hover:text-black transition-colors" title="Toggle Sidebar">
                    <i class="fas fa-outdent fa-lg" id="collapseIcon"></i>
                </button>

                <div class="text-xs font-medium text-gray-400 tracking-wider uppercase hidden sm:block ml-2">
                    Freelancer Panel
                </div>
            </div>
            
            <div class="flex items-center space-x-4 sm:space-x-6">
                <span class="text-xs font-medium text-gray-800">Freelancer</span>
                <div class="h-4 w-px bg-gray-300"></div>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="text-xs text-gray-500 hover:text-black font-medium transition-colors uppercase tracking-wider">
                        Logout
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 sm:p-8">
            <div class="max-w-6xl mx-auto">
                @yield('content')
            </div>
        </main>
        
    </div>

    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobileOverlay');
        const sidebarTexts = document.querySelectorAll('.sidebar-text');
        const collapseIcon = document.getElementById('collapseIcon');
        const brandLogo = document.getElementById('brandLogo');

        let isCollapsed = false;

        // Fungsi Buka/Tutup Sidebar di Mobile
        function toggleMobileSidebar() {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        // Fungsi Perkecil/Perbesar Sidebar di Desktop
        function toggleDesktopSidebar() {
            isCollapsed = !isCollapsed;
            
            if(isCollapsed) {
                // Mode Perkecil (Collapsed)
                sidebar.classList.remove('w-64');
                sidebar.classList.add('w-20');
                
                // Sembunyikan Teks
                sidebarTexts.forEach(el => el.classList.add('hidden'));
                
                // Ganti Icon Tombol & Logo
                collapseIcon.classList.remove('fa-outdent');
                collapseIcon.classList.add('fa-indent');
                brandLogo.innerText = 'EV.';
            } else {
                // Mode Perbesar (Expanded)
                sidebar.classList.remove('w-20');
                sidebar.classList.add('w-64');
                
                // Tampilkan Teks
                sidebarTexts.forEach(el => el.classList.remove('hidden'));
                
                // Kembalikan Icon Tombol & Logo
                collapseIcon.classList.remove('fa-indent');
                collapseIcon.classList.add('fa-outdent');
                brandLogo.innerText = 'everlast.';
            }
        }
    </script>
</body>
</html>