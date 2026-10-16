<!DOCTYPE html>
<html lang="id" class="h-full bg-canvas">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arunika — Learning Operating System')</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1A2E5E">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-canvas text-text font-sans antialiased selection:bg-accent selection:text-white" x-data="{ aiDrawerOpen: false, aiPrompt: '', aiResponse: '', aiLoading: false, socraticMode: true, mobileSidebar: false }">

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
            <div class="p-3 border-t border-white/10 bg-navy-900/60">
                <div class="flex items-center justify-between gap-2 px-1">
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
                    <div class="relative hidden sm:block">
                        <input 
                            type="text" 
                            placeholder="Cari materi, tugas, atau mata kuliah..." 
                            class="w-72 h-8 pl-8 pr-3 rounded-[6px] bg-canvas border border-line text-text text-xs placeholder:text-text-subtle focus:outline-none focus:border-accent focus:bg-surface transition-colors"
                        >
                        <x-icon name="search" :size="14" class="text-text-subtle absolute left-2.5 top-2" />
                    </div>

                    <!-- Notifikasi Lonceng dengan Badge Angka 2 (Persis Gambar Acuan) -->
                    <div class="relative cursor-pointer p-1 text-text-muted hover:text-text transition-colors">
                        <x-icon name="bell" :size="18" />
                        <span class="absolute top-0 right-0 w-3.5 h-3.5 bg-danger text-white text-[9px] font-bold rounded-full flex items-center justify-center">
                            2
                        </span>
                    </div>

                    <!-- User Profile Topbar (Avatar + Nama + Chevron) -->
                    <div class="flex items-center gap-2 pl-2 border-l border-line cursor-pointer" @click="aiDrawerOpen = true" title="Profil Pengguna & AI Tutor">
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

    <!-- Script AI Copilot -->
    <script>
        function askAi() {
            const prompt = Alpine.$data(document.body).aiPrompt;
            if (!prompt) return;

            Alpine.$data(document.body).aiLoading = true;
            Alpine.$data(document.body).aiResponse = '';

            fetch('/api/copilot/ask', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    prompt: prompt,
                    socratic: Alpine.$data(document.body).socraticMode,
                    context: 'Pemrograman Berorientasi Objek & Desain Pola (IF2104)'
                })
            })
            .then(res => res.json())
            .then(data => {
                Alpine.$data(document.body).aiLoading = false;
                Alpine.$data(document.body).aiResponse = data.response;
            })
            .catch(err => {
                Alpine.$data(document.body).aiLoading = false;
                Alpine.$data(document.body).aiResponse = 'Mohon maaf, terjadi kendala jaringan saat menghubungi Pendamping Belajar.';
            });
        }
    </script>
</body>
</html>
