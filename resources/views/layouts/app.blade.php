<!DOCTYPE html>
<html lang="id" class="h-full bg-paper">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arunika — Learning Operating System')</title>
    <link rel="icon" href="/favicon.ico">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#14513F">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-paper text-ink font-sans antialiased selection:bg-brand selection:text-paper" x-data="{ aiDrawerOpen: false, aiPrompt: '', aiResponse: '', aiLoading: false, socraticMode: true, mobileSidebar: false }">

    <!-- App Main Wrapper -->
    <div class="min-h-full flex flex-col md:flex-row">
        
        <!-- Sidebar 240px: Navigasi Teks Murni, Dikelompokkan Mono Uppercase -->
        <aside 
            :class="mobileSidebar ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
            class="fixed inset-y-0 left-0 z-40 w-60 bg-surface border-r border-line flex flex-col justify-between transition-transform duration-200 md:static md:translate-x-0 shrink-0"
        >
            <div class="p-5 overflow-y-auto">
                <!-- Logo & Brand Wordmark -->
                <div class="flex items-center justify-between pb-5 border-b border-line mb-5">
                    <a href="{{ route('app.dashboard') }}" class="group">
                        <x-logo />
                    </a>
                    <button @click="mobileSidebar = false" class="md:hidden text-ink-muted hover:text-ink text-base cursor-pointer">
                        <x-icon name="x" class="w-4 h-4" />
                    </button>
                </div>

                <!-- Info Kampus & Semester (Mono) -->
                <div class="mb-6 p-2.5 rounded-[4px] border border-line bg-paper text-xs font-mono space-y-1">
                    <span class="text-[10px] uppercase text-ink-muted block tracking-wider">Perguruan Tinggi</span>
                    <p class="font-medium text-ink truncate font-sans text-xs">{{ Auth::user()?->campus?->name ?? 'Universitas Nusantara Indonesia' }}</p>
                    <div class="flex items-center justify-between text-[10px] text-ink-muted pt-1 border-t border-line/60">
                        <span>Ganjil 2026/2027</span>
                        <span class="text-brand font-semibold">OBE Aktif</span>
                    </div>
                </div>

                <!-- Navigasi Dikelompokkan Berdasarkan Modul -->
                <nav class="space-y-6 text-xs">
                    
                    <!-- Kelompok 1: Akademik Harian -->
                    <div class="space-y-1">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-muted px-2 block mb-1">
                            Akademik
                        </span>
                        <a href="{{ route('app.dashboard') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-[2px] bg-paper text-ink font-medium border-l-2 border-brand transition-editorial">
                            <span>Beranda & Ringkasan</span>
                            <span class="font-mono text-[10px] text-brand">Aktif</span>
                        </a>
                        <a href="#mata-kuliah" class="flex items-center px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Mata Kuliah & Silabus</span>
                        </a>
                        <a href="#tugas-asesmen" class="flex items-center px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Tugas & Asesmen Rubrik</span>
                        </a>
                        <a href="#presensi" class="flex items-center px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Presensi Dinamis</span>
                        </a>
                    </div>

                    <!-- Kelompok 2: Penjaminan Mutu OBE -->
                    <div class="space-y-1">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-muted px-2 block mb-1">
                            OBE & Mutu
                        </span>
                        <a href="#obe-capaian" class="flex items-center px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Matriks Ketercapaian CPL</span>
                        </a>
                        <a href="#gamifikasi" class="flex items-center px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Buku Besar XP & Lencana</span>
                        </a>
                    </div>

                    <!-- Kelompok 3: Portal & Referensi -->
                    <div class="space-y-1">
                        <span class="font-mono text-[10px] uppercase tracking-wider text-ink-muted px-2 block mb-1">
                            Portal
                        </span>
                        <a href="{{ route('marketing.home') }}" class="flex items-center justify-between px-2.5 py-1.5 rounded-[2px] text-ink-muted hover:text-ink hover:bg-paper transition-editorial">
                            <span>Website Kampus</span>
                            <x-icon name="arrow-up-right" class="w-3 h-3 text-ink-muted" />
                        </a>
                    </div>

                </nav>
            </div>

            <!-- Footer Sidebar: Profil Pengguna & Keluar -->
            <div class="p-4 border-t border-line bg-surface text-xs space-y-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-7 h-7 rounded-[2px] bg-paper border border-line text-ink font-mono font-semibold flex items-center justify-center text-xs shrink-0">
                        {{ substr(Auth::user()?->name ?? 'A', 0, 1) }}
                    </div>
                    <div class="truncate leading-tight">
                        <p class="font-medium text-ink truncate text-xs">{{ Auth::user()?->name }}</p>
                        <p class="font-mono text-[10px] text-ink-muted">{{ Auth::user()?->role?->label() }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Body Area -->
        <div class="flex-1 flex flex-col min-w-0 bg-paper">
            
            <!-- Topbar Ramping: Breadcrumb, Pencarian Ctrl+K, Tombol Pendamping Belajar -->
            <header class="h-14 border-b border-line bg-surface px-4 sm:px-6 flex items-center justify-between sticky top-0 z-30">
                <div class="flex items-center gap-3">
                    <button @click="mobileSidebar = true" class="md:hidden text-ink-muted hover:text-ink p-1 cursor-pointer">
                        <x-icon name="sliders" class="w-4 h-4" />
                    </button>
                    <!-- Breadcrumb -->
                    <div class="flex items-center gap-2 text-xs font-mono text-ink-muted">
                        <span>Arunika</span>
                        <span>/</span>
                        <span class="text-ink font-sans font-medium">@yield('page_title', 'Dashboard Pembelajaran')</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Search Input (Ctrl+K) -->
                    <div class="relative hidden sm:block">
                        <input 
                            type="text" 
                            placeholder="Cari materi, tugas, atau CPL (Ctrl+K)..." 
                            class="w-64 px-2.5 py-1.5 pl-8 rounded-[4px] bg-paper border border-line text-ink text-xs focus:outline-none focus:border-brand transition-editorial font-sans"
                        >
                        <x-icon name="search" class="w-3.5 h-3.5 text-ink-muted absolute left-2.5 top-2.5" />
                    </div>

                    <!-- Tombol Pemicu Pendamping Belajar (AI) - Teks Bersih + Shortcut Hint -->
                    <button 
                        @click="aiDrawerOpen = true" 
                        class="px-3 py-1.5 rounded-[4px] border border-line hover:border-ink bg-paper text-ink font-medium text-xs flex items-center gap-1.5 transition-editorial cursor-pointer"
                        title="Buka panel Pendamping Belajar (AI)"
                    >
                        <x-icon name="chat-text" class="w-3.5 h-3.5 text-brand" />
                        <span>Tanya Pendamping</span>
                        <span class="font-mono text-[10px] text-ink-muted hidden md:inline ml-1">Ctrl+/</span>
                    </button>

                    <!-- Role Badge Mono -->
                    <span class="font-mono text-[11px] px-2 py-0.5 border border-line rounded-[2px] bg-paper text-ink">
                        {{ Auth::user()?->role?->label() }}
                    </span>
                </div>
            </header>

            <!-- Page Main Content Area -->
            <main class="flex-1 p-4 sm:p-8 max-w-6xl w-full mx-auto">
                <!-- Success notification -->
                @if(session('success'))
                    <div class="mb-6 p-3.5 rounded-[4px] bg-brand-tint border border-brand text-brand text-xs font-medium flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <x-icon name="check-circle" class="w-4 h-4 text-brand shrink-0" />
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>

    </div>

    <!-- ==========================================
         PENDAMPING BELAJAR (PANEL CATATAN PINGGIR / MARGIN NOTES)
         ========================================== -->
    <div 
        x-show="aiDrawerOpen" 
        x-transition.opacity.duration.150ms 
        class="fixed inset-0 bg-ink/30 z-50 flex justify-end" 
        style="display: none;"
    >
        <div 
            class="w-full max-w-md bg-surface border-l border-line h-full flex flex-col justify-between p-6 shadow-none" 
            @click.away="aiDrawerOpen = false"
        >
            <div class="overflow-y-auto space-y-4">
                <!-- Header Panel Margin Notes -->
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <div>
                        <h3 class="font-display font-medium text-base text-ink">Pendamping Belajar</h3>
                        <p class="font-mono text-[10px] uppercase tracking-wider text-ink-muted">Tutor Kontekstual Berbasis RPS</p>
                    </div>
                    <button @click="aiDrawerOpen = false" class="text-ink-muted hover:text-ink text-lg cursor-pointer">&times;</button>
                </div>

                <!-- Kontrol Mode Sokrates -->
                <div class="p-3 rounded-[4px] border border-line bg-paper text-xs space-y-1.5">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-ink">Mode Sokrates</span>
                        <button 
                            @click="socraticMode = !socraticMode" 
                            :class="socraticMode ? 'bg-ink text-paper' : 'border border-line text-ink-muted'"
                            class="px-2 py-0.5 rounded-[2px] font-mono text-[10px] font-semibold transition-editorial cursor-pointer"
                        >
                            <span x-text="socraticMode ? 'AKTIF' : 'NONAKTIF'"></span>
                        </button>
                    </div>
                    <p class="text-[11px] text-ink-muted leading-relaxed">
                        Menuntun penalaran mahasiswa langkah demi langkah tanpa membagikan contekan instan.
                    </p>
                </div>

                <!-- Topik Cepat Konsultasi Akademik -->
                <div class="space-y-1.5">
                    <span class="font-mono text-[10px] uppercase text-ink-muted tracking-wider block">Topik Cepat:</span>
                    <div class="flex flex-wrap gap-1.5">
                        <button 
                            @click="aiPrompt = 'Jelaskan konsep Single Responsibility Principle (SRP) dengan analogi dunia nyata!'; askAi()" 
                            class="px-2 py-1 rounded-[2px] bg-paper border border-line hover:border-ink text-ink text-[11px] transition-editorial text-left cursor-pointer"
                        >
                            Prinsip SOLID SRP
                        </button>
                        <button 
                            @click="aiPrompt = 'Apa perbedaan Pewarisan dan Komposisi dalam OOP?'; askAi()" 
                            class="px-2 py-1 rounded-[2px] bg-paper border border-line hover:border-ink text-ink text-[11px] transition-editorial text-left cursor-pointer"
                        >
                            Inheritance vs Composition
                        </button>
                        <button 
                            @click="aiPrompt = 'Bagaimana nilai tugas saya berkontribusi pada CPL-02?'; askAi()" 
                            class="px-2 py-1 rounded-[2px] bg-paper border border-line hover:border-ink text-ink text-[11px] transition-editorial text-left cursor-pointer"
                        >
                            Capaian CPL-02
                        </button>
                    </div>
                </div>

                <!-- Tampilan Jawaban Catatan Pinggir -->
                <div class="min-h-56 p-4 rounded-[4px] bg-paper border border-line text-xs text-ink leading-relaxed space-y-3">
                    <template x-if="aiLoading">
                        <div class="flex items-center gap-2 text-ink-muted font-mono text-xs">
                            <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand animate-ping"></span>
                            <span>Menelaah silabus & materi kuliah...</span>
                        </div>
                    </template>
                    
                    <template x-if="!aiLoading && !aiResponse">
                        <p class="text-ink-muted italic">
                            Ajukan pertanyaan tentang materi kuliah, konsep kode, atau panduan asesmen CPL kepada Pendamping Belajar.
                        </p>
                    </template>

                    <!-- Konten Respons Catatan Pinggir dengan Garis Tepi dan Catatan Kaki -->
                    <div x-show="!aiLoading && aiResponse" class="space-y-2.5">
                        <div class="inline-flex items-center gap-1.5 font-mono text-[10px] uppercase tracking-wider text-ink-muted border-b border-line pb-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-brand"></span>
                            <span>Dihasilkan AI &middot; Periksa Kembali</span>
                        </div>
                        <div class="pl-3 border-l-2 border-brand text-xs text-ink whitespace-pre-wrap leading-relaxed" x-text="aiResponse"></div>
                        <div class="pt-2 border-t border-line text-[10px] font-mono text-ink-muted space-y-0.5">
                            <p>Rujukan: [1] Silabus PBO Pertemuan 5 &middot; SOLID Architecture</p>
                            <p>Evaluasi: Terhubung ke CPMK-02 & CPL-02</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulir Input Pertanyaan -->
            <div class="pt-4 border-t border-line space-y-2">
                <form @submit.prevent="askAi()" class="space-y-2">
                    <label class="block font-mono text-[10px] uppercase text-ink-muted tracking-wider">Pertanyaan Akademik</label>
                    <textarea 
                        x-model="aiPrompt" 
                        rows="2" 
                        placeholder="Tanyakan konsep teori atau logika kurikulum..." 
                        class="w-full px-3 py-2 rounded-[4px] bg-paper border border-line text-xs text-ink focus:outline-none focus:border-brand transition-editorial resize-none font-sans"
                    ></textarea>
                    <button 
                        type="submit" 
                        :disabled="aiLoading || !aiPrompt.trim()" 
                        class="w-full py-2 rounded-[4px] bg-ink hover:bg-black text-paper font-medium text-xs disabled:opacity-40 transition-editorial flex items-center justify-center gap-1.5 cursor-pointer"
                    >
                        <span>Tanya Pendamping Belajar</span>
                        <x-icon name="arrow-right" class="w-3.5 h-3.5 text-paper" />
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Developer Role Switcher (Toolbar Bawah, Ringkas, Tertutup Default) -->
    <div 
        x-data="{ open: false }" 
        class="fixed bottom-3 right-3 z-50 font-mono text-xs"
        @click.outside="open = false"
    >
        <div 
            x-show="open" 
            x-transition.opacity.duration.150ms
            class="mb-2 p-3 bg-surface border border-line rounded-[4px] shadow-sm max-w-xs space-y-2"
            style="display: none;"
        >
            <div class="flex items-center justify-between pb-1.5 border-b border-line">
                <span class="font-semibold text-ink uppercase tracking-wider text-[10px]">Ganti Peran Simulasi</span>
                <button @click="open = false" class="text-ink-muted hover:text-ink text-xs cursor-pointer">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-1 text-[11px]">
                <a href="{{ route('demo.login', 'student') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between {{ Auth::user()?->isStudent() ? 'font-semibold text-brand bg-paper' : 'text-ink-muted' }}">
                    <span>Mahasiswa</span>
                    <span class="text-ink-muted text-[10px]">Arya Pratama</span>
                </a>
                <a href="{{ route('demo.login', 'lecturer') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between {{ Auth::user()?->isLecturer() ? 'font-semibold text-brand bg-paper' : 'text-ink-muted' }}">
                    <span>Dosen</span>
                    <span class="text-ink-muted text-[10px]">Prof. Budi</span>
                </a>
                <a href="{{ route('demo.login', 'campus_admin') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between {{ Auth::user()?->isCampusAdmin() ? 'font-semibold text-brand bg-paper' : 'text-ink-muted' }}">
                    <span>Admin BAAK</span>
                    <span class="text-ink-muted text-[10px]">Dr. Hendra</span>
                </a>
                <a href="{{ route('demo.login', 'auditor') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between {{ Auth::user()?->isAuditor() ? 'font-semibold text-brand bg-paper' : 'text-ink-muted' }}">
                    <span>Auditor Mutu</span>
                    <span class="text-ink-muted text-[10px]">Ir. Siti Aminah</span>
                </a>
                <a href="{{ route('demo.login', 'industry_mentor') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between {{ Auth::user()?->isIndustryMentor() ? 'font-semibold text-brand bg-paper' : 'text-ink-muted' }}">
                    <span>Mentor Industri</span>
                    <span class="text-ink-muted text-[10px]">Rian Santoso</span>
                </a>
            </div>
        </div>

        <button 
            @click="open = !open" 
            class="px-2.5 py-1.5 bg-surface hover:bg-paper border border-line rounded-[4px] text-ink font-medium text-[11px] flex items-center gap-1.5 transition-editorial cursor-pointer"
            title="Simulasi Akun Peran"
        >
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand"></span>
            <span>Uji Peran: {{ Auth::user()?->role?->label() }}</span>
            <x-icon name="caret-down" class="w-3 h-3 text-ink-muted" />
        </button>
    </div>

    <!-- Script to execute AI Copilot request -->
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
