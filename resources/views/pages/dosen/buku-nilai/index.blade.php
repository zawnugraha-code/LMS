@extends('layouts.dosen')

@section('title', 'Buku Nilai — Portal Dosen Arunika')
@section('page_breadcrumb', 'Buku Nilai')

@section('content')
<div class="space-y-6">
    
    <!-- 1. Header Halaman -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="book-open" :size="20" />
            </div>
            <div>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Buku Nilai
                </h1>
                <p class="text-xs sm:text-[13px] text-text-muted mt-0.5 leading-relaxed">
                    Lihat rekap nilai, kelola komponen penilaian, dan ekspor nilai mahasiswa.
                </p>
            </div>
        </div>
    </div>

    <!-- Alert Success / Error -->
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
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-alert" :size="16" class="text-red-600 shrink-0" />
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-red-600 hover:text-red-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif

    <!-- 2. 4 Top Stat Cards (Screenshot 2) -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Card 1: Total Mahasiswa -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Total Mahasiswa</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['total_mahasiswa'] }}</span>
                </div>
                <p class="text-[11px] text-text-muted mt-0.5">di semua kelas yang diampu</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="users" :size="20" />
            </div>
        </div>

        <!-- Card 2: Total Mata Kuliah -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Total Mata Kuliah</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['total_mata_kuliah'] }}</span>
                </div>
                <p class="text-[11px] text-text-muted mt-0.5">mata kuliah yang diampu</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="file-text" :size="20" />
            </div>
        </div>

        <!-- Card 3: Nilai Final -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Nilai Final</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['nilai_final'] }}</span>
                </div>
                <p class="text-[11px] text-emerald-600 font-medium mt-0.5">mahasiswa sudah lengkap</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <x-icon name="circle-check" :size="20" />
            </div>
        </div>

        <!-- Card 4: Belum Lengkap -->
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Belum Lengkap</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['belum_lengkap'] }}</span>
                </div>
                <p class="text-[11px] text-amber-600 font-medium mt-0.5">mahasiswa masih ada penilaian</p>
            </div>
            <div class="w-11 h-11 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                <x-icon name="clock" :size="20" />
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Tabs, Semester, Search, Filter Button) -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        
        <!-- Left Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 lg:pb-0">
            <a 
                href="{{ route('dosen.buku-nilai.index', array_merge(request()->query(), ['tab' => 'semua'])) }}"
                class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeTab === 'semua' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line hover:border-slate-300' }}"
            >
                Semua Mata Kuliah
            </a>
            <a 
                href="{{ route('dosen.buku-nilai.index', array_merge(request()->query(), ['tab' => 'IF2101'])) }}"
                class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeTab === 'IF2101' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line hover:border-slate-300' }}"
            >
                IF2101
            </a>
            <a 
                href="{{ route('dosen.buku-nilai.index', array_merge(request()->query(), ['tab' => 'IF2103'])) }}"
                class="px-3.5 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeTab === 'IF2103' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line hover:border-slate-300' }}"
            >
                IF2103
            </a>
        </div>

        <!-- Right Controls: Semester Dropdown + Search + Filter Button -->
        <div class="flex flex-wrap sm:flex-nowrap items-center gap-2.5">
            
            <!-- Semester Dropdown Pill -->
            <div class="flex items-center gap-1.5 bg-surface border border-line rounded-[6px] px-3 py-1.5 text-xs text-text shadow-xs">
                <x-icon name="calendar" :size="14" class="text-text-muted shrink-0" />
                <span class="font-medium truncate">{{ $activeTerm ? $activeTerm->name : 'Semester 3 (Ganjil 2026/2027)' }}</span>
                <x-icon name="chevron-down" :size="13" class="text-text-muted ml-1" />
            </div>

            <!-- Search Input -->
            <form action="{{ route('dosen.buku-nilai.index') }}" method="GET" class="relative flex-1 sm:w-56">
                <input type="hidden" name="tab" value="{{ $activeTab }}">
                <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-text-muted">
                    <x-icon name="search" :size="14" />
                </span>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}"
                    placeholder="Cari mahasiswa..." 
                    class="w-full pl-8 pr-3 py-1.5 bg-surface border border-line rounded-[6px] text-xs text-text placeholder:text-text-muted/60 focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] shadow-xs"
                >
            </form>

            <!-- Filter Button -->
            <button 
                type="button" 
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-surface border border-line rounded-[6px] text-xs font-medium text-text hover:bg-slate-50 shadow-xs cursor-pointer"
            >
                <x-icon name="sliders-horizontal" :size="14" class="text-text-muted" />
                <span>Filter</span>
            </button>
        </div>
    </div>

    <!-- 4. Layout 2 Kolom (Kiri: Daftar Nilai Mata Kuliah 2/3, Kanan: 4 Widget 1/3) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM KIRI (8/12 = 2/3): TABEL UTAMA NILAI MATA KULIAH -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
                
                <!-- Table Card Header -->
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2.5">
                        <x-icon name="graduation-cap" :size="18" class="text-text-muted" />
                        <h2 class="text-sm font-bold text-text tracking-tight">
                            Daftar Nilai Mata Kuliah
                        </h2>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-line text-[11px] font-semibold text-text-muted uppercase tracking-wider">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4 min-w-[200px]">Mata Kuliah</th>
                                <th class="py-3 px-3 text-center w-16">Kelas</th>
                                <th class="py-3 px-3 text-center min-w-[90px]">Jumlah Mahasiswa</th>
                                <th class="py-3 px-4 min-w-[150px]">Komponen Penilaian</th>
                                <th class="py-3 px-3 text-center min-w-[100px]">Rata-rata Nilai</th>
                                <th class="py-3 px-4 text-center w-28">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line">
                            @forelse ($courses as $course)
                                @php
                                    $sectionsCount = count($course['sections']);
                                @endphp
                                @foreach ($course['sections'] as $sIndex => $sec)
                                    <tr class="hover:bg-slate-50/50 transition-colors">
                                        
                                        <!-- No (spanned across sections or per section) -->
                                        @if ($sIndex === 0)
                                            <td class="py-4 px-4 text-center font-medium text-text-muted align-top border-r border-line/40" rowspan="{{ $sectionsCount }}">
                                                {{ $course['no'] }}
                                            </td>
                                            <td class="py-4 px-4 align-top border-r border-line/40" rowspan="{{ $sectionsCount }}">
                                                <p class="font-semibold text-text text-xs leading-snug">
                                                    {{ $course['code'] }} - {{ $course['name'] }}
                                                </p>
                                                <span class="inline-block mt-1.5 px-2 py-0.5 rounded-[4px] text-[10px] font-medium bg-blue-50 text-[#2563EB] border border-blue-100">
                                                    {{ $course['prodi'] }}
                                                </span>
                                            </td>
                                        @endif

                                        <!-- Kelas Section (A, B) -->
                                        <td class="py-3.5 px-3 text-center font-semibold text-text">
                                            {{ $sec['section'] }}
                                        </td>

                                        <!-- Jumlah Mahasiswa -->
                                        <td class="py-3.5 px-3 text-center text-text font-normal">
                                            {{ $sec['student_count'] }}
                                        </td>

                                        <!-- Komponen Penilaian -->
                                        <td class="py-3.5 px-4 text-text-muted text-[11px] leading-relaxed">
                                            @foreach ($sec['components'] as $comp)
                                                <div>{{ $comp }}</div>
                                            @endforeach
                                        </td>

                                        <!-- Rata-rata Nilai & Badge Mutu -->
                                        <td class="py-3.5 px-3 text-center">
                                            <div class="inline-flex items-center gap-1.5 justify-center">
                                                <span class="font-bold text-text text-xs">{{ number_format($sec['average'], 1) }}</span>
                                                <span class="px-1.5 py-0.5 rounded-[4px] text-[10px] font-bold {{ $sec['letter'] === 'A' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                                    {{ $sec['letter'] }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- Aksi -->
                                        <td class="py-3.5 px-4 text-center">
                                            <a 
                                                href="{{ route('dosen.buku-nilai.show', $sec['id']) }}"
                                                class="inline-flex items-center gap-1 text-[11px] text-[#2563EB] hover:text-[#1A2E5E] border border-blue-200 hover:border-blue-300 rounded-[6px] px-2.5 py-1 font-medium transition-colors bg-white shadow-2xs"
                                            >
                                                <span>Lihat Detail</span>
                                                <x-icon name="chevron-right" :size="12" />
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-text-muted">
                                        <x-icon name="book-x" :size="28" class="mx-auto mb-2 text-text-muted/60" />
                                        <p class="font-medium text-xs">Tidak ada data mata kuliah yang sesuai filter.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination Info -->
                <div class="px-5 py-3 border-t border-line flex items-center justify-between text-xs text-text-muted">
                    <p>Menampilkan 1-{{ count($courses) }} dari {{ count($courses) }} mata kuliah</p>
                    <div class="flex items-center gap-1">
                        <button type="button" class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted cursor-not-allowed" disabled>
                            <x-icon name="chevron-left" :size="12" />
                        </button>
                        <span class="w-6 h-6 rounded-[4px] bg-[#2563EB] text-white flex items-center justify-center font-medium text-[11px]">
                            1
                        </span>
                        <button type="button" class="w-6 h-6 rounded-[4px] border border-line flex items-center justify-center text-text-muted cursor-not-allowed" disabled>
                            <x-icon name="chevron-right" :size="12" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- KOLOM KANAN (4/12 = 1/3): 4 WIDGET SCREENSHOT 2 -->
        <div class="lg:col-span-4 space-y-5">
            
            <!-- Widget 1: Rekap Nilai (Pure SVG Donut Chart) -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                    <div class="flex items-center gap-2">
                        <x-icon name="pie-chart" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text tracking-tight">Rekap Nilai</h3>
                    </div>
                    <a href="#daftar-nilai" class="text-[11px] font-medium text-[#2563EB] hover:underline">
                        Lihat Detail
                    </a>
                </div>

                <!-- Donut Chart & Legend -->
                <div class="flex flex-col sm:flex-row items-center gap-4">
                    
                    <!-- SVG Donut Chart -->
                    <div class="relative w-32 h-32 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 160 160">
                            <!-- Background Circle -->
                            <circle
                                cx="80"
                                cy="80"
                                r="65"
                                fill="transparent"
                                stroke="#F1F5F9"
                                stroke-width="18"
                            />
                            
                            <!-- Donut Segments (A, B, C, D, E) -->
                            @php
                                $circumference = 2 * M_PI * 65; // ~408.4
                                $accumulated = 0;
                            @endphp
                            @foreach ($donut['items'] as $item)
                                @php
                                    $dash = ($item['percent'] / 100) * $circumference;
                                    $offset = -($accumulated / 100) * $circumference;
                                    $accumulated += $item['percent'];
                                @endphp
                                <circle
                                    cx="80"
                                    cy="80"
                                    r="65"
                                    fill="transparent"
                                    stroke="{{ $item['color'] }}"
                                    stroke-width="18"
                                    stroke-dasharray="{{ $dash }} {{ $circumference - $dash }}"
                                    stroke-dashoffset="{{ $offset }}"
                                    stroke-linecap="butt"
                                />
                            @endforeach
                        </svg>

                        <!-- Center Text -->
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none text-center">
                            <span class="text-xl font-extrabold text-text leading-none">{{ $donut['total_courses'] }}</span>
                            <span class="text-[10px] text-text-muted mt-0.5 font-medium">Mata Kuliah</span>
                        </div>
                    </div>

                    <!-- Legend Items -->
                    <div class="space-y-1.5 flex-1 min-w-0 text-[11px]">
                        @foreach ($donut['items'] as $item)
                            <div class="flex items-center justify-between text-text-muted">
                                <div class="flex items-center gap-2 truncate">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background-color: {{ $item['color'] }}"></span>
                                    <span class="truncate">{{ $item['label'] }}</span>
                                </div>
                                <span class="font-semibold text-text ml-2 shrink-0">
                                    {{ $item['count'] }} ({{ $item['percent'] }}%)
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Widget 2: Komponen Penilaian (Default) -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="layers" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text tracking-tight">Komponen Penilaian (Default)</h3>
                    </div>
                    <a href="#daftar-nilai" class="text-[11px] font-medium text-[#2563EB] hover:underline">
                        Lihat Detail
                    </a>
                </div>

                <div class="space-y-2.5 text-xs">
                    @foreach ($defaultComponents as $comp)
                        <div class="flex items-center justify-between py-1 border-b border-line/40 last:border-b-0">
                            <span class="text-text font-normal">{{ $comp['name'] }}</span>
                            <div class="flex items-center gap-2.5">
                                <span class="font-bold text-text">{{ $comp['weight'] }}%</span>
                                <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-medium bg-slate-100 text-slate-700">
                                    {{ $comp['count'] }} {{ $comp['unit'] }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Widget 3: Aksi Cepat (2x2 Grid) -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                <div class="flex items-center gap-2 pb-3 border-b border-line mb-3">
                    <x-icon name="zap" :size="16" class="text-text-muted" />
                    <h3 class="text-xs font-bold text-text tracking-tight">Aksi Cepat</h3>
                </div>

                <div class="grid grid-cols-2 gap-2.5 text-xs">
                    
                    <!-- 1. Export Nilai -->
                    @php
                        $firstClassId = $courses->first()['sections'][0]['id'] ?? 22;
                    @endphp
                    <a 
                        href="{{ route('dosen.buku-nilai.export', $firstClassId) }}"
                        class="p-2.5 bg-slate-50 hover:bg-blue-50/60 border border-line hover:border-blue-200 rounded-[6px] text-text hover:text-[#2563EB] transition-colors flex items-center gap-2 font-medium"
                    >
                        <x-icon name="download" :size="15" class="text-text-muted shrink-0" />
                        <span class="truncate">Export Nilai</span>
                    </a>

                    <!-- 2. Rekap Nilai -->
                    <a 
                        href="#rekap"
                        class="p-2.5 bg-slate-50 hover:bg-blue-50/60 border border-line hover:border-blue-200 rounded-[6px] text-text hover:text-[#2563EB] transition-colors flex items-center gap-2 font-medium"
                    >
                        <x-icon name="bar-chart-2" :size="15" class="text-text-muted shrink-0" />
                        <span class="truncate">Rekap Nilai</span>
                    </a>

                    <!-- 3. Pengaturan Penilaian -->
                    <a 
                        href="{{ route('dosen.buku-nilai.show', $firstClassId) }}#pengaturan"
                        class="p-2.5 bg-slate-50 hover:bg-blue-50/60 border border-line hover:border-blue-200 rounded-[6px] text-text hover:text-[#2563EB] transition-colors flex items-center gap-2 font-medium"
                    >
                        <x-icon name="sliders" :size="15" class="text-text-muted shrink-0" />
                        <span class="truncate">Pengaturan Penilaian</span>
                    </a>

                    <!-- 4. Rubrik Penilaian -->
                    <a 
                        href="#rubrik"
                        class="p-2.5 bg-slate-50 hover:bg-blue-50/60 border border-line hover:border-blue-200 rounded-[6px] text-text hover:text-[#2563EB] transition-colors flex items-center gap-2 font-medium"
                    >
                        <x-icon name="file-text" :size="15" class="text-text-muted shrink-0" />
                        <span class="truncate">Rubrik Penilaian</span>
                    </a>
                </div>
            </div>

            <!-- Widget 4: Pengumuman -->
            <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                    <div class="flex items-center gap-2">
                        <x-icon name="megaphone" :size="16" class="text-text-muted" />
                        <h3 class="text-xs font-bold text-text tracking-tight">Pengumuman</h3>
                    </div>
                    <a href="{{ route('dosen.dashboard') }}" class="text-[11px] font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="flex items-start gap-3 p-3 bg-blue-50/40 border border-blue-100 rounded-[6px]">
                    <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-200/50">
                        <x-icon name="file-text" :size="16" />
                    </div>
                    <div class="min-w-0">
                        <p class="text-xs font-semibold text-text leading-snug">
                            Batas penilaian UTS diperpanjang hingga 22 Okt 2026
                        </p>
                        <p class="text-[11px] text-text-muted mt-1">
                            16 Okt 2026, 10:30
                        </p>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
