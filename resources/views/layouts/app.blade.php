<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Sistem RAB') - PT SBK</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style type="text/tailwindcss">
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .sidebar-link { @apply flex items-center py-2.5 px-4 rounded-lg transition duration-200; }
        .sidebar-link.active { @apply bg-emerald-500/20 text-emerald-400 font-bold border-l-2 border-emerald-400; }
        .sidebar-link:not(.active) { @apply hover:bg-white/5 text-slate-300; }
        .sticky-table thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: #f9fafb;
            box-shadow: inset 0 -1px 0 #e5e7eb;
        }

        /* Sidebar Collapsed States */
        body.sidebar-collapsed #sidebar { width: 5.5rem; }
        body.sidebar-collapsed .sidebar-text { display: none; }
        body.sidebar-collapsed .sidebar-title { display: none; }
        body.sidebar-collapsed .sidebar-link { justify-content: center; padding-left: 0; padding-right: 0; }
        body.sidebar-collapsed .sidebar-link svg.sidebar-icon { margin-right: 0; }

        body.sidebar-collapsed .sidebar-header { padding: 1rem 0.5rem; }
        body.sidebar-collapsed .logo-container { padding: 0.5rem; width: 100%; display: flex; justify-content: center; margin-top: 0; }
        body.sidebar-collapsed .logo-img { height: auto; width: 100%; max-width: 3.5rem; }
        body.sidebar-collapsed #sidebarToggleIcon { transform: rotate(180deg); }

        body.sidebar-collapsed .logout-text { display: none; }
        body.sidebar-collapsed .logout-btn { padding-left: 0; padding-right: 0; justify-content: center; }
        body.sidebar-collapsed .logout-btn svg { margin-right: 0; }

        @media (max-width: 768px) {
            body { @apply flex-col h-auto min-h-screen overflow-auto; }
            #sidebar { @apply w-full flex-shrink-0; }
            #sidebarToggleBtn { @apply hidden; }
            .sidebar-header { @apply py-4; }
            .logo-img { @apply h-12; }
            nav { @apply mt-3 px-3; }
            nav ul { @apply grid grid-cols-2 gap-2; }
            .sidebar-link { @apply justify-center text-center px-3 py-2; }
            .sidebar-link svg.sidebar-icon { @apply mr-2; }
            .logout-btn { @apply py-2; }
            header { @apply h-auto min-h-16 px-4 py-3 items-start gap-3; }
            header > div:last-child { @apply space-x-2; }
            main { @apply p-4; }
            body.sidebar-collapsed #sidebar { width: 100%; }
            body.sidebar-collapsed .sidebar-text,
            body.sidebar-collapsed .sidebar-title,
            body.sidebar-collapsed .logout-text { display: inline; }
        }
    </style>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.13/cropper.min.css" rel="stylesheet">
    <style>
        .avatar-preview { width: 100px; height: 100px; overflow: hidden; border-radius: 50%; border: 3px solid #10b981; }
    </style>
    @stack('styles')
</head>
<body class="bg-[#F0F2F5] flex h-screen overflow-hidden">
    <script>
        if(localStorage.getItem('sidebar-collapsed') === 'true') {
            document.body.classList.add('sidebar-collapsed');
        }
    </script>

    {{-- ========== SIDEBAR ========== --}}
    <aside id="sidebar" class="w-64 bg-[#1E293B] text-white flex flex-col justify-between shadow-xl z-30 flex-shrink-0 transition-all duration-300 ease-in-out relative">
        {{-- Toggle Button --}}
        <button type="button" id="sidebarToggleBtn" class="absolute -right-6 top-5 bg-blue-500 hover:bg-blue-600 text-white w-6 h-10 flex items-center justify-center rounded-r-lg shadow-md cursor-pointer transition-colors z-[40]">
            <svg id="sidebarToggleIcon" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
            </svg>
        </button>

        <div>
            {{-- Logo Area --}}
            <div class="sidebar-header border-b border-white/10 flex items-center justify-center py-6 px-4 transition-all duration-300">
                <div class="bg-white rounded-xl p-3 inline-block shadow-lg logo-container transition-all duration-300">
                    <img src="{{ asset('foto/logo_sbk.png') }}" alt="Logo SBK" class="h-16 w-auto block logo-img transition-all duration-300 object-contain">
                </div>
            </div>

            {{-- Navigation --}}
            <nav class="mt-6 px-4">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-3 px-4 sidebar-title">Menu Utama</p>
                <ul class="space-y-1 text-sm font-medium">
                    @yield('sidebar-menu')
                </ul>
            </nav>
        </div>

        {{-- Logout --}}
        <div class="p-6">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="w-full bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white font-bold py-2.5 px-4 rounded-lg text-center transition duration-300 flex items-center justify-center logout-btn" title="Logout">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                    <span class="logout-text">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    {{-- ========== MAIN CONTENT ========== --}}
    <div class="flex-1 flex flex-col relative overflow-hidden">
        {{-- Header --}}
        <header class="bg-white h-20 border-b border-gray-200 flex items-center justify-between px-8 shadow-sm z-10 flex-shrink-0 transition-all duration-300">
            <div class="flex items-center gap-4">
                <div>
                    <h1 class="text-xl font-extrabold text-gray-800">@yield('page-title', 'Dashboard')</h1>
                    <p class="text-sm text-gray-500 font-medium hidden sm:block">@yield('page-subtitle', 'Aplikasi Rancangan Anggaran Biaya')</p>
                </div>
            </div>
            <div class="flex items-center space-x-6">
                <div class="text-right border-r border-gray-300 pr-6 hidden md:block">
                    @php
                        \Carbon\Carbon::setLocale('id');
                        $hour = \Carbon\Carbon::now()->timezone('Asia/Jakarta')->hour;
                        if ($hour < 11) {
                            $greeting = 'Selamat Pagi, Semangat!';
                        } elseif ($hour < 15) {
                            $greeting = 'Selamat Siang, Tetap Produktif!';
                        } elseif ($hour < 18) {
                            $greeting = 'Selamat Sore, Hampir Selesai!';
                        } else {
                            $greeting = 'Selamat Malam, Selamat Istirahat!';
                        }
                    @endphp
                    <p class="text-sm font-bold text-gray-800">{{ \Carbon\Carbon::now()->timezone('Asia/Jakarta')->isoFormat('dddd, D MMMM YYYY') }}</p>
                    <p class="text-xs text-emerald-600 font-medium mt-0.5">{{ $greeting }}</p>
                </div>
                @php
                    $rabNotifications = Auth::user()
                        ? Auth::user()->rabNotifications()->with('rab')->latest()->limit(8)->get()
                        : collect();
                    $unreadRabNotifications = Auth::user()
                        ? Auth::user()->rabNotifications()->unread()->count()
                        : 0;
                @endphp
                <div class="relative group">
                    <button type="button" class="relative h-10 w-10 rounded-xl border border-gray-200 bg-white text-gray-600 hover:bg-gray-50 hover:text-emerald-600 transition flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0m6 0H9"/>
                        </svg>
                        @if($unreadRabNotifications > 0)
                        <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-extrabold flex items-center justify-center">
                            {{ $unreadRabNotifications > 9 ? '9+' : $unreadRabNotifications }}
                        </span>
                        @endif
                    </button>
                    {{-- Wrapper luar menggunakan pt-2 (padding-top) sebagai jembatan hover transparan --}}
<div class="absolute right-0 top-full pt-2 w-80 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 origin-top-right">

    {{-- Kotak putih (visual) dipindahkan ke dalam wrapper --}}
    <div class="bg-white rounded-xl shadow-xl border border-gray-100 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100">
            <p class="text-sm font-extrabold text-gray-800">Notifikasi RAB</p>
            <p class="text-xs text-gray-500">{{ $unreadRabNotifications }} belum dibaca</p>
        </div>
        <div class="max-h-96 overflow-y-auto">
            @forelse($rabNotifications as $notification)
            <a href="{{ route('rab.notifications.open', $notification) }}" class="block px-4 py-3 border-b border-gray-50 hover:bg-emerald-50 transition {{ $notification->read_at ? 'bg-white' : 'bg-emerald-50/60' }}">
                <div class="flex gap-3">
                    <span class="mt-1 h-2 w-2 rounded-full flex-shrink-0 {{ $notification->read_at ? 'bg-gray-300' : 'bg-emerald-500' }}"></span>
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-gray-800 truncate">{{ $notification->title }}</p>
                        <p class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $notification->message }}</p>
                        <p class="text-[11px] text-gray-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                </div>
            </a>
            @empty
            <div class="px-4 py-8 text-center text-sm text-gray-400">Belum ada notifikasi.</div>
            @endforelse
        </div>
    </div>
</div>
                </div>
                <div class="relative group">
                    <button type="button" class="flex items-center space-x-3 hover:bg-gray-50 px-3 py-2 rounded-xl transition focus:outline-none">
                        <div class="text-right hidden sm:block">
                            <p class="text-sm font-bold text-gray-800">{{ Auth::user()->name ?? 'User' }}</p>
                            <span class="bg-emerald-100 text-emerald-700 text-[10px] font-extrabold px-2 py-0.5 rounded uppercase tracking-wider">
                                {{ Auth::user()->role->label() ?? 'User' }}
                            </span>
                        </div>
                        @if(Auth::user()->avatar)
                        <div class="avatar-clickable">
                        <img src="{{ asset('storage/' . Auth::user()->avatar) }}" alt="{{ Auth::user()->name }}" class="h-10 w-10 rounded-full object-cover border-2 border-emerald-300 shadow-md">
                        </div>
                        @else
                        @php
                            $name = Auth::user()->name ?? 'U';
                            $initials = collect(explode(' ', $name))->map(fn($n) => substr($n, 0, 1))->take(2)->implode('');
                        @endphp
                        <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white font-bold border-2 border-emerald-300 shadow-md">
                            {{ strtoupper($initials) }}
                        </div>
                        @endif
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="absolute right-0 top-full mt-1 w-48 bg-white rounded-xl shadow-lg border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50 origin-top-right">
                        <div class="p-2 space-y-1">
                            <button type="button" onclick="document.getElementById('profileModal').style.display='flex'" class="w-full text-left flex items-center px-3 py-2 text-sm text-gray-700 hover:bg-emerald-50 hover:text-emerald-600 rounded-lg transition">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit Profil
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Flash Messages --}}
        @if(session('success') && !session('submitted_rab_id'))
        <div id="globalSuccessModal" class="fixed inset-0 bg-black/50 z-[100] flex items-center justify-center">
            <div class="bg-white rounded-2xl p-8 max-w-sm w-full mx-4 shadow-2xl text-center animate-fade-in">
                <div class="w-16 h-16 rounded-full bg-emerald-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h3 class="text-xl font-extrabold text-gray-800 mb-2">Berhasil!</h3>
                <p class="text-sm text-gray-600 mb-6">{{ session('success') }}</p>
                <button onclick="document.getElementById('globalSuccessModal').style.display='none'" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-6 py-3 rounded-xl text-sm font-bold transition">
                    Tutup
                </button>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="mx-8 mt-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-sm font-medium flex items-center">
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
            {{ session('error') }}
        </div>
        @endif

        {{-- Page Content --}}
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-[#F0F2F5] p-8">
            @yield('content')
        </main>
    </div>

    <script>
        // Auto-hide success modal after 5 seconds if not closed manually
        setTimeout(() => {
            const modal = document.getElementById('globalSuccessModal');
            if (modal) modal.style.display = 'none';
        }, 5000);
    </script>

    {{-- Global Profile Modal --}}
    @include('profile._modal')

    {{-- Global Image Lightbox --}}
    <div id="imageLightbox" class="fixed inset-0 bg-black/80 z-[200] items-center justify-center hidden backdrop-blur-sm cursor-pointer" onclick="this.style.display='none'">
        <button onclick="document.getElementById('imageLightbox').style.display='none'" class="absolute top-6 right-6 text-white/80 hover:text-white transition z-10">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
        <img id="lightboxImage" src="" class="max-w-[90vw] max-h-[90vh] object-contain rounded-2xl shadow-2xl" onclick="event.stopPropagation()">
    </div>

    <script>
        // Hamburger Menu Toggle
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const body = document.body;

            toggleBtn.addEventListener('click', function() {
                body.classList.toggle('sidebar-collapsed');
                // Save state
                localStorage.setItem('sidebar-collapsed', body.classList.contains('sidebar-collapsed'));
            });
        });

        // Global lightbox for any clickable avatar
        function openImageLightbox(imgSrc) {
            const lightbox = document.getElementById('imageLightbox');
            const lightboxImg = document.getElementById('lightboxImage');
            if (lightbox && lightboxImg && imgSrc) {
                lightboxImg.src = imgSrc;
                lightbox.style.display = 'flex';
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-attach to all elements with .avatar-clickable
            document.querySelectorAll('.avatar-clickable').forEach(el => {
                el.style.cursor = 'pointer';
                el.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const img = this.querySelector('img');
                    if (img && img.src) {
                        openImageLightbox(img.src);
                    }
                });
            });

            // Close lightbox with Escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.getElementById('imageLightbox').style.display = 'none';
                }
            });

            // Session Keep-Alive ping to prevent CSRF timeout (every 5 minutes)
            setInterval(function() {
                fetch('/')
                    .then(response => {
                        console.log('Session refreshed');
                    })
                    .catch(error => {
                        console.warn('Session refresh failed', error);
                    });
            }, 300000); // 5 minutes
        });
    </script>

    @stack('scripts')
</body>
</html>
