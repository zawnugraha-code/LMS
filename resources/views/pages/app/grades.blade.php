@extends('layouts.app')

@section('title', 'Nilai — Arunika')
@section('page_breadcrumb', 'Nilai')

@section('content')

    <div 
        x-data="{
            activeSemester: 'all',
            selectedCourse: null,
            detailModalOpen: false,
            openDetail(item) {
                this.selectedCourse = item;
                this.detailModalOpen = true;
            }
        }"
    >
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI & 4 STAT CARDS
             ========================================== -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-6 mb-6">
            <!-- Left Header -->
            <div class="flex items-start gap-3.5 shrink-0 xl:max-w-xs">
                <div class="shrink-0 text-[#17306B] pt-0.5">
                    <x-icon name="bar-chart-3" :size="36" :strokeWidth="2" class="text-[#17306B]" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Nilai
                    </h1>
                    <p class="text-xs sm:text-[13px] text-text-muted mt-1 leading-relaxed">
                        Lihat dan kelola nilai dari semua mata kuliah yang kamu ambil di semester ini.
                    </p>
                </div>
            </div>

            <!-- Right: 4 Stat Cards Horizontal (Persis Gambar Acuan) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3.5 flex-1 xl:max-w-5xl">
                
                <!-- Stat 1: IPK Semester Ini (3.72) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <x-icon name="graduation-cap" :size="16" />
                        </div>
                        <span class="text-xs font-medium text-text-muted">IPK Semester Ini</span>
                    </div>
                    <div class="mt-2.5">
                        <span class="text-2xl font-bold text-text tabular-nums leading-none block">{{ $summary['gpa'] }}</span>
                        <div class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 mt-2">
                            <x-icon name="arrow-up" :size="12" />
                            <span>{{ $summary['gpa_change_label'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 2: Total SKS (18 / 24 SKS, 75%) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                            <x-icon name="book-open" :size="16" />
                        </div>
                        <span class="text-xs font-medium text-text-muted">Total SKS</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['credits_taken'] }}</span>
                            <span class="text-xs text-text-muted ml-1.5 font-normal">/ {{ $summary['credits_max'] }} SKS</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2.5">
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden flex-1">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $summary['credits_percent'] }}%;"></div>
                            </div>
                            <span class="text-[11px] font-medium text-text-muted shrink-0 tabular-nums">{{ $summary['credits_percent'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 3: Mata Kuliah Dinilai (10 / 12 mata kuliah, 83%) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                            <x-icon name="check-circle" :size="16" />
                        </div>
                        <span class="text-xs font-medium text-text-muted">Mata Kuliah Dinilai</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['graded_count'] }}</span>
                            <span class="text-xs text-text-muted ml-1.5 font-normal">/ {{ $summary['total_courses'] }} mata kuliah</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2.5">
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden flex-1">
                                <div class="h-full bg-[#10B981] rounded-full" style="width: {{ $summary['graded_percent'] }}%;"></div>
                            </div>
                            <span class="text-[11px] font-medium text-text-muted shrink-0 tabular-nums">{{ $summary['graded_percent'] }}%</span>
                        </div>
                    </div>
                </div>

                <!-- Stat 4: Mata Kuliah Belum Selesai (2 / 12 mata kuliah, 17%) -->
                <div class="bg-surface border border-line rounded-[10px] p-3.5 px-4 flex flex-col justify-between shadow-2xs">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-[8px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                            <x-icon name="clock" :size="16" />
                        </div>
                        <span class="text-xs font-medium text-text-muted">Mata Kuliah Belum Selesai</span>
                    </div>
                    <div class="mt-2.5">
                        <div class="flex items-baseline">
                            <span class="text-2xl font-bold text-text tabular-nums leading-none">{{ $summary['unfinished_count'] }}</span>
                            <span class="text-xs text-text-muted ml-1.5 font-normal">/ {{ $summary['total_courses'] }} mata kuliah</span>
                        </div>
                        <div class="flex items-center gap-2 mt-2.5">
                            <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden flex-1">
                                <div class="h-full bg-[#F59E0B] rounded-full" style="width: {{ $summary['unfinished_percent'] }}%;"></div>
                            </div>
                            <span class="text-[11px] font-medium text-text-muted shrink-0 tabular-nums">{{ $summary['unfinished_percent'] }}%</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- ==========================================
             2. MAIN LAYOUT: 2 KOLOM (KIRI ~68%, KANAN ~32%)
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- ==========================================
                 KOLOM KIRI (lg:col-span-8): DAFTAR NILAI MATA KULIAH TABEL
                 ========================================== -->
            <div class="lg:col-span-8">
                <x-ui.card class="p-5">
                    
                    <!-- Header Kartu: Judul & Dropdown Semester -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-line/60">
                        <h2 class="text-base font-bold text-text">
                            Daftar Nilai Mata Kuliah
                        </h2>

                        <!-- Dropdown Semester (Kanan) -->
                        <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors self-start sm:self-auto">
                            <x-icon name="calendar" :size="14" class="text-text-muted" />
                            <span>{{ $activeTerm->name ?? 'Semester 3 (Ganjil 2026/2027)' }}</span>
                            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                        </div>
                    </div>

                    <!-- Semester Tab Pills: Semua, Semester 1 - 8 (Persis Gambar Acuan) -->
                    <div class="overflow-x-auto py-3.5 no-scrollbar">
                        <div class="inline-flex items-center gap-1.5 whitespace-nowrap">
                            <button 
                                type="button" 
                                @click="activeSemester = 'all'"
                                :class="activeSemester === 'all' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-4 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semua
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '1'"
                                :class="activeSemester === '1' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 1
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '2'"
                                :class="activeSemester === '2' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 2
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '3'"
                                :class="activeSemester === '3' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 3
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '4'"
                                :class="activeSemester === '4' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 4
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '5'"
                                :class="activeSemester === '5' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 5
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '6'"
                                :class="activeSemester === '6' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 6
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '7'"
                                :class="activeSemester === '7' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 7
                            </button>

                            <button 
                                type="button" 
                                @click="activeSemester = '8'"
                                :class="activeSemester === '8' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-[#94A3B8] font-normal hover:text-text hover:bg-slate-50'"
                                class="px-3 py-1.5 rounded-[6px] text-xs transition-colors cursor-pointer"
                            >
                                Semester 8
                            </button>
                        </div>
                    </div>

                    <!-- TABEL DAFTAR NILAI (12 BARIS PERSIS GAMBAR ACUAN) -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="border-b border-line text-[11px] font-semibold text-text-muted">
                                    <th class="py-2.5 px-3">No</th>
                                    <th class="py-2.5 px-3">Kode MK</th>
                                    <th class="py-2.5 px-3">Mata Kuliah</th>
                                    <th class="py-2.5 px-3 text-center">SKS</th>
                                    <th class="py-2.5 px-3 text-center">Nilai Angka</th>
                                    <th class="py-2.5 px-3 text-center">Nilai Huruf</th>
                                    <th class="py-2.5 px-3 text-center">Status</th>
                                    <th class="py-2.5 px-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/60 text-xs text-text">
                                @forelse($grades as $item)
                                    <tr 
                                        x-show="activeSemester === 'all' || activeSemester === '{{ $item['semester'] }}'"
                                        class="hover:bg-canvas/40 transition-colors"
                                    >
                                        <td class="py-2.5 px-3 text-text-muted tabular-nums">{{ $item['no'] }}</td>
                                        <td class="py-2.5 px-3 font-medium text-text-muted whitespace-nowrap">{{ $item['course_code'] }}</td>
                                        <td class="py-2.5 px-3 font-medium text-text max-w-xs truncate">{{ $item['course_name'] }}</td>
                                        <td class="py-2.5 px-3 text-center tabular-nums">{{ $item['credits'] }}</td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                            @if($item['final_score'] !== null)
                                                <span class="inline-block px-2 py-0.5 rounded text-[11px] font-semibold text-emerald-700 bg-emerald-50 tabular-nums">
                                                    {{ $item['final_score_display'] }}
                                                </span>
                                            @else
                                                <span class="text-text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-center font-bold {{ $item['letter_grade'] && in_array($item['letter_grade'], ['A', 'A-']) ? 'text-emerald-700' : 'text-text' }}">
                                            {{ $item['letter_grade_display'] }}
                                        </td>
                                        <td class="py-2.5 px-3 text-center whitespace-nowrap">
                                            @if($item['final_score'] !== null)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                                    {{ $item['status_label'] }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-medium bg-amber-50 text-amber-700">
                                                    {{ $item['status_label'] }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 px-3 text-right whitespace-nowrap">
                                            <button 
                                                type="button"
                                                @click="openDetail({
                                                    no: {{ $item['no'] }},
                                                    code: '{{ $item['course_code'] }}',
                                                    name: '{{ addslashes($item['course_name']) }}',
                                                    sks: {{ $item['credits'] }},
                                                    score: '{{ $item['final_score_display'] }}',
                                                    grade: '{{ $item['letter_grade_display'] }}',
                                                    status: '{{ $item['status_label'] }}',
                                                    lecturer: '{{ addslashes($item['lecturer_name']) }}',
                                                    assignmentsScore: '{{ $item['assignments_score'] }}',
                                                    quizzesScore: '{{ $item['quizzes_score'] }}',
                                                    midtermScore: '{{ $item['midterm_score'] }}',
                                                    finalScore: '{{ $item['final_score_exam'] }}',
                                                    detailUrl: '{{ $item['detail_url'] }}'
                                                })"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-[6px] border border-blue-200 bg-blue-50/50 hover:bg-blue-100/70 text-[#2563EB] text-xs font-medium transition-colors cursor-pointer"
                                            >
                                                <span>Lihat Detail</span>
                                                <x-icon name="chevron-right" :size="11" />
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="py-8 text-center text-xs text-text-muted">
                                            Belum ada mata kuliah yang dinilai untuk periode ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Footer Tabel: Info Jumlah & Pagination -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-4 border-t border-line/60 mt-2">
                        <span class="text-xs text-text-muted">
                            Menampilkan 1-{{ count($grades) }} dari {{ count($grades) }} mata kuliah
                        </span>

                        <div class="inline-flex items-center gap-1">
                            <button 
                                type="button" 
                                class="w-7 h-7 rounded-[6px] border border-line bg-surface text-text-muted hover:bg-canvas flex items-center justify-center text-xs transition-colors cursor-pointer"
                            >
                                <x-icon name="chevron-left" :size="12" />
                            </button>
                            <button 
                                type="button" 
                                class="w-7 h-7 rounded-[6px] bg-[#2563EB] text-white flex items-center justify-center text-xs font-semibold shadow-2xs"
                            >
                                1
                            </button>
                            <button 
                                type="button" 
                                class="w-7 h-7 rounded-[6px] border border-line bg-surface text-text-muted hover:bg-canvas flex items-center justify-center text-xs transition-colors cursor-pointer"
                            >
                                <x-icon name="chevron-right" :size="12" />
                            </button>
                        </div>
                    </div>

                </x-ui.card>
            </div>

            <!-- ==========================================
                 KOLOM KANAN (lg:col-span-4): WIDGET DISTRIBUSI, PERFORMA & PENGUMUMAN
                 ========================================== -->
            <div class="lg:col-span-4 space-y-5">
                
                <!-- 1. KARTU DISTRIBUSI NILAI (Donut Chart + Legend Breakdown) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center gap-2 font-bold text-sm text-text mb-4">
                        <x-icon name="pie-chart" :size="16" class="text-text" />
                        <span>Distribusi Nilai</span>
                    </div>

                    <div class="flex items-center justify-between gap-4 py-1">
                        <!-- Donut Chart -->
                        <div class="relative w-28 h-28 flex items-center justify-center shrink-0">
                            <svg class="w-28 h-28 -rotate-90" viewBox="0 0 100 100">
                                <circle cx="50" cy="50" r="38" fill="transparent" stroke="#F1F5F9" stroke-width="12" />
                                
                                <!-- Slice 1: A (41.7% -> 99.5px) Green -->
                                <circle 
                                    cx="50" cy="50" r="38" 
                                    fill="transparent" 
                                    stroke="#10B981" 
                                    stroke-width="12" 
                                    stroke-dasharray="99.5 238.76" 
                                    stroke-dashoffset="0" 
                                />

                                <!-- Slice 2: B+ (33.3% -> 79.5px) Blue -->
                                <circle 
                                    cx="50" cy="50" r="38" 
                                    fill="transparent" 
                                    stroke="#2563EB" 
                                    stroke-width="12" 
                                    stroke-dasharray="79.5 238.76" 
                                    stroke-dashoffset="-99.5" 
                                />

                                <!-- Slice 3: B (16.7% -> 39.8px) Amber -->
                                <circle 
                                    cx="50" cy="50" r="38" 
                                    fill="transparent" 
                                    stroke="#F59E0B" 
                                    stroke-width="12" 
                                    stroke-dasharray="39.8 238.76" 
                                    stroke-dashoffset="-179" 
                                />

                                <!-- Slice 4: C+ (8.3% -> 19.8px) Orange -->
                                <circle 
                                    cx="50" cy="50" r="38" 
                                    fill="transparent" 
                                    stroke="#F97316" 
                                    stroke-width="12" 
                                    stroke-dasharray="19.8 238.76" 
                                    stroke-dashoffset="-218.8" 
                                />
                            </svg>
                            <div class="absolute text-center flex flex-col items-center">
                                <span class="text-xl font-bold text-text tabular-nums leading-none">10</span>
                                <span class="text-[10px] text-text-muted font-normal mt-0.5">Mata Kuliah</span>
                            </div>
                        </div>

                        <!-- Legend Items -->
                        <div class="flex-1 space-y-2 text-xs">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#10B981] shrink-0"></span>
                                    <span class="text-text">A (85-100)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">5</span>
                                    <span class="text-[11px] text-text-muted">41.7%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#2563EB] shrink-0"></span>
                                    <span class="text-text">B+ (75-84)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">4</span>
                                    <span class="text-[11px] text-text-muted">33.3%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#F59E0B] shrink-0"></span>
                                    <span class="text-text">B (70-74)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">2</span>
                                    <span class="text-[11px] text-text-muted">16.7%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#F97316] shrink-0"></span>
                                    <span class="text-text">C+ (60-69)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">1</span>
                                    <span class="text-[11px] text-text-muted">8.3%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#EF4444] shrink-0"></span>
                                    <span class="text-text">C (50-59)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">0</span>
                                    <span class="text-[11px] text-text-muted">0%</span>
                                </div>
                            </div>

                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full bg-[#DC2626] shrink-0"></span>
                                    <span class="text-text">D (0-49)</span>
                                </div>
                                <div class="flex items-center gap-2 tabular-nums">
                                    <span class="font-bold text-text">0</span>
                                    <span class="text-[11px] text-text-muted">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 2. KARTU PERFORMA AKADEMIK (Line Graph) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <div class="flex items-center gap-2 font-bold text-sm text-text">
                            <x-icon name="bar-chart-3" :size="16" class="text-text" />
                            <span>Performa Akademik</span>
                        </div>

                        <!-- Dropdown Semester -->
                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border border-line bg-surface text-xs text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors">
                            <span>Semester 3 (Ganjil 2026/2027)</span>
                            <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                        </div>
                    </div>

                    <!-- Area / Line Chart SVG (Persis Gambar Acuan) -->
                    <div class="pt-2">
                        <div class="flex items-start gap-2">
                            <!-- Y-Axis Labels -->
                            <div class="flex flex-col justify-between text-[10px] text-text-subtle font-medium h-[120px] text-right tabular-nums pr-1 shrink-0">
                                <span>4.00</span>
                                <span>3.00</span>
                                <span>2.00</span>
                                <span>1.00</span>
                                <span>0.00</span>
                            </div>

                            <!-- SVG Chart Canvas -->
                            <div class="flex-1 h-[120px] relative">
                                <svg class="w-full h-full overflow-visible" viewBox="0 0 320 120" preserveAspectRatio="none">
                                    <defs>
                                        <linearGradient id="chartGradient" x1="0" y1="0" x2="0" y2="1">
                                            <stop offset="0%" stop-color="#2563EB" stop-opacity="0.18" />
                                            <stop offset="100%" stop-color="#2563EB" stop-opacity="0.0" />
                                        </linearGradient>
                                    </defs>

                                    <!-- Horizontal Grid Lines -->
                                    <line x1="0" y1="0" x2="320" y2="0" stroke="#F1F5F9" stroke-width="1" />
                                    <line x1="0" y1="30" x2="320" y2="30" stroke="#F1F5F9" stroke-width="1" />
                                    <line x1="0" y1="60" x2="320" y2="60" stroke="#F1F5F9" stroke-width="1" />
                                    <line x1="0" y1="90" x2="320" y2="90" stroke="#F1F5F9" stroke-width="1" />
                                    <line x1="0" y1="120" x2="320" y2="120" stroke="#E2E8F0" stroke-width="1" />

                                    <!-- Fill Area -->
                                    <path 
                                        d="M 20 45 C 45 45, 55 49, 75 49 C 95 49, 110 54, 130 54 C 150 54, 170 38, 190 38 C 210 38, 230 42, 250 42 C 270 42, 285 18, 305 18 L 305 120 L 20 120 Z" 
                                        fill="url(#chartGradient)" 
                                    />

                                    <!-- Curved Line -->
                                    <path 
                                        d="M 20 45 C 45 45, 55 49, 75 49 C 95 49, 110 54, 130 54 C 150 54, 170 38, 190 38 C 210 38, 230 42, 250 42 C 270 42, 285 18, 305 18" 
                                        fill="none" 
                                        stroke="#2563EB" 
                                        stroke-width="2.5" 
                                    />

                                    <!-- Data Points / Circles -->
                                    <circle cx="20" cy="45" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                    <circle cx="75" cy="49" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                    <circle cx="130" cy="54" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                    <circle cx="190" cy="38" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                    <circle cx="250" cy="42" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                    <circle cx="305" cy="18" r="3.5" fill="#2563EB" stroke="#FFFFFF" stroke-width="1.5" />
                                </svg>
                            </div>
                        </div>

                        <!-- X-Axis Labels -->
                        <div class="grid grid-cols-6 text-center text-[9px] text-text-muted mt-2 pl-7">
                            <span class="truncate">Mata Kuliah 1</span>
                            <span class="truncate">Mata Kuliah 2</span>
                            <span class="truncate">Mata Kuliah 3</span>
                            <span class="truncate">Mata Kuliah 4</span>
                            <span class="truncate">Mata Kuliah 5</span>
                            <span class="truncate">Mata Kuliah 6</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 3. KARTU PENGUMUMAN NILAI -->
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between gap-2 mb-3.5">
                        <div class="flex items-center gap-2 font-bold text-sm text-text">
                            <x-icon name="megaphone" :size="16" class="text-text" />
                            <span>Pengumuman Nilai</span>
                        </div>
                        <a href="#pengumuman" class="inline-flex items-center gap-1 text-xs font-semibold text-[#2563EB] hover:underline">
                            <span>Lihat Semua</span>
                            <x-icon name="arrow-right" :size="12" />
                        </a>
                    </div>

                    <div class="space-y-4">
                        <!-- Pengumuman 1 -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[8px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="file-text" :size="16" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Nilai Ujian Tengah Semester (UTS) Sudah Tersedia
                                </h4>
                                <p class="text-[10px] text-text-subtle mt-0.5">
                                    28 Sep 2026, 09:00
                                </p>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-snug">
                                    Silakan cek nilai UTS pada mata kuliah masing-masing.
                                </p>
                            </div>
                        </div>

                        <!-- Pengumuman 2 -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[8px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="calendar" :size="16" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Jadwal Pengumuman Nilai Akhir
                                </h4>
                                <p class="text-[10px] text-text-subtle mt-0.5">
                                    21 Nov 2026, 10:00
                                </p>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-snug">
                                    Nilai akhir akan diumumkan setelah seluruh tugas dan ujian selesai.
                                </p>
                            </div>
                        </div>

                        <!-- Pengumuman 3 -->
                        <div class="flex items-start gap-3">
                            <div class="w-8 h-8 rounded-[8px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="file-text" :size="16" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    Perubahan Bobot Penilaian
                                </h4>
                                <p class="text-[10px] text-text-subtle mt-0.5">
                                    15 Sep 2026, 14:30
                                </p>
                                <p class="text-[11px] text-text-muted mt-0.5 leading-snug">
                                    Terdapat penyesuaian bobot pada mata kuliah tertentu.
                                </p>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

            </div>
        </div>

        <!-- ==========================================
             MODAL DETAIL NILAI (INTERAKTIF & EDUKATIF OBE)
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
                        <span class="text-[10px] uppercase tracking-wider font-bold text-accent" x-text="selectedCourse?.code"></span>
                        <h3 class="text-base font-bold text-text mt-0.5" x-text="selectedCourse?.name"></h3>
                    </div>
                    <button type="button" @click="detailModalOpen = false" class="text-text-muted hover:text-text p-1">
                        <x-icon name="x" :size="18" />
                    </button>
                </div>

                <div class="p-5 space-y-4 text-xs">
                    <!-- Ringkasan Nilai Akhir -->
                    <div class="grid grid-cols-3 gap-3 p-3.5 rounded-[8px] bg-slate-50 border border-line text-center">
                        <div>
                            <span class="text-text-muted block text-[11px]">SKS</span>
                            <p class="font-bold text-text text-sm mt-0.5" x-text="selectedCourse?.sks"></p>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Nilai Akhir</span>
                            <p class="font-bold text-emerald-700 text-sm mt-0.5" x-text="selectedCourse?.score"></p>
                        </div>
                        <div>
                            <span class="text-text-muted block text-[11px]">Nilai Huruf</span>
                            <p class="font-bold text-emerald-700 text-sm mt-0.5" x-text="selectedCourse?.grade"></p>
                        </div>
                    </div>

                    <!-- Rincian Bobot Komponen Penilaian -->
                    <div>
                        <h4 class="font-bold text-text mb-2 text-xs">Rincian Komponen Penilaian</h4>
                        <div class="space-y-2 border border-line rounded-[8px] p-3 bg-surface">
                            <div class="flex items-center justify-between py-1 border-b border-line/60">
                                <span class="text-text-muted">Tugas & Praktikum (Bobot 30%)</span>
                                <span class="font-semibold text-text" x-text="selectedCourse?.assignmentsScore"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-line/60">
                                <span class="text-text-muted">Kuis & Diskusi (Bobot 20%)</span>
                                <span class="font-semibold text-text" x-text="selectedCourse?.quizzesScore"></span>
                            </div>
                            <div class="flex items-center justify-between py-1 border-b border-line/60">
                                <span class="text-text-muted">Ujian Tengah Semester (Bobot 25%)</span>
                                <span class="font-semibold text-text" x-text="selectedCourse?.midtermScore"></span>
                            </div>
                            <div class="flex items-center justify-between py-1">
                                <span class="text-text-muted">Ujian Akhir Semester (Bobot 25%)</span>
                                <span class="font-semibold text-text" x-text="selectedCourse?.finalScore"></span>
                            </div>
                        </div>
                    </div>

                    <!-- Dosen Pengampu -->
                    <div>
                        <span class="text-text-muted font-medium block text-[11px]">Dosen Pengampu Utama:</span>
                        <p class="font-semibold text-text mt-0.5" x-text="selectedCourse?.lecturer"></p>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-between">
                        <span class="text-[11px] text-text-muted">Transkrip sementara resmi Arunika</span>
                        <div class="inline-flex items-center gap-2">
                            <template x-if="selectedCourse?.detailUrl">
                                <a :href="selectedCourse.detailUrl" class="px-3 py-2 rounded-[6px] border border-line bg-surface hover:bg-canvas text-text font-semibold text-xs transition-colors shadow-2xs">
                                    Rincian Transkrip
                                </a>
                            </template>
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

    </div>

@endsection
