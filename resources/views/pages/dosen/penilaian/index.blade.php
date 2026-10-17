@extends('layouts.dosen')

@section('title', 'Penilaian — Arunika Learning System')
@section('page_breadcrumb', 'Penilaian')

@section('content')
<div class="space-y-6">

    <!-- Header Judul Halaman -->
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
            <x-icon name="clipboard-check" :size="22" />
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight">
                Penilaian
            </h1>
            <p class="text-xs text-text-muted mt-0.5">
                Kelola dan nilai tugas, kuis, dan ujian mahasiswa dengan mudah dan efisien.
            </p>
        </div>
    </div>

    <!-- 4 Top Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1: Total Penilaian -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <x-icon name="file-text" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Total Penilaian</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['total_penilaian'] }}
                </span>
                <span class="text-[11px] text-[#2563EB] mt-0.5 inline-flex items-center gap-0.5 font-medium">
                    <span>&uarr;</span> 2 dari semester lalu
                </span>
            </div>
        </div>

        <!-- Card 2: Menunggu Penilaian -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <x-icon name="clock" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Menunggu Penilaian</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['menunggu_penilaian'] }}
                </span>
                <span class="text-[11px] text-amber-600 mt-0.5 inline-flex items-center gap-0.5 font-medium">
                    <span>&uarr;</span> 1 dari semester lalu
                </span>
            </div>
        </div>

        <!-- Card 3: Sudah Dinilai -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <x-icon name="check-circle" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Sudah Dinilai</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['sudah_dinilai'] }}
                </span>
                <span class="text-[11px] text-emerald-600 mt-0.5 inline-flex items-center gap-0.5 font-medium">
                    <span>&uarr;</span> 2 dari semester lalu
                </span>
            </div>
        </div>

        <!-- Card 4: Terlambat -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                <x-icon name="alert-circle" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Terlambat</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['terlambat'] }}
                </span>
                <span class="text-[11px] text-rose-600 mt-0.5 inline-flex items-center gap-0.5 font-medium">
                    <span>&uarr;</span> 1 dari semester lalu
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Tab & Dropdown Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Filter Tabs: Semua, Tugas, Kuis, UTS, UAS -->
        <div class="inline-flex items-center gap-1.5 p-1 rounded-[8px] bg-surface border border-line text-xs font-medium overflow-x-auto max-w-full">
            @foreach (['semua' => 'Semua', 'tugas' => 'Tugas', 'kuis' => 'Kuis', 'uts' => 'UTS', 'uas' => 'UAS'] as $key => $label)
                <a 
                    href="{{ route('dosen.penilaian.index', array_merge(request()->query(), ['jenis' => $key, 'page' => 1])) }}" 
                    class="px-3.5 py-1.5 rounded-[6px] transition-colors whitespace-nowrap {{ ($filters['jenis'] ?? 'semua') === $key ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-50' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Right Dropdowns: Semester, Mata Kuliah, Kelas, Search -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[8px] bg-surface border border-line text-xs font-medium text-text">
                <span>Semester 3 ({{ $term->name ?? 'Ganjil 2026/2027' }})</span>
                <x-icon name="chevron-down" :size="12" class="text-text-subtle" />
            </div>

            <!-- Filter Mata Kuliah -->
            <form method="GET" action="{{ route('dosen.penilaian.index') }}" class="inline-flex items-center gap-2">
                @if ($filters['jenis'] !== 'semua')
                    <input type="hidden" name="jenis" value="{{ $filters['jenis'] }}">
                @endif

                <select 
                    name="course_id" 
                    onchange="this.form.submit()" 
                    class="px-2.5 py-1.5 text-xs font-medium rounded-[8px] bg-surface border border-line text-text focus:outline-none focus:border-[#2563EB] cursor-pointer"
                >
                    <option value="semua">Mata Kuliah Semua</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}" {{ ($filters['course_id'] ?? '') == $c->id ? 'selected' : '' }}>
                            {{ $c->code }} - {{ $c->name }}
                        </option>
                    @endforeach
                </select>

                <select 
                    name="class_id" 
                    onchange="this.form.submit()" 
                    class="px-2.5 py-1.5 text-xs font-medium rounded-[8px] bg-surface border border-line text-text focus:outline-none focus:border-[#2563EB] cursor-pointer"
                >
                    <option value="semua">Kelas Semua</option>
                    @foreach ($classes as $cl)
                        <option value="{{ $cl->id }}" {{ ($filters['class_id'] ?? '') == $cl->id ? 'selected' : '' }}>
                            {{ $cl->section }} ({{ $cl->course->code }})
                        </option>
                    @endforeach
                </select>

                <div class="relative w-40">
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ $filters['search'] }}" 
                        placeholder="Cari assessment..." 
                        class="w-full pl-7 pr-2 py-1.5 text-xs rounded-[8px] border border-line bg-surface text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                    >
                    <x-icon name="search" :size="12" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-subtle pointer-events-none" />
                </div>
            </form>
        </div>
    </div>

    <!-- Main Grid: Left Table, Right Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Tabel Penilaian (8 Kolom) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-line/60">
                        <thead class="bg-slate-50 text-[11px] font-semibold text-text-muted uppercase">
                            <tr>
                                <th class="px-4 py-3 w-10 text-center">No</th>
                                <th class="px-3 py-3">Judul Assessment</th>
                                <th class="px-2 py-3 text-center">Jenis</th>
                                <th class="px-3 py-3">Mata Kuliah</th>
                                <th class="px-2 py-3 text-center">Kelas</th>
                                <th class="px-2 py-3 text-center">Jumlah Mahasiswa</th>
                                <th class="px-3 py-3">Deadline</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/40">
                            @forelse ($assessments as $item)
                                @php
                                    $letter = substr(str_replace('Kelas ', '', $item->academicClass?->section ?? 'A'), 0, 1);
                                    $jenisColor = match ($item->jenis) {
                                        'tugas' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'kuis' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'uts' => 'bg-rose-50 text-rose-700 border-rose-200',
                                        'uas' => 'bg-orange-50 text-orange-700 border-orange-200',
                                        default => 'bg-slate-50 text-slate-700 border-slate-200',
                                    };
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-4 py-3.5 text-center text-text-muted tabular-nums">
                                        {{ $loop->iteration + ($assessments->currentPage() - 1) * $assessments->perPage() }}
                                    </td>
                                    <td class="px-3 py-3.5 max-w-[200px]">
                                        <div class="flex items-start gap-2">
                                            <x-icon name="file-text" :size="14" class="text-text-muted mt-0.5 shrink-0" />
                                            <div class="min-w-0">
                                                <p class="font-bold text-text truncate" title="{{ $item->title }}">
                                                    {{ $item->title }}
                                                </p>
                                                <p class="text-[11px] text-text-muted mt-0.5 truncate">
                                                    {{ ucfirst($item->jenis) }} {{ $item->mode }} &bull; 20%
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-2 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $jenisColor }}">
                                            {{ $item->jenis }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-3.5 max-w-[170px]">
                                        <p class="font-bold text-text truncate">{{ $item->academicClass?->course?->code ?? '' }}</p>
                                        <p class="text-[11px] text-text-muted truncate mt-0.5">{{ $item->academicClass?->course?->name ?? '' }}</p>
                                    </td>
                                    <td class="px-2 py-3.5 text-center font-bold text-text">
                                        {{ $letter }}
                                    </td>
                                    <td class="px-2 py-3.5 text-center font-semibold text-text tabular-nums">
                                        {{ $item->students_count }}
                                    </td>
                                    <td class="px-3 py-3.5 text-text-muted whitespace-nowrap">
                                        <p class="font-medium text-text tabular-nums">
                                            {{ $item->due_at ? $item->due_at->locale('id')->isoFormat('D MMM Y') : '-' }}
                                        </p>
                                        <p class="text-[10px] text-text-subtle tabular-nums">
                                            {{ $item->due_at ? $item->due_at->format('H:i') : '' }}
                                        </p>
                                    </td>
                                    <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                        <x-dosen.status-badge :status="$item->penilaian_status ?? $item->status" size="xs" />
                                    </td>
                                    <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                        <div class="inline-flex items-center gap-1.5 justify-end">
                                            <a 
                                                href="{{ route('dosen.penilaian.show', $item->id) }}" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[#2563EB] bg-blue-50/40 hover:bg-blue-50 rounded-[4px] border border-blue-200 transition-colors"
                                                title="Lihat Rincian Penilaian"
                                            >
                                                <x-icon name="eye" :size="12" />
                                                <span>Lihat Detail</span>
                                            </a>

                                            @if (($item->penilaian_status ?? '') === 'Menunggu')
                                                <a 
                                                    href="{{ route('dosen.penilaian.show', $item->id) }}" 
                                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[4px] shadow-2xs transition-colors"
                                                >
                                                    <span>&#9654; Mulai Menilai</span>
                                                </a>
                                            @elseif (($item->penilaian_status ?? '') === 'Sudah Dinilai')
                                                <a 
                                                    href="{{ route('dosen.penilaian.show', $item->id) }}" 
                                                    class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-[#2563EB] bg-surface hover:bg-slate-50 border border-line rounded-[4px] transition-colors"
                                                >
                                                    <span>Lihat Hasil</span>
                                                </a>
                                            @elseif (($item->penilaian_status ?? '') === 'Draft')
                                                <a 
                                                    href="{{ route('dosen.tugas.edit', $item->id) }}" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-text-muted hover:text-text bg-surface border border-line rounded-[4px] transition-colors"
                                                >
                                                    <span>Kelola</span>
                                                    <x-icon name="chevron-down" :size="11" />
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-8 text-center text-xs text-text-muted">
                                        Tidak ada assessment untuk dinilai saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Footer Pagination -->
                <div class="px-5 py-3.5 bg-slate-50/60 border-t border-line/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs text-text-muted">
                    <div>
                        Menampilkan <span class="font-semibold text-text">{{ $assessments->firstItem() ?? 0 }}-{{ $assessments->lastItem() ?? 0 }}</span> dari <span class="font-semibold text-text">{{ $assessments->total() }}</span> assessment
                    </div>

                    @if ($assessments->hasPages())
                        <div class="inline-flex items-center gap-1">
                            @if ($assessments->onFirstPage())
                                <span class="w-7 h-7 rounded-[4px] border border-line bg-slate-100 text-text-subtle flex items-center justify-center cursor-not-allowed">
                                    <x-icon name="chevron-left" :size="13" />
                                </span>
                            @else
                                <a href="{{ $assessments->previousPageUrl() }}" class="w-7 h-7 rounded-[4px] border border-line bg-surface hover:bg-slate-100 text-text flex items-center justify-center transition-colors">
                                    <x-icon name="chevron-left" :size="13" />
                                </a>
                            @endif

                            @foreach ($assessments->getUrlRange(1, $assessments->lastPage()) as $page => $url)
                                <a 
                                    href="{{ $url }}" 
                                    class="w-7 h-7 rounded-[4px] border text-xs font-semibold flex items-center justify-center transition-colors {{ $page == $assessments->currentPage() ? 'bg-[#2563EB] text-white border-[#2563EB]' : 'bg-surface border-line text-text hover:bg-slate-100' }}"
                                >
                                    {{ $page }}
                                </a>
                            @endforeach

                            @if ($assessments->hasMorePages())
                                <a href="{{ $assessments->nextPageUrl() }}" class="w-7 h-7 rounded-[4px] border border-line bg-surface hover:bg-slate-100 text-text flex items-center justify-center transition-colors">
                                    <x-icon name="chevron-right" :size="13" />
                                </a>
                            @else
                                <span class="w-7 h-7 rounded-[4px] border border-line bg-slate-100 text-text-subtle flex items-center justify-center cursor-not-allowed">
                                    <x-icon name="chevron-right" :size="13" />
                                </span>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Widgets (4 Kolom) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- 1. Ringkasan Penilaian Widget -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-3">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="clipboard-list" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Ringkasan Penilaian</h2>
                    </div>
                    <a href="{{ route('dosen.penilaian.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="grid grid-cols-4 gap-2 text-center pt-1">
                    <div class="p-2 rounded-[6px] bg-canvas/60 border border-line">
                        <span class="text-base font-bold text-text tabular-nums block">{{ $summary['total_penilaian'] }}</span>
                        <span class="text-[10px] text-text-muted flex items-center justify-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Total
                        </span>
                    </div>

                    <div class="p-2 rounded-[6px] bg-canvas/60 border border-line">
                        <span class="text-base font-bold text-text tabular-nums block">{{ $summary['menunggu_penilaian'] }}</span>
                        <span class="text-[10px] text-text-muted flex items-center justify-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Menunggu
                        </span>
                    </div>

                    <div class="p-2 rounded-[6px] bg-canvas/60 border border-line">
                        <span class="text-base font-bold text-text tabular-nums block">{{ $summary['sudah_dinilai'] }}</span>
                        <span class="text-[10px] text-text-muted flex items-center justify-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Sudah Dinilai
                        </span>
                    </div>

                    <div class="p-2 rounded-[6px] bg-canvas/60 border border-line">
                        <span class="text-base font-bold text-text tabular-nums block">{{ $summary['terlambat'] }}</span>
                        <span class="text-[10px] text-text-muted flex items-center justify-center gap-1 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Terlambat
                        </span>
                    </div>
                </div>
            </div>

            <!-- 2. Progress Penilaian (Donut Chart Widget) -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                <div class="flex items-center gap-2">
                    <x-icon name="bar-chart-2" :size="16" class="text-navy-800" />
                    <h2 class="text-sm font-bold text-text">Progress Penilaian</h2>
                </div>

                <div class="flex items-center gap-6">
                    <!-- Donut SVG -->
                    <div class="relative w-32 h-32 shrink-0 flex items-center justify-center">
                        <svg class="w-full h-full -rotate-90 transform" viewBox="0 0 100 100">
                            <!-- Background Ring -->
                            <circle cx="50" cy="50" r="38" class="stroke-slate-100" stroke-width="12" fill="none" />
                            
                            <!-- Sudah Dinilai (Emerald) -->
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="38" 
                                stroke="#10B981" 
                                stroke-width="12" 
                                fill="none"
                                stroke-dasharray="{{ ($progress['p_sudah'] / 100) * 238.76 }} 238.76" 
                                stroke-dashoffset="0"
                            />

                            <!-- Menunggu (Amber) -->
                            <circle 
                                cx="50" 
                                cy="50" 
                                r="38" 
                                stroke="#F59E0B" 
                                stroke-width="12" 
                                fill="none"
                                stroke-dasharray="{{ ($progress['p_menunggu'] / 100) * 238.76 }} 238.76" 
                                stroke-dashoffset="{{ -(($progress['p_sudah'] / 100) * 238.76) }}"
                            />
                        </svg>

                        <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                            <span class="text-xl font-bold text-text tabular-nums leading-none">
                                {{ $progress['percentage'] }}%
                            </span>
                            <span class="text-[10px] font-semibold text-text-muted mt-1 uppercase tracking-wider">
                                Selesai
                            </span>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="space-y-2 text-xs flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#10B981] shrink-0"></span>
                                <span class="text-text-muted truncate">Sudah Dinilai</span>
                            </div>
                            <span class="font-bold text-text tabular-nums">{{ $progress['sudah_dinilai'] }} <span class="font-normal text-text-subtle text-[11px]">({{ $progress['p_sudah'] }}%)</span></span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B] shrink-0"></span>
                                <span class="text-text-muted truncate">Menunggu</span>
                            </div>
                            <span class="font-bold text-text tabular-nums">{{ $progress['menunggu'] }} <span class="font-normal text-text-subtle text-[11px]">({{ $progress['p_menunggu'] }}%)</span></span>
                        </div>

                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444] shrink-0"></span>
                                <span class="text-text-muted truncate">Terlambat</span>
                            </div>
                            <span class="font-bold text-text tabular-nums">{{ $progress['terlambat'] }} <span class="font-normal text-text-subtle text-[11px]">({{ $progress['p_terlambat'] }}%)</span></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Kelas yang Diampu Widget -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="users" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Kelas yang Diampu</h2>
                    </div>
                    <a href="{{ route('dosen.kelas.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    <a href="{{ route('dosen.kelas.index') }}" class="p-4 block hover:bg-slate-50/60 transition-colors group">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-text group-hover:text-[#2563EB] transition-colors truncate">
                                IF2101 - Pemrograman Berorientasi Objek &amp; Desain Pola
                            </h4>
                            <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                        </div>
                        <p class="text-[11px] text-text-muted mt-1">
                            Kelas A &bull; 32 mahasiswa &nbsp;|&nbsp; Kelas B &bull; 30 mahasiswa
                        </p>
                    </a>

                    <a href="{{ route('dosen.kelas.index') }}" class="p-4 block hover:bg-slate-50/60 transition-colors group">
                        <div class="flex items-center justify-between">
                            <h4 class="text-xs font-bold text-text group-hover:text-[#2563EB] transition-colors truncate">
                                IF2103 - Rancang Bangun Mini Core Banking System (OOP)
                            </h4>
                            <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                        </div>
                        <p class="text-[11px] text-text-muted mt-1">
                            Kelas A &bull; 28 mahasiswa
                        </p>
                    </a>
                </div>
            </div>

            <!-- 4. Aksi Cepat Widget -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-3">
                <h3 class="text-xs font-bold text-text uppercase tracking-wider text-text-muted">
                    Aksi Cepat
                </h3>

                <div class="grid grid-cols-2 gap-2.5">
                    <button type="button" class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center gap-2 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Rekap Nilai</span>
                    </button>

                    <button type="button" class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center gap-2 transition-colors cursor-pointer">
                        <x-icon name="download" :size="14" class="text-text-muted" />
                        <span>Export Nilai</span>
                    </button>

                    <button type="button" class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center gap-2 transition-colors cursor-pointer">
                        <x-icon name="layout" :size="14" class="text-text-muted" />
                        <span>Kelola Rubrik</span>
                    </button>

                    <button type="button" class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center gap-2 transition-colors cursor-pointer">
                        <x-icon name="settings" :size="14" class="text-text-muted" />
                        <span>Pengaturan</span>
                    </button>
                </div>
            </div>

            <!-- 5. Pengumuman Widget -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="megaphone" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumuman</h2>
                    </div>
                    <a href="{{ route('dosen.dashboard') }}" class="text-xs font-semibold text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    <div class="p-4 hover:bg-slate-50/60 transition-colors">
                        <h4 class="text-xs font-bold text-text leading-snug">
                            Batas penilaian UTS diperpanjang hingga 22 Okt 2026
                        </h4>
                        <span class="text-[10px] text-text-subtle mt-1 block">16 Okt 2026, 10:30</span>
                    </div>

                    <div class="p-4 hover:bg-slate-50/60 transition-colors">
                        <h4 class="text-xs font-bold text-text leading-snug">
                            Jadwal pengumpulan tugas kelompok berubah
                        </h4>
                        <span class="text-[10px] text-text-subtle mt-1 block">14 Okt 2026, 14:20</span>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
