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
<body class="bg-paper text-ink font-sans antialiased selection:bg-brand selection:text-paper min-h-screen flex flex-col">

    <!-- Header Editorial: Wordmark, Teks Navigasi, Link Aksi, Border 1px -->
    <header class="border-b border-line bg-paper sticky top-0 z-40">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between">
            <!-- Brand Wordmark & Tanda Fajar -->
            <a href="{{ route('marketing.home') }}" class="flex items-center gap-2 group">
                <x-logo />
            </a>

            <!-- Navigasi Teks Murni (Tanpa Pill, Tanpa Ikon Berlebihan) -->
            <nav class="hidden md:flex items-center gap-7 text-sm font-medium text-ink-muted">
                <a href="#masalah-solusi" class="hover:text-ink transition-editorial">Masalah & Solusi</a>
                <a href="#fitur-unggulan" class="hover:text-ink transition-editorial">Fitur</a>
                <a href="#siklus-obe" class="hover:text-ink transition-editorial">Siklus OBE</a>
                <a href="#komparasi" class="hover:text-ink transition-editorial">Komparasi</a>
                <a href="#harga" class="hover:text-ink transition-editorial">Paket Kampus</a>
            </nav>

            <!-- Aksi Header -->
            <div class="flex items-center gap-4">
                <a href="#demo-form" class="text-sm font-medium text-ink-muted hover:text-ink transition-editorial underline underline-offset-4 decoration-line hover:decoration-ink">
                    Minta demo
                </a>
                <a href="{{ route('app.dashboard') }}" class="px-3.5 py-1.5 rounded-[4px] bg-ink text-paper hover:bg-black font-medium text-sm transition-editorial">
                    Buka Aplikasi
                </a>
            </div>
        </div>
    </header>

    <!-- Konten Halaman Utama -->
    <main class="flex-1">
        @yield('content')
    </main>

    <!-- Footer Editorial: Padat, Garis 1px, Anotasi Jelas -->
    <footer class="border-t border-line bg-surface pt-12 pb-10 text-xs text-ink-muted">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8 pb-10 border-b border-line">
                <div class="space-y-3 md:col-span-1">
                    <x-logo />
                    <p class="leading-relaxed text-ink-muted">
                        Arunika Learning Operating System dirancang khusus untuk perguruan tinggi di Indonesia. Menghubungkan proses belajar mengajar harian langsung dengan ketercapaian CPL dan akreditasi prodi.
                    </p>
                    <p class="font-mono text-[11px] text-ink-muted">
                        Data pada halaman ini adalah contoh simulasi.
                    </p>
                </div>

                <div>
                    <h4 class="font-mono uppercase tracking-wider text-[11px] text-ink font-semibold mb-3">Arsitektur OBE</h4>
                    <ul class="space-y-2 text-ink-muted">
                        <li><a href="#fitur-unggulan" class="hover:text-ink transition-editorial">Pemetaan CPL ↔ CPMK ↔ Tugas</a></li>
                        <li><a href="#siklus-obe" class="hover:text-ink transition-editorial">Continuous Quality Improvement (CQI)</a></li>
                        <li><a href="#komparasi" class="hover:text-ink transition-editorial">Laporan Standar LAM & BAN-PT</a></li>
                        <li><a href="#fitur-unggulan" class="hover:text-ink transition-editorial">Penilaian Rubrik Berbobot</a></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-mono uppercase tracking-wider text-[11px] text-ink font-semibold mb-3">Teknologi & Standar</h4>
                    <ul class="space-y-2 text-ink-muted">
                        <li><span>PWA Offline-First Cache</span></li>
                        <li><span>Sinkronisasi SIAKAD & Neo Feeder</span></li>
                        <li><span>Kepatuhan UU PDP No. 27/2022</span></li>
                        <li><span>Standar Permendikbudristek No. 53/2023</span></li>
                    </ul>
                </div>

                <div>
                    <h4 class="font-mono uppercase tracking-wider text-[11px] text-ink font-semibold mb-3">Kontak Institusi</h4>
                    <p class="mb-2 leading-relaxed">Untuk presentasi kurikulum dan uji coba institusi:</p>
                    <p class="font-mono text-ink">kemitraan@arunika.ac.id</p>
                    <p class="font-mono text-ink">+62 21 5088-0199</p>
                    <div class="pt-3">
                        <a href="#demo-form" class="inline-block px-3 py-1.5 rounded-[4px] border border-line hover:border-ink text-ink font-medium transition-editorial">
                            Formulir Penjadwalan
                        </a>
                    </div>
                </div>
            </div>

            <div class="pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-[11px] text-ink-muted">
                <p>&copy; 2026 Arunika. Hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-6">
                    <span class="font-mono">Versi 2.4.0 (Rilis Stabil)</span>
                    <a href="#" class="hover:text-ink transition-editorial">Kebijakan Privasi</a>
                    <a href="#" class="hover:text-ink transition-editorial">Dokumentasi API</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Developer Role Switcher (Toolbar Bawah, Ringkas, Tertutup Default) -->
    <div 
        x-data="{ open: false }" 
        class="fixed bottom-3 right-3 z-50 font-mono text-xs"
        @click.outside="open = false"
    >
        <!-- Panel Konten Switcher -->
        <div 
            x-show="open" 
            x-transition.opacity.duration.150ms
            class="mb-2 p-3 bg-surface border border-line rounded-[4px] shadow-sm max-w-xs space-y-2"
            style="display: none;"
        >
            <div class="flex items-center justify-between pb-1.5 border-b border-line">
                <span class="font-semibold text-ink uppercase tracking-wider text-[10px]">Simulasi Akun Peran</span>
                <button @click="open = false" class="text-ink-muted hover:text-ink text-xs">&times;</button>
            </div>
            <div class="grid grid-cols-1 gap-1 text-[11px]">
                <a href="{{ route('demo.login', 'student') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between">
                    <span>Mahasiswa</span>
                    <span class="text-ink-muted text-[10px]">Arya Pratama</span>
                </a>
                <a href="{{ route('demo.login', 'lecturer') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between">
                    <span>Dosen</span>
                    <span class="text-ink-muted text-[10px]">Prof. Budi</span>
                </a>
                <a href="{{ route('demo.login', 'campus_admin') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between">
                    <span>Admin BAAK</span>
                    <span class="text-ink-muted text-[10px]">Dr. Hendra</span>
                </a>
                <a href="{{ route('demo.login', 'auditor') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between">
                    <span>Auditor Mutu</span>
                    <span class="text-ink-muted text-[10px]">Ir. Siti Aminah</span>
                </a>
                <a href="{{ route('demo.login', 'industry_mentor') }}" class="px-2 py-1 rounded-[2px] hover:bg-paper hover:text-brand transition-editorial flex items-center justify-between">
                    <span>Mentor Industri</span>
                    <span class="text-ink-muted text-[10px]">Rian Santoso</span>
                </a>
            </div>
        </div>

        <!-- Tombol Pemicu Toolbar -->
        <button 
            @click="open = !open" 
            class="px-2.5 py-1.5 bg-surface hover:bg-paper border border-line rounded-[4px] text-ink font-medium text-[11px] flex items-center gap-1.5 transition-editorial cursor-pointer"
            title="Buka panel simulasi peran demo"
        >
            <span class="inline-block w-1.5 h-1.5 rounded-full bg-brand"></span>
            <span>Uji Peran</span>
            <x-icon name="caret-down" class="w-3 h-3 text-ink-muted" />
        </button>
    </div>

</body>
</html>
