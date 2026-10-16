<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Arunika — Learning Operating System untuk Perguruan Tinggi Indonesia')</title>
    <meta name="description" content="Arunika adalah Learning Operating System (LMS) berbasis Native OBE, Pendamping Belajar berbasis konteks, dan pelaporan akreditasi terstandar Dikti.">
    <link rel="icon" href="/favicon.ico">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-canvas text-text font-sans antialiased selection:bg-accent selection:text-white min-h-screen flex flex-col">

    <!-- Header: Surface Putih, Border Line 1px, Wordmark Landmark + Arunika -->
    <header class="border-b border-line bg-surface sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <!-- Brand Wordmark -->
            <a href="{{ route('marketing.home') }}" class="flex items-center gap-2 group">
                <x-logo />
            </a>

            <!-- Navigasi Teks Murni (Inter Variable) -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-text-muted">
                <a href="#masalah-solusi" class="hover:text-text transition-colors">Masalah & Solusi</a>
                <a href="#fitur-unggulan" class="hover:text-text transition-colors">Fitur</a>
                <a href="#siklus-obe" class="hover:text-text transition-colors">Siklus OBE</a>
                <a href="#komparasi" class="hover:text-text transition-colors">Komparasi</a>
                <a href="#harga" class="hover:text-text transition-colors">Paket Kampus</a>
            </nav>

            <!-- Aksi Header -->
            <div class="flex items-center gap-4">
                <a href="#demo-form" class="text-sm font-medium text-text-muted hover:text-text transition-colors underline underline-offset-4 decoration-line hover:decoration-text">
                    Minta demo
                </a>
                <a href="{{ route('app.dashboard') }}" class="px-4 py-2 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white font-medium text-sm transition-colors shadow-xs">
                    Buka Aplikasi
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Halaman Utama -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer: Latar Navy-900 (#1A2E5E), Tipografi Inter Bersih -->
    <footer class="border-t border-navy-900 bg-navy-900 pt-12 pb-10 text-xs text-white/70">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-white/10">
                <div class="space-y-3 md:col-span-1">
                    <x-logo variant="inverse" />
                    <p class="leading-relaxed text-white/70 mt-3">
                        Arunika Learning Operating System dirancang khusus untuk perguruan tinggi di Indonesia. Menghubungkan proses belajar mengajar harian langsung dengan ketercapaian CPL dan akreditasi prodi.
                    </p>
                    <p class="text-[11px] text-white/50">
                        Data pada halaman ini adalah contoh simulasi.
                    </p>
                </div>

                <div>
                    <h4 class="uppercase tracking-wider text-[11px] text-white font-semibold mb-3">Arsitektur OBE</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><a href="#fitur-unggulan" class="hover:text-white transition-colors">Pemetaan CPL &bull; CPMK &bull; Tugas</a></li>
                        <li><a href="#siklus-obe" class="hover:text-white transition-colors">Continuous Quality Improvement (CQI)</a></li>
                        <li><a href="#komparasi" class="hover:text-white transition-colors">Laporan Standar LAM & BAN-PT</a></li>
                        <li><a href="#fitur-unggulan" class="hover:text-white transition-colors">Penilaian Rubrik Berbobot</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="uppercase tracking-wider text-[11px] text-white font-semibold mb-3">Teknologi & Standar</h4>
                    <ul class="space-y-2 text-white/70">
                        <li><span>PWA Offline-First Cache</span></li>
                        <li><span>Sinkronisasi SIAKAD & Neo Feeder</span></li>
                        <li><span>Kepatuhan UU PDP No. 27/2022</span></li>
                        <li><span>Standar Permendikbudristek No. 53/2023</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="uppercase tracking-wider text-[11px] text-white font-semibold mb-3">Kontak Institusi</h4>
                    <p class="mb-2 leading-relaxed text-white/70">Untuk presentasi kurikulum dan uji coba institusi:</p>
                    <p class="text-white font-medium">kemitraan@arunika.ac.id</p>
                    <p class="text-white font-medium">+62 21 5088-0199</p>
                    <div class="pt-3">
                        <a href="#demo-form" class="inline-block px-3 py-1.5 rounded-[6px] border border-white/20 hover:border-white text-white font-medium transition-colors">
                            Formulir Penjadwalan
                        </a>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-white/60">
                <p>&copy; 2026 Arunika. Hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-6">
                    <span>Versi 2.4.0 (Rilis Stabil)</span>
                    <a href="#" class="hover:text-white transition-colors">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-white transition-colors">Dokumentasi API</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Developer Role Switcher (Toolbar Bawah, Ringkas, Tertutup Default) -->
    <div 
        x-data="{ open: false }" 
        class="fixed bottom-3 right-3 z-50 text-xs"
        @click.outside="open = false"
    >
        <!-- Panel Konten Switcher -->
        <div 
            x-show="open" 
            x-transition.opacity.duration.150ms
            class="mb-2 p-3 bg-surface border border-line rounded-[8px] shadow-card max-w-xs space-y-2"
            style="display: none;"
        >
            <div class="flex items-center justify-between pb-1.5 border-b border-line">
                <span class="font-semibold text-text uppercase tracking-wider text-[10px]">Simulasi Akun Peran</span>
                <button @click="open = false" class="text-text-muted hover:text-text text-xs cursor-pointer">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-1 text-[11px]">
                <a href="{{ route('demo.login', 'student') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between">
                    <span>Mahasiswa</span>
                    <span class="text-text-muted text-[10px]">Arya Pratama</span>
                </a>
                <a href="{{ route('demo.login', 'lecturer') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between">
                    <span>Dosen</span>
                    <span class="text-text-muted text-[10px]">Prof. Budi</span>
                </a>
                <a href="{{ route('demo.login', 'campus_admin') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between">
                    <span>Admin BAAK</span>
                    <span class="text-text-muted text-[10px]">Dr. Hendra</span>
                </a>
                <a href="{{ route('demo.login', 'auditor') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between">
                    <span>Auditor Mutu</span>
                    <span class="text-text-muted text-[10px]">Ir. Siti Aminah</span>
                </a>
                <a href="{{ route('demo.login', 'industry_mentor') }}" class="px-2.5 py-1.5 rounded-[6px] hover:bg-tile transition-colors flex items-center justify-between">
                    <span>Mentor Industri</span>
                    <span class="text-text-muted text-[10px]">Rian Santoso</span>
                </a>
            </div>
        </div>

        <!-- Tombol Pemicu Toolbar -->
        <button 
            @click="open = !open" 
            class="px-2.5 py-1.5 bg-surface hover:bg-tile border border-line rounded-[6px] text-text font-medium text-[11px] flex items-center gap-1.5 shadow-card transition-colors cursor-pointer"
            title="Buka panel simulasi peran demo"
        >
            <span class="inline-block w-2 h-2 rounded-full bg-success"></span>
            <span>Uji Peran</span>
            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
        </button>
    </div>

</body>
</html>
