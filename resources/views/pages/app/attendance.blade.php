@extends('layouts.app')

@section('title', 'Presensi — Arunika')
@section('page_breadcrumb', 'Presensi')

@section('content')

    @if(session('success'))
        <div class="mb-4 p-3.5 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="check-circle" :size="16" class="text-emerald-600" />
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 p-3.5 rounded-[8px] bg-red-50 border border-red-200 text-red-800 text-xs flex items-center justify-between">
            <div class="flex items-center gap-2">
                <x-icon name="alert-circle" :size="16" class="text-red-600" />
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif

    <div 
        x-data="{
            filterStatus: 'all',
            selectedMeeting: null,
            detailModalOpen: false,
            permitModalOpen: false,
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
                                stroke-dasharray="{{ $summary['dasharray_val'] ?? '115.6 125.66' }}" 
                                stroke-linecap="round" 
                            />
                        </svg>
                        <span class="absolute text-xs font-bold text-text tabular-nums">{{ $summary['rate_formatted'] ?? '92%' }}</span>
                    </div>
                    <div>
                        <span class="text-xs font-bold text-text block leading-tight">Tingkat Kehadiran</span>
                        <span class="text-[11px] text-text-muted block mt-0.5">Dari {{ $summary['total_sessions'] ?? 12 }} sesi perkuliahan</span>
                        <span class="inline-flex items-center gap-1 text-[10px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full mt-1.5">
                            <x-icon name="check" :size="10" />
                            <span>{{ $summary['status_label'] ?? 'Sangat Baik' }}</span>
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
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['present_count'] ?? 11 }}</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ {{ $summary['total_sessions'] ?? 12 }} sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-emerald-600 mt-2">
                            <x-icon name="arrow-up" :size="10" />
                            <span>{{ $summary['trend_present'] ?? '8% dari semester lalu' }}</span>
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
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['permit_count'] ?? 1 }}</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ {{ $summary['total_sessions'] ?? 12 }} sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-medium text-text-muted mt-2">
                            <x-icon name="arrow-up" :size="10" />
                            <span>{{ $summary['trend_permit'] ?? '0% dari semester lalu' }}</span>
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
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['absent_count'] ?? 0 }}</span>
                            <span class="text-[11px] text-text-muted ml-1.5 font-normal">/ {{ $summary['total_sessions'] ?? 12 }} sesi</span>
                        </div>
                        <div class="inline-flex items-center gap-0.5 text-[10px] font-semibold text-red-600 mt-2">
                            <x-icon name="arrow-down" :size="10" />
                            <span>{{ $summary['trend_absent'] ?? '2% dari semester lalu' }}</span>
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
                            <span>{{ $activeTerm->name ?? 'Semester 3 (Ganjil 2026/2027)' }}</span>
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
                                
                                @forelse($meetingLogs as $item)
                                    <tr 
                                        x-show="filterStatus === 'all' || filterStatus === '{{ $item['status_key'] }}'"
                                        class="hover:bg-canvas/40 transition-colors"
                                    >
                                        <td class="py-2.5 px-3 text-text-muted whitespace-nowrap">{{ $item['date'] }}</td>
                                        <td class="py-2.5 px-3 tabular-nums">{{ $item['meeting'] }}</td>
                                        <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">{{ $item['topic'] }}</td>
                                        <td class="py-2.5 px-3 whitespace-nowrap">
                                            @if($item['status_key'] === 'hadir')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-700">
                                                    <x-icon name="check" :size="11" />
                                                    <span>Hadir</span>
                                                </span>
                                            @elseif($item['status_key'] === 'izin')
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-700">
                                                    <x-icon name="dot" :size="12" class="text-amber-500" />
                                                    <span>Izin</span>
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-red-50 text-red-600">
                                                    <x-icon name="x" :size="11" />
                                                    <span>Alfa</span>
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-text-muted font-normal">{{ $item['keterangan'] }}</td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                            <button 
                                                type="button"
                                                @click="openDetail({{ json_encode($item) }})"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                            >
                                                <x-icon name="eye" :size="13" />
                                                <span>Lihat</span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-8 text-center text-xs text-text-muted">
                                            Belum ada data presensi perkuliahan untuk semester ini.
                                        </td>
                                    </tr>
                                @endforelse

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
                                    stroke-dasharray="{{ $donut['green_dasharray'] ?? '231.22 251.327' }}" 
                                    stroke-dashoffset="0"
                                />
                                <!-- Amber Arc (8%) -->
                                <circle 
                                    cx="50" cy="50" r="40" 
                                    fill="transparent" 
                                    stroke="#F59E0B" 
                                    stroke-width="12" 
                                    stroke-dasharray="{{ $donut['amber_dasharray'] ?? '20.106 251.327' }}" 
                                    stroke-dashoffset="{{ $donut['amber_dashoffset'] ?? '-231.22' }}"
                                />
                            </svg>
                            <div class="absolute text-center flex flex-col items-center">
                                <span class="text-xl font-bold text-text tabular-nums leading-none">{{ $donut['rate'] ?? 92 }}%</span>
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
                                    <span class="font-bold text-text tabular-nums">{{ $donut['present_count'] ?? 11 }}</span>
                                    <span class="text-[11px] text-text-muted">({{ $donut['present_pct'] ?? 92 }}%)</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                                    <span class="text-text">Izin</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-text tabular-nums">{{ $donut['permit_count'] ?? 1 }}</span>
                                    <span class="text-[11px] text-text-muted">({{ $donut['permit_pct'] ?? 8 }}%)</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                                    <span class="text-text">Alfa</span>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-bold text-text tabular-nums">{{ $donut['absent_count'] ?? 0 }}</span>
                                    <span class="text-[11px] text-text-muted">({{ $donut['absent_pct'] ?? 0 }}%)</span>
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
                        <a href="#riwayat" class="text-xs font-semibold text-[#2563EB] hover:underline">
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
                                    {{ $nextSchedule['schedule_formatted'] ?? 'Pertemuan 13 • 24 Nov 2026, 10:00 - 12:00' }}
                                </p>
                                <h4 class="text-xs font-bold text-text mt-0.5 truncate">
                                    {{ $nextSchedule['topic'] ?? 'Aplikasi Web dengan Laravel' }}
                                </h4>
                            </div>
                        </div>

                        <!-- Button Lihat Detail -->
                        <div class="shrink-0">
                            <button 
                                type="button" 
                                @click="openDetail({{ json_encode($nextSchedule ?? [
                                    'date' => '24 Nov 2026',
                                    'meeting' => '13',
                                    'topic' => 'Aplikasi Web dengan Laravel',
                                    'status' => 'Mendatang',
                                    'time' => '10:00 - 12:00 WIB',
                                    'method' => 'Scan QR di Kelas',
                                    'room' => 'Lab Multimedia Lt. 3 Gedung B',
                                    'notes' => 'Sesi perkuliahan belum dimulai',
                                    'is_open' => true,
                                    'meeting_id' => 13
                                ]) }})"
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
                        @foreach($courseBreakdown as $c)
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs gap-2">
                                    <span class="font-medium text-text truncate">
                                        {{ $c['name'] }}
                                    </span>
                                    <div class="flex items-center gap-1 font-bold text-text tabular-nums shrink-0">
                                        <span>{{ $c['percentage_formatted'] }}</span>
                                        <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                                    </div>
                                </div>
                                <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $c['percentage'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
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
                    <button type="button" @click="detailModalOpen = false" class="text-text-muted hover:text-text p-1 cursor-pointer">
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
                                    'bg-blue-50 text-blue-700': selectedMeeting?.status === 'Mendatang' || selectedMeeting?.status === 'Buka Presensi'
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

                    <!-- Interactive Check-In Bar when Session is Open / Upcoming -->
                    <template x-if="selectedMeeting?.status === 'Mendatang' || selectedMeeting?.status === 'Buka Presensi' || selectedMeeting?.is_open">
                        <div class="pt-3 border-t border-line space-y-3">
                            <div class="p-3 bg-blue-50/60 border border-blue-200 rounded-[8px] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="text-[11px] font-bold text-primary flex items-center gap-1">
                                        <x-icon name="qr-code" :size="13" />
                                        <span>Check-in Mandiri Sesi Aktif</span>
                                    </span>
                                    <span class="text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded font-bold">+5 XP</span>
                                </div>
                                <form action="{{ route('attendance.check_in.code') }}" method="POST" class="flex gap-2">
                                    @csrf
                                    <input 
                                        type="text" 
                                        name="code" 
                                        maxlength="6" 
                                        placeholder="Ketik 6 digit kode presensi..." 
                                        class="flex-1 px-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs text-text focus:outline-none focus:border-accent" 
                                        required 
                                    />
                                    <button 
                                        type="submit" 
                                        class="px-3 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors shrink-0 cursor-pointer"
                                    >
                                        Check-in
                                    </button>
                                </form>
                            </div>

                            <!-- Link to Submit Absence Permit (Izin / Sakit) -->
                            <div class="text-right">
                                <button 
                                    type="button" 
                                    @click="detailModalOpen = false; permitModalOpen = true"
                                    class="text-[11px] font-medium text-amber-600 hover:text-amber-700 hover:underline inline-flex items-center gap-1 cursor-pointer"
                                >
                                    <x-icon name="file-text" :size="12" />
                                    <span>Berhalangan hadir? Ajukan Izin / Sakit</span>
                                </button>
                            </div>
                        </div>
                    </template>

                    <div class="pt-3 border-t border-line flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="detailModalOpen = false" 
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors shadow-2xs cursor-pointer"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ==========================================
             MODAL PENGAJUAN IZIN / SAKIT (INTERAKTIF & NYATA)
             ========================================== -->
        <div 
            x-show="permitModalOpen" 
            x-transition.opacity.duration.150ms 
            class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
            style="display: none;"
        >
            <div 
                class="w-full max-w-md bg-surface border border-line rounded-[10px] shadow-card overflow-hidden" 
                @click.away="permitModalOpen = false"
            >
                <div class="p-5 border-b border-line flex items-center justify-between">
                    <div>
                        <span class="text-[10px] uppercase tracking-wider font-bold text-amber-600">Formulir Ketidakhadiran</span>
                        <h3 class="text-base font-bold text-text mt-0.5">Pengajuan Izin / Sakit</h3>
                    </div>
                    <button type="button" @click="permitModalOpen = false" class="text-text-muted hover:text-text p-1 cursor-pointer">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <form action="{{ route('attendance.permit') }}" method="POST" enctype="multipart/form-data" class="p-5 space-y-3.5 text-xs">
                    @csrf
                    <input type="hidden" name="meeting_id" :value="selectedMeeting?.id || 13" />

                    <div>
                        <label class="block text-text-muted font-medium mb-1">Mata Kuliah & Pertemuan:</label>
                        <p class="font-semibold text-text" x-text="(selectedMeeting?.topic || 'Aplikasi Web dengan Laravel') + ' (Minggu ' + (selectedMeeting?.meeting || '13') + ')'"></p>
                    </div>

                    <div>
                        <label class="block text-text-muted font-medium mb-1">Jenis Ketidakhadiran:</label>
                        <div class="grid grid-cols-2 gap-3">
                            <label class="flex items-center gap-2 p-2.5 rounded-[6px] border border-line bg-canvas/40 cursor-pointer hover:bg-canvas">
                                <input type="radio" name="type" value="izin" checked class="text-[#2563EB]" />
                                <span class="font-medium text-text">Izin Kegiatan</span>
                            </label>
                            <label class="flex items-center gap-2 p-2.5 rounded-[6px] border border-line bg-canvas/40 cursor-pointer hover:bg-canvas">
                                <input type="radio" name="type" value="sakit" class="text-[#2563EB]" />
                                <span class="font-medium text-text">Sakit (Dokter)</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-text-muted font-medium mb-1">Alasan / Penjelasan Resmi:</label>
                        <textarea 
                            name="reason" 
                            rows="3" 
                            required 
                            placeholder="Tuliskan keterangan lengkap alasan ketidakhadiran..." 
                            class="w-full p-2.5 rounded-[6px] border border-line bg-canvas text-xs text-text focus:outline-none focus:border-accent"
                        ></textarea>
                    </div>

                    <div>
                        <label class="block text-text-muted font-medium mb-1">Unggah Surat Bukti (PDF / JPG / PNG max 5MB):</label>
                        <input 
                            type="file" 
                            name="document" 
                            accept=".pdf,.jpg,.jpeg,.png" 
                            class="w-full text-xs text-text file:mr-3 file:py-1.5 file:px-3 file:rounded-[6px] file:border-0 file:text-xs file:font-medium file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 cursor-pointer" 
                        />
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-end gap-2.5">
                        <button 
                            type="button" 
                            @click="permitModalOpen = false" 
                            class="px-3.5 py-2 rounded-[6px] border border-line text-text-muted hover:text-text font-medium transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-4 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors shadow-2xs cursor-pointer"
                        >
                            Kirim Permohonan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

@endsection
