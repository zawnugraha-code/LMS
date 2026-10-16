@extends('layouts.app')

@section('title', 'Materi Pembelajaran — Arunika')
@section('page_breadcrumb', 'Materi Pembelajaran')

@section('content')

    <div 
        x-data="{
            activeTab: 'all',
            bab1Open: true,
            bab2Open: true,
            bab3Open: false,
            selectedCourseFilter: 'IF2101',
            selectedTypeFilter: 'all',
            selectedStatusFilter: 'all',
            searchQuery: '',
            viewerModalOpen: false,
            selectedMaterial: null,
            openMaterial(item) {
                this.selectedMaterial = item;
                this.viewerModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI
             ========================================== -->
        <div class="flex items-start gap-3.5 mb-6">
            <div class="shrink-0 text-[#17306B] pt-0.5">
                <x-icon name="book-open" :size="36" :strokeWidth="2" class="text-[#17306B]" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Materi Pembelajaran
                </h1>
                <p class="text-xs sm:text-[13px] text-text-muted mt-1 max-w-2xl leading-relaxed">
                    Akses semua materi pembelajaran, mulai dari modul, slide, video, hingga referensi yang disediakan oleh dosen untuk mendukung proses belajar Anda.
                </p>
            </div>
        </div>

        <!-- ==========================================
             2. ACTIVE COURSE BANNER & PROGRESS CARD (PERSIS GAMBAR ACUAN)
             ========================================== -->
        <div class="bg-surface border border-line rounded-[10px] p-4 sm:p-5 mb-5 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-5">
            
            <!-- Left Banner: Icon, Title, Subtitle, Campus Photo -->
            <div class="flex items-center gap-4 flex-1 min-w-0">
                <!-- Dark Navy Square Icon with </> -->
                <div class="w-12 h-12 rounded-[10px] bg-[#17306B] text-white flex items-center justify-center shrink-0 shadow-2xs">
                    <x-icon name="code" :size="24" :strokeWidth="2.2" class="text-white" />
                </div>

                <!-- Info Kursus & Meta -->
                <div class="min-w-0 flex-1">
                    <span class="inline-block px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 mb-1">
                        IF2101
                    </span>
                    <h2 class="text-base sm:text-lg font-bold text-text truncate">
                        Pemrograman Berorientasi Objek & Desain Pola
                    </h2>
                    <p class="text-xs text-text-muted mt-0.5">
                        Semester 3 &bull; Teknik Informatika &bull; Kelas A
                    </p>
                </div>

                <!-- Modern Campus Building Thumbnail (Persis Gambar Acuan) -->
                <div class="w-60 h-22 sm:w-64 sm:h-22 rounded-[8px] overflow-hidden shrink-0 hidden lg:block border border-line shadow-2xs">
                    <img 
                        src="/images/campus-hero.jpg" 
                        alt="Universitas Nusantara Indonesia" 
                        class="w-full h-full object-cover"
                    >
                </div>
            </div>

            <!-- Right: Progress Materi Card (Persis Gambar Acuan) -->
            <div class="shrink-0 md:w-56 lg:w-64 border-t md:border-t-0 md:border-l border-line md:pl-6 pt-3 md:pt-0 flex flex-col justify-center">
                <span class="text-xs font-bold text-text block mb-2">
                    Progress Materi
                </span>

                <!-- Progress Track & Fill -->
                <div class="w-full h-2 bg-blue-100/70 rounded-full overflow-hidden">
                    <div class="h-full bg-[#2563EB] rounded-full transition-all duration-300" style="width: 35.7%;"></div>
                </div>

                <!-- Stats Text -->
                <div class="mt-2.5">
                    <span class="text-xs text-text-muted font-normal block leading-tight">
                        5 / 14 Materi
                    </span>
                    <span class="text-xs font-bold text-[#2563EB] mt-0.5 block leading-tight">
                        35.7% Selesai
                    </span>
                </div>
            </div>

        </div>

        <!-- ==========================================
             3. MATERIAL TYPE FILTER PILLS (PERSIS GAMBAR ACUAN)
             ========================================== -->
        <div class="overflow-x-auto py-1 mb-6 no-scrollbar">
            <div class="inline-flex items-center gap-2 whitespace-nowrap">
                
                <!-- Tab: Semua Materi (Active) -->
                <button 
                    type="button" 
                    @click="activeTab = 'all'"
                    :class="activeTab === 'all' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-4 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="file-text" :size="14" />
                    <span>Semua Materi</span>
                </button>

                <!-- Tab: Modul -->
                <button 
                    type="button" 
                    @click="activeTab = 'modul'"
                    :class="activeTab === 'modul' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="notebook-text" :size="14" />
                    <span>Modul</span>
                </button>

                <!-- Tab: Video -->
                <button 
                    type="button" 
                    @click="activeTab = 'video'"
                    :class="activeTab === 'video' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="video" :size="14" />
                    <span>Video</span>
                </button>

                <!-- Tab: Tugas -->
                <button 
                    type="button" 
                    @click="activeTab = 'tugas'"
                    :class="activeTab === 'tugas' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="clipboard-list" :size="14" />
                    <span>Tugas</span>
                </button>

                <!-- Tab: Kuis -->
                <button 
                    type="button" 
                    @click="activeTab = 'kuis'"
                    :class="activeTab === 'kuis' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="help-circle" :size="14" />
                    <span>Kuis</span>
                </button>

                <!-- Tab: Referensi -->
                <button 
                    type="button" 
                    @click="activeTab = 'referensi'"
                    :class="activeTab === 'referensi' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                    class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                >
                    <x-icon name="link" :size="14" />
                    <span>Referensi</span>
                </button>

            </div>
        </div>

        <!-- ==========================================
             4. MAIN LAYOUT: 2 KOLOM (KIRI ~68%, KANAN ~32%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): BAB & TOPIK ACCORDIONS
                 ========================================== -->
            <div class="lg:col-span-8 space-y-4">

                <!-- ==========================================
                     BAB 1: Pengenalan dan Konsep Dasar OOP (3/3 Materi)
                     ========================================== -->
                <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs transition-all">
                    <!-- Bab 1 Header (Clickable Accordion) -->
                    <button 
                        type="button"
                        @click="bab1Open = !bab1Open" 
                        class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-canvas/30 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Blue Circle Number 1 -->
                            <div class="w-7 h-7 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                1
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-text truncate">
                                    Pengenalan dan Konsep Dasar OOP
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5 line-clamp-1">
                                    Materi dasar mengenai konsep Object Oriented Programming dan paradigma pemrograman.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 ml-3">
                            <span class="text-xs font-semibold text-text-muted">
                                3 / 3 Materi
                            </span>
                            <div class="text-text-muted">
                                <x-icon name="chevron-down" :size="16" class="text-text-muted" />
                            </div>
                        </div>
                    </button>

                    <!-- Bab 1 Items List -->
                    <div x-show="bab1Open" x-collapse class="border-t border-line/60 divide-y divide-line/60">
                        
                        <!-- Item 1.1: Konsep Dasar OOP -->
                        <div 
                            @click="openMaterial({
                                code: '1.1',
                                title: 'Konsep Dasar OOP',
                                type: 'Modul',
                                duration: '45 menit',
                                date: '10 Sep 2026',
                                status: 'Selesai',
                                icon: 'file-text',
                                description: 'Pembahasan mendalam tentang pilar utama OOP: Abstraction, Encapsulation, Inheritance, dan Polymorphism dalam konteks pengembangan software modern.',
                                fileUrl: '#download-pdf',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="file-text" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        1.1 Konsep Dasar OOP
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Modul &bull; 45 menit &bull; Dibuat 10 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" :size="11" />
                                    <span>Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                        <!-- Item 1.2: Video Pembahasan OOP -->
                        <div 
                            @click="openMaterial({
                                code: '1.2',
                                title: 'Video Pembahasan OOP',
                                type: 'Video',
                                duration: '1 jam',
                                date: '11 Sep 2026',
                                status: 'Selesai',
                                icon: 'play',
                                description: 'Rekaman video perkuliahan sesi 1: Paradigma OOP vs Procedural, live coding implementasi class, object, dan memory allocation di Java.',
                                fileUrl: '#watch-video',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="play" :size="14" class="fill-[#2563EB]" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        1.2 Video Pembahasan OOP
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Video &bull; 1 jam &bull; Dibuat 11 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" :size="11" />
                                    <span>Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                        <!-- Item 1.3: Studi Kasus dan Latihan -->
                        <div 
                            @click="openMaterial({
                                code: '1.3',
                                title: 'Studi Kasus dan Latihan',
                                type: 'Referensi',
                                duration: '30 menit',
                                date: '12 Sep 2026',
                                status: 'Selesai',
                                icon: 'link',
                                description: 'Latihan soal mandiri dan studi kasus perancangan sistem perbankan sederhana dengan prinsip enkapsulasi data nasabah.',
                                fileUrl: '#external-resource',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="link" :size="14" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        1.3 Studi Kasus dan Latihan
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Referensi &bull; 30 menit &bull; Dibuat 12 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" :size="11" />
                                    <span>Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ==========================================
                     BAB 2: Desain Pola (Design Pattern) (2/4 Materi)
                     ========================================== -->
                <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs transition-all">
                    <!-- Bab 2 Header (Clickable Accordion) -->
                    <button 
                        type="button"
                        @click="bab2Open = !bab2Open" 
                        class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-canvas/30 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Blue Circle Number 2 -->
                            <div class="w-7 h-7 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                2
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-text truncate">
                                    Desain Pola (Design Pattern)
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5 line-clamp-1">
                                    Mempelajari berbagai desain pola yang umum digunakan dalam pengembangan aplikasi.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 ml-3">
                            <span class="text-xs font-semibold text-text-muted">
                                2 / 4 Materi
                            </span>
                            <div class="text-text-muted">
                                <x-icon name="chevron-down" :size="16" class="text-text-muted" />
                            </div>
                        </div>
                    </button>

                    <!-- Bab 2 Items List -->
                    <div x-show="bab2Open" x-collapse class="border-t border-line/60 divide-y divide-line/60">
                        
                        <!-- Item 2.1: Singleton Pattern -->
                        <div 
                            @click="openMaterial({
                                code: '2.1',
                                title: 'Singleton Pattern',
                                type: 'Modul',
                                duration: '50 menit',
                                date: '14 Sep 2026',
                                status: 'Selesai',
                                icon: 'file-text',
                                description: 'Studi mendalam implementasi Creational Design Pattern: Singleton untuk koneksi Database connection pool yang aman thread-safe.',
                                fileUrl: '#download-pdf',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="file-text" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        2.1 Singleton Pattern
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Modul &bull; 50 menit &bull; Dibuat 14 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" :size="11" />
                                    <span>Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                        <!-- Item 2.2: Factory Pattern -->
                        <div 
                            @click="openMaterial({
                                code: '2.2',
                                title: 'Factory Pattern',
                                type: 'Modul',
                                duration: '40 menit',
                                date: '15 Sep 2026',
                                status: 'Selesai',
                                icon: 'file-text',
                                description: 'Konsep Factory Method dan Abstract Factory untuk loose-coupling instansiasi objek pembayaran multi-gateway.',
                                fileUrl: '#download-pdf',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="file-text" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        2.2 Factory Pattern
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Modul &bull; 40 menit &bull; Dibuat 15 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-600">
                                    <x-icon name="check" :size="11" />
                                    <span>Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                        <!-- Item 2.3: Observer Pattern (Belum Selesai) -->
                        <div 
                            @click="openMaterial({
                                code: '2.3',
                                title: 'Observer Pattern',
                                type: 'Video',
                                duration: '55 menit',
                                date: '16 Sep 2026',
                                status: 'Belum Selesai',
                                icon: 'play',
                                description: 'Behavioral Pattern: Mekanisme publish-subscribe event notification pada modul notifikasi email dan SMS nasabah.',
                                fileUrl: '#watch-video',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="play" :size="14" class="fill-[#2563EB]" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        2.3 Observer Pattern
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Video &bull; 55 menit &bull; Dibuat 16 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-blue-50 text-[#2563EB] border border-blue-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB]"></span>
                                    <span>Belum Selesai</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                        <!-- Item 2.4: Implementasi Design Pattern (Tersedia Nanti) -->
                        <div 
                            @click="openMaterial({
                                code: '2.4',
                                title: 'Implementasi Design Pattern',
                                type: 'Tugas',
                                duration: 'Deadline 20 Sep 2026',
                                date: '20 Sep 2026',
                                status: 'Tersedia Nanti',
                                icon: 'clipboard-list',
                                description: 'Tugas proyek mandiri: Refaktorisasi legacy code monolitik menjadi clean architecture dengan 3 buah design pattern.',
                                fileUrl: '#assignment-brief',
                                lecturer: 'Prof. Dr. Budi Rahardjo, Ph.D.'
                            })"
                            class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group"
                        >
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                                    <x-icon name="clipboard-list" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate group-hover:text-[#2563EB] transition-colors">
                                        2.4 Implementasi Design Pattern
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Tugas &bull; Deadline 20 Sep 2026
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-3 shrink-0">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-600">
                                    <x-icon name="lock" :size="11" />
                                    <span>Tersedia Nanti</span>
                                </span>
                                <x-icon name="chevron-right" :size="14" class="text-text-muted group-hover:text-text transition-colors" />
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ==========================================
                     BAB 3: Framework dan Studi Kasus Lanjutan (0/3 Materi)
                     ========================================== -->
                <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs transition-all">
                    <!-- Bab 3 Header (Clickable Accordion) -->
                    <button 
                        type="button"
                        @click="bab3Open = !bab3Open" 
                        class="w-full p-4 sm:p-5 flex items-center justify-between text-left hover:bg-canvas/30 transition-colors cursor-pointer"
                    >
                        <div class="flex items-center gap-3.5 min-w-0">
                            <!-- Blue Circle Number 3 -->
                            <div class="w-7 h-7 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-2xs">
                                3
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-bold text-sm text-text truncate">
                                    Framework dan Studi Kasus Lanjutan
                                </h3>
                                <p class="text-xs text-text-muted mt-0.5 line-clamp-1">
                                    Penerapan OOP dan desain pola dalam pengembangan aplikasi nyata.
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 shrink-0 ml-3">
                            <span class="text-xs font-semibold text-text-muted">
                                0 / 3 Materi
                            </span>
                            <div class="text-text-muted">
                                <x-icon name="chevron-right" :size="16" class="text-text-muted" x-show="!bab3Open" />
                                <x-icon name="chevron-down" :size="16" class="text-text-muted" x-show="bab3Open" style="display: none;" />
                            </div>
                        </div>
                    </button>

                    <!-- Bab 3 Items List (Collapsed by Default) -->
                    <div x-show="bab3Open" x-collapse class="border-t border-line/60 divide-y divide-line/60" style="display: none;">
                        
                        <!-- Item 3.1 -->
                        <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors opacity-75">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                    <x-icon name="file-text" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate">
                                        3.1 Arsitektur Microservices & Spring Core
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Modul &bull; 60 menit &bull; Rilis 28 Sep 2026
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                <x-icon name="lock" :size="11" />
                                <span>Terkunci</span>
                            </span>
                        </div>

                        <!-- Item 3.2 -->
                        <div class="p-4 sm:px-6 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors opacity-75">
                            <div class="flex items-center gap-3.5 min-w-0">
                                <div class="w-8 h-8 rounded-full bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                                    <x-icon name="video" :size="15" />
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-semibold text-text truncate">
                                        3.2 Implementasi Core Banking API
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Video &bull; 1.5 jam &bull; Rilis 01 Okt 2026
                                    </p>
                                </div>
                            </div>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 text-slate-500">
                                <x-icon name="lock" :size="11" />
                                <span>Terkunci</span>
                            </span>
                        </div>

                    </div>
                </div>

            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): WIDGET FILTER, RINGKASAN, AKTIVITAS & COPILOT
                 ========================================== -->
            <div class="lg:col-span-4 space-y-4">
                
                <!-- 1. KARTU FILTER MATA KULIAH (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <!-- Search Input & Filter Button -->
                    <div class="flex items-center gap-2">
                        <div class="relative flex-1">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                                <x-icon name="search" :size="14" />
                            </span>
                            <input 
                                type="text" 
                                x-model="searchQuery"
                                placeholder="Cari materi berdasarkan judul..." 
                                class="w-full pl-9 pr-3 py-2 rounded-[6px] border border-line bg-canvas/40 text-xs text-text placeholder:text-text-subtle focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors"
                            >
                        </div>
                        <button 
                            type="button" 
                            class="px-3 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-2xs"
                        >
                            <x-icon name="filter" :size="13" class="text-text-muted" />
                            <span>Filter</span>
                            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                        </button>
                    </div>

                    <!-- Label: Filter Mata Kuliah -->
                    <label class="block text-xs font-bold text-text mt-4 mb-2.5">
                        Filter Mata Kuliah
                    </label>

                    <!-- 3 Dropdown Filters -->
                    <div class="space-y-2.5">
                        
                        <!-- Dropdown 1: Semua Mata Kuliah -->
                        <div class="relative">
                            <button 
                                type="button" 
                                class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs text-text flex items-center justify-between transition-colors cursor-pointer shadow-2xs"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <x-icon name="graduation-cap" :size="14" class="text-text-muted shrink-0" />
                                    <span class="truncate">Semua Mata Kuliah</span>
                                </div>
                                <x-icon name="chevron-down" :size="12" class="text-text-muted shrink-0" />
                            </button>
                        </div>

                        <!-- Dropdown 2: Semua Jenis Materi -->
                        <div class="relative">
                            <button 
                                type="button" 
                                class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs text-text flex items-center justify-between transition-colors cursor-pointer shadow-2xs"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <x-icon name="layers" :size="14" class="text-text-muted shrink-0" />
                                    <span class="truncate">Semua Jenis Materi</span>
                                </div>
                                <x-icon name="chevron-down" :size="12" class="text-text-muted shrink-0" />
                            </button>
                        </div>

                        <!-- Dropdown 3: Semua Status -->
                        <div class="relative">
                            <button 
                                type="button" 
                                class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs text-text flex items-center justify-between transition-colors cursor-pointer shadow-2xs"
                            >
                                <div class="flex items-center gap-2 truncate">
                                    <x-icon name="check-circle" :size="14" class="text-text-muted shrink-0" />
                                    <span class="truncate">Semua Status</span>
                                </div>
                                <x-icon name="chevron-down" :size="12" class="text-text-muted shrink-0" />
                            </button>
                        </div>

                    </div>
                </x-ui.card>

                <!-- 2. KARTU RINGKASAN MATERI (4 Mini Tiles in 1 Row) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <h3 class="font-bold text-sm text-text mb-3.5">
                        Ringkasan Materi
                    </h3>

                    <div class="grid grid-cols-4 gap-2">
                        
                        <!-- Tile 1: 14 Total Materi -->
                        <div class="p-2.5 rounded-[8px] border border-line bg-canvas/40 flex flex-col items-start justify-between min-h-[78px]">
                            <div class="w-6 h-6 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="file-text" :size="13" />
                            </div>
                            <div class="mt-2">
                                <span class="text-lg font-bold text-text tabular-nums leading-none block">14</span>
                                <span class="text-[10px] text-text-muted mt-1 block truncate">Total Materi</span>
                            </div>
                        </div>

                        <!-- Tile 2: 5 Selesai -->
                        <div class="p-2.5 rounded-[8px] border border-line bg-canvas/40 flex flex-col items-start justify-between min-h-[78px]">
                            <div class="w-6 h-6 rounded-[6px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <x-icon name="check" :size="13" />
                            </div>
                            <div class="mt-2">
                                <span class="text-lg font-bold text-text tabular-nums leading-none block">5</span>
                                <span class="text-[10px] text-text-muted mt-1 block truncate">Selesai</span>
                            </div>
                        </div>

                        <!-- Tile 3: 6 Dalam Proses -->
                        <div class="p-2.5 rounded-[8px] border border-line bg-canvas/40 flex flex-col items-start justify-between min-h-[78px]">
                            <div class="w-6 h-6 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="clock" :size="13" />
                            </div>
                            <div class="mt-2">
                                <span class="text-lg font-bold text-text tabular-nums leading-none block">6</span>
                                <span class="text-[10px] text-text-muted mt-1 block truncate">Dalam Proses</span>
                            </div>
                        </div>

                        <!-- Tile 4: 3 Terkunci -->
                        <div class="p-2.5 rounded-[8px] border border-line bg-canvas/40 flex flex-col items-start justify-between min-h-[78px]">
                            <div class="w-6 h-6 rounded-[6px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <x-icon name="lock" :size="13" />
                            </div>
                            <div class="mt-2">
                                <span class="text-lg font-bold text-text tabular-nums leading-none block">3</span>
                                <span class="text-[10px] text-text-muted mt-1 block truncate">Terkunci</span>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

                <!-- 3. KARTU AKTIVITAS TERBARU (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <h3 class="font-bold text-sm text-text">
                            Aktivitas Terbaru
                        </h3>
                        <a href="#aktivitas" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div class="space-y-4">
                        
                        <!-- Aktivitas 1: Selesai Konsep Dasar OOP -->
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="check-circle" :size="15" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Anda menyelesaikan materi Konsep Dasar OOP
                                </h4>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    IF2101 &bull; 12 Sep 2026, 14:32
                                </p>
                            </div>
                        </div>

                        <!-- Aktivitas 2: Video baru tersedia Desain Pola -->
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="play" :size="13" class="fill-blue-600" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Video baru tersedia: Desain Pola
                                </h4>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    IF2101 &bull; 12 Sep 2026, 10:15
                                </p>
                            </div>
                        </div>

                        <!-- Aktivitas 3: Tugas Praktikum 1 telah dibuka -->
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="calendar" :size="13" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Tugas Praktikum 1 telah dibuka
                                </h4>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    IF2101 &bull; 11 Sep 2026, 16:45
                                </p>
                            </div>
                        </div>

                        <!-- Aktivitas 4: Materi referensi Clean Code -->
                        <div class="flex items-start gap-3">
                            <div class="w-7 h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="link" :size="13" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Materi referensi: Clean Code
                                </h4>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    IF2101 &bull; 10 Sep 2026, 09:20
                                </p>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

                <!-- 4. KARTU ARUNIKA AI COPILOT (Persis Gambar Acuan) -->
                <div class="rounded-[12px] p-4 sm:p-4.5 border border-indigo-100 bg-gradient-to-br from-[#EFF6FF] via-[#F5F3FF] to-[#FAF5FF] shadow-2xs relative overflow-hidden">
                    <div class="flex items-start gap-3.5">
                        <!-- Cute Bot Icon -->
                        <div class="w-10 h-10 rounded-full bg-white text-[#2563EB] shadow-2xs border border-blue-100 flex items-center justify-center shrink-0">
                            <x-icon name="bot" :size="20" class="text-[#2563EB]" />
                        </div>

                        <!-- Text Info -->
                        <div class="min-w-0 flex-1">
                            <h4 class="text-sm font-bold text-text">
                                Arunika AI Copilot
                            </h4>
                            <p class="text-xs text-text-muted mt-0.5 leading-snug">
                                Tanya materi, rangkum, atau buat flashcard secara cerdas dengan AI.
                            </p>

                            <!-- Button Action -->
                            <button 
                                type="button" 
                                @click="aiDrawerOpen = true; aiPrompt = 'Tolong berikan ringkasan materi Konsep Dasar OOP dan 3 contoh soal evaluasi CPMK.'"
                                class="mt-3.5 px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                            >
                                <span>Coba Sekarang</span>
                                <x-icon name="arrow-right" :size="13" />
                            </button>
                        </div>
                    </div>
                </div>

            </div>

        </div>

        <!-- ==========================================
             5. MODAL INTERAKTIF: VIEWER MATERI PEMBELAJARAN
             ========================================== -->
        <div 
            x-show="viewerModalOpen" 
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="viewerModalOpen = false"
        >
            <div 
                @click.outside="viewerModalOpen = false"
                class="bg-surface border border-line rounded-[12px] max-w-2xl w-full p-6 shadow-card space-y-5"
            >
                <!-- Modal Header -->
                <div class="flex items-start justify-between gap-4 pb-4 border-b border-line">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 mt-0.5">
                            <x-icon name="book-open" :size="18" />
                        </div>
                        <div>
                            <div class="inline-flex items-center gap-2 mb-1">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-700" x-text="selectedMaterial?.type"></span>
                                <span class="text-xs text-text-muted font-medium" x-text="selectedMaterial?.code"></span>
                            </div>
                            <h3 class="text-base font-bold text-text" x-text="selectedMaterial?.title"></h3>
                            <p class="text-xs text-text-muted mt-0.5" x-text="selectedMaterial?.lecturer"></p>
                        </div>
                    </div>
                    <button 
                        type="button" 
                        @click="viewerModalOpen = false" 
                        class="text-text-muted hover:text-text p-1 cursor-pointer transition-colors"
                    >
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Modal Body: Deskripsi & Informasi -->
                <div class="space-y-4 text-xs text-text">
                    <div class="p-3.5 rounded-[8px] bg-canvas border border-line">
                        <span class="font-bold text-text block mb-1">Ringkasan Silabus & RPS:</span>
                        <p class="text-text-muted leading-relaxed" x-text="selectedMaterial?.description"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div class="p-3 rounded-[6px] border border-line bg-surface">
                            <span class="text-[11px] text-text-muted block">Estimasi Durasi</span>
                            <span class="font-semibold text-text text-xs mt-0.5 block" x-text="selectedMaterial?.duration"></span>
                        </div>
                        <div class="p-3 rounded-[6px] border border-line bg-surface">
                            <span class="text-[11px] text-text-muted block">Tanggal Publikasi</span>
                            <span class="font-semibold text-text text-xs mt-0.5 block" x-text="selectedMaterial?.date"></span>
                        </div>
                    </div>

                    <!-- Dokumen Terlampir -->
                    <div class="p-3.5 rounded-[8px] border border-blue-100 bg-blue-50/40 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2.5">
                            <x-icon name="file-text" :size="18" class="text-blue-600" />
                            <div>
                                <p class="font-semibold text-text text-xs" x-text="selectedMaterial ? selectedMaterial.title + '.pdf' : ''"></p>
                                <p class="text-[10px] text-text-muted">Dokumen PDF &bull; 2.4 MB &bull; RPS Verified</p>
                            </div>
                        </div>
                        <a 
                            href="#unduh" 
                            class="px-3 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-medium inline-flex items-center gap-1 transition-colors shadow-2xs"
                        >
                            <x-icon name="download" :size="13" />
                            <span>Unduh Materi</span>
                        </a>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-between pt-4 border-t border-line">
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-text-muted">Status:</span>
                        <span 
                            class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                            :class="selectedMaterial?.status === 'Selesai' ? 'bg-emerald-50 text-emerald-700' : 'bg-blue-50 text-blue-700'"
                            x-text="selectedMaterial?.status"
                        ></span>
                    </div>
                    <button 
                        type="button" 
                        @click="viewerModalOpen = false" 
                        class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text cursor-pointer transition-colors shadow-2xs"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
