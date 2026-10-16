<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arunika — Learning Operating System')</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1A2E5E">

    <!-- Theme initialization (Default Light Theme as per Arunika design guidelines) -->
    <script>
        if (localStorage.getItem('theme') === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body 
    class="h-full bg-canvas text-text font-sans antialiased selection:bg-accent selection:text-white" 
    x-data="{ 
        aiDrawerOpen: false, 
        aiPrompt: '', 
        aiContext: 'Pemrograman Berorientasi Objek & Desain Pola',
        aiMode: 'chat',
        aiResponse: '', 
        aiLoading: false, 
        socraticMode: true, 
        aiMessages: [
            {
                role: 'ai',
                text: 'Halo Arya! Saya Arunika AI Copilot, asisten belajar cerdas Anda. Ada konsep materi kuliah, tugas, atau capaian CPL yang ingin Anda diskusikan?',
                time: 'Baru saja'
            }
        ],
        aiSuggestions: [
            'Jelaskan Prinsip Single Responsibility (SRP)',
            'Apa perbedaan Abstraksi dan Interface?',
            'Buatkan 3 kartu flashcard materi ini',
            'Rangkum capaian CPL-02 mata kuliah ini'
        ],
        sendAiMessage(promptText, mode = null) {
            const text = promptText || this.aiPrompt;
            if (!text || !text.trim() || this.aiLoading) return;
            
            const activeMode = mode || this.aiMode || 'chat';
            
            this.aiMessages.push({
                role: 'user',
                text: text,
                time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
            });
            
            this.aiPrompt = '';
            this.aiLoading = true;
            this.aiDrawerOpen = true;
            
            this.$nextTick(() => {
                const el = document.getElementById('aiChatContainer');
                if (el) el.scrollTop = el.scrollHeight;
            });

            fetch('{{ route('copilot.ask') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    prompt: text,
                    socratic: this.socraticMode,
                    context: this.aiContext,
                    mode: activeMode
                })
            })
            .then(res => res.json())
            .then(data => {
                this.aiLoading = false;
                this.aiResponse = data.response;
                this.aiMessages.push({
                    role: 'ai',
                    text: data.response,
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }),
                    provider: data.provider,
                    xp: data.xp_awarded
                });
                if (data.suggested_prompts && data.suggested_prompts.length) {
                    this.aiSuggestions = data.suggested_prompts;
                }
                this.$nextTick(() => {
                    const el = document.getElementById('aiChatContainer');
                    if (el) el.scrollTop = el.scrollHeight;
                });
            })
            .catch(() => {
                this.aiLoading = false;
                this.aiMessages.push({
                    role: 'ai',
                    text: 'Mohon maaf, terjadi kendala jaringan saat menghubungi Arunika AI Copilot.',
                    time: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
                });
            });
        },
        clearAiChat() {
            this.aiMessages = [
                {
                    role: 'ai',
                    text: 'Percakapan telah dibersihkan. Apa konsep materi yang ingin Anda telaah selanjutnya?',
                    time: 'Baru saja'
                }
            ];
        },
        mobileSidebar: false,
        searchModalOpen: false,
        searchQuery: '',
        searchResults: null,
        searchLoading: false,
        notificationsOpen: false,
        profileDropdownOpen: false,
        sidebarProfileOpen: false,
        unreadCount: {{ (int) ($unreadNotificationsCount ?? 2) }},
        performSearch() {
            if (this.searchQuery.trim().length < 2) {
                this.searchResults = null;
                return;
            }
            this.searchLoading = true;
            fetch('{{ route('app.search') }}?q=' + encodeURIComponent(this.searchQuery))
                .then(res => res.json())
                .then(data => {
                    this.searchResults = data;
                    this.searchLoading = false;
                })
                .catch(() => { this.searchLoading = false; });
        }
    }"
    @keydown.window.prevent.cmd.k="searchModalOpen = true"
    @keydown.window.prevent.ctrl.k="searchModalOpen = true"
>

    <!-- App Main Layout Container -->
    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Sidebar: Lebar 234px, Latar Navy-900 (#1A2E5E) -->
        <aside 
            :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-[234px] bg-navy-900 border-r border-navy-900 flex flex-col justify-between transition-transform duration-200 md:sticky md:top-0 md:h-screen md:translate-x-0 shrink-0 overflow-hidden"
        >
            <div class="flex flex-col flex-1 overflow-y-auto px-3.5 py-4">
                <!-- Logo & Brand Wordmark -->
                <div class="flex items-center justify-between px-2 pb-4 border-b border-white/10 mb-3">
                    <a href="{{ route('app.dashboard') }}" class="group inline-flex items-center">
                        <x-logo variant="inverse" />
                    </a>
                    <button @click="mobileSidebar = false" class="md:hidden text-white/70 hover:text-white text-base cursor-pointer">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Navigasi Utama (Persis Gambar Acuan) -->
                <nav class="space-y-1 text-[13px] font-normal flex-1">
                    @php
                        $currentRoute = request()->route()?->getName();
                    @endphp

                    <!-- 1. Beranda (Active State) -->
                    <a 
                        href="{{ route('app.dashboard') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.dashboard' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="house" :size="18" class="shrink-0 {{ $currentRoute === 'app.dashboard' ? 'text-white' : 'text-white/80' }}" />
                        <span>Beranda</span>
                    </a>

                    <!-- 2. Mata Kuliah -->
                    <a 
                        href="{{ route('app.courses') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.courses' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="book-open" :size="18" class="shrink-0 {{ $currentRoute === 'app.courses' ? 'text-white' : 'text-white/80' }}" />
                        <span>Mata Kuliah</span>
                    </a>

                    <!-- 3. Tugas & Ujian -->
                    <a 
                        href="{{ route('app.assignments') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.assignments' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="clipboard-list" :size="18" class="shrink-0 {{ $currentRoute === 'app.assignments' ? 'text-white' : 'text-white/80' }}" />
                        <span>Tugas & Ujian</span>
                    </a>

                    <!-- 4. Presensi -->
                    <a 
                        href="{{ route('app.attendance') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.attendance' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="calendar-check" :size="18" class="shrink-0 {{ $currentRoute === 'app.attendance' ? 'text-white' : 'text-white/80' }}" />
                        <span>Presensi</span>
                    </a>

                    <!-- 5. Nilai -->
                    <a 
                        href="{{ route('app.grades') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.grades' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="bar-chart-3" :size="18" class="shrink-0 {{ $currentRoute === 'app.grades' ? 'text-white' : 'text-white/80' }}" />
                        <span>Nilai</span>
                    </a>

                    <!-- 6. Materi Pembelajaran -->
                    <a 
                        href="{{ route('app.materials') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.materials' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="folder-open" :size="18" class="shrink-0 {{ $currentRoute === 'app.materials' ? 'text-white' : 'text-white/80' }}" />
                        <span>Materi Pembelajaran</span>
                    </a>

                    <!-- 7. Diskusi -->
                    <a 
                        href="{{ route('app.discussions') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.discussions' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="message-circle" :size="18" class="shrink-0 {{ $currentRoute === 'app.discussions' ? 'text-white' : 'text-white/80' }}" />
                        <span>Diskusi</span>
                    </a>

                    <!-- 8. Pengaturan -->
                    <a 
                        href="{{ route('app.settings') }}" 
                        class="flex items-center gap-3 px-3 py-2 rounded-[6px] font-medium transition-colors {{ $currentRoute === 'app.settings' ? 'bg-navy-700 text-white shadow-xs' : 'text-white/80 hover:bg-white/10 hover:text-white' }}"
                    >
                        <x-icon name="settings" :size="18" class="shrink-0 {{ $currentRoute === 'app.settings' ? 'text-white' : 'text-white/80' }}" />
                        <span>Pengaturan</span>
                    </a>
                </nav>

                <!-- Blok Institusi Kampus (Persis Gambar Acuan) -->
                <div class="mt-4 pt-4 border-t border-white/10">
                    <div class="p-3 rounded-[6px] border border-white/15 bg-white/5 flex items-start gap-2.5">
                        <x-icon name="landmark" :size="20" class="text-white shrink-0 mt-0.5" />
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-medium text-white truncate leading-tight">
                                {{ Auth::user()?->campus?->name ?? 'Universitas Nusantara Indonesia' }}
                            </p>
                            <p class="text-[10px] text-white/60 truncate mt-0.5">
                                Maju Bersama Ilmu
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Profil Pengguna Sidebar Bawah (Persis Gambar Acuan) -->
            <div class="p-3 border-t border-white/10 bg-navy-900/60 relative">
                <div 
                    @click="sidebarProfileOpen = !sidebarProfileOpen" 
                    class="flex items-center justify-between gap-2 px-1 cursor-pointer select-none hover:bg-white/5 p-1 rounded-[6px] transition-colors"
                >
                    <div class="flex items-center gap-2.5 min-w-0">
                        <x-ui.avatar 
                            :src="Auth::user()?->isStudent() ? '/images/avatar-arya.jpg' : null" 
                            :name="Auth::user()?->name ?? 'Arya Pratama'" 
                            :size="32" 
                        />
                        <div class="truncate leading-tight">
                            <p class="text-xs font-semibold text-white truncate">{{ Auth::user()?->name ?? 'Arya Pratama' }}</p>
                            <p class="text-[11px] text-white/70">{{ Auth::user()?->role?->label() ?? 'Mahasiswa' }}</p>
                        </div>
                    </div>
                    <x-icon name="chevron-down" :size="16" class="text-white/60 shrink-0" />
                </div>

                <!-- Popover Menu Sidebar Bawah -->
                <div 
                    x-show="sidebarProfileOpen" 
                    @click.outside="sidebarProfileOpen = false"
                    x-transition.opacity.duration.150ms
                    class="absolute bottom-full mb-2 inset-x-2 bg-surface text-text rounded-[8px] border border-line shadow-lg p-1.5 space-y-1 text-xs z-50"
                    style="display: none;"
                >
                    <a href="{{ route('app.settings') }}" class="flex items-center gap-2 px-2.5 py-1.5 rounded-[4px] hover:bg-canvas text-text transition-colors">
                        <x-icon name="settings" :size="14" class="text-text-muted" />
                        <span>Pengaturan</span>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="w-full flex items-center gap-2 px-2.5 py-1.5 rounded-[4px] hover:bg-rose-50 text-danger transition-colors text-left cursor-pointer">
                            <x-icon name="log-out" :size="14" class="text-danger" />
                            <span>Keluar</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-canvas">
            
            <!-- Topbar (48px, Surface Putih, Border Line, Breadcrumb, Search, Bell, Profile) -->
            <header class="h-12 border-b border-line bg-surface px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="mobileSidebar = true" class="md:hidden text-text-muted hover:text-text p-1 cursor-pointer">
                        <x-icon name="menu" :size="18" />
                    </button>

                    <!-- Breadcrumb (Persis Gambar: Beranda > Mata Kuliah / Dashboard) -->
                    <div class="flex items-center gap-2 text-xs">
                        <a href="{{ route('app.dashboard') }}" class="text-text-muted hover:text-text transition-colors">Beranda</a>
                        <x-icon name="chevron-right" :size="12" class="text-text-subtle" />
                        <span class="font-medium text-text">@yield('page_breadcrumb', 'Dashboard')</span>
                    </div>
                </div>

                <div class="flex items-center gap-4">
                    <!-- Search Input (Persis Gambar Acuan) -->
                    <div class="relative hidden sm:block cursor-pointer" @click="searchModalOpen = true">
                        <input 
                            type="text" 
                            readonly
                            placeholder="Cari materi, tugas, atau mata kuliah..." 
                            class="w-72 h-8 pl-8 pr-3 rounded-[6px] bg-canvas border border-line text-text text-xs placeholder:text-text-subtle focus:outline-none focus:border-accent focus:bg-surface transition-colors cursor-pointer"
                        >
                        <x-icon name="search" :size="14" class="text-text-subtle absolute left-2.5 top-2" />
                    </div>

                    <!-- AI Copilot Trigger Button (Topbar) -->
                    <button 
                        type="button" 
                        @click="aiDrawerOpen = true"
                        class="relative cursor-pointer px-2.5 py-1 rounded-[6px] hover:bg-blue-50 text-[#2563EB] hover:text-blue-700 transition-colors flex items-center gap-1.5 shadow-2xs border border-blue-100 bg-blue-50/50 text-xs font-semibold"
                        title="Buka Arunika AI Copilot"
                    >
                        <x-icon name="bot" :size="15" class="text-[#2563EB]" />
                        <span class="hidden sm:inline text-[11px] font-semibold text-[#2563EB]">AI Copilot</span>
                    </button>

                    <!-- Notifikasi Lonceng dengan Dropdown Real-time (Persis Gambar Acuan) -->
                    <div class="relative">
                        <button 
                            @click="notificationsOpen = !notificationsOpen" 
                            @click.outside="notificationsOpen = false"
                            class="relative cursor-pointer p-1 text-text-muted hover:text-text transition-colors block"
                            title="Notifikasi"
                        >
                            <x-icon name="bell" :size="18" />
                            <span 
                                x-show="unreadCount > 0" 
                                x-text="unreadCount" 
                                class="absolute top-0 right-0 w-3.5 h-3.5 bg-danger text-white text-[9px] font-bold rounded-full flex items-center justify-center"
                            >
                                {{ $unreadNotificationsCount ?? 2 }}
                            </span>
                        </button>

                        <!-- Dropdown Notifikasi -->
                        <div 
                            x-show="notificationsOpen" 
                            x-transition.opacity.duration.150ms 
                            class="absolute right-0 mt-2 w-80 bg-surface border border-line rounded-[8px] shadow-lg py-2 z-50 text-xs" 
                            style="display: none;"
                        >
                            <div class="px-3.5 py-1.5 border-b border-line flex items-center justify-between">
                                <span class="font-semibold text-text">Notifikasi</span>
                                <form action="{{ route('notifications.mark_all_read') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-[11px] text-primary hover:underline cursor-pointer">Tandai semua dibaca</button>
                                </form>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-line/70">
                                @forelse($recentNotifications ?? [] as $notif)
                                    <div class="p-3 hover:bg-canvas transition-colors flex gap-2.5 {{ !$notif['is_read'] ? 'bg-blue-50/40' : '' }}">
                                        <div class="mt-0.5 shrink-0 text-primary">
                                            <x-icon :name="$notif['icon'] ?? 'bell'" :size="15" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <a href="{{ $notif['action_url'] ?? '#' }}" class="font-semibold text-text hover:text-primary truncate block leading-tight">
                                                {{ $notif['title'] }}
                                            </a>
                                            <p class="text-[11px] text-text-muted mt-0.5 line-clamp-2 leading-relaxed">
                                                {{ $notif['message'] }}
                                            </p>
                                            <span class="text-[10px] text-text-subtle mt-1 block">
                                                {{ \App\Helpers\ArunikaDate::diffForHumans($notif['created_at']) }}
                                            </span>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-4 text-center text-text-muted text-xs">
                                        Belum ada notifikasi baru.
                                    </div>
                                @endforelse
                            </div>
                            <div class="px-3.5 pt-2 border-t border-line text-center">
                                <a href="{{ route('app.notifications') }}" class="text-[11px] font-medium text-primary hover:underline">
                                    Lihat semua notifikasi →
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Topbar Dropdown (Avatar + Nama + Chevron) -->
                    <div class="relative">
                        <div 
                            class="flex items-center gap-2 pl-2 border-l border-line cursor-pointer select-none" 
                            @click="profileDropdownOpen = !profileDropdownOpen" 
                            @click.outside="profileDropdownOpen = false"
                            title="Menu Pengguna"
                        >
                            <x-ui.avatar 
                                :src="Auth::user()?->isStudent() ? '/images/avatar-arya.jpg' : null" 
                                :name="Auth::user()?->name ?? 'Arya Pratama'" 
                                :size="28" 
                            />
                            <span class="text-xs font-semibold text-text hidden sm:inline truncate max-w-[120px]">
                                {{ Auth::user()?->name ?? 'Arya Pratama' }}
                            </span>
                            <x-icon name="chevron-down" :size="14" class="text-text-muted hidden sm:inline" />
                        </div>

                        <!-- Dropdown Menu Profil -->
                        <div 
                            x-show="profileDropdownOpen" 
                            x-transition.opacity.duration.150ms 
                            class="absolute right-0 mt-2 w-52 bg-surface border border-line rounded-[8px] shadow-lg py-1.5 z-50 text-xs" 
                            style="display: none;"
                        >
                            <div class="px-3 py-2 border-b border-line">
                                <p class="font-semibold text-text truncate">{{ Auth::user()?->name ?? 'Arya Pratama' }}</p>
                                <p class="text-[11px] text-text-muted truncate">{{ Auth::user()?->email ?? 'arya.pratama@student.unus.ac.id' }}</p>
                            </div>
                            <a href="{{ route('app.settings') }}" class="flex items-center gap-2 px-3 py-2 text-text hover:bg-canvas transition-colors">
                                <x-icon name="user" :size="14" class="text-text-muted" />
                                <span>Profil Saya</span>
                            </a>
                            <a href="{{ route('app.settings') }}" class="flex items-center gap-2 px-3 py-2 text-text hover:bg-canvas transition-colors">
                                <x-icon name="settings" :size="14" class="text-text-muted" />
                                <span>Pengaturan</span>
                            </a>
                            <div class="border-t border-line my-1"></div>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-danger hover:bg-rose-50 transition-colors text-left cursor-pointer">
                                    <x-icon name="log-out" :size="14" class="text-danger" />
                                    <span>Keluar</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Main Content Canvas -->
            <main class="flex-1 w-full max-w-[1600px] mx-auto px-4 sm:px-8 py-6">
                <!-- Success notification -->
                @if(session('success'))
                    <div class="mb-5 p-3.5 rounded-[8px] bg-success-soft border border-success/30 text-success text-xs font-medium flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle" :size="16" class="text-success shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    <!-- ==========================================
         PENDAMPING BELAJAR (SLIDE-OVER PANEL AI)
         ========================================== -->
    <div 
        x-show="aiDrawerOpen" 
        x-transition.opacity.duration.150ms 
        class="fixed inset-0 bg-text/30 z-50 flex justify-end" 
        style="display: none;"
    >
        <div 
            class="w-full max-w-md bg-surface border-l border-line h-full flex flex-col justify-between p-6 shadow-card" 
            @click.away="aiDrawerOpen = false"
        >
            <div class="overflow-y-auto space-y-4">
                <!-- Header Panel -->
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <div class="flex items-center gap-2">
                        <x-icon name="message-circle" :size="20" class="text-primary" />
                        <div>
                            <h3 class="font-semibold text-sm text-text">Pendamping Belajar</h3>
                            <p class="text-[11px] text-text-muted">Tutor Kontekstual Berbasis RPS</p>
                        </div>
                    </div>
                    <button @click="aiDrawerOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Kontrol Mode Sokrates -->
                <div class="p-3 rounded-[6px] border border-line bg-tile text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-semibold text-text">Mode Sokrates</span>
                        <button 
                            @click="socraticMode = !socraticMode" 
                            :class="socraticMode ? 'bg-primary text-white' : 'border border-line text-text-muted bg-white'"
                            class="px-2 py-0.5 rounded-[4px] text-[10px] font-semibold transition-colors cursor-pointer"
                        >
                            <span x-text="socraticMode ? 'AKTIF' : 'NONAKTIF'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-text-muted leading-relaxed">
                        Menuntun penalaran mahasiswa langkah demi langkah tanpa membagikan jawaban instan.
                    </p>
                </div>

                <!-- Topik Cepat -->
                <div class="space-y-1.5">
                    <span class="text-[11px] font-medium text-text-muted block">Topik Cepat:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button 
                            @click="aiPrompt = 'Jelaskan konsep Single Responsibility Principle (SRP) dengan analogi dunia nyata!'; askAi()" 
                            class="px-2.5 py-1 rounded-[6px] bg-canvas border border-line hover:border-line-strong text-text text-[11px] transition-colors text-left cursor-pointer"
                        >
                            Prinsip SOLID SRP
                        </button>
                        <button 
                            @click="aiPrompt = 'Apa perbedaan Pewarisan dan Komposisi dalam OOP?'; askAi()" 
                            class="px-2.5 py-1 rounded-[6px] bg-canvas border border-line hover:border-line-strong text-text text-[11px] transition-colors text-left cursor-pointer"
                        >
                            Inheritance vs Composition
                        </button>
                        <button 
                            @click="aiPrompt = 'Bagaimana nilai tugas saya berkontribusi pada CPL-02?'; askAi()" 
                            class="px-2.5 py-1 rounded-[6px] bg-canvas border border-line hover:border-line-strong text-text text-[11px] transition-colors text-left cursor-pointer"
                        >
                            Capaian CPL-02
                        </button>
                    </div>
                </div>

                <!-- Tampilan Jawaban Catatan Pinggir -->
                <div class="min-h-56 p-4 rounded-[6px] bg-canvas border border-line text-xs text-text leading-relaxed space-y-3">
                    <template x-if="aiLoading">
                        <div class="flex items-center gap-2 text-text-muted text-xs">
                            <span class="inline-block w-2 h-2 rounded-full bg-accent animate-ping"></span>
                            <span>Menelaah silabus & materi kuliah...</span>
                        </div>
                    </template>
                    
                    <template x-if="!aiLoading && !aiResponse">
                        <p class="text-text-muted italic">
                            Ajukan pertanyaan tentang materi kuliah, konsep kode, atau panduan asesmen CPL kepada Pendamping Belajar.
                        </p>
                    </template>

                    <!-- Respons AI -->
                    <div x-show="!aiLoading && aiResponse" class="space-y-2.5">
                        <div class="inline-flex items-center gap-1.5 text-[10px] uppercase font-semibold text-text-muted border-b border-line pb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                            <span>Dihasilkan AI &middot; Periksa Kembali</span>
                        </div>
                        <div class="pl-3 border-l-2 border-accent text-xs text-text whitespace-pre-wrap leading-relaxed" x-text="aiResponse"></div>
                        <div class="pt-2 border-t border-line text-[10px] text-text-muted space-y-0.5">
                            <p>Rujukan: [1] Silabus PBO Pertemuan 5 &middot; SOLID Architecture</p>
                            <p>Evaluasi: Terhubung ke CPMK-02 & CPL-02</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulir Input Pertanyaan -->
            <div class="pt-4 border-t border-line space-y-2">
                <form @submit.prevent="askAi()" class="space-y-2">
                    <label class="block text-[11px] font-medium text-text-muted">Pertanyaan Akademik</label>
                    <textarea 
                        x-model="aiPrompt" 
                        rows="2" 
                        placeholder="Tanyakan konsep teori atau logika kurikulum..." 
                        class="w-full px-3 py-2 rounded-[6px] bg-canvas border border-line text-xs text-text focus:outline-none focus:border-accent transition-colors resize-none font-sans"
                    ></textarea>
                    <button 
                        type="submit" 
                        :disabled="aiLoading || !aiPrompt.trim()" 
                        class="w-full h-8 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white font-medium text-xs disabled:opacity-40 transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <span>Tanya Pendamping Belajar</span>
                        <x-icon name="arrow-right" :size="14" class="text-white" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Developer Role Switcher (Toolbar Bawah, Ringkas, Non-Intrusif) -->
    <div 
        x-data="{ open: false }" 
        class="fixed bottom-3 right-3 z-50 text-xs"
        @click.outside="open = false"
    >
        <div 
            x-show="open" 
            x-transition.opacity.duration.150ms
            class="mb-2 p-3 bg-surface border border-line rounded-[8px] shadow-card max-w-xs space-y-2"
            style="display: none;"
        >
            <div class="flex items-center justify-between pb-1.5 border-b border-line">
                <span class="font-semibold text-text uppercase tracking-wider text-[10px]">Ganti Peran Simulasi</span>
                <button @click="open = false" class="text-text-muted hover:text-text text-xs cursor-pointer">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-1 text-[11px]">
                <a href="{{ route('demo.login', 'student') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between {{ Auth::user()?->isStudent() ? 'font-semibold text-primary bg-tile' : 'text-text-muted' }}">
                    <span>Mahasiswa</span>
                    <span class="text-text-muted text-[10px]">Arya Pratama</span>
                </a>
                <a href="{{ route('demo.login', 'lecturer') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between {{ Auth::user()?->isLecturer() ? 'font-semibold text-primary bg-tile' : 'text-text-muted' }}">
                    <span>Dosen</span>
                    <span class="text-text-muted text-[10px]">Prof. Budi</span>
                </a>
                <a href="{{ route('demo.login', 'campus_admin') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between {{ Auth::user()?->isCampusAdmin() ? 'font-semibold text-primary bg-tile' : 'text-text-muted' }}">
                    <span>Admin BAAK</span>
                    <span class="text-text-muted text-[10px]">Dr. Hendra</span>
                </a>
                <a href="{{ route('demo.login', 'auditor') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between {{ Auth::user()?->isAuditor() ? 'font-semibold text-primary bg-tile' : 'text-text-muted' }}">
                    <span>Auditor Mutu</span>
                    <span class="text-text-muted text-[10px]">Ir. Siti Aminah</span>
                </a>
                <a href="{{ route('demo.login', 'industry_mentor') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between {{ Auth::user()?->isIndustryMentor() ? 'font-semibold text-primary bg-tile' : 'text-text-muted' }}">
                    <span>Mentor Industri</span>
                    <span class="text-text-muted text-[10px]">Rian Santoso</span>
                </a>
            </div>
        </div>

        <button 
            @click="open = !open" 
            class="px-2.5 py-1.5 bg-surface hover:bg-tile border border-line rounded-[6px] text-text font-medium text-[11px] flex items-center gap-1.5 shadow-card transition-colors cursor-pointer"
            title="Simulasi Akun Peran"
        >
            <span class="inline-block w-2 h-2 rounded-full bg-success"></span>
            <span>Uji Peran: {{ Auth::user()?->role?->label() }}</span>
            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
        </button>
    </div>

    <!-- ==========================================
         GLOBAL SEARCH MODAL (Ctrl+K)
         ========================================== -->
    <div 
        x-show="searchModalOpen" 
        x-transition.opacity.duration.150ms 
        class="fixed inset-0 bg-navy-950/40 backdrop-blur-xs z-50 flex items-start justify-center pt-16 sm:pt-24 px-4" 
        style="display: none;"
    >
        <div 
            @click.outside="searchModalOpen = false"
            class="w-full max-w-xl bg-surface border border-line rounded-[10px] shadow-lg overflow-hidden flex flex-col text-xs"
        >
            <!-- Search Input Header -->
            <div class="p-3.5 border-b border-line flex items-center gap-3">
                <x-icon name="search" :size="18" class="text-text-subtle shrink-0" />
                <input 
                    type="text" 
                    x-model="searchQuery" 
                    @input.debounce.250ms="performSearch()" 
                    x-ref="searchInput"
                    x-effect="if (searchModalOpen) { $nextTick(() => $refs.searchInput.focus()) }"
                    placeholder="Ketik untuk mencari mata kuliah, materi, tugas, atau diskusi..."
                    class="flex-1 bg-transparent border-0 outline-none text-text placeholder:text-text-subtle text-xs"
                >
                <button 
                    @click="searchModalOpen = false" 
                    class="px-1.5 py-0.5 rounded-[4px] bg-canvas border border-line text-[10px] font-mono text-text-subtle hover:text-text cursor-pointer"
                >
                    ESC
                </button>
            </div>

            <!-- Search Results Body -->
            <div class="max-h-96 overflow-y-auto p-2">
                <!-- Loading State -->
                <div x-show="searchLoading" class="p-6 text-center text-text-muted flex items-center justify-center gap-2">
                    <span class="inline-block w-2 h-2 rounded-full bg-primary animate-ping"></span>
                    <span>Mencari data akademik...</span>
                </div>

                <!-- Empty State (No query yet) -->
                <div x-show="!searchLoading && !searchResults && !searchQuery.trim()" class="p-6 text-center text-text-muted space-y-1">
                    <p class="font-medium text-text">Pencarian Terpadu Arunika</p>
                    <p class="text-[11px]">Temukan materi kuliah, tugas, ujian, dan forum diskusi kelas Anda dengan cepat.</p>
                </div>

                <!-- No Results Found -->
                <div x-show="!searchLoading && searchResults && searchResults.total === 0" class="p-6 text-center text-text-muted space-y-1">
                    <p class="font-medium text-text">Tidak ada hasil ditemukan</p>
                    <p class="text-[11px]">Tidak ada mata kuliah, materi, atau tugas yang cocok dengan kata kunci tersebut.</p>
                </div>

                <!-- Results Grouped -->
                <div x-show="!searchLoading && searchResults && searchResults.total > 0" class="space-y-3 p-1">
                    <!-- 1. Mata Kuliah -->
                    <template x-if="searchResults.courses && searchResults.courses.length > 0">
                        <div>
                            <div class="px-2 py-1 text-[10px] font-semibold text-text-subtle uppercase tracking-wider">Mata Kuliah</div>
                            <div class="space-y-0.5">
                                <template x-for="item in searchResults.courses" :key="item.title">
                                    <a :href="item.url" @click="searchModalOpen = false" class="flex items-center justify-between p-2 rounded-[6px] hover:bg-canvas transition-colors group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-6 h-6 rounded-[4px] bg-blue-50 text-primary flex items-center justify-center shrink-0">
                                                <x-icon name="book-open" :size="13" />
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-text group-hover:text-primary truncate" x-text="item.title"></p>
                                                <p class="text-[10px] text-text-muted truncate" x-text="item.subtitle"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-medium text-text-subtle px-1.5 py-0.5 rounded-[4px] bg-tile shrink-0" x-text="item.badge"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- 2. Materi Pembelajaran -->
                    <template x-if="searchResults.materials && searchResults.materials.length > 0">
                        <div>
                            <div class="px-2 py-1 text-[10px] font-semibold text-text-subtle uppercase tracking-wider">Materi Pembelajaran</div>
                            <div class="space-y-0.5">
                                <template x-for="item in searchResults.materials" :key="item.title">
                                    <a :href="item.url" @click="searchModalOpen = false" class="flex items-center justify-between p-2 rounded-[6px] hover:bg-canvas transition-colors group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-6 h-6 rounded-[4px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                                <x-icon name="folder-open" :size="13" />
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-text group-hover:text-primary truncate" x-text="item.title"></p>
                                                <p class="text-[10px] text-text-muted truncate" x-text="item.subtitle"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-medium text-text-subtle px-1.5 py-0.5 rounded-[4px] bg-tile shrink-0" x-text="item.badge"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- 3. Tugas & Ujian -->
                    <template x-if="searchResults.assignments && searchResults.assignments.length > 0">
                        <div>
                            <div class="px-2 py-1 text-[10px] font-semibold text-text-subtle uppercase tracking-wider">Tugas & Ujian</div>
                            <div class="space-y-0.5">
                                <template x-for="item in searchResults.assignments" :key="item.title">
                                    <a :href="item.url" @click="searchModalOpen = false" class="flex items-center justify-between p-2 rounded-[6px] hover:bg-canvas transition-colors group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-6 h-6 rounded-[4px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                                <x-icon name="clipboard-list" :size="13" />
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-text group-hover:text-primary truncate" x-text="item.title"></p>
                                                <p class="text-[10px] text-text-muted truncate" x-text="item.subtitle"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-medium text-text-subtle px-1.5 py-0.5 rounded-[4px] bg-tile shrink-0" x-text="item.badge"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>

                    <!-- 4. Diskusi -->
                    <template x-if="searchResults.discussions && searchResults.discussions.length > 0">
                        <div>
                            <div class="px-2 py-1 text-[10px] font-semibold text-text-subtle uppercase tracking-wider">Forum Diskusi</div>
                            <div class="space-y-0.5">
                                <template x-for="item in searchResults.discussions" :key="item.title">
                                    <a :href="item.url" @click="searchModalOpen = false" class="flex items-center justify-between p-2 rounded-[6px] hover:bg-canvas transition-colors group">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            <div class="w-6 h-6 rounded-[4px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                                <x-icon name="message-circle" :size="13" />
                                            </div>
                                            <div class="truncate">
                                                <p class="font-medium text-text group-hover:text-primary truncate" x-text="item.title"></p>
                                                <p class="text-[10px] text-text-muted truncate" x-text="item.subtitle"></p>
                                            </div>
                                        </div>
                                        <span class="text-[10px] font-medium text-text-subtle px-1.5 py-0.5 rounded-[4px] bg-tile shrink-0" x-text="item.badge"></span>
                                    </a>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Footer Hint -->
            <div class="p-2 border-t border-line bg-canvas/50 px-3 flex items-center justify-between text-[10px] text-text-muted">
                <span>Tekan <kbd class="font-mono bg-surface border border-line px-1 rounded">ESC</kbd> untuk menutup</span>
                <span>Navigasi instan ke halaman terkait</span>
            </div>
        </div>
    </div>

    <!-- ==========================================
         SLIDE-OVER DRAWER: ARUNIKA AI COPILOT
         ========================================== -->
    <div 
        x-show="aiDrawerOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-hidden" 
        role="dialog" 
        aria-modal="true"
        @keydown.escape.window="aiDrawerOpen = false"
    >
        <!-- Backdrop -->
        <div 
            x-show="aiDrawerOpen"
            x-transition:enter="ease-in-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in-out duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-navy-950/40 backdrop-blur-xs transition-opacity" 
            @click="aiDrawerOpen = false"
        ></div>

        <!-- Slide-over Container -->
        <div class="fixed inset-y-0 right-0 max-w-full flex pl-10 pointer-events-none">
            <div 
                x-show="aiDrawerOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="w-screen max-w-md bg-surface border-l border-line shadow-2xl flex flex-col justify-between pointer-events-auto"
            >
                <!-- Drawer Header -->
                <div class="p-4 border-b border-line bg-gradient-to-r from-[#EFF6FF] via-[#F5F3FF] to-[#FAF5FF] flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#1A2E5E] text-white flex items-center justify-center shrink-0 shadow-2xs">
                            <x-icon name="bot" :size="18" />
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h3 class="font-bold text-sm text-navy-950">Arunika AI Copilot</h3>
                                <span class="flex h-2 w-2 relative">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                            </div>
                            <p class="text-[11px] text-text-muted mt-0.5">Asisten &amp; Pendamping Belajar Cerdas</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5">
                        <a 
                            href="{{ route('app.copilot') }}" 
                            class="p-1.5 rounded-[6px] text-text-muted hover:text-[#2563EB] hover:bg-white/60 transition-colors"
                            title="Buka Workspace Lengkap"
                        >
                            <x-icon name="maximize-2" :size="15" />
                        </a>
                        <button 
                            type="button" 
                            @click="aiDrawerOpen = false" 
                            class="p-1.5 rounded-[6px] text-text-muted hover:text-text hover:bg-white/60 transition-colors cursor-pointer"
                        >
                            <x-icon name="x" :size="16" />
                        </button>
                    </div>
                </div>

                <!-- Sub-bar: Socratic Mode Toggle & Active Learning Context -->
                <div class="px-4 py-2 border-b border-line bg-canvas/40 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-2 min-w-0">
                        <span class="text-[11px] text-text-muted shrink-0">Konteks:</span>
                        <span class="text-[11px] font-semibold text-text truncate max-w-[170px]" x-text="aiContext || 'Pemrograman Berorientasi Objek'"></span>
                    </div>

                    <!-- Socratic Mode Toggle -->
                    <div class="flex items-center gap-1.5 shrink-0" title="Metode Sokrates: membimbing dengan pertanyaan pemantik tanpa membocorkan jawaban langsung">
                        <span class="text-[11px] font-medium" :class="socraticMode ? 'text-[#2563EB] font-semibold' : 'text-text-muted'">Sokrates</span>
                        <button 
                            type="button" 
                            @click="socraticMode = !socraticMode" 
                            :class="socraticMode ? 'bg-[#2563EB]' : 'bg-slate-300'"
                            class="relative inline-flex h-4 w-7 shrink-0 cursor-pointer rounded-full transition-colors duration-200 ease-in-out focus:outline-none"
                        >
                            <span 
                                :class="socraticMode ? 'translate-x-3.5' : 'translate-x-0.5'"
                                class="inline-block h-3 w-3 transform rounded-full bg-white transition duration-200 ease-in-out mt-0.5 shadow-xs"
                            ></span>
                        </button>
                    </div>
                </div>

                <!-- Chat Messages Stream (Scrollable) -->
                <div id="aiChatContainer" class="flex-1 overflow-y-auto p-4 space-y-3 text-xs bg-canvas/20">
                    <template x-for="(msg, index) in aiMessages" :key="index">
                        <div>
                            <!-- User Message -->
                            <template x-if="msg.role === 'user'">
                                <div class="flex justify-end mb-2">
                                    <div class="max-w-[85%] rounded-[10px] rounded-tr-xs bg-[#1A2E5E] text-white p-3 shadow-2xs">
                                        <p class="leading-relaxed whitespace-pre-wrap text-xs" x-text="msg.text"></p>
                                        <span class="text-[9px] text-white/60 block text-right mt-1" x-text="msg.time"></span>
                                    </div>
                                </div>
                            </template>

                            <!-- AI Message -->
                            <template x-if="msg.role === 'ai'">
                                <div class="flex items-start gap-2.5 mb-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                                        <x-icon name="bot" :size="14" />
                                    </div>
                                    <div class="max-w-[85%] rounded-[10px] rounded-tl-xs bg-surface border border-line text-text p-3 shadow-2xs">
                                        <div class="prose prose-xs leading-relaxed whitespace-pre-wrap font-sans text-xs text-text" x-text="msg.text"></div>
                                        <div class="flex items-center justify-between mt-2 pt-1.5 border-t border-line/50 text-[9px] text-text-muted">
                                            <span x-show="msg.xp" class="text-amber-600 font-bold flex items-center gap-1">
                                                <span>+10 XP</span>
                                            </span>
                                            <span x-text="msg.time"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>

                    <!-- Loading Indicator Bubble -->
                    <div x-show="aiLoading" class="flex items-start gap-2.5 mb-2">
                        <div class="w-7 h-7 rounded-full bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5">
                            <x-icon name="bot" :size="14" />
                        </div>
                        <div class="rounded-[10px] rounded-tl-xs bg-surface border border-line text-text p-3 shadow-2xs flex items-center gap-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] animate-bounce"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] animate-bounce [animation-delay:0.2s]"></span>
                            <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] animate-bounce [animation-delay:0.4s]"></span>
                            <span class="text-[11px] text-text-muted ml-1.5">Arunika Copilot sedang menelaah materi...</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompts Chips -->
                <div class="px-4 py-2 border-t border-line/60 bg-surface">
                    <span class="text-[10px] font-bold text-text-muted uppercase tracking-wider block mb-1.5">Topik Cepat Diskusikan</span>
                    <div class="flex flex-wrap gap-1.5">
                        <template x-for="chip in aiSuggestions" :key="chip">
                            <button 
                                type="button" 
                                @click="sendAiMessage(chip)" 
                                class="px-2 py-1 rounded-[5px] bg-canvas hover:bg-blue-50 text-[11px] text-text hover:text-[#2563EB] border border-line hover:border-blue-200 transition-colors cursor-pointer text-left truncate max-w-[200px]"
                                :title="chip"
                            >
                                <span x-text="chip"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Input Footer Area -->
                <div class="p-3.5 border-t border-line bg-surface space-y-2">
                    <!-- Learning Mode Pill Switches -->
                    <div class="flex items-center justify-between text-[11px]">
                        <div class="flex items-center gap-1">
                            <button 
                                type="button" 
                                @click="aiMode = 'chat'" 
                                :class="aiMode === 'chat' ? 'bg-blue-100 text-[#2563EB] font-bold' : 'text-text-muted hover:text-text'" 
                                class="px-2 py-0.5 rounded-[4px] transition-colors cursor-pointer"
                            >
                                Tanya Konsep
                            </button>
                            <button 
                                type="button" 
                                @click="aiMode = 'flashcard'" 
                                :class="aiMode === 'flashcard' ? 'bg-blue-100 text-[#2563EB] font-bold' : 'text-text-muted hover:text-text'" 
                                class="px-2 py-0.5 rounded-[4px] transition-colors cursor-pointer"
                            >
                                Flashcard
                            </button>
                            <button 
                                type="button" 
                                @click="aiMode = 'summarize'" 
                                :class="aiMode === 'summarize' ? 'bg-blue-100 text-[#2563EB] font-bold' : 'text-text-muted hover:text-text'" 
                                class="px-2 py-0.5 rounded-[4px] transition-colors cursor-pointer"
                            >
                                Rangkum
                            </button>
                            <button 
                                type="button" 
                                @click="aiMode = 'quiz'" 
                                :class="aiMode === 'quiz' ? 'bg-blue-100 text-[#2563EB] font-bold' : 'text-text-muted hover:text-text'" 
                                class="px-2 py-0.5 rounded-[4px] transition-colors cursor-pointer"
                            >
                                Latihan Kuis
                            </button>
                        </div>

                        <button 
                            type="button" 
                            @click="clearAiChat()" 
                            class="text-text-muted hover:text-red-500 transition-colors cursor-pointer p-1"
                            title="Bersihkan Percakapan"
                        >
                            <x-icon name="trash-2" :size="12" />
                        </button>
                    </div>

                    <!-- Input Bar -->
                    <form @submit.prevent="sendAiMessage(aiPrompt)" class="flex items-center gap-2">
                        <input 
                            type="text" 
                            x-model="aiPrompt" 
                            :disabled="aiLoading"
                            placeholder="Tanyakan materi atau konsep yang ingin dipelajari..." 
                            class="flex-1 px-3 py-2 rounded-[6px] border border-line bg-canvas text-xs text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB] focus:bg-surface transition-colors"
                        >
                        <button 
                            type="submit" 
                            :disabled="aiLoading || !aiPrompt.trim()"
                            :class="(aiLoading || !aiPrompt.trim()) ? 'opacity-50 cursor-not-allowed bg-slate-300' : 'bg-[#2563EB] hover:bg-blue-700 cursor-pointer'"
                            class="w-8 h-8 rounded-[6px] text-white flex items-center justify-center shrink-0 shadow-2xs transition-colors"
                        >
                            <x-icon name="send" :size="14" />
                        </button>
                    </form>
                    <p class="text-[10px] text-text-muted text-center">
                        Arunika AI membantu Anda bernalar &bull; Tekan <kbd class="font-mono bg-canvas border border-line px-1 rounded">Enter</kbd> untuk mengirim
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Script AI Copilot Backward-Compatibility -->
    <script>
        function askAi() {
            const bodyData = Alpine.$data(document.body);
            if (bodyData && typeof bodyData.sendAiMessage === 'function') {
                bodyData.sendAiMessage(bodyData.aiPrompt);
            }
        }
    </script>
</body>
</html>
