@extends('layouts.app')

@section('title', 'Tugas & Ujian — Arunika')
@section('page_breadcrumb', 'Tugas & Ujian')

@section('content')

    <div 
        x-data="{
            activeTab: 'semua',
            sortBy: 'terbaru',
            selectedCourse: 'all',
            submissionModalOpen: false,
            detailModalOpen: false,
            activeAssessment: null,
            openSubmission(item) {
                this.activeAssessment = item;
                this.submissionModalOpen = true;
            },
            openDetail(item) {
                this.activeAssessment = item;
                this.detailModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI & 4 STAT TILES
             ========================================== -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-6">
            <!-- Left Header -->
            <div class="flex items-start gap-3.5">
                <div class="shrink-0 text-slate-900 pt-0.5">
                    <x-icon name="file-text" :size="36" :strokeWidth="2" class="text-slate-900" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Tugas & Ujian
                    </h1>
                    <p class="text-xs sm:text-[13px] text-text-muted mt-1 max-w-xl leading-relaxed">
                        Kelola dan pantau semua tugas serta ujian yang harus kamu selesaikan pada setiap mata kuliah.
                    </p>
                </div>
            </div>

            <!-- Right: 4 Ringkasan Stat Cards Horizontal (Persis Gambar Acuan) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 shrink-0">
                <!-- Stat 1: 8 Tugas Aktif (Blue Circle) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex items-center justify-between gap-3 shadow-2xs min-w-[140px]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="file-text" :size="18" />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-text block leading-none">{{ $summary['active_tasks'] ?? 8 }}</span>
                            <span class="text-[11px] text-text-muted block mt-1 font-normal">Tugas Aktif</span>
                        </div>
                    </div>
                    <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                </div>

                <!-- Stat 2: 3 Ujian Mendatang (Green Circle) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex items-center justify-between gap-3 shadow-2xs min-w-[140px]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-icon name="calendar" :size="18" />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-text block leading-none">{{ $summary['upcoming_exams'] ?? 3 }}</span>
                            <span class="text-[11px] text-text-muted block mt-1 font-normal">Ujian Mendatang</span>
                        </div>
                    </div>
                    <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                </div>

                <!-- Stat 3: 5 Tugas Selesai (Orange Circle) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex items-center justify-between gap-3 shadow-2xs min-w-[140px]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <x-icon name="check-circle" :size="18" />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-text block leading-none">{{ $summary['completed_tasks'] ?? 5 }}</span>
                            <span class="text-[11px] text-text-muted block mt-1 font-normal">Tugas Selesai</span>
                        </div>
                    </div>
                    <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                </div>

                <!-- Stat 4: 1 Tugas Terlambat (Purple Circle) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex items-center justify-between gap-3 shadow-2xs min-w-[140px]">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <x-icon name="clock" :size="18" />
                        </div>
                        <div>
                            <span class="text-xl font-bold text-text block leading-none">{{ $summary['late_tasks'] ?? 1 }}</span>
                            <span class="text-[11px] text-text-muted block mt-1 font-normal">Tugas Terlambat</span>
                        </div>
                    </div>
                    <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. MAIN LAYOUT: 2 KOLOM (KIRI ~70%, KANAN ~30%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): LIST ASESMEN & TOOLBAR
                 ========================================== -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Toolbar: Tab Pills & Sort Dropdown -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <!-- Tab Pills (Persis Gambar Acuan) -->
                    <div class="inline-flex items-center gap-1.5 p-0.5">
                        <button 
                            type="button" 
                            @click="activeTab = 'semua'"
                            :class="activeTab === 'semua' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-200/50'"
                            class="inline-flex items-center gap-2 px-4 py-2 rounded-[6px] text-xs font-semibold transition-colors cursor-pointer"
                        >
                            <x-icon name="layout-grid" :size="14" />
                            <span>Semua</span>
                        </button>

                        <button 
                            type="button" 
                            @click="activeTab = 'tugas'"
                            :class="activeTab === 'tugas' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-200/50'"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            <x-icon name="file-text" :size="14" />
                            <span>Tugas</span>
                        </button>

                        <button 
                            type="button" 
                            @click="activeTab = 'ujian'"
                            :class="activeTab === 'ujian' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-200/50'"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            <x-icon name="calendar" :size="14" />
                            <span>Ujian</span>
                        </button>

                        <button 
                            type="button" 
                            @click="activeTab = 'terlambat'"
                            :class="activeTab === 'terlambat' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-200/50'"
                            class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            <x-icon name="alert-triangle" :size="14" />
                            <span>Terlambat</span>
                        </button>
                    </div>

                    <!-- Dropdown Sort (Terbaru ▾) -->
                    <div class="relative" x-data="{ sortOpen: false }">
                        <button 
                            type="button"
                            @click="sortOpen = !sortOpen"
                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs hover:bg-canvas transition-colors cursor-pointer"
                        >
                            <span x-text="sortBy === 'terbaru' ? 'Terbaru' : (sortBy === 'deadline' ? 'Tenggat Terdekat' : 'A-Z')">Terbaru</span>
                            <x-icon name="chevron-down" :size="13" class="text-text-muted" />
                        </button>
                        <div 
                            x-show="sortOpen" 
                            @click.away="sortOpen = false" 
                            x-transition.opacity.duration.100ms
                            class="absolute right-0 mt-1 w-36 bg-surface border border-line rounded-[6px] shadow-card py-1 z-20 text-xs"
                            style="display: none;"
                        >
                            <button 
                                type="button" 
                                @click="sortBy = 'terbaru'; sortOpen = false" 
                                class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text"
                            >
                                Terbaru
                            </button>
                            <button 
                                type="button" 
                                @click="sortBy = 'deadline'; sortOpen = false" 
                                class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text"
                            >
                                Tenggat Terdekat
                            </button>
                            <button 
                                type="button" 
                                @click="sortBy = 'alphabetical'; sortOpen = false" 
                                class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text"
                            >
                                Nama (A-Z)
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ==========================================
                     LIST KARTU ASESMEN (6 KARTU PERSIS GAMBAR ACUAN)
                     ========================================== -->
                <div class="space-y-3.5">
                    
                    <!-- KARTU 1: IF2101 - Tugas 1: Latihan Class dan Object -->
                    <!-- KARTU 1: IF2101 - Tugas 1: Latihan Class dan Object -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'tugas') && (selectedCourse === 'all' || selectedCourse === 'IF2101')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Blue) -->
                            <div class="w-12 h-12 rounded-[10px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="file-text" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2101 - Pemrograman Berorientasi Objek & Desain Pola
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Tugas 1 - Latihan Class dan Object
                                </h3>
                                <a href="{{ $cardTask1 ? route('assignments.download', $cardTask1) : '#' }}" class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-[#2563EB] transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Panduan_Tugas1.pdf <span class="text-text-subtle">(2.4 MB)</span></span>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                        Tugas
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Jum, 26 Sep 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>23:59 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Kerjakan -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openSubmission({
                                        id: '{{ $cardTask1?->id ?? ($assignments->first()?->id ?? 'assign-1') }}',
                                        title: 'Tugas 1 - Latihan Class dan Object',
                                        course: 'IF2101 - Pemrograman Berorientasi Objek & Desain Pola',
                                        deadline: 'Jum, 26 Sep 2026, 23:59 WIB'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Kerjakan</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 2: IF2102 - Tugas 2: Normalisasi Database -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'tugas') && (selectedCourse === 'all' || selectedCourse === 'IF2102')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Blue) -->
                            <div class="w-12 h-12 rounded-[10px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="file-text" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2102 - Basis Data & Sistem Terdistribusi
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Tugas 2 - Normalisasi Database
                                </h3>
                                <a href="{{ $cardTask2 ? route('assignments.download', $cardTask2) : '#' }}" class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-[#2563EB] transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Soal_Tugas2.pdf <span class="text-text-subtle">(1.8 MB)</span></span>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                        Tugas
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Sen, 29 Sep 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>23:59 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Kerjakan -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openSubmission({
                                        id: '{{ $cardTask2?->id ?? ($assignments->skip(1)->first()?->id ?? 'assign-2') }}',
                                        title: 'Tugas 2 - Normalisasi Database',
                                        course: 'IF2102 - Basis Data & Sistem Terdistribusi',
                                        deadline: 'Sen, 29 Sep 2026, 23:59 WIB'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Kerjakan</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 3: IF2103 - Ujian Tengah Semester (UTS) (Purple) -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'ujian') && (selectedCourse === 'all' || selectedCourse === 'IF2103')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Purple) -->
                            <div class="w-12 h-12 rounded-[10px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <x-icon name="clipboard-check" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2103 - Rancang Bangun Mini Core Banking System (OOP)
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Ujian Tengah Semester (UTS)
                                </h3>
                                <div class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-purple-600 transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Kisi-Kisi_UTS.pdf <span class="text-text-subtle">(1.2 MB)</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700">
                                        Ujian
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Rab, 8 Okt 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>08:00 - 10:00 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Lihat Detail -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openDetail({
                                        quiz_id: '{{ $cardQuiz1?->id ?? ($quizzes->first()?->id ?? 'quiz-1') }}',
                                        title: 'Ujian Tengah Semester (UTS)',
                                        course: 'IF2103 - Rancang Bangun Mini Core Banking System (OOP)',
                                        schedule: 'Rabu, 8 Oktober 2026 (08:00 - 10:00 WIB)',
                                        room: 'Ruang Ujian Online / Lab Gedung B',
                                        rules: 'Ujian CBT terawasi, membawa KTM fisik, dan dilarang membuka tab lain.'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Lihat Detail</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 4: IF2104 - Tugas 1: Konfigurasi IP Address -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'tugas') && (selectedCourse === 'all' || selectedCourse === 'IF2104')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Blue) -->
                            <div class="w-12 h-12 rounded-[10px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="file-text" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2104 - Jaringan Komputer
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Tugas 1 - Konfigurasi IP Address
                                </h3>
                                <a href="{{ $cardTask4 ? route('assignments.download', $cardTask4) : '#' }}" class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-[#2563EB] transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Panduan_Tugas1.pdf <span class="text-text-subtle">(1.1 MB)</span></span>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                        Tugas
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Kam, 2 Okt 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>23:59 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Kerjakan -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openSubmission({
                                        id: '{{ $cardTask4?->id ?? ($assignments->skip(2)->first()?->id ?? 'assign-4') }}',
                                        title: 'Tugas 1 - Konfigurasi IP Address',
                                        course: 'IF2104 - Jaringan Komputer',
                                        deadline: 'Kam, 2 Okt 2026, 23:59 WIB'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Kerjakan</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 5: IF2105 - Tugas 3: Analisis Performa CPU -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'tugas') && (selectedCourse === 'all' || selectedCourse === 'IF2105')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Blue) -->
                            <div class="w-12 h-12 rounded-[10px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <x-icon name="file-text" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2105 - Arsitektur & Organisasi Komputer
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Tugas 3 - Analisis Performa CPU
                                </h3>
                                <a href="{{ $cardTask5 ? route('assignments.download', $cardTask5) : '#' }}" class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-[#2563EB] transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Soal_Tugas3.pdf <span class="text-text-subtle">(2.0 MB)</span></span>
                                </a>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-700">
                                        Tugas
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Sel, 7 Okt 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>23:59 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Kerjakan -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openSubmission({
                                        id: '{{ $cardTask5?->id ?? ($assignments->skip(3)->first()?->id ?? 'assign-5') }}',
                                        title: 'Tugas 3 - Analisis Performa CPU',
                                        course: 'IF2105 - Arsitektur & Organisasi Komputer',
                                        deadline: 'Sel, 7 Okt 2026, 23:59 WIB'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Kerjakan</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU 6: IF2102 - Ujian Akhir Semester (UAS) (Purple) -->
                    <div 
                        x-show="(activeTab === 'semua' || activeTab === 'ujian') && (selectedCourse === 'all' || selectedCourse === 'IF2102')"
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                    >
                        <button type="button" class="absolute top-4 right-4 text-text-subtle hover:text-text p-1 transition-colors">
                            <x-icon name="more-vertical" :size="16" />
                        </button>

                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <!-- Square Icon Box (Purple) -->
                            <div class="w-12 h-12 rounded-[10px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <x-icon name="clipboard-check" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2102 - Basis Data & Sistem Terdistribusi
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Ujian Akhir Semester (UAS)
                                </h3>
                                <div class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-text-muted hover:text-purple-600 transition-colors cursor-pointer">
                                    <x-icon name="file-text" :size="13" class="text-text-subtle" />
                                    <span>Jadwal_UAS.pdf <span class="text-text-subtle">(1.5 MB)</span></span>
                                </div>
                            </div>
                        </div>

                        <div class="flex flex-row md:flex-row items-start md:items-center justify-between md:justify-end gap-6 pt-3 md:pt-0 border-t md:border-t-0 border-line/60">
                            <!-- Meta: Badge + Jadwal -->
                            <div class="flex flex-col gap-1.5 min-w-[150px]">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-700">
                                        Ujian
                                    </span>
                                </div>
                                <div class="space-y-0.5 text-xs">
                                    <div class="flex items-center gap-2 text-text font-medium">
                                        <x-icon name="calendar" :size="13" class="text-text-subtle shrink-0" />
                                        <span>Sen, 20 Okt 2026</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-text-muted">
                                        <x-icon name="clock" :size="13" class="text-text-subtle shrink-0" />
                                        <span>08:00 - 10:00 WIB</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Tombol Lihat Detail -->
                            <div class="shrink-0 pt-1 md:pt-0">
                                <button 
                                    type="button"
                                    @click="openDetail({
                                        quiz_id: '{{ $cardQuiz2?->id ?? ($quizzes->skip(1)->first()?->id ?? 'quiz-2') }}',
                                        title: 'Ujian Akhir Semester (UAS)',
                                        course: 'IF2102 - Basis Data & Sistem Terdistribusi',
                                        schedule: 'Senin, 20 Oktober 2026 (08:00 - 10:00 WIB)',
                                        room: 'Ruang Ujian Gedung C Lt. 2 / CBT Online',
                                        rules: 'Membawa kartu ujian fisik & kartu identitas. Pengawasan aktif via webcam AI Proctoring.'
                                    })"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] border border-[#2563EB] text-[#2563EB] hover:bg-blue-50 text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                                >
                                    <span>Lihat Detail</span>
                                    <x-icon name="arrow-right" :size="13" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- KARTU TERLAMBAT (Muncul saat filter Terlambat aktif) -->
                    <div 
                        x-show="activeTab === 'terlambat' && (selectedCourse === 'all' || selectedCourse === 'IF2101')" 
                        class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs hover:border-slate-300 transition-colors relative flex flex-col md:flex-row md:items-center justify-between gap-4"
                        style="display: none;"
                    >
                        <div class="flex items-start gap-4 flex-1 min-w-0 pr-6 md:pr-0">
                            <div class="w-12 h-12 rounded-[10px] bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <x-icon name="alert-triangle" :size="24" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] font-medium text-text-muted truncate">
                                    IF2101 - Pemrograman Berorientasi Objek & Desain Pola
                                </p>
                                <h3 class="text-[15px] font-bold text-text mt-0.5 tracking-tight truncate">
                                    Tugas Praktikum 0 - Environment Setup & Git Workflow
                                </h3>
                                <div class="mt-2 inline-flex items-center gap-1.5 text-[11px] text-danger">
                                    <x-icon name="clock" :size="13" class="text-danger" />
                                    <span>Terlambat 3 hari (Batas toleransi penalti -10%)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <button 
                                type="button"
                                @click="openSubmission({
                                    id: '{{ $cardTaskLate?->id ?? ($assignments->last()?->id ?? 'assign-late') }}',
                                    title: 'Tugas Praktikum 0 - Environment Setup & Git Workflow',
                                    course: 'IF2101 - Pemrograman Berorientasi Objek',
                                    deadline: 'Terlambat (Pengumpulan Susulan)'
                                })"
                                class="px-4 py-2 rounded-[6px] bg-[#2563EB] text-white text-xs font-semibold hover:bg-[#1D4ED8] transition-colors"
                            >
                                Kumpulkan Susulan →
                            </button>
                        </div>
                    </div>

                </div>
            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): WIDGET FILTER, KALENDER & TERDEKAT
                 ========================================== -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- 1. KARTU FILTER (Persis Gambar Acuan) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center gap-2 font-bold text-sm text-text mb-4">
                        <x-icon name="filter" :size="16" class="text-text" />
                        <span>Filter</span>
                    </div>

                    <div class="space-y-3">
                        <!-- Dropdown 1: Semua Mata Kuliah -->
                        <div class="relative" x-data="{ openCourse: false }">
                            <div 
                                @click="openCourse = !openCourse"
                                class="flex items-center justify-between w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text cursor-pointer hover:border-slate-300 transition-colors"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <x-icon name="graduation-cap" :size="15" class="text-text-muted shrink-0" />
                                    <span class="truncate" x-text="selectedCourse === 'all' ? 'Semua Mata Kuliah' : selectedCourse">Semua Mata Kuliah</span>
                                </div>
                                <x-icon name="chevron-down" :size="13" class="text-text-muted shrink-0 ml-2" />
                            </div>
                            <div 
                                x-show="openCourse" 
                                @click.away="openCourse = false"
                                x-transition.opacity.duration.100ms
                                class="absolute left-0 right-0 mt-1 bg-surface border border-line rounded-[6px] shadow-card py-1 z-20 text-xs"
                                style="display: none;"
                                                        <button type="button" @click="selectedCourse = 'all'; openCourse = false" class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text">
                                    Semua Mata Kuliah
                                </button>
                                @foreach($enrolledClasses as $enrolled)
                                    <button type="button" @click="selectedCourse = '{{ $enrolled->course->code }}'; openCourse = false" class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text truncate">
                                        {{ $enrolled->course->code }} - {{ $enrolled->course->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Dropdown 2: Semua Jenis -->
                        <div class="relative" x-data="{ openType: false, selectedType: 'Semua Jenis' }">
                            <div 
                                @click="openType = !openType"
                                class="flex items-center justify-between w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text cursor-pointer hover:border-slate-300 transition-colors"
                            >
                                <div class="flex items-center gap-2.5 truncate">
                                    <x-icon name="sliders-horizontal" :size="15" class="text-text-muted shrink-0" />
                                    <span class="truncate" x-text="selectedType">Semua Jenis</span>
                                </div>
                                <x-icon name="chevron-down" :size="13" class="text-text-muted shrink-0 ml-2" />
                            </div>
                            <div 
                                x-show="openType" 
                                @click.away="openType = false" 
                                x-transition.opacity.duration.100ms
                                class="absolute left-0 right-0 mt-1 bg-surface border border-line rounded-[6px] shadow-card py-1 z-20 text-xs"
                                style="display: none;"
                            >
                                <button type="button" @click="selectedType = 'Semua Jenis'; activeTab = 'semua'; openType = false" class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text">
                                    Semua Jenis
                                </button>
                                <button type="button" @click="selectedType = 'Tugas Individu / Kelompok'; activeTab = 'tugas'; openType = false" class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text">
                                    Tugas Individu / Kelompok
                                </button>
                                <button type="button" @click="selectedType = 'Ujian (UTS / UAS)'; activeTab = 'ujian'; openType = false" class="w-full text-left px-3 py-1.5 hover:bg-canvas text-text">
                                    Ujian (UTS / UAS)
                                </button>
                            </div>
                        </div>

                        <!-- Input 3: Rentang Tanggal -->
                        <div class="relative">
                            <div class="flex items-center gap-2.5 w-full px-3 py-2 rounded-[6px] border border-line bg-surface text-xs text-text-muted cursor-pointer hover:border-slate-300 transition-colors">
                                <x-icon name="calendar" :size="15" class="text-text-muted shrink-0" />
                                <span>Pilih Rentang Tanggal</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 2. KARTU KALENDER TUGAS & UJIAN (Persis Gambar Acuan) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center gap-2 font-bold text-sm text-text mb-3">
                        <x-icon name="calendar" :size="16" class="text-text" />
                        <span>Kalender Tugas & Ujian</span>
                    </div>

                    <!-- Month Navigation: < Oktober 2026 > -->
                    <div class="flex items-center justify-between py-1 text-xs font-semibold text-text mb-2">
                        <button type="button" class="p-1 text-text-muted hover:text-text rounded hover:bg-canvas transition-colors cursor-pointer">
                            <x-icon name="chevron-left" :size="14" />
                        </button>
                        <div class="flex items-center gap-2">
                            <span>Oktober 2026</span>
                            <a 
                                href="{{ route('assignments.calendar.ics') }}" 
                                title="Ekspor Jadwal ke Google / Apple Calendar (.ics)" 
                                class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-50 text-[#2563EB] hover:bg-blue-100 transition-colors"
                            >
                                <x-icon name="download" :size="11" />
                                <span>.ics</span>
                            </a>
                        </div>
                        <button type="button" class="p-1 text-text-muted hover:text-text rounded hover:bg-canvas transition-colors cursor-pointer">
                            <x-icon name="chevron-right" :size="14" />
                        </button>
                    </div>

                    <!-- Days Header: Sen Sel Rab Kam Jum Sab Min -->
                    <div class="grid grid-cols-7 gap-1 text-center text-[11px] font-medium text-text-subtle mb-1">
                        <div>Sen</div>
                        <div>Sel</div>
                        <div>Rab</div>
                        <div>Kam</div>
                        <div>Jum</div>
                        <div>Sab</div>
                        <div>Min</div>
                    </div>

                    <!-- Days Grid dengan Lingkaran Sorotan Biru & Ungu (Persis Gambar Acuan) -->
                    <div class="grid grid-cols-7 gap-y-2.5 gap-x-1 text-center text-xs text-text select-none">
                        <!-- Baris 1: Empty Sen, Sel, Rab, lalu Kam 1, Jum 2 (Blue), Sab 3, Min 4 -->
                        <div class="py-0.5"></div>
                        <div class="py-0.5"></div>
                        <div class="py-0.5"></div>
                        <div class="py-0.5 text-text-muted">1</div>
                        <div class="py-0.5">
                            <span class="w-6 h-6 mx-auto rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center text-[11px]">2</span>
                        </div>
                        <div class="py-0.5 text-text-muted">3</div>
                        <div class="py-0.5 text-text-muted">4</div>

                        <!-- Baris 2: 5, 6, 7 (Blue), 8 (Purple), 9, 10, 11/12 -->
                        <div class="py-0.5 text-text-muted">5</div>
                        <div class="py-0.5 text-text-muted">6</div>
                        <div class="py-0.5">
                            <span class="w-6 h-6 mx-auto rounded-full bg-blue-100 text-[#2563EB] font-bold flex items-center justify-center text-[11px]">7</span>
                        </div>
                        <div class="py-0.5">
                            <span class="w-6 h-6 mx-auto rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-[11px]">8</span>
                        </div>
                        <div class="py-0.5 text-text-muted">9</div>
                        <div class="py-0.5 text-text-muted">10</div>
                        <div class="py-0.5 text-text-muted">11</div>

                        <!-- Baris 3: 12, 13, 14, 15, 16, 17, 18 -->
                        <div class="py-0.5 text-text-muted">12</div>
                        <div class="py-0.5 text-text-muted">13</div>
                        <div class="py-0.5 text-text-muted">14</div>
                        <div class="py-0.5 text-text-muted">15</div>
                        <div class="py-0.5 text-text-muted">16</div>
                        <div class="py-0.5 text-text-muted">17</div>
                        <div class="py-0.5 text-text-muted">18</div>

                        <!-- Baris 4: 19, 20 (Purple), 21, 22, 23, 24, 26 -->
                        <div class="py-0.5 text-text-muted">19</div>
                        <div class="py-0.5">
                            <span class="w-6 h-6 mx-auto rounded-full bg-purple-100 text-purple-700 font-bold flex items-center justify-center text-[11px]">20</span>
                        </div>
                        <div class="py-0.5 text-text-muted">21</div>
                        <div class="py-0.5 text-text-muted">22</div>
                        <div class="py-0.5 text-text-muted">23</div>
                        <div class="py-0.5 text-text-muted">24</div>
                        <div class="py-0.5 text-text-muted">25</div>

                        <!-- Baris 5: 26, 27, 28, 29, 30, 31, 1 (faded) -->
                        <div class="py-0.5 text-text-muted">26</div>
                        <div class="py-0.5 text-text-muted">27</div>
                        <div class="py-0.5 text-text-muted">28</div>
                        <div class="py-0.5 text-text-muted">29</div>
                        <div class="py-0.5 text-text-muted">30</div>
                        <div class="py-0.5 text-text-muted">31</div>
                        <div class="py-0.5 text-text-subtle/40">1</div>
                    </div>
                </x-ui.card>

                <!-- 3. KARTU TUGAS & UJIAN TERDEKAT (Persis Gambar Acuan) -->
                <x-ui.card class="p-5">
                    <h3 class="font-bold text-sm text-text mb-3.5">
                        Tugas & Ujian Terdekat
                    </h3>

                    <div class="space-y-4">
                        @forelse($upcomingAssessments as $item)
                            <a href="{{ $item['url'] }}" class="flex items-start gap-3 group">
                                <span class="w-2.5 h-2.5 rounded-full mt-1 shrink-0" style="background-color: {{ $item['color'] }};"></span>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-xs font-semibold text-text group-hover:text-[#2563EB] transition-colors truncate">
                                        {{ $item['title'] }}
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5 truncate">
                                        {{ $item['course'] }} • {{ $item['date_str'] }}
                                    </p>
                                </div>
                            </a>
                        @empty
                            <p class="text-xs text-text-muted">Tidak ada tugas/ujian terdekat.</p>
                        @endforelse
                    </div>

                    <!-- Footer: Lihat Semua -> -->
                    <div class="mt-4 pt-3.5 border-t border-line/60 text-center">
                        <a 
                            href="#semua" 
                            @click.prevent="activeTab = 'semua'; selectedCourse = 'all'"
                            class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563EB] hover:underline"
                        >
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>
                </x-ui.card>

            </div>
        </div>

        <!-- ==========================================
             MODAL SUBMISSION TUGAS (INTERAKTIF & NYATA)
             ========================================== -->
        <div 
            x-show="submissionModalOpen" 
            x-transition.opacity.duration.150ms 
            class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div 
                class="w-full max-w-lg bg-surface border border-line rounded-[10px] shadow-card overflow-hidden" 
                @click.away="submissionModalOpen = false"
            >
                <div class="p-5 border-b border-line flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-accent" x-text="activeAssessment?.course"></span>
                        <h3 class="text-base font-bold text-text mt-0.5" x-text="activeAssessment?.title"></h3>
                    </div>
                    <button type="button" @click="submissionModalOpen = false" class="text-text-muted hover:text-text p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <form 
                    :action="'/app/assignments/' + (activeAssessment?.id || '{{ $assignments->first()?->id }}') + '/submit'" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    class="p-5 space-y-4 text-xs"
                >
                    @csrf
                    <div class="flex items-center justify-between">
                        <div>
                            <label class="block font-medium text-text mb-1">Batas Waktu Pengumpulan</label>
                            <p class="text-text-muted font-medium" x-text="activeAssessment?.deadline"></p>
                        </div>
                        <a 
                            :href="'/app/assignments/' + activeAssessment?.id + '/submit'"
                            class="text-[11px] font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1"
                        >
                            <span>Buka Lembar Kerja Penuh</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Catatan / Jawaban Teks / Tautan Repository</label>
                        <textarea 
                            name="content" 
                            rows="3" 
                            placeholder="Tuliskan catatan, ringkasan solusi, atau tautan repository GitHub/Google Drive..."
                            class="w-full rounded-[6px] border border-line bg-canvas p-2.5 text-xs text-text focus:outline-none focus:border-accent focus:bg-surface transition-colors"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">Unggah Berkas Pendukung (PDF, ZIP, DOCX - Maks. 25MB)</label>
                        <div class="border-2 border-dashed border-line rounded-[8px] p-6 text-center hover:border-accent bg-canvas/40 transition-colors cursor-pointer">
                            <x-icon name="upload-cloud" :size="24" class="text-text-muted mx-auto mb-2" />
                            <p class="text-xs font-medium text-text">Tarik dan letakkan berkas di sini atau klik untuk memilih</p>
                            <p class="text-[10px] text-text-subtle mt-1">Format yang diizinkan: .pdf, .zip, .rar, .docx</p>
                            <input type="file" name="file" class="hidden" id="taskFileInput">
                        </div>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="submissionModalOpen = false" 
                            class="px-4 py-2 rounded-[6px] border border-line text-text font-medium hover:bg-canvas transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors flex items-center gap-1.5 shadow-2xs"
                        >
                            <x-icon name="send" :size="13" />
                            <span>Kirim Tugas (+XP)</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ==========================================
             MODAL DETAIL UJIAN (INFORMASI LENGKAP UTS/UAS)
             ========================================== -->
        <div 
            x-show="detailModalOpen" 
            x-transition.opacity.duration.150ms 
            class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div 
                class="w-full max-w-lg bg-surface border border-line rounded-[10px] shadow-card overflow-hidden" 
                @click.away="detailModalOpen = false"
            >
                <div class="p-5 border-b border-line flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-purple-600">Jadwal & Panduan Ujian Resmi</span>
                        <h3 class="text-base font-bold text-text mt-0.5" x-text="activeAssessment?.title"></h3>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-text-muted hover:text-text p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <div>
                        <span class="text-text-muted font-medium block">Mata Kuliah:</span>
                        <p class="font-bold text-text text-sm mt-0.5" x-text="activeAssessment?.course"></p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 p-3.5 rounded-[8px] bg-slate-50 border border-line">
                        <div>
                            <span class="text-text-muted block text-[11px]">Waktu Pelaksanaan</span>
                            <p class="font-semibold text-text mt-0.5" x-text="activeAssessment?.schedule"></p>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Lokasi / Ruang</span>
                            <p class="font-semibold text-text mt-0.5" x-text="activeAssessment?.room"></p>
                        </div>
                    </div>

                    <div>
                        <span class="text-text-muted font-medium block">Tata Tertib & Persyaratan:</span>
                        <p class="text-text mt-1 leading-relaxed" x-text="activeAssessment?.rules"></p>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="detailModalOpen = false" 
                            class="px-4 py-2 rounded-[6px] border border-line text-text font-medium hover:bg-canvas transition-colors"
                        >
                            Tutup
                        </button>
                        <a 
                            :href="'/app/quizzes/' + (activeAssessment?.quiz_id || '{{ $quizzes->first()?->id }}') + '/attempt'"
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors inline-flex items-center gap-1.5 shadow-2xs"
                        >
                            <span>Mulai Ujian Online (CBT)</span>
                            <x-icon name="arrow-right" :size="13" />
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
