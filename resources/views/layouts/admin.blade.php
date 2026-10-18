<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Administrator — LMS Arunika')</title>
    <link rel="icon" href="/favicon.ico">
    <meta name="theme-color" content="#1A2E5E">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    class="h-full bg-slate-50 text-slate-800 font-sans antialiased selection:bg-[#2563EB] selection:text-white"
    x-data="{ 
        mobileSidebar: false,
        profileDropdownOpen: false
    }"
>
    @php
        $user = Auth::user();
        $currentRoute = request()->route()?->getName() ?? '';
        $activeTerm = \App\Models\Term::where('status', 'aktif')->orWhere('is_active', true)->first();
    @endphp

    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Sidebar: Lebar 240px, Navy Academic Theme (#1A2E5E) -->
        <aside 
            :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-[240px] bg-[#1A2E5E] text-white flex flex-col justify-between transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0 shrink-0 overflow-hidden shadow-lg md:shadow-none"
        >
            <div class="flex flex-col flex-1 overflow-y-auto px-4 py-5">
                <!-- Brand Header -->
                <div class="flex items-center justify-between pb-4 border-b border-white/10 mb-4">
                    <a href="{{ route('admin.dashboard') }}" class="group flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl bg-blue-500/20 border border-white/20 flex items-center justify-center font-bold text-lg text-white shadow-inner">
                            A
                        </div>
                        <div>
                            <span class="text-sm font-extrabold tracking-tight text-white block">ARUNIKA</span>
                            <span class="text-[10px] text-blue-200 block font-medium">Universitas Nusantara</span>
                        </div>
                    </a>
                    <button @click="mobileSidebar = false" class="md:hidden text-white/70 hover:text-white p-1 cursor-pointer">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Navigation Links -->
                <nav class="space-y-1 text-xs font-medium flex-1">
                    
                    <!-- 1. Beranda -->
                    <a 
                        href="{{ route('admin.dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ $currentRoute === 'admin.dashboard' ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="house" :size="18" class="shrink-0 {{ $currentRoute === 'admin.dashboard' ? 'text-white' : 'text-slate-300' }}" />
                        <span>Beranda</span>
                    </a>

                    <!-- 2. Semester -->
                    <a 
                        href="{{ route('admin.semester.index') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.semester') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="calendar" :size="18" class="shrink-0 {{ str_starts_with($currentRoute, 'admin.semester') ? 'text-white' : 'text-slate-300' }}" />
                        <span>Semester</span>
                    </a>

                    <!-- 3. Mata Kuliah -->
                    <a 
                        href="{{ route('admin.mata-kuliah.index') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.mata-kuliah') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="book-open" :size="18" class="shrink-0 {{ str_starts_with($currentRoute, 'admin.mata-kuliah') ? 'text-white' : 'text-slate-300' }}" />
                        <span>Mata Kuliah</span>
                    </a>

                    <!-- 4. Kelas -->
                    <a 
                        href="{{ route('admin.kelas.index') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.kelas') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="users" :size="18" class="shrink-0 {{ str_starts_with($currentRoute, 'admin.kelas') ? 'text-white' : 'text-slate-300' }}" />
                        <span>Kelas</span>
                    </a>

                    <!-- 5. Pendaftaran Mahasiswa -->
                    <a 
                        href="{{ route('admin.pendaftaran-mahasiswa.index') }}" 
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.pendaftaran-mahasiswa') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="clipboard-list" :size="18" class="shrink-0 {{ str_starts_with($currentRoute, 'admin.pendaftaran-mahasiswa') ? 'text-white' : 'text-slate-300' }}" />
                        <span>Pendaftaran Mahasiswa</span>
                    </a>

                    <!-- Section Divider: Pengguna & Sistem -->
                    <div class="pt-5 pb-2 px-3">
                        <span class="text-[10px] font-bold uppercase tracking-wider text-blue-200/60 block">
                            Pengguna & Sistem
                        </span>
                    </div>

                    <!-- 6. Pengguna: Dosen (Batch 2) -->
                    <a 
                        href="{{ route('admin.pengguna.dosen.index') }}" 
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.pengguna.dosen') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <div class="flex items-center gap-3">
                            <x-icon name="user-check" :size="18" class="shrink-0 text-slate-300" />
                            <span>Pengguna: Dosen</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white/10 text-blue-200">B2</span>
                    </a>

                    <!-- 7. Pengguna: Mahasiswa (Batch 2) -->
                    <a 
                        href="{{ route('admin.pengguna.mahasiswa.index') }}" 
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.pengguna.mahasiswa') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <div class="flex items-center gap-3">
                            <x-icon name="academic-cap" :size="18" class="shrink-0 text-slate-300" />
                            <span>Pengguna: Mahasiswa</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white/10 text-blue-200">B2</span>
                    </a>

                    <!-- 8. Log Aktivitas (Batch 2) -->
                    <a 
                        href="{{ route('admin.log-aktivitas.index') }}" 
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.log-aktivitas') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <div class="flex items-center gap-3">
                            <x-icon name="history" :size="18" class="shrink-0 text-slate-300" />
                            <span>Log Aktivitas</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white/10 text-blue-200">B2</span>
                    </a>

                    <!-- 9. Pengaturan Sistem (Batch 2) -->
                    <a 
                        href="{{ route('admin.pengaturan.edit') }}" 
                        class="flex items-center justify-between px-3 py-2.5 rounded-xl transition-all duration-150 {{ str_starts_with($currentRoute, 'admin.pengaturan') ? 'bg-[#2563EB] text-white font-bold shadow-xs' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}"
                    >
                        <div class="flex items-center gap-3">
                            <x-icon name="cog" :size="18" class="shrink-0 text-slate-300" />
                            <span>Pengaturan</span>
                        </div>
                        <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-white/10 text-blue-200">B2</span>
                    </a>
                </nav>
            </div>

            <!-- Sidebar User Profile Footer -->
            <div class="p-3 border-t border-white/10 bg-[#142347]">
                <div class="flex items-center justify-between gap-2 p-2 rounded-xl">
                    <div class="flex items-center gap-2.5 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-bold text-xs flex items-center justify-center shrink-0 shadow-xs">
                            {{ strtoupper(substr($user->name ?? 'Admin', 0, 2)) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-bold text-white truncate leading-tight">{{ $user->name ?? 'Administrator' }}</p>
                            <p class="text-[10px] text-blue-200 truncate font-medium">Administrator</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Keluar dari sistem" class="p-1.5 text-white/60 hover:text-rose-400 hover:bg-white/10 rounded-lg transition-colors cursor-pointer">
                            <x-icon name="arrow-right-on-rectangle" :size="16" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar Header -->
            <header class="sticky top-0 z-30 bg-white border-b border-slate-200/80 px-4 sm:px-6 py-3 flex items-center justify-between gap-4 shadow-2xs">
                
                <div class="flex items-center gap-3 min-w-0">
                    <!-- Hamburger Mobile -->
                    <button 
                        @click="mobileSidebar = true" 
                        class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 cursor-pointer"
                        aria-label="Buka menu navigasi"
                    >
                        <x-icon name="bars-3" :size="20" />
                    </button>

                    <!-- Title / Breadcrumb -->
                    <div class="min-w-0">
                        <h1 class="text-base sm:text-lg font-bold text-[#1A2E5E] truncate leading-tight">
                            @yield('page_title', 'Dasbor Administrator')
                        </h1>
                        <p class="text-xs text-slate-500 hidden sm:block truncate">
                            @yield('page_subtitle', 'Portal Pengelolaan Akademik Kampus')
                        </p>
                    </div>
                </div>

                <!-- Right Section: Global Search & Indicators -->
                <div class="flex items-center gap-3">
                    
                    <!-- Global Search Form -->
                    <form action="{{ route('admin.cari') }}" method="GET" class="hidden sm:block relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ request('q') }}"
                            placeholder="Cari kelas, mata kuliah, dosen..." 
                            class="w-56 lg:w-72 pl-9 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                        />
                        <x-icon name="magnifying-glass" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
                    </form>

                    <!-- Semester Indicator Badge -->
                    @if ($activeTerm)
                        <div class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-800 text-xs font-semibold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="truncate max-w-[180px]">{{ $activeTerm->name }}</span>
                        </div>
                    @endif

                    <!-- User Profile Dropdown Button -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            @click.outside="open = false" 
                            class="flex items-center gap-2 p-1 rounded-xl hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                            <div class="w-8 h-8 rounded-full bg-[#1A2E5E] text-white font-bold text-xs flex items-center justify-center">
                                {{ strtoupper(substr($user->name ?? 'A', 0, 2)) }}
                            </div>
                            <x-icon name="chevron-down" :size="12" class="text-slate-500 hidden sm:block" />
                        </button>

                        <div 
                            x-show="open" 
                            x-cloak 
                            x-transition:enter="transition ease-out duration-100"
                            x-transition:enter-start="transform opacity-0 scale-95"
                            x-transition:enter-end="transform opacity-100 scale-100"
                            x-transition:leave="transition ease-in duration-75"
                            x-transition:leave-start="transform opacity-100 scale-100"
                            x-transition:leave-end="transform opacity-0 scale-95"
                            class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-lg border border-slate-200/80 py-2 z-50"
                        >
                            <div class="px-4 py-2 border-b border-slate-100">
                                <p class="text-xs font-bold text-slate-800 truncate">{{ $user->name ?? 'Administrator' }}</p>
                                <p class="text-[11px] text-slate-500 truncate">{{ $user->email ?? 'admin@arunika.test' }}</p>
                            </div>
                            <a href="{{ route('admin.pengaturan.edit') }}" class="flex items-center gap-2 px-4 py-2 text-xs text-slate-700 hover:bg-slate-50">
                                <x-icon name="cog" :size="14" class="text-slate-400" />
                                <span>Pengaturan Sistem</span>
                            </a>
                            <div class="border-t border-slate-100 my-1"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left flex items-center gap-2 px-4 py-2 text-xs text-rose-600 hover:bg-rose-50 cursor-pointer">
                                    <x-icon name="arrow-right-on-rectangle" :size="14" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>

            </header>

            <!-- Main Page Content Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                
                <!-- Session Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                        <x-icon name="check-circle" :size="20" class="shrink-0 text-emerald-600 mt-0.5" />
                        <div class="flex-1 font-medium">{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                        <x-icon name="exclamation-circle" :size="20" class="shrink-0 text-rose-600 mt-0.5" />
                        <div class="flex-1 font-medium">{{ session('error') }}</div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 text-xs sm:text-sm flex items-start gap-3 shadow-2xs">
                        <x-icon name="exclamation-triangle" :size="20" class="shrink-0 text-amber-600 mt-0.5" />
                        <div class="flex-1 font-medium">{{ session('warning') }}</div>
                    </div>
                @endif

                @yield('content')

            </main>

            <!-- Admin Footer -->
            <footer class="py-4 px-6 border-t border-slate-200/80 text-center text-xs text-slate-400 bg-white">
                &copy; {{ date('Y') }} LMS Arunika — Biro Administrasi Akademik & Kemahasiswaan (BAAK) Universitas Nusantara Indonesia.
            </footer>

        </div>

    </div>

    @stack('scripts')
</body>
</html>
