@extends('layouts.app')

@section('title', 'Diskusi — Arunika')
@section('page_breadcrumb', 'Diskusi')

@section('content')

    <div 
        x-data="{
            activeTab: 'all',
            searchQuery: '',
            selectedCategory: 'all',
            newThreadModalOpen: false,
            detailModalOpen: false,
            selectedThread: null,
            openThread(thread) {
                this.selectedThread = thread;
                this.detailModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI & TOMBOL BUAT DISKUSI
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <!-- Left Header -->
            <div class="flex items-start gap-3.5">
                <div class="w-10 h-10 rounded-full bg-[#17306B] text-white flex items-center justify-center shrink-0 mt-0.5 shadow-2xs">
                    <x-icon name="message-circle" :size="20" class="text-white fill-white" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Diskusi
                    </h1>
                    <p class="text-xs sm:text-[13px] text-text-muted mt-1 max-w-xl leading-relaxed">
                        Diskusi, tanya jawab, dan berbagi pengetahuan bersama dosen dan teman kelas.
                    </p>
                </div>
            </div>

            <!-- Right Header Button: + Buat Diskusi Baru -->
            <button 
                type="button" 
                @click="newThreadModalOpen = true"
                class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer self-start sm:self-auto shrink-0"
            >
                <x-icon name="plus" :size="14" />
                <span>Buat Diskusi Baru</span>
            </button>
        </div>

        <!-- ==========================================
             2. FILTER TABS & SEARCH BAR (PERSIS GAMBAR ACUAN)
             ========================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-5">
            
            <!-- Left: Tab Pills (Semua, Forum Kelas, Tanya Jawab, Pengumuman, Topik Populer) -->
            <div class="overflow-x-auto py-1 no-scrollbar">
                <div class="inline-flex items-center gap-2 whitespace-nowrap">
                    
                    <!-- Tab 1: Semua (Active) -->
                    <button 
                        type="button" 
                        @click="activeTab = 'all'; selectedCategory = 'all'"
                        :class="activeTab === 'all' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-4 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="message-square" :size="14" />
                        <span>Semua</span>
                    </button>

                    <!-- Tab 2: Forum Kelas -->
                    <button 
                        type="button" 
                        @click="activeTab = 'forum'; selectedCategory = 'forum'"
                        :class="activeTab === 'forum' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="notebook-text" :size="14" />
                        <span>Forum Kelas</span>
                    </button>

                    <!-- Tab 3: Tanya Jawab -->
                    <button 
                        type="button" 
                        @click="activeTab = 'tanya'; selectedCategory = 'tanya'"
                        :class="activeTab === 'tanya' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="help-circle" :size="14" />
                        <span>Tanya Jawab</span>
                    </button>

                    <!-- Tab 4: Pengumuman -->
                    <button 
                        type="button" 
                        @click="activeTab = 'pengumuman'; selectedCategory = 'pengumuman'"
                        :class="activeTab === 'pengumuman' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="megaphone" :size="14" />
                        <span>Pengumuman</span>
                    </button>

                    <!-- Tab 5: Topik Populer -->
                    <button 
                        type="button" 
                        @click="activeTab = 'populer'; selectedCategory = 'all'"
                        :class="activeTab === 'populer' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-100 font-medium'"
                        class="px-3.5 py-1.5 rounded-[6px] text-xs inline-flex items-center gap-1.5 transition-colors cursor-pointer"
                    >
                        <x-icon name="trophy" :size="14" />
                        <span>Topik Populer</span>
                    </button>

                </div>
            </div>

            <!-- Right: Search Bar & Filter Button -->
            <div class="flex items-center gap-2 shrink-0">
                <div class="relative w-full sm:w-60">
                    <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-text-muted">
                        <x-icon name="search" :size="13" />
                    </span>
                    <input 
                        type="text" 
                        x-model="searchQuery"
                        placeholder="Cari topik diskusi..." 
                        class="w-full pl-8 pr-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs text-text placeholder:text-text-subtle focus:outline-none focus:border-[#2563EB] transition-colors shadow-2xs"
                    >
                </div>
                <button 
                    type="button" 
                    class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer shrink-0 shadow-2xs"
                >
                    <x-icon name="filter" :size="13" class="text-text-muted" />
                    <span>Filter</span>
                </button>
            </div>

        </div>

        <!-- ==========================================
             3. MAIN LAYOUT: 2 KOLOM (KIRI ~68%, KANAN ~32%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): KOTAK TULIS DISKUSI & LIST THREAD
                 ========================================== -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Kotak Tulis Diskusi Cepat (Persis Gambar Acuan) -->
                <div class="bg-surface border border-line rounded-[10px] p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-2xs">
                    <div class="flex items-center gap-3.5 min-w-0 flex-1">
                        <div class="w-10 h-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="file-text" :size="18" />
                        </div>
                        <div class="min-w-0 flex-1 cursor-pointer" @click="newThreadModalOpen = true">
                            <h3 class="font-bold text-xs sm:text-sm text-text truncate">
                                Ada pertanyaan atau ingin berbagi sesuatu?
                            </h3>
                            <p class="text-xs text-text-muted mt-0.5 truncate hover:text-text transition-colors">
                                Tulis di sini untuk memulai diskusi baru...
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0 self-end sm:self-auto">
                        <button 
                            type="button" 
                            @click="newThreadModalOpen = true"
                            class="px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-medium text-text inline-flex items-center gap-1.5 transition-colors cursor-pointer shadow-2xs"
                        >
                            <x-icon name="paperclip" :size="13" class="text-text-muted" />
                            <span>Lampirkan File</span>
                        </button>
                        <button 
                            type="button" 
                            @click="newThreadModalOpen = true"
                            class="px-4 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                        >
                            <span>Kirim</span>
                            <x-icon name="send" :size="12" />
                        </button>
                    </div>
                </div>

                <!-- ==========================================
                     DAFTAR THREAD DISKUSI (1 KARTU UTUH DENGAN GARIS PEMISAH PERSIS ACUAN)
                     ========================================== -->
                <div class="bg-surface border border-line rounded-[10px] divide-y divide-line/70 shadow-2xs overflow-hidden">

                    <!-- Thread 1 (Pinned / Pengumuman Dosen) -->
                    <div 
                        @click="openThread({
                            id: 1,
                            title: 'Panduan Penggunaan Fitur AI Copilot di Arunika',
                            author: 'Dr. Budi Rahardjo, S.T., M.Kom.',
                            role: 'Dosen Pengampu',
                            course: 'Pemrograman Berorientasi Objek & Desain Pola',
                            category: 'Pengumuman',
                            categoryColor: 'bg-blue-50 text-blue-600',
                            snippet: 'Halo semua, berikut adalah panduan lengkap penggunaan fitur AI Copilot di Arunika, termasuk contoh prompt akademis, verifikasi rujukan RPS, dan kepatuhan integritas akademik dalam penulisan kode.',
                            commentsCount: 142,
                            viewsCount: '2.4k',
                            time: '3 hari yang lalu',
                            avatar: '/images/avatar-budi.jpg',
                            isPinned: true
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <!-- Author Avatar -->
                                <div class="w-9 h-9 rounded-full bg-navy-800 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs overflow-hidden mt-0.5">
                                    <img src="/images/avatar-budi.jpg" alt="Dr. Budi" class="w-full h-full object-cover">
                                </div>

                                <!-- Content -->
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <x-icon name="pin" :size="13" class="text-[#2563EB] shrink-0" />
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Panduan Penggunaan Fitur AI Copilot di Arunika
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Dr. Budi Rahardjo, S.T., M.Kom.</span> &bull; Mata Kuliah: Pemrograman Berorientasi Objek &amp; Desain Pola
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-600">
                                            Pengumuman
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Halo semua, berikut adalah panduan lengkap penggunaan fitur AI Copilot di Arunika, termasuk contoh ...
                                    </p>
                                </div>
                            </div>

                            <!-- Right Stats & Menu -->
                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>142</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>2.4k</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        3 hari yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 2 (Andi Saputra - Tanya Jawab) -->
                    <div 
                        @click="openThread({
                            id: 2,
                            title: 'Kesulitan memahami konsep Inheritance di OOP',
                            author: 'Andi Saputra',
                            role: 'Mahasiswa',
                            course: 'PBO & Desain Pola',
                            category: 'Tanya Jawab',
                            categoryColor: 'bg-emerald-50 text-emerald-600',
                            snippet: 'Saya masih bingung dengan konsep inheritance dan bedanya dengan polymorphism. Ada yang bisa menjelaskan dengan studi kasus konkret misalnya sistem pembayaran perbankan?',
                            commentsCount: 28,
                            viewsCount: 567,
                            time: '5 jam yang lalu',
                            initials: 'AS',
                            avatarBg: 'bg-purple-600'
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <!-- Initials Avatar AS -->
                                <div class="w-9 h-9 rounded-full bg-purple-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs mt-0.5">
                                    AS
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Kesulitan memahami konsep Inheritance di OOP
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Andi Saputra</span> &bull; Mata Kuliah: PBO &amp; Desain Pola
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-600">
                                            Tanya Jawab
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Saya masih bingung dengan konsep inheritance dan bedanya dengan polymorphism. Ada yang bisa ...
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>28</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>567</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        5 jam yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 3 (Siti Nurhaliza - Diskusi) -->
                    <div 
                        @click="openThread({
                            id: 3,
                            title: 'Rekomendasi laptop untuk kebutuhan pemrograman',
                            author: 'Siti Nurhaliza',
                            role: 'Mahasiswa',
                            course: 'Basis Data & Sistem Terdistribusi',
                            category: 'Diskusi',
                            categoryColor: 'bg-purple-50 text-purple-600',
                            snippet: 'Menurut kalian laptop dengan spesifikasi seperti apa yang cocok untuk pemrograman, terutama untuk menjalankan Docker, database Postgres, dan Android Studio tanpa throttling?',
                            commentsCount: 45,
                            viewsCount: 892,
                            time: '7 jam yang lalu',
                            avatar: '/images/avatar-siti.jpg',
                            isPinned: false
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full bg-slate-200 text-white flex items-center justify-center shrink-0 shadow-2xs overflow-hidden mt-0.5">
                                    <img src="/images/avatar-siti.jpg" alt="Siti Nurhaliza" class="w-full h-full object-cover">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Rekomendasi laptop untuk kebutuhan pemrograman
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Siti Nurhaliza</span> &bull; Mata Kuliah: Basis Data &amp; Sistem Terdistribusi
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-600">
                                            Diskusi
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Menurut kalian laptop dengan spesifikasi seperti apa yang cocok untuk pemrograman, terutama untuk ...
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>45</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>892</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        7 jam yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 4 (Raka Maulana - Forum Kelas) -->
                    <div 
                        @click="openThread({
                            id: 4,
                            title: 'Tugas 2 - Normalisasi Database',
                            author: 'Raka Maulana',
                            role: 'Mahasiswa',
                            course: 'Basis Data & Sistem Terdistribusi',
                            category: 'Forum Kelas',
                            categoryColor: 'bg-blue-50 text-blue-600',
                            snippet: 'Apakah ada yang sudah selesai mengerjakan tugas 2? Saya masih bingung di bagian normalisasi 3NF pada tabel transaksi penjualan dan relasi transitive dependency-nya.',
                            commentsCount: 19,
                            viewsCount: 342,
                            time: '10 jam yang lalu',
                            initials: 'RM',
                            avatarBg: 'bg-teal-500'
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full bg-teal-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs mt-0.5">
                                    RM
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Tugas 2 - Normalisasi Database
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Raka Maulana</span> &bull; Mata Kuliah: Basis Data &amp; Sistem Terdistribusi
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-blue-50 text-blue-600">
                                            Forum Kelas
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Apakah ada yang sudah selesai mengerjakan tugas 2? Saya masih bingung di bagian normalisasi 3NF ...
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>19</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>342</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        10 jam yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 5 (Dewi Lestari - Diskusi) -->
                    <div 
                        @click="openThread({
                            id: 5,
                            title: 'Diskusi kelompok untuk proyek akhir',
                            author: 'Dewi Lestari',
                            role: 'Mahasiswa',
                            course: 'Manajemen Proyek TI',
                            category: 'Diskusi',
                            categoryColor: 'bg-purple-50 text-purple-600',
                            snippet: 'Untuk anggota kelompok yang belum bergabung, silakan segera join di grup WhatsApp. Kita akan menyusun dokumen Project Charter dan WBS sore ini.',
                            commentsCount: 12,
                            viewsCount: 201,
                            time: '1 hari yang lalu',
                            avatar: '/images/avatar-dewi.jpg',
                            isPinned: false
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full bg-slate-200 text-white flex items-center justify-center shrink-0 shadow-2xs overflow-hidden mt-0.5">
                                    <img src="/images/avatar-dewi.jpg" alt="Dewi Lestari" class="w-full h-full object-cover">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="w-1.5 h-1.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Diskusi kelompok untuk proyek akhir
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Dewi Lestari</span> &bull; Mata Kuliah: Manajemen Proyek TI
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-purple-50 text-purple-600">
                                            Diskusi
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Untuk anggota kelompok yang belum bergabung, silakan segera join di grup WhatsApp. Kita akan ...
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>12</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>201</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        1 hari yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 6 (Bayu Pratama - Tanya Jawab) -->
                    <div 
                        @click="openThread({
                            id: 6,
                            title: 'Kapan jadwal ujian tengah semester?',
                            author: 'Bayu Pratama',
                            role: 'Mahasiswa',
                            course: 'Jaringan Komputer',
                            category: 'Tanya Jawab',
                            categoryColor: 'bg-emerald-50 text-emerald-600',
                            snippet: 'Mohon informasi mengenai jadwal UTS untuk mata kuliah Jaringan Komputer. Terima kasih.',
                            commentsCount: 36,
                            viewsCount: 674,
                            time: '1 hari yang lalu',
                            initials: 'B',
                            avatarBg: 'bg-amber-500'
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full bg-amber-500 text-white flex items-center justify-center shrink-0 text-xs font-bold shadow-2xs mt-0.5">
                                    B
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <x-icon name="arrow-up" :size="12" class="text-blue-500 shrink-0" />
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Kapan jadwal ujian tengah semester?
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Bayu Pratama</span> &bull; Mata Kuliah: Jaringan Komputer
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-emerald-600">
                                            Tanya Jawab
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Mohon informasi mengenai jadwal UTS untuk mata kuliah Jaringan Komputer. Terima kasih.
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>36</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>674</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        1 hari yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Thread 7 (Fajar Nugroho - Berbagi) -->
                    <div 
                        @click="openThread({
                            id: 7,
                            title: 'Materi tambahan: Referensi untuk Database Design',
                            author: 'Fajar Nugroho',
                            role: 'Mahasiswa',
                            course: 'Basis Data & Sistem Terdistribusi',
                            category: 'Berbagi',
                            categoryColor: 'bg-orange-50 text-orange-600',
                            snippet: 'Berikut beberapa link dan buku yang bisa dijadikan referensi tambahan untuk materi database design, indexing strategy, dan query optimization.',
                            commentsCount: 20,
                            viewsCount: 412,
                            time: '2 hari yang lalu',
                            avatar: '/images/avatar-fajar.jpg',
                            isPinned: false
                        })"
                        class="p-4 sm:px-5 sm:py-4.5 hover:bg-canvas/50 transition-colors cursor-pointer group"
                    >
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex items-start gap-3.5 min-w-0 flex-1">
                                <div class="w-9 h-9 rounded-full bg-slate-200 text-white flex items-center justify-center shrink-0 shadow-2xs overflow-hidden mt-0.5">
                                    <img src="/images/avatar-fajar.jpg" alt="Fajar Nugroho" class="w-full h-full object-cover">
                                </div>

                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <x-icon name="arrow-up" :size="12" class="text-blue-500 shrink-0" />
                                        <h3 class="font-bold text-xs sm:text-sm text-text group-hover:text-[#2563EB] transition-colors leading-snug">
                                            Materi tambahan: Referensi untuk Database Design
                                        </h3>
                                    </div>

                                    <p class="text-[11px] text-text-muted mt-0.5">
                                        Dibuat oleh <span class="font-medium text-text">Fajar Nugroho</span> &bull; Mata Kuliah: Basis Data &amp; Sistem Terdistribusi
                                    </p>

                                    <div class="mt-1.5">
                                        <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-orange-50 text-orange-600">
                                            Berbagi
                                        </span>
                                    </div>

                                    <p class="text-xs text-text-muted mt-1.5 line-clamp-2 leading-relaxed">
                                        Berikut beberapa link dan buku yang bisa dijadikan referensi tambahan untuk materi database design ...
                                    </p>
                                </div>
                            </div>

                            <div class="shrink-0 flex flex-col items-end justify-between self-stretch text-right">
                                <button type="button" class="text-text-muted hover:text-text p-1 cursor-pointer">
                                    <x-icon name="more-horizontal" :size="14" />
                                </button>
                                
                                <div class="mt-3">
                                    <div class="flex items-center gap-3 text-xs text-text-muted">
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="message-circle" :size="13" />
                                            <span>20</span>
                                        </span>
                                        <span class="inline-flex items-center gap-1 tabular-nums">
                                            <x-icon name="eye" :size="13" />
                                            <span>412</span>
                                        </span>
                                    </div>
                                    <span class="text-[11px] text-text-subtle mt-1 block">
                                        2 hari yang lalu
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): STATISTIK, KATEGORI & DISKUSI TERPOPULER
                 ========================================== -->
            <div class="lg:col-span-4 space-y-4">
                
                <!-- 1. KARTU STATISTIK DISKUSI (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <h3 class="font-bold text-sm text-text mb-3">
                        Statistik Diskusi
                    </h3>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <!-- Stat 1: 128 Total Diskusi -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="message-circle" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">128</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Diskusi</span>
                            </div>
                        </div>

                        <!-- Stat 2: 526 Total Komentar -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="message-square" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">526</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Komentar</span>
                            </div>
                        </div>

                        <!-- Stat 3: 8.4k Total Dilihat -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="eye" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">8.4k</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Total Dilihat</span>
                            </div>
                        </div>

                        <!-- Stat 4: 247 Pengguna Aktif -->
                        <div class="p-3 rounded-[8px] border border-line bg-canvas/30 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="users" :size="15" />
                            </div>
                            <div class="min-w-0">
                                <span class="text-base font-bold text-text tabular-nums leading-none block">247</span>
                                <span class="text-[11px] text-text-muted mt-0.5 block truncate">Pengguna Aktif</span>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

                <!-- 2. KARTU KATEGORI (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-2.5">
                        <h3 class="font-bold text-sm text-text">
                            Kategori
                        </h3>
                        <a href="#kategori" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div class="space-y-1">
                        
                        <!-- Kategori 1: Semua (Active Style) -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'all'; activeTab = 'all'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] bg-blue-50/70 text-[#2563EB] text-xs font-semibold transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="message-square" :size="14" class="text-[#2563EB]" />
                                <span>Semua</span>
                            </div>
                            <span class="tabular-nums text-xs">128</span>
                        </button>

                        <!-- Kategori 2: Forum Kelas -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'forum'; activeTab = 'forum'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-text hover:bg-canvas/60 text-xs font-medium transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="notebook-text" :size="14" class="text-blue-500" />
                                <span>Forum Kelas</span>
                            </div>
                            <span class="tabular-nums text-text-muted text-xs">42</span>
                        </button>

                        <!-- Kategori 3: Tanya Jawab -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'tanya'; activeTab = 'tanya'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-text hover:bg-canvas/60 text-xs font-medium transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="help-circle" :size="14" class="text-emerald-500" />
                                <span>Tanya Jawab</span>
                            </div>
                            <span class="tabular-nums text-text-muted text-xs">38</span>
                        </button>

                        <!-- Kategori 4: Pengumuman -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'pengumuman'; activeTab = 'pengumuman'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-text hover:bg-canvas/60 text-xs font-medium transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="megaphone" :size="14" class="text-amber-500" />
                                <span>Pengumuman</span>
                            </div>
                            <span class="tabular-nums text-text-muted text-xs">16</span>
                        </button>

                        <!-- Kategori 5: Diskusi -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'diskusi'; activeTab = 'all'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-text hover:bg-canvas/60 text-xs font-medium transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="message-circle" :size="14" class="text-purple-500" />
                                <span>Diskusi</span>
                            </div>
                            <span class="tabular-nums text-text-muted text-xs">22</span>
                        </button>

                        <!-- Kategori 6: Berbagi -->
                        <button 
                            type="button" 
                            @click="selectedCategory = 'berbagi'; activeTab = 'all'"
                            class="w-full flex items-center justify-between p-2 rounded-[6px] text-text hover:bg-canvas/60 text-xs font-medium transition-colors cursor-pointer"
                        >
                            <div class="flex items-center gap-2.5">
                                <x-icon name="share-2" :size="14" class="text-orange-500" />
                                <span>Berbagi</span>
                            </div>
                            <span class="tabular-nums text-text-muted text-xs">10</span>
                        </button>

                    </div>
                </x-ui.card>

                <!-- 3. KARTU DISKUSI TERPOPULER (Persis Gambar Acuan) -->
                <x-ui.card class="p-4 sm:p-4.5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <h3 class="font-bold text-sm text-text">
                            Diskusi Terpopuler
                        </h3>
                        <a href="#populer" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div class="space-y-3.5">
                        
                        <!-- Rank 1: Panduan AI Copilot -->
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                1
                            </span>
                            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 mt-0.5 shadow-2xs">
                                <img src="/images/avatar-budi.jpg" alt="Dr. Budi" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer">
                                    Panduan Penggunaan Fitur AI Copilot di Arunika
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="message-circle" :size="10" />
                                        <span>142</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="eye" :size="10" />
                                        <span>2.4k</span>
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span>3 hari yang lalu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rank 2: Kesulitan memahami konsep Inheritance -->
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                2
                            </span>
                            <div class="w-6 h-6 rounded-full bg-[#8B5CF6] text-white flex items-center justify-center text-[9px] font-bold shrink-0 mt-0.5 shadow-2xs">
                                AS
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer">
                                    Kesulitan memahami konsep Inheritance di OOP
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="message-circle" :size="10" />
                                        <span>28</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="eye" :size="10" />
                                        <span>567</span>
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span>5 jam yang lalu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rank 3: Rekomendasi laptop -->
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                3
                            </span>
                            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 mt-0.5 shadow-2xs">
                                <img src="/images/avatar-siti.jpg" alt="Siti" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer">
                                    Rekomendasi laptop untuk kebutuhan pemrograman
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="message-circle" :size="10" />
                                        <span>45</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="eye" :size="10" />
                                        <span>892</span>
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span>7 jam yang lalu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rank 4: Diskusi kelompok proyek akhir -->
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                4
                            </span>
                            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 mt-0.5 shadow-2xs">
                                <img src="/images/avatar-dewi.jpg" alt="Dewi" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer">
                                    Diskusi kelompok untuk proyek akhir
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="message-circle" :size="10" />
                                        <span>12</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="eye" :size="10" />
                                        <span>201</span>
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span>1 hari yang lalu</span>
                                </div>
                            </div>
                        </div>

                        <!-- Rank 5: Kapan jadwal ujian tengah semester? -->
                        <div class="flex items-start gap-2.5">
                            <span class="w-5 h-5 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center text-[10px] font-bold shrink-0 mt-0.5">
                                5
                            </span>
                            <div class="w-6 h-6 rounded-full overflow-hidden shrink-0 mt-0.5 shadow-2xs">
                                <img src="/images/avatar-fajar.jpg" alt="Bayu" class="w-full h-full object-cover">
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-semibold text-text truncate hover:text-[#2563EB] cursor-pointer">
                                    Kapan jadwal ujian tengah semester?
                                </h4>
                                <div class="flex items-center gap-2 text-[10px] text-text-muted mt-0.5 tabular-nums">
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="message-circle" :size="10" />
                                        <span>36</span>
                                    </span>
                                    <span class="inline-flex items-center gap-1">
                                        <x-icon name="eye" :size="10" />
                                        <span>674</span>
                                    </span>
                                    <span class="text-slate-300">|</span>
                                    <span>1 hari yang lalu</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </x-ui.card>

            </div>

        </div>

        <!-- ==========================================
             4. MODAL INTERAKTIF: BUAT DISKUSI BARU
             ========================================== -->
        <div 
            x-show="newThreadModalOpen" 
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="newThreadModalOpen = false"
        >
            <div 
                @click.outside="newThreadModalOpen = false"
                class="bg-surface border border-line rounded-[12px] max-w-xl w-full p-6 shadow-card space-y-4"
            >
                <div class="flex items-center justify-between pb-3 border-b border-line">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center">
                            <x-icon name="plus" :size="16" />
                        </div>
                        <h3 class="font-bold text-sm text-text">Buat Diskusi Baru</h3>
                    </div>
                    <button @click="newThreadModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <div class="space-y-3 text-xs">
                    <div>
                        <label class="font-semibold text-text block mb-1">Judul Topik Diskusi</label>
                        <input 
                            type="text" 
                            placeholder="Tuliskan pertanyaan atau topik yang jelas..." 
                            class="w-full px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors"
                        >
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="font-semibold text-text block mb-1">Mata Kuliah</label>
                            <select class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-[#2563EB]">
                                <option>IF2101 - PBO & Desain Pola</option>
                                <option>IF2102 - Basis Data & Sistem Terdistribusi</option>
                                <option>IF2104 - Jaringan Komputer</option>
                                <option>IF2108 - Manajemen Proyek TI</option>
                            </select>
                        </div>
                        <div>
                            <label class="font-semibold text-text block mb-1">Kategori</label>
                            <select class="w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-[#2563EB]">
                                <option>Tanya Jawab</option>
                                <option>Forum Kelas</option>
                                <option>Diskusi</option>
                                <option>Berbagi</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="font-semibold text-text block mb-1">Isi Pesan / Pertanyaan</label>
                        <textarea 
                            rows="4" 
                            placeholder="Jelaskan secara rinci permasalahan, potongan kode, atau pertanyaan yang ingin Anda diskusikan..." 
                            class="w-full px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors resize-none"
                        ></textarea>
                    </div>

                    <div class="p-3 rounded-[6px] border border-dashed border-line bg-canvas/40 flex items-center justify-between text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="paperclip" :size="14" class="text-text-muted" />
                            <span>Lampirkan tangkapan layar atau berkas kode (opsional)</span>
                        </div>
                        <button type="button" class="text-[#2563EB] font-semibold hover:underline">Pilih Berkas</button>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-line">
                    <button 
                        type="button" 
                        @click="newThreadModalOpen = false" 
                        class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text cursor-pointer transition-colors"
                    >
                        Batal
                    </button>
                    <button 
                        type="button" 
                        @click="newThreadModalOpen = false" 
                        class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                    >
                        <span>Publikasikan Diskusi</span>
                        <x-icon name="send" :size="12" />
                    </button>
                </div>
            </div>
        </div>

        <!-- ==========================================
             5. MODAL INTERAKTIF: DETAIL THREAD & KOMENTAR
             ========================================== -->
        <div 
            x-show="detailModalOpen" 
            x-transition.opacity.duration.150ms
            class="fixed inset-0 z-50 bg-text/40 backdrop-blur-xs flex items-center justify-center p-4"
            style="display: none;"
            @keydown.escape.window="detailModalOpen = false"
        >
            <div 
                @click.outside="detailModalOpen = false"
                class="bg-surface border border-line rounded-[12px] max-w-2xl w-full p-6 shadow-card space-y-4 max-h-[90vh] overflow-y-auto"
            >
                <div class="flex items-start justify-between gap-4 pb-3 border-b border-line">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2 mb-1.5">
                            <span 
                                class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                :class="selectedThread?.categoryColor"
                                x-text="selectedThread?.category"
                            ></span>
                            <span class="text-xs text-text-muted" x-text="selectedThread?.course"></span>
                        </div>
                        <h3 class="text-base font-bold text-text leading-snug" x-text="selectedThread?.title"></h3>
                        <p class="text-xs text-text-muted mt-0.5">
                            Dibuat oleh <span class="font-medium text-text" x-text="selectedThread?.author"></span> &bull; <span x-text="selectedThread?.time"></span>
                        </p>
                    </div>
                    <button @click="detailModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <!-- Isi Diskusi -->
                <div class="p-3.5 rounded-[8px] bg-canvas border border-line text-xs text-text leading-relaxed" x-text="selectedThread?.snippet">
                </div>

                <!-- Interaksi & Komentar -->
                <div class="pt-2 border-t border-line space-y-3">
                    <div class="flex items-center justify-between text-xs text-text-muted">
                        <span class="font-semibold text-text">Tanggapan &amp; Komentar (3)</span>
                        <div class="flex items-center gap-3">
                            <button type="button" class="inline-flex items-center gap-1 hover:text-[#2563EB] cursor-pointer">
                                <x-icon name="thumbs-up" :size="13" />
                                <span>Suka (18)</span>
                            </button>
                            <button type="button" class="inline-flex items-center gap-1 hover:text-[#2563EB] cursor-pointer">
                                <x-icon name="share-2" :size="13" />
                                <span>Bagikan</span>
                            </button>
                        </div>
                    </div>

                    <!-- Balasan Contoh 1 -->
                    <div class="p-3 rounded-[8px] bg-surface border border-line text-xs space-y-1">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-text">Prof. Dr. Budi Rahardjo, Ph.D. <span class="text-[10px] font-normal text-blue-600 bg-blue-50 px-1.5 py-0.2 rounded ml-1">Dosen</span></span>
                            <span class="text-[10px] text-text-muted">2 jam yang lalu</span>
                        </div>
                        <p class="text-text-muted leading-relaxed">
                            Penjelasan singkat: Inheritance adalah relasi is-a (misal: SavingsAccount adalah Account), sedangkan Polymorphism memungkinkan objek turunan mengeksekusi method dengan perilaku spesifik tanpa mengubah interface pemanggil.
                        </p>
                    </div>

                    <!-- Input Tambah Komentar -->
                    <div class="pt-2">
                        <div class="flex items-center gap-2">
                            <input 
                                type="text" 
                                placeholder="Tulis balasan Anda untuk diskusi ini..." 
                                class="flex-1 px-3 py-2 rounded-[6px] border border-line bg-canvas/30 text-xs text-text focus:bg-white focus:outline-none focus:border-[#2563EB] transition-colors"
                            >
                            <button 
                                type="button" 
                                class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-1 shadow-2xs transition-colors cursor-pointer shrink-0"
                            >
                                <span>Balas</span>
                                <x-icon name="send" :size="12" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end pt-3 border-t border-line">
                    <button 
                        type="button" 
                        @click="detailModalOpen = false" 
                        class="px-4 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text cursor-pointer transition-colors"
                    >
                        Tutup
                    </button>
                </div>
            </div>
        </div>

    </div>

@endsection
