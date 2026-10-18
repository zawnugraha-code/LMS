@extends('layouts.dosen')

@section('title', 'Presensi Perkuliahan — Portal Dosen Arunika')
@section('page_breadcrumb', 'Presensi')

@section('content')
<div class="space-y-6">
    
    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="calendar-check" :size="22" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Presensi
                </h1>
                <p class="text-xs sm:text-[13px] text-text-muted mt-0.5 leading-relaxed">
                    Kelola presensi kehadiran mahasiswa untuk setiap kelas yang Anda ampu.
                </p>
            </div>
        </div>
    </div>

    <!-- Success / Error Alert -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-check" :size="16" class="text-emerald-600 shrink-0" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif

    <!-- 2. 4 Top Stat Cards (Screenshot 1) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Stat 1: Total Kelas -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Total Kelas</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['total_kelas'] }}</span>
                    <span class="text-xs text-text-muted">kelas yang diampu</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="users" :size="20" />
            </div>
        </div>

        <!-- Stat 2: Pertemuan Hari Ini -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Pertemuan Hari Ini</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['pertemuan_hari_ini'] }}</span>
                    <span class="text-xs text-text-muted">kelas</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="circle-check" :size="20" />
            </div>
        </div>

        <!-- Stat 3: Hadir Rata-rata -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Hadir Rata-rata</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['hadir_rata_rata'] }}%</span>
                </div>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5 flex items-center gap-1">
                    <span>↑ {{ $stats['hadir_trend'] }}</span>
                </p>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="users" :size="20" />
            </div>
        </div>

        <!-- Stat 4: Belum Absen -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Belum Absen</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['belum_absen'] }}</span>
                    <span class="text-xs text-text-muted">mahasiswa</span>
                </div>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="clock" :size="20" />
            </div>
        </div>

    </div>

    <!-- 3. Filter Tabs + Semester Selector + Search Bar + Button Input Presensi -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Status Tabs: Semua | Hadir | Tidak Hadir | Izin | Sakit -->
        <div class="inline-flex items-center gap-1.5 p-1 bg-surface border border-line rounded-[8px] overflow-x-auto text-xs font-medium">
            @php
                $tabs = [
                    'semua' => 'Semua',
                    'hadir' => 'Hadir',
                    'tidak_hadir' => 'Tidak Hadir',
                    'izin' => 'Izin',
                    'sakit' => 'Sakit',
                ];
            @endphp
            @foreach ($tabs as $key => $label)
                <a 
                    href="{{ route('dosen.presensi.index', array_merge(request()->query(), ['tab' => $key])) }}" 
                    class="px-3 py-1.5 rounded-[6px] transition-colors whitespace-nowrap {{ $activeTab === $key ? 'bg-[#2563EB] text-white font-semibold shadow-xs' : 'text-text-muted hover:text-text hover:bg-slate-100' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Right Controls: Semester Dropdown + Search Input + Button Input Presensi -->
        <div class="flex flex-wrap items-center gap-2.5">
            
            <!-- Semester Dropdown -->
            <div class="relative">
                <select 
                    onchange="location.href = '{{ route('dosen.presensi.index') }}?term_id=' + this.value"
                    class="h-9 pl-8 pr-8 text-xs bg-surface border border-line rounded-[6px] text-text font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer appearance-none shadow-xs"
                >
                    <option value="{{ $term?->id }}" selected>
                        {{ $term?->name ?? 'Semester 3 (Ganjil 2026/2027)' }}
                    </option>
                </select>
                <x-icon name="calendar" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
                <x-icon name="chevron-down" :size="12" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
            </div>

            <!-- Search Input -->
            <form method="GET" action="{{ route('dosen.presensi.index') }}" class="relative">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <input 
                    type="text" 
                    name="q" 
                    value="{{ $searchQuery }}" 
                    placeholder="Cari kelas atau mata kuliah..." 
                    class="h-9 pl-8 pr-3 w-56 sm:w-64 text-xs bg-surface border border-line rounded-[6px] text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB] shadow-xs"
                >
                <x-icon name="search" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
            </form>

            <!-- Tombol Primer: Input Presensi -->
            <a 
                href="{{ route('dosen.presensi.input') }}" 
                class="h-9 px-4 rounded-[6px] bg-[#1A2E5E] hover:bg-[#2C4A8F] text-white text-xs font-semibold flex items-center gap-2 shadow-xs transition-colors shrink-0"
            >
                <x-icon name="plus" :size="14" />
                <span>Input Presensi</span>
            </a>

        </div>

    </div>

    <!-- 4. Layout 2 Kolom (70% Tabel Kelas, 30% Panel Samping) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI (7/12 atau 8/12): Tabel Daftar Presensi Kelas -->
        <div class="lg:col-span-8 space-y-4">
            
            <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
                
                <!-- Header Card -->
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <x-icon name="calendar-check" :size="18" class="text-text-muted" />
                        <h2 class="text-sm font-bold text-text">Daftar Presensi Kelas</h2>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-line text-text-muted font-medium text-[11px]">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4 min-w-[200px]">Mata Kuliah</th>
                                <th class="py-3 px-4 text-center w-16">Kelas</th>
                                <th class="py-3 px-4 min-w-[180px]">Jadwal</th>
                                <th class="py-3 px-4 min-w-[160px]">Pertemuan</th>
                                <th class="py-3 px-4 min-w-[140px]">Kehadiran</th>
                                <th class="py-3 px-4 text-center w-20">Status</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/60">
                            @forelse ($classList as $idx => $cls)
                                <tr class="hover:bg-slate-50/50 transition-colors">
                                    
                                    <!-- No -->
                                    <td class="py-3.5 px-4 text-center font-medium text-text-muted">
                                        {{ $idx + 1 }}
                                    </td>

                                    <!-- Mata Kuliah -->
                                    <td class="py-3.5 px-4">
                                        <div class="flex items-start gap-2.5">
                                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100 mt-0.5">
                                                <x-icon name="code" :size="16" />
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-semibold text-text leading-tight hover:text-[#2563EB] cursor-pointer">
                                                    <a href="{{ route('dosen.presensi.show', $cls['id']) }}">
                                                        {{ $cls['course_name'] }}
                                                    </a>
                                                </p>
                                                <p class="text-[11px] text-text-muted mt-0.5">
                                                    {{ $cls['course_code'] }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Kelas -->
                                    <td class="py-3.5 px-4 text-center">
                                        @php
                                            $pillColor = str_contains($cls['section'], 'A') 
                                                ? 'bg-blue-50 text-[#2563EB] border-blue-200' 
                                                : 'bg-purple-50 text-purple-700 border-purple-200';
                                            $cleanSection = str_replace('Kelas ', '', $cls['section']);
                                        @endphp
                                        <span class="inline-flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold border {{ $pillColor }}">
                                            {{ $cleanSection }}
                                        </span>
                                    </td>

                                    <!-- Jadwal -->
                                    <td class="py-3.5 px-4">
                                        <p class="font-medium text-text leading-snug">
                                            {{ $cls['schedule_day'] }}, {{ $cls['time_range'] }}
                                        </p>
                                        <p class="text-[11px] text-text-muted mt-0.5">
                                            Ruang {{ $cls['room'] }}
                                        </p>
                                    </td>

                                    <!-- Pertemuan Terakhir -->
                                    <td class="py-3.5 px-4">
                                        <p class="text-text font-medium leading-snug">
                                            {{ $cls['last_meeting'] }}
                                        </p>
                                    </td>

                                    <!-- Kehadiran % + Progress Bar -->
                                    <td class="py-3.5 px-4">
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between text-xs">
                                                <span class="font-bold text-text">{{ $cls['attendance_rate'] }}%</span>
                                            </div>
                                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                                <div 
                                                    class="h-full bg-[#2563EB] rounded-full transition-all duration-300" 
                                                    style="width: {{ min(100, $cls['attendance_rate']) }}%"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Status -->
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            {{ $cls['status'] }}
                                        </span>
                                    </td>

                                    <!-- Aksi -->
                                    <td class="py-3.5 px-4 text-center">
                                        <a 
                                            href="{{ route('dosen.presensi.show', $cls['id']) }}" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[4px] text-xs font-semibold text-[#2563EB] hover:bg-blue-50 transition-colors"
                                        >
                                            <span>Lihat Detail</span>
                                            <x-icon name="chevron-right" :size="12" />
                                        </a>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-8 text-center text-text-muted text-xs">
                                        Tidak ada data kelas yang cocok dengan filter atau kata kunci pencarian.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="px-5 py-3 border-t border-line flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-text-muted">
                    <p>
                        Menampilkan 1–{{ count($classList) }} dari {{ count($classList) }} kelas
                    </p>
                    <div class="inline-flex items-center gap-1">
                        <button class="w-7 h-7 rounded-[4px] border border-line bg-surface text-text-muted hover:bg-slate-50 flex items-center justify-center cursor-not-allowed" disabled>
                            <x-icon name="chevron-left" :size="12" />
                        </button>
                        <span class="w-7 h-7 rounded-[4px] bg-[#2563EB] text-white font-bold flex items-center justify-center text-xs">
                            1
                        </span>
                        <button class="w-7 h-7 rounded-[4px] border border-line bg-surface text-text-muted hover:bg-slate-50 flex items-center justify-center cursor-not-allowed" disabled>
                            <x-icon name="chevron-right" :size="12" />
                        </button>
                    </div>
                </div>

            </div>

        </div>

        <!-- KOLOM KANAN (4/12): Rekap Presensi Donut + Kehadiran Hari Ini + Jadwal Mendatang -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- PANEL 1: Rekap Presensi (Pure SVG Donut) -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2">
                        <x-icon name="chart-pie" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text">Rekap Presensi</h3>
                    </div>
                    <span class="text-[11px] text-[#2563EB] font-medium hover:underline cursor-pointer">
                        Lihat Semua
                    </span>
                </div>

                <!-- Donut SVG & Central Label -->
                <div class="flex flex-col items-center">
                    <div class="relative w-44 h-44 my-2">
                        <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
                            <!-- Background Circle -->
                            <circle cx="50" cy="50" r="40" stroke="#F1F5F9" stroke-width="12" fill="transparent" />

                            <!-- Segment 1: Hadir (Blue #2563EB) -->
                            <circle 
                                cx="50" cy="50" r="40" 
                                stroke="#2563EB" stroke-width="12" fill="transparent"
                                stroke-dasharray="{{ $donut['hadir_dasharray'] }}" 
                                stroke-dashoffset="{{ $donut['hadir_dashoffset'] }}"
                                stroke-linecap="butt"
                            />

                            <!-- Segment 2: Izin (Amber #F59E0B) -->
                            <circle 
                                cx="50" cy="50" r="40" 
                                stroke="#F59E0B" stroke-width="12" fill="transparent"
                                stroke-dasharray="{{ $donut['izin_dasharray'] }}" 
                                stroke-dashoffset="{{ $donut['izin_dashoffset'] }}"
                                stroke-linecap="butt"
                            />

                            <!-- Segment 3: Sakit (Purple #8B5CF6) -->
                            <circle 
                                cx="50" cy="50" r="40" 
                                stroke="#8B5CF6" stroke-width="12" fill="transparent"
                                stroke-dasharray="{{ $donut['sakit_dasharray'] }}" 
                                stroke-dashoffset="{{ $donut['sakit_dashoffset'] }}"
                                stroke-linecap="butt"
                            />

                            <!-- Segment 4: Tidak Hadir (Red #EF4444) -->
                            <circle 
                                cx="50" cy="50" r="40" 
                                stroke="#EF4444" stroke-width="12" fill="transparent"
                                stroke-dasharray="{{ $donut['tidak_hadir_dasharray'] }}" 
                                stroke-dashoffset="{{ $donut['tidak_hadir_dashoffset'] }}"
                                stroke-linecap="butt"
                            />
                        </svg>

                        <!-- Central Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center select-none pointer-events-none">
                            <span class="text-2xl font-bold text-text tracking-tight">{{ $donut['rate'] }}%</span>
                            <span class="text-[10px] text-text-muted leading-tight mt-0.5">Rata-rata<br>Kehadiran</span>
                        </div>
                    </div>

                    <!-- Legend List -->
                    <div class="w-full mt-3 space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB]"></span>
                                <span class="text-text-muted">Hadir</span>
                            </div>
                            <span class="font-bold text-text">{{ $donut['hadir_count'] }} ({{ $donut['hadir_pct'] }}%)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B]"></span>
                                <span class="text-text-muted">Izin</span>
                            </div>
                            <span class="font-bold text-text">{{ $donut['izin_count'] }} ({{ $donut['izin_pct'] }}%)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#8B5CF6]"></span>
                                <span class="text-text-muted">Sakit</span>
                            </div>
                            <span class="font-bold text-text">{{ $donut['sakit_count'] }} ({{ $donut['sakit_pct'] }}%)</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444]"></span>
                                <span class="text-text-muted">Tidak Hadir</span>
                            </div>
                            <span class="font-bold text-text">{{ $donut['tidak_hadir_count'] }} ({{ $donut['tidak_hadir_pct'] }}%)</span>
                        </div>
                    </div>

                </div>

            </div>

            <!-- PANEL 2: Kehadiran Hari Ini (2x2 Grid) -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text">Kehadiran Hari Ini</h3>
                    </div>
                    <span class="text-[11px] text-[#2563EB] font-medium hover:underline cursor-pointer">
                        Lihat Detail
                    </span>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    
                    <!-- Hadir -->
                    <div class="p-3 rounded-[6px] border border-emerald-100 bg-emerald-50/40 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                            <x-icon name="circle-check" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-emerald-800">Hadir</p>
                            <p class="text-xs font-bold text-text mt-0.5">{{ $kehadiranHariIni['hadir'] }} mhs</p>
                        </div>
                    </div>

                    <!-- Izin -->
                    <div class="p-3 rounded-[6px] border border-amber-100 bg-amber-50/40 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
                            <x-icon name="user-check" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-amber-800">Izin</p>
                            <p class="text-xs font-bold text-text mt-0.5">{{ $kehadiranHariIni['izin'] }} mhs</p>
                        </div>
                    </div>

                    <!-- Sakit -->
                    <div class="p-3 rounded-[6px] border border-purple-100 bg-purple-50/40 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center shrink-0">
                            <x-icon name="activity" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-purple-800">Sakit</p>
                            <p class="text-xs font-bold text-text mt-0.5">{{ $kehadiranHariIni['sakit'] }} mhs</p>
                        </div>
                    </div>

                    <!-- Tidak Hadir -->
                    <div class="p-3 rounded-[6px] border border-red-100 bg-red-50/40 flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-full bg-red-100 text-red-700 flex items-center justify-center shrink-0">
                            <x-icon name="circle-x" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-[11px] font-semibold text-red-800">Tidak Hadir</p>
                            <p class="text-xs font-bold text-text mt-0.5">{{ $kehadiranHariIni['tidak_hadir'] }} mhs</p>
                        </div>
                    </div>

                </div>

            </div>

            <!-- PANEL 3: Jadwal Pertemuan Mendatang -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                
                <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text">Jadwal Pertemuan Mendatang</h3>
                    </div>
                    <span class="text-[11px] text-[#2563EB] font-medium hover:underline cursor-pointer">
                        Lihat Semua
                    </span>
                </div>

                <div class="space-y-3">
                    @forelse ($upcomingMeetings as $up)
                        <div class="flex items-start gap-3 p-2.5 rounded-[6px] hover:bg-slate-50 transition-colors border border-line/60">
                            <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100 mt-0.5">
                                <x-icon name="calendar" :size="16" />
                            </div>
                            <div class="min-w-0 flex-1 text-xs">
                                <p class="font-semibold text-text leading-tight truncate">
                                    {{ $up['class_name'] }} ({{ str_replace('Kelas ', '', $up['section']) }})
                                </p>
                                <p class="text-[11px] text-text-muted mt-0.5">
                                    {{ $up['day'] }}, {{ $up['date_formatted'] }} • {{ $up['time'] }} • {{ $up['room'] }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-text-muted text-center py-3">
                            Tidak ada jadwal perkuliahan mendatang.
                        </p>
                    @endforelse
                </div>

            </div>

        </div>

    </div>

</div>
@endsection
