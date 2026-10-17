<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Dosen — Arunika Learning System')</title>
    <link rel="icon" href="/favicon.ico">
    <meta name="theme-color" content="#1A2E5E">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    class="h-full bg-canvas text-text font-sans antialiased selection:bg-accent selection:text-white" 
    x-data="{ 
        mobileSidebar: false,
        notificationsOpen: false,
        profileDropdownOpen: false,
        sidebarProfileOpen: false,
        searchQuery: ''
    }"
>
    @php
        $user = Auth::user();
        $currentRoute = request()->route()?->getName();
        // Dynamic badge count: pengumpulan yang belum dinilai milik kelas dosen
        $pendingSubmissionsCount = 0;
        $unreadNotificationsCount = 0;
        $recentNotifications = collect();
        if ($user && $user->role === \App\Enums\UserRole::Lecturer) {
            $pendingSubmissionsCount = \App\Models\Submission::whereNull('score')
                ->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $user->id))
                ->count();
            $unreadNotificationsCount = $user->unreadNotifications()->count();
            $recentNotifications = $user->notifications()->take(5)->get();
        }
    @endphp

    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Sidebar: Lebar 234px, Latar Navy-900 (#1A2E5E) -->
        <aside 
            :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-[234px] bg-navy-900 border-r border-navy-900 flex flex-col justify-between transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0 shrink-0 overflow-hidden"
        >
            <div class="flex flex-col flex-1 overflow-y-auto px-3.5 py-4">
                <!-- Logo & Brand Wordmark -->
                <div class="flex items-center justify-between px-2 pb-4 border-b border-white/10 mb-3">
                    <a href="{{ route('dosen.dashboard') }}" class="group inline-flex items-center">
                        <x-logo variant="inverse" />
                    </a>
                    <button @click="mobileSidebar = false" class="md:hidden text-white/70 hover:text-white text-base cursor-pointer">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Navigasi Utama Dosen (9 Menu Berurutan Sesuai Desain Acuan) -->
                <nav class="space-y-1 text-[13px] font-normal flex-1">
                    
                    <!-- 1. Beranda -->
                    <a 
                        href="{{ route('dosen.dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'dosen.dashboard' ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="house" :size="18" class="shrink-0 {{ $currentRoute === 'dosen.dashboard' ? 'text-white' : 'text-white/80' }}" />
                        <span>Beranda</span>
                    </a>

                    <!-- 2. Kelas Saya -->
                    <a 
                        href="{{ route('dosen.kelas.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.kelas') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="users" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.kelas') ? 'text-white' : 'text-white/80' }}" />
                        <span>Kelas Saya</span>
                    </a>

                    <!-- 3. Materi Pembelajaran -->
                    <a 
                        href="{{ route('dosen.materi.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.materi') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="folder-open" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.materi') ? 'text-white' : 'text-white/80' }}" />
                        <span>Materi Pembelajaran</span>
                    </a>

                    <!-- 4. Tugas & Ujian -->
                    <div x-data="{ openSubmenu: {{ str_starts_with($currentRoute ?? '', 'dosen.tugas') ? 'true' : 'false' }} }">
                        <a 
                            href="{{ route('dosen.tugas.index') }}" 
                            class="flex items-center justify-between px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.tugas') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                        >
                            <div class="flex items-center gap-3">
                                <x-icon name="clipboard-list" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.tugas') ? 'text-white' : 'text-white/80' }}" />
                                <span>Tugas & Ujian</span>
                            </div>
                            <span @click.prevent="openSubmenu = !openSubmenu" class="text-white/60 hover:text-white p-0.5">
                                <x-icon name="chevron-down" :size="14" class="transition-transform duration-200" ::class="openSubmenu ? 'rotate-180' : ''" />
                            </span>
                        </a>

                        <!-- Submenu Tugas & Ujian -->
                        <div x-show="openSubmenu" x-cloak class="pl-9 pr-2 py-1 space-y-1 text-xs text-white/70">
                            <a href="{{ route('dosen.tugas.index') }}" class="block py-1 hover:text-white {{ $currentRoute === 'dosen.tugas.index' ? 'text-white font-semibold' : '' }}">
                                + Semua Assessment
                            </a>
                            <a href="{{ route('dosen.tugas.create') }}" class="block py-1 hover:text-white {{ $currentRoute === 'dosen.tugas.create' ? 'text-white font-semibold' : '' }}">
                                + Buat Tugas / Ujian
                            </a>
                            <span class="block py-1 text-white/40 cursor-not-allowed" title="Fitur Bank Soal masuk backlog pengembangan">
                                + Bank Soal (Backlog)
                            </span>
                        </div>
                    </div>

                    <!-- 5. Penilaian (Dengan Badge Dinamis) -->
                    <a 
                        href="{{ route('dosen.penilaian.index') }}" 
                        class="flex items-center justify-between px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.penilaian') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <div class="flex items-center gap-3">
                            <x-icon name="clipboard-check" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.penilaian') ? 'text-white' : 'text-white/80' }}" />
                            <span>Penilaian</span>
                        </div>
                        @if ($pendingSubmissionsCount > 0)
                            <span class="px-1.5 py-0.5 text-[10px] font-bold rounded-full bg-red-500 text-white leading-none">
                                {{ $pendingSubmissionsCount }}
                            </span>
                        @endif
                    </a>

                    <!-- 6. Presensi (Batch 2) -->
                    <a 
                        href="{{ route('dosen.presensi.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.presensi') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="calendar-check" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.presensi') ? 'text-white' : 'text-white/80' }}" />
                        <span>Presensi</span>
                    </a>

                    <!-- 7. Buku Nilai (Batch 2) -->
                    <a 
                        href="{{ route('dosen.buku-nilai.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.buku-nilai') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="bar-chart-3" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.buku-nilai') ? 'text-white' : 'text-white/80' }}" />
                        <span>Buku Nilai</span>
                    </a>

                    <!-- 8. Diskusi (Batch 2) -->
                    <a 
                        href="{{ route('dosen.diskusi.index') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.diskusi') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="message-circle" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.diskusi') ? 'text-white' : 'text-white/80' }}" />
                        <span>Diskusi</span>
                    </a>

                    <!-- 9. Pengaturan (Batch 2) -->
                    <a 
                        href="{{ route('dosen.pengaturan.edit') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ str_starts_with($currentRoute ?? '', 'dosen.pengaturan') ? 'bg-[#2563EB] text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="settings" :size="18" class="shrink-0 {{ str_starts_with($currentRoute ?? '', 'dosen.pengaturan') ? 'text-white' : 'text-white/80' }}" />
                        <span>Pengaturan</span>
                    </a>
                </nav>

                <!-- Blok Institusi Kampus -->
                <div class="mt-4 pt-4 border-t border-white/10">
                    <div class="p-3 rounded-[6px] border border-white/15 bg-white/5 flex items-start gap-2.5">
                        <x-icon name="landmark" :size="20" class="text-white shrink-0 mt-0.5" />
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-white truncate leading-tight">
                                {{ $user?->campus?->name ?? 'Universitas Nusantara Indonesia' }}
                            </p>
                            <p class="text-[10px] text-white/60 truncate mt-0.5">
                                Maju Bersama Ilmu
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profil Dosen Sidebar Bawah -->
            <div class="p-3 border-t border-white/10 bg-navy-900/60 relative">
                <div 
                    @click="sidebarProfileOpen = !sidebarProfileOpen" 
                    class="flex items-center gap-2.5 p-1.5 rounded-[6px] hover:bg-white/10 transition-colors cursor-pointer select-none"
                >
                    <img 
                        src="{{ $user?->avatar_path ?? '/images/avatar-budi.jpg' }}" 
                        alt="{{ $user?->name ?? 'Dr. Budi Rahardjo' }}" 
                        class="w-8 h-8 rounded-full object-cover shrink-0 border border-white/20"
                        onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=Budi+Rahardjo&background=2563EB&color=fff';"
                    >
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-medium text-white truncate leading-tight">
                            {{ $user?->name ?? 'Dr. Budi Rahardjo' }}
                        </p>
                        <p class="text-[10px] text-white/60 truncate mt-0.5">
                            Dosen
                        </p>
                    </div>
                    <x-icon name="chevron-down" :size="14" class="text-white/60 shrink-0" />
                </div>

                <!-- Dropdown Profil Sidebar -->
                <div 
                    x-show="sidebarProfileOpen" 
                    @click.away="sidebarProfileOpen = false" 
                    x-cloak 
                    class="absolute bottom-16 left-3 right-3 bg-surface rounded-[8px] border border-line shadow-lg py-1.5 z-50 text-xs text-text"
                >
                    <a href="{{ route('dosen.pengaturan.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition-colors">
                        <x-icon name="user" :size="14" class="text-text-muted" />
                        <span>Profil Saya</span>
                    </a>
                    <a href="{{ route('dosen.pengaturan.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition-colors">
                        <x-icon name="settings" :size="14" class="text-text-muted" />
                        <span>Pengaturan</span>
                    </a>
                    <hr class="my-1 border-line">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer">
                            <x-icon name="log-out" :size="14" />
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0">
            
            <!-- Topbar (Putih Bersih, Border Bawah) -->
            <header class="sticky top-0 z-30 bg-surface border-b border-line px-4 sm:px-6 py-2.5 flex items-center justify-between gap-4">
                
                <!-- Left: Breadcrumb Dinamis -->
                <div class="flex items-center gap-2 text-xs">
                    <button @click="mobileSidebar = true" class="md:hidden text-text p-1 hover:bg-slate-100 rounded-[4px] cursor-pointer mr-1">
                        <x-icon name="menu" :size="18" />
                    </button>

                    <a href="{{ route('dosen.dashboard') }}" class="text-text-muted hover:text-text font-medium transition-colors">
                        Beranda
                    </a>
                    @hasSection('page_breadcrumb')
                        <x-icon name="chevron-right" :size="12" class="text-text-subtle" />
                        <span class="text-text font-semibold truncate">
                            @yield('page_breadcrumb')
                        </span>
                    @endif
                </div>

                <!-- Right: Search Input + Notifikasi + User Profile -->
                <div class="flex items-center gap-3">
                    
                    <!-- Search Input -->
                    <div class="relative hidden sm:block w-72 md:w-80">
                        <x-icon name="search" :size="14" class="absolute left-3 top-1/2 -translate-y-1/2 text-text-subtle pointer-events-none" />
                        <input 
                            type="text" 
                            placeholder="Cari materi, tugas, atau mata kuliah..." 
                            class="w-full pl-8 pr-3 py-1.5 text-xs rounded-[6px] border border-line bg-canvas/60 text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB] focus:bg-surface transition-colors"
                        >
                    </div>

                    <!-- Notifikasi Lonceng -->
                    <div class="relative" x-data="{ open: false }">
                        <button 
                            @click="open = !open" 
                            type="button" 
                            class="w-8 h-8 rounded-full flex items-center justify-center text-text-muted hover:text-text hover:bg-slate-100 transition-colors relative cursor-pointer"
                        >
                            <x-icon name="bell" :size="18" />
                            @if ($unreadNotificationsCount > 0)
                                <span class="absolute top-1.5 right-1.5 min-w-[14px] h-[14px] px-1 rounded-full bg-red-500 text-[9px] font-bold text-white flex items-center justify-center leading-none">
                                    {{ $unreadNotificationsCount }}
                                </span>
                            @endif
                        </button>

                        <!-- Popover Dropdown Notifikasi -->
                        <div 
                            x-show="open" 
                            @click.away="open = false" 
                            x-cloak 
                            class="absolute right-0 mt-2 w-80 bg-surface rounded-[8px] border border-line shadow-lg py-2 z-50 text-xs"
                        >
                            <div class="px-3 pb-2 border-b border-line flex items-center justify-between">
                                <span class="font-bold text-text">Notifikasi</span>
                                @if ($unreadNotificationsCount > 0)
                                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-red-50 text-red-600 font-semibold">{{ $unreadNotificationsCount }} Baru</span>
                                @endif
                            </div>
                            <div class="divide-y divide-line/60 max-h-72 overflow-y-auto">
                                @forelse ($recentNotifications as $notif)
                                    <div class="p-3 hover:bg-slate-50 transition-colors cursor-pointer {{ $notif->read_at ? 'opacity-70' : 'bg-blue-50/20' }}">
                                        <p class="font-medium text-text leading-snug">{{ $notif->data['title'] ?? 'Pemberitahuan Sistem' }}</p>
                                        <p class="text-[11px] text-text-muted mt-0.5">{{ $notif->data['message'] ?? '' }}</p>
                                        <span class="text-[10px] text-text-subtle mt-1 block">{{ $notif->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') }}</span>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-text-muted text-xs">
                                        Tidak ada notifikasi baru
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- User Avatar & Dropdown -->
                    <div class="relative" x-data="{ openProfile: false }">
                        <button 
                            @click="openProfile = !openProfile" 
                            type="button" 
                            class="flex items-center gap-2 pl-2 pr-1 py-1 rounded-[6px] hover:bg-slate-100 transition-colors cursor-pointer"
                        >
                            <img 
                                src="{{ $user?->avatar_path ?? '/images/avatar-budi.jpg' }}" 
                                alt="{{ $user?->name ?? 'Dr. Budi Rahardjo' }}" 
                                class="w-7 h-7 rounded-full object-cover border border-line"
                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name=Budi+Rahardjo&background=2563EB&color=fff';"
                            >
                            <span class="text-xs font-semibold text-text hidden sm:inline-block">
                                {{ $user?->name ?? 'Dr. Budi Rahardjo' }}
                            </span>
                            <x-icon name="chevron-down" :size="12" class="text-text-subtle" />
                        </button>

                        <div 
                            x-show="openProfile" 
                            @click.away="openProfile = false" 
                            x-cloak 
                            class="absolute right-0 mt-2 w-48 bg-surface rounded-[8px] border border-line shadow-lg py-1.5 z-50 text-xs text-text"
                        >
                            <div class="px-3 py-2 border-b border-line">
                                <p class="font-bold text-text truncate">{{ $user?->name ?? 'Dr. Budi Rahardjo' }}</p>
                                <p class="text-[11px] text-text-muted truncate">{{ $user?->email ?? 'dosen@arunika.test' }}</p>
                            </div>
                            <a href="{{ route('dosen.pengaturan.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition-colors">
                                <x-icon name="user" :size="14" class="text-text-muted" />
                                <span>Profil</span>
                            </a>
                            <a href="{{ route('dosen.pengaturan.edit') }}" class="flex items-center gap-2 px-3 py-2 hover:bg-slate-50 transition-colors">
                                <x-icon name="settings" :size="14" class="text-text-muted" />
                                <span>Pengaturan</span>
                            </a>
                            <hr class="my-1 border-line">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer">
                                    <x-icon name="log-out" :size="14" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </header>

            <!-- Main Page Content Container -->
            <main class="flex-1 p-4 sm:p-6 lg:p-7 max-w-7xl w-full mx-auto">
                <!-- Flash Message Alerts -->
                @if (session('success'))
                    <div class="mb-5 p-3.5 px-4 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="check-circle" :size="16" class="text-emerald-600 shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-5 p-3.5 px-4 rounded-[8px] bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between gap-3 shadow-2xs">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="x" :size="16" class="text-red-600 shrink-0" />
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
