@extends('layouts.app')

@section('title', 'Presensi — Arunika')
@section('page_breadcrumb', 'Presensi')

@section('content')

    <div 
        x-data="{
            filterStatus: 'all',
            selectedMeeting: null,
            detailModalOpen: false,
            openDetail(item) {
                this.selectedMeeting = item;
                this.detailModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI & 4 STAT CARDS
             ========================================== -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-6">
            <!-- Left Header -->
            <div class="flex items-start gap-3.5">
                <div class="shrink-0 text-[#17306B] pt-0.5">
                    <x-icon name="calendar-check" :size="36" :strokeWidth="2" class="text-[#17306B]" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Presensi
                    </h1>
                    <p class="text-xs sm:text-[13px] text-text-muted mt-1 max-w-xl leading-relaxed">
                        Kelola kehadiran perkuliahan dan pantau riwayat absensi Anda.
                    </p>
                </div>
            </div>

            <!-- Right: 4 Stat Cards Horizontal (Persis Gambar Acuan) -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3.5 shrink-0">
                
                <!-- Stat 1: Tingkat Kehadiran (Donut Ring 92%) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex items-center gap-3.5 shadow-2xs min-w-[155px]">
                    <!-- Donut Gauge Ring -->
                    <div class="relative w-12 h-12 flex items-center justify-center shrink-0">
                        <svg class="w-12 h-12 -rotate-90" viewBox="0 0 50 50">
                            <circle 
                                cx="25" cy="25" r="20" 
                                fill="transparent" 
                                stroke="#F1F5F9" 
                                stroke-width="4.5" 
                            />
                            <circle 
                                cx="25" cy="25" r="20" 
                                fill="transparent" 
                                stroke="#10B981" 
                                stroke-width="4.5" 
                                stroke-dasharray="115.6 125.66" 
                                stroke-linecap="round" 
                            />
                        </svg>
                        <span class="absolute text-xs font-bold text-text tabular-nums">92%</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-text block leading-tight">Tingkat Kehadiran</span>
                        <span class="text-[11px] text-text-muted block mt-0.5">Dari 12 sesi perkuliahan</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full mt-1.5">
                            <x-icon name="check" :size="10" />
                            <span>Sangat Baik</span>
                        </span>
                    </div>
                </div>

                <!-- Stat 2: Hadir (11 / 12 sesi) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs min-w-[135px]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-icon name="check" :size="14" />
                        </div>
                        <span class="text-[11px] font-medium text-text-muted">Hadir</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">11</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ 12 sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-600 mt-2">
                            <x-icon name="arrow-up" :size="10" />
                            <span>8% dari semester lalu</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Izin (1 / 12 sesi) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs min-w-[135px]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <x-icon name="user" :size="14" />
                        </div>
                        <span class="text-[11px] font-medium text-text-muted">Izin</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">1</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ 12 sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-medium text-text-muted mt-2">
                            <x-icon name="arrow-up" :size="10" />
                            <span>0% dari semester lalu</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Alfa (0 / 12 sesi) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs min-w-[135px]">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-full bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                            <x-icon name="x" :size="14" />
                        </div>
                        <span class="text-[11px] font-medium text-text-muted">Alfa</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">0</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ 12 sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-red-600 mt-2">
                            <x-icon name="arrow-down" :size="10" />
                            <span>2% dari semester lalu</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==========================================
             2. MAIN LAYOUT: 2 KOLOM (KIRI ~70%, KANAN ~30%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): RIWAYAT PRESENSI TABEL
                 ========================================== -->
            <div class="lg:col-span-8">
                <x-ui.card class="p-5">
                    
                    <!-- Header Kartu: Judul & Dropdown Semester -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-line/60">
                        <h2 class="text-base font-bold text-text">
                            Riwayat Presensi
                        </h2>

                        <!-- Dropdown Semester (Kanan) -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors self-start sm:self-auto">
                            <x-icon name="calendar" :size="14" class="text-text-muted" />
                            <span>Semester 3 (Ganjil 2026/2027)</span>
                            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                        </div>
                    </div>

                    <!-- Filter Pills: Semua, Hadir, Izin, Alfa -->
                    <div class="inline-flex items-center gap-1.5 py-3.5">
                        <button 
                            type="button" 
                            @click="filterStatus = 'all'"
                            :class="filterStatus === 'all' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-100'"
                            class="px-4 py-1.5 rounded-[6px] text-xs font-semibold transition-colors cursor-pointer"
                        >
                            Semua
                        </button>

                        <button 
                            type="button" 
                            @click="filterStatus = 'hadir'"
                            :class="filterStatus === 'hadir' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-100'"
                            class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            Hadir
                        </button>

                        <button 
                            type="button" 
                            @click="filterStatus = 'izin'"
                            :class="filterStatus === 'izin' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-100'"
                            class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            Izin
                        </button>

                        <button 
                            type="button" 
                            @click="filterStatus = 'alfa'"
                            :class="filterStatus === 'alfa' ? 'bg-[#2563EB] text-white shadow-2xs' : 'text-text-muted hover:text-text hover:bg-slate-100'"
                            class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors cursor-pointer"
                        >
                            Alfa
                        </button>
                    </div>

                    <!-- TABEL RIWAYAT PRESENSI (12 BARIS PERSIS GAMBAR ACUAN) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-line text-[11px] font-semibold text-text-muted">
                                    <th class="py-2.5 px-3">Tanggal</th>
                                    <th class="py-2.5 px-3">Pertemuan</th>
                                    <th class="py-2.5 px-3">Topik</th>
                                    <th class="py-2.5 px-3">Status</th>
                                    <th class="py-2.5 px-3">Keterangan</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/60 text-xs text-text">
                                
                                <!-- Baris 1: 08 Sep 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">08 Sep 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">1</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Pengenalan Mata Kuliah dan RPS</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '08 Sep 2026',
                                                meeting: '1',
                                                topic: 'Pengenalan Mata Kuliah dan RPS',
                                                status: 'Hadir',
                                                time: '08:05 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 2: 15 Sep 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">15 Sep 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">2</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Konsep Dasar Pemrograman Berorientasi Objek</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '15 Sep 2026',
                                                meeting: '2',
                                                topic: 'Konsep Dasar Pemrograman Berorientasi Objek',
                                                status: 'Hadir',
                                                time: '08:02 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 3: 22 Sep 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">22 Sep 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">3</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Struktur Data dan Array</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '22 Sep 2026',
                                                meeting: '3',
                                                topic: 'Struktur Data dan Array',
                                                status: 'Hadir',
                                                time: '08:10 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 4: 29 Sep 2026 (Izin - Sakit Surat Dokter) -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'izin'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">29 Sep 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">4</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Fungsi dan Modularitas</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700">
                                            <x-icon name="dot" :size="12" class="text-amber-500" />
                                            <span>Izin</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted font-normal">Sakit (Surat Dokter)</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '29 Sep 2026',
                                                meeting: '4',
                                                topic: 'Fungsi dan Modularitas',
                                                status: 'Izin',
                                                time: 'Terverifikasi Dosen PA',
                                                method: 'Unggah Surat Dokter Resmi',
                                                room: 'Online Portal',
                                                notes: 'Surat dokter terverifikasi BAAK'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 5: 06 Okt 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">06 Okt 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">5</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Class dan Object</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '06 Okt 2026',
                                                meeting: '5',
                                                topic: 'Class dan Object',
                                                status: 'Hadir',
                                                time: '08:04 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 6: 13 Okt 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">13 Okt 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">6</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Inheritance dan Polymorphism</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '13 Okt 2026',
                                                meeting: '6',
                                                topic: 'Inheritance dan Polymorphism',
                                                status: 'Hadir',
                                                time: '08:00 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 7: 20 Okt 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">20 Okt 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">7</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Interface dan Abstract Class</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '20 Okt 2026',
                                                meeting: '7',
                                                topic: 'Interface dan Abstract Class',
                                                status: 'Hadir',
                                                time: '08:06 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 8: 27 Okt 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">27 Okt 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">8</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Exception Handling</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '27 Okt 2026',
                                                meeting: '8',
                                                topic: 'Exception Handling',
                                                status: 'Hadir',
                                                time: '08:01 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 9: 03 Nov 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">03 Nov 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">9</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">File Handling</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '03 Nov 2026',
                                                meeting: '9',
                                                topic: 'File Handling',
                                                status: 'Hadir',
                                                time: '08:08 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 10: 10 Nov 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">10 Nov 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">10</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Database dan ORM</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '10 Nov 2026',
                                                meeting: '10',
                                                topic: 'Database dan ORM',
                                                status: 'Hadir',
                                                time: '08:03 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 11: 17 Nov 2026 -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'hadir'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">17 Nov 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">11</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Aplikasi Web dengan Laravel</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                            <x-icon name="check" :size="11" />
                                            <span>Hadir</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '17 Nov 2026',
                                                meeting: '11',
                                                topic: 'Aplikasi Web dengan Laravel',
                                                status: 'Hadir',
                                                time: '08:05 WIB',
                                                method: 'Scan QR di Kelas',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tepat waktu'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                                <!-- Baris 12: 24 Nov 2026 (Alfa) -->
                                <tr 
                                    x-show="filterStatus === 'all' || filterStatus === 'alfa'"
                                    class="hover:bg-canvas/40 transition-colors"
                                >
                                    <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">24 Nov 2026</td>
                                    <td class="py-2.5 px-3 tabular-nums">12</td>
                                    <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">Studi Kasus & Presentasi</td>
                                    <td class="py-2.5 px-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-600">
                                            <x-icon name="x" :size="11" />
                                            <span>Alfa</span>
                                        </span>
                                    </td>
                                    <td class="py-2.5 px-3 text-text-muted">-</td>
                                    <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                        <button 
                                            type="button"
                                            @click="openDetail({
                                                date: '24 Nov 2026',
                                                meeting: '12',
                                                topic: 'Studi Kasus & Presentasi',
                                                status: 'Alfa',
                                                time: 'Tidak Ada Catatan Presensi',
                                                method: 'Tidak Hadir',
                                                room: 'Lab Multimedia Lt. 3',
                                                notes: 'Tanpa konfirmasi kehadiran / surat izin'
                                            })"
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                        >
                                            <x-icon name="eye" :size="13" />
                                            <span>Lihat</span>
                                        </button>
                                    </td>
                                </tr>

                            </tbody>
                        </table>
                    </div>

                </x-ui.card>
            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): WIDGET REKAP, JADWAL & MATA KULIAH
                 ========================================== -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- 1. KARTU REKAP KEHADIRAN (Donut Chart + Legend) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between gap-2 mb-4">
                        <div class="flex items-center gap-2 font-bold text-sm text-text">
                            <x-icon name="pie-chart" :size="16" class="text-text" />
                            <span>Rekap Kehadiran</span>
                        </div>

                        <!-- Dropdown 7 Pertemuan Terakhir -->
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border border-line bg-surface text-xs text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors">
                            <span>7 Pertemuan Terakhir</span>
                            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                        </div>
                    </div>

                    <!-- Donut Chart & Legend -->
                    <div class="flex items-center justify-between gap-5 py-2">
                        <!-- Left: Circular Donut Chart -->
                        <div class="relative w-28 h-28 flex items-center justify-center shrink-0">
                            <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                                <!-- Background Track -->
                                <circle 
                                    cx="50" cy="50" r="40" 
                                    fill="transparent" 
                                    stroke="#F1F5F9" 
                                    stroke-width="12" 
                                />
                                <!-- Green Arc (92%) -->
                                <circle 
                                    cx="50" cy="50" r="40" 
                                    fill="transparent" 
                                    stroke="#10B981" 
                                    stroke-width="12" 
                                    stroke-dasharray="231.22 251.327" 
                                    stroke-dashoffset="0"
                                />
                                <!-- Amber Arc (8%) -->
                                <circle 
                                    cx="50" cy="50" r="40" 
                                    fill="transparent" 
                                    stroke="#F59E0B" 
                                    stroke-width="12" 
                                    stroke-dasharray="20.106 251.327" 
                                    stroke-dashoffset="-231.22"
                                />
                            </svg>
                            <div class="absolute text-center flex flex-col items-center">
                                <span class="text-xl font-bold text-text tabular-nums leading-none">92%</span>
                                <span class="text-[10px] text-text-muted font-normal mt-0.5">Kehadiran</span>
                            </div>
                        </div>

                        <!-- Right: Legend Breakdown -->
                        <div class="flex-1 space-y-2.5 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                                    <span class="text-text">Hadir</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-text tabular-nums">11</span>
                                    <span class="text-[11px] text-text-muted">(92%)</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span class="text-text">Izin</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-text tabular-nums">1</span>
                                    <span class="text-[11px] text-text-muted">(8%)</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                                    <span class="text-text">Alfa</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-text tabular-nums">0</span>
                                    <span class="text-[11px] text-text-muted">(0%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 2. KARTU JADWAL PRESENSI BERIKUTNYA -->
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <div class="flex items-center gap-2 font-bold text-sm text-text">
                            <x-icon name="calendar" :size="16" class="text-text" />
                            <span>Jadwal Presensi Berikutnya</span>
                        </div>
                        <a href="#jadwal" class="text-xs font-semibold text-[#2563EB] hover:underline">
                            Lihat Semua
                        </a>
                    </div>

                    <!-- Alert Banner Jadwal -->
                    <div class="bg-blue-50/50 border border-blue-100 rounded-[8px] p-3.5 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-[8px] bg-blue-100 text-[#2563EB] flex items-center justify-center shrink-0">
                                <x-icon name="calendar" :size="18" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-[11px] text-text-muted font-medium whitespace-nowrap">
                                    Pertemuan 13 • 24 Nov 2026, 10:00 - 12:00
                                </p>
                                <h4 class="text-xs font-bold text-text mt-0.5 truncate">
                                    Aplikasi Web dengan Laravel
                                </h4>
                            </div>
                        </div>

                        <!-- Button Lihat Detail -->
                        <div class="shrink-0">
                            <button 
                                type="button" 
                                @click="openDetail({
                                    date: '24 Nov 2026',
                                    meeting: '13',
                                    topic: 'Aplikasi Web dengan Laravel',
                                    status: 'Mendatang',
                                    time: '10:00 - 12:00 WIB',
                                    method: 'Scan QR di Kelas',
                                    room: 'Lab Multimedia Lt. 3 Gedung B',
                                    notes: 'Sesi perkuliahan belum dimulai'
                                })"
                                class="px-3 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer"
                            >
                                Lihat Detail
                            </button>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 3. KARTU KEHADIRAN PER MATA KULIAH -->
                <x-ui.card class="p-5">
                    <div class="flex items-center gap-2 font-bold text-sm text-text mb-4">
                        <x-icon name="bar-chart-3" :size="16" class="text-text" />
                        <span>Kehadiran per Mata Kuliah</span>
                    </div>

                    <div class="space-y-4">
                        <!-- MK 1: PBO -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-medium text-text truncate">
                                    Pemrograman Berorientasi Objek & Desain Pola
                                </span>
                                <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                    <span>92%</span>
                                    <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                </div>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: 92%;"></div>
                            </div>
                        </div>

                        <!-- MK 2: Basis Data -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-medium text-text truncate">
                                    Basis Data & Sistem Terdistribusi
                                </span>
                                <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                    <span>88%</span>
                                    <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                </div>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: 88%;"></div>
                            </div>
                        </div>

                        <!-- MK 3: Mini Core Banking -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-medium text-text truncate">
                                    Rancang Bangun Mini Core Banking System (OOP)
                                </span>
                                <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                    <span>100%</span>
                                    <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                </div>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: 100%;"></div>
                            </div>
                        </div>

                        <!-- MK 4: Jaringan Komputer -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-medium text-text truncate">
                                    Jaringan Komputer
                                </span>
                                <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                    <span>85%</span>
                                    <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                </div>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: 85%;"></div>
                            </div>
                        </div>

                        <!-- MK 5: Arsitektur Komputer -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between text-xs gap-2">
                                <span class="font-medium text-text truncate">
                                    Arsitektur & Organisasi Komputer
                                </span>
                                <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                    <span>88%</span>
                                    <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                </div>
                            </div>
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: 88%;"></div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

            </div>
        </div>

        <!-- ==========================================
             MODAL DETAIL PRESENSI (INTERAKTIF & NYATA)
             ========================================== -->
        <div 
            x-show="detailModalOpen" 
            x-transition.opacity.duration.150ms 
            class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div 
                class="w-full max-w-md bg-surface border border-line rounded-[10px] shadow-card overflow-hidden" 
                @click.away="detailModalOpen = false"
            >
                <div class="p-5 border-b border-line flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-accent" x-text="'Pertemuan ' + (selectedMeeting?.meeting || '')"></span>
                        <h3 class="text-base font-bold text-text mt-0.5" x-text="selectedMeeting?.topic"></h3>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-text-muted hover:text-text p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <div class="p-5 space-y-3.5 text-xs">
                    <div class="grid grid-cols-2 gap-3 p-3.5 rounded-[8px] bg-slate-50 border border-line">
                        <div>
                            <span class="text-text-muted block text-[11px]">Tanggal Perkuliahan</span>
                            <p class="font-semibold text-text mt-0.5" x-text="selectedMeeting?.date"></p>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Status Kehadiran</span>
                            <span 
                                class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold mt-0.5"
                                :class="{
                                    'bg-emerald-50 text-emerald-700': selectedMeeting?.status === 'Hadir',
                                    'bg-amber-50 text-amber-700': selectedMeeting?.status === 'Izin',
                                    'bg-red-50 text-red-700': selectedMeeting?.status === 'Alfa',
                                    'bg-blue-50 text-blue-700': selectedMeeting?.status === 'Mendatang'
                                }"
                                x-text="selectedMeeting?.status"
                            ></span>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Waktu Presensi</span>
                            <p class="font-medium text-text mt-0.5" x-text="selectedMeeting?.time"></p>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Metode Validasi</span>
                            <p class="font-medium text-text mt-0.5" x-text="selectedMeeting?.method"></p>
                        </div>
                    </div>

                    <div>
                        <span class="text-text-muted font-medium block text-[11px]">Ruang Kelas / Lokasi</span>
                        <p class="font-semibold text-text mt-0.5" x-text="selectedMeeting?.room"></p>
                    </div>

                    <div>
                        <span class="text-text-muted font-medium block text-[11px]">Catatan / Keterangan</span>
                        <p class="text-text mt-0.5" x-text="selectedMeeting?.notes"></p>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="detailModalOpen = false" 
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors shadow-2xs"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
