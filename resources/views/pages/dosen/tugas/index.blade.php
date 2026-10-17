@extends('layouts.dosen')

@section('title', 'Tugas & Ujian — Arunika Learning System')
@section('page_breadcrumb', 'Tugas & Ujian')

@section('content')
<div class="space-y-6">

    <!-- Header Judul & Tombol Buat -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <x-icon name="clipboard-list" :size="22" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight">
                    Tugas &amp; Ujian
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Kelola assessment, tugas, kuis, dan ujian untuk semua kelas yang Anda ampu.
                </p>
            </div>
        </div>

        <div class="shrink-0">
            <a 
                href="{{ route('dosen.tugas.create') }}" 
                class="inline-flex items-center gap-2 px-4 py-2 rounded-[6px] bg-[#1A2E5E] hover:bg-navy-800 text-white text-xs font-semibold shadow-xs transition-colors"
            >
                <x-icon name="plus" :size="15" />
                <span>Buat Tugas &amp; Ujian</span>
            </a>
        </div>
    </div>

    <!-- 2 Quick Action Banners -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Card 1: Buat Tugas -->
        <a 
            href="{{ route('dosen.tugas.create', ['jenis' => 'tugas']) }}" 
            class="bg-surface border border-line rounded-[10px] p-4 sm:p-5 flex items-center justify-between gap-4 shadow-2xs hover:border-[#2563EB] hover:shadow-xs transition-all group"
        >
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <x-icon name="file-text" :size="22" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-text group-hover:text-[#2563EB] transition-colors">
                        Buat Tugas
                    </h3>
                    <p class="text-[11px] text-text-muted mt-0.5 truncate">
                        Individu atau kelompok, deadline, lampiran, rubrik.
                    </p>
                </div>
            </div>
            <x-icon name="chevron-right" :size="16" class="text-text-subtle group-hover:text-[#2563EB] group-hover:translate-x-0.5 transition-all shrink-0" />
        </a>

        <!-- Card 2: Buat Kuis / Ujian -->
        <a 
            href="{{ route('dosen.tugas.create', ['jenis' => 'kuis']) }}" 
            class="bg-surface border border-line rounded-[10px] p-4 sm:p-5 flex items-center justify-between gap-4 shadow-2xs hover:border-[#2563EB] hover:shadow-xs transition-all group"
        >
            <div class="flex items-center gap-3.5 min-w-0">
                <div class="w-11 h-11 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 group-hover:scale-105 transition-transform">
                    <x-icon name="clipboard-check" :size="22" />
                </div>
                <div class="min-w-0">
                    <h3 class="text-xs sm:text-sm font-bold text-text group-hover:text-[#2563EB] transition-colors">
                        Buat Kuis / Ujian
                    </h3>
                    <p class="text-[11px] text-text-muted mt-0.5 truncate">
                        Bank soal, jadwal, durasi, dan pengaturan ujian.
                    </p>
                </div>
            </div>
            <x-icon name="chevron-right" :size="16" class="text-text-subtle group-hover:text-[#2563EB] group-hover:translate-x-0.5 transition-all shrink-0" />
        </a>
    </div>

    <!-- Filter Tab & Dropdown Bar -->
    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-3">
        <!-- Filter Tabs: Semua, Tugas, Kuis, UTS, UAS -->
        <div class="inline-flex items-center gap-1.5 p-1 rounded-[8px] bg-surface border border-line text-xs font-medium overflow-x-auto max-w-full">
            @foreach (['semua' => 'Semua', 'tugas' => 'Tugas', 'kuis' => 'Kuis', 'uts' => 'UTS', 'uas' => 'UAS'] as $key => $label)
                <a 
                    href="{{ route('dosen.tugas.index', array_merge(request()->query(), ['jenis' => $key, 'page' => 1])) }}" 
                    class="px-3.5 py-1.5 rounded-[6px] transition-colors whitespace-nowrap {{ ($filters['jenis'] ?? 'semua') === $key ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-50' }}"
                >
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <!-- Right Dropdowns: Semester & Mata Kuliah -->
        <div class="flex items-center gap-2.5 flex-wrap">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-[8px] bg-surface border border-line text-xs font-medium text-text">
                <x-icon name="calendar" :size="13" class="text-text-muted" />
                <span>Semester 3 ({{ $term->name ?? 'Ganjil 2026/2027' }})</span>
                <x-icon name="chevron-down" :size="12" class="text-text-subtle" />
            </div>

            <!-- Filter Mata Kuliah Dropdown -->
            <form method="GET" action="{{ route('dosen.tugas.index') }}" class="inline-block">
                @if ($filters['jenis'] !== 'semua')
                    <input type="hidden" name="jenis" value="{{ $filters['jenis'] }}">
                @endif
                <div class="relative inline-flex items-center">
                    <x-icon name="graduation-cap" :size="13" class="absolute left-3 text-text-muted pointer-events-none" />
                    <select 
                        name="course_id" 
                        onchange="this.form.submit()" 
                        class="pl-8 pr-7 py-1.5 text-xs font-medium rounded-[8px] bg-surface border border-line text-text focus:outline-none focus:border-[#2563EB] cursor-pointer appearance-none"
                    >
                        <option value="semua">Semua Mata Kuliah</option>
                        @foreach ($myCourses as $course)
                            <option value="{{ $course->id }}" {{ ($filters['course_id'] ?? '') == $course->id ? 'selected' : '' }}>
                                {{ $course->code }} - {{ $course->name }}
                            </option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" :size="12" class="absolute right-2.5 text-text-subtle pointer-events-none" />
                </div>
            </form>
        </div>
    </div>

    <!-- Main Grid: Left Table, Right Summary & Donut -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Tabel Assessment (8 Kolom) -->
        <div class="lg:col-span-8 space-y-4">
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-line/60">
                        <thead class="bg-slate-50 text-[11px] font-semibold text-text-muted uppercase">
                            <tr>
                                <th class="px-4 py-3 w-10 text-center">No</th>
                                <th class="px-3 py-3">Judul Assessment</th>
                                <th class="px-3 py-3">Mata Kuliah</th>
                                <th class="px-2 py-3 text-center">Kelas</th>
                                <th class="px-2 py-3 text-center">Jenis</th>
                                <th class="px-3 py-3">Jadwal</th>
                                <th class="px-3 py-3 text-center">Status</th>
                                <th class="px-3 py-3 text-right">Aksi</th>
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
                                    <td class="px-3 py-3.5 font-semibold text-text max-w-[200px]">
                                        <p class="truncate" title="{{ $item->title }}">{{ $item->title }}</p>
                                    </td>
                                    <td class="px-3 py-3.5 max-w-[180px]">
                                        <p class="font-bold text-text truncate">{{ $item->academicClass?->course?->code ?? '' }}</p>
                                        <p class="text-[11px] text-text-muted truncate mt-0.5">{{ $item->academicClass?->course?->name ?? '' }}</p>
                                    </td>
                                    <td class="px-2 py-3.5 text-center font-bold text-text">
                                        {{ $letter }}
                                    </td>
                                    <td class="px-2 py-3.5 text-center">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $jenisColor }}">
                                            {{ $item->jenis }}
                                        </span>
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
                                        <x-dosen.status-badge :status="$item->status" size="xs" />
                                    </td>
                                    <td class="px-3 py-3.5 text-right whitespace-nowrap">
                                        <!-- Dropdown Menu Aksi -->
                                        <div class="relative inline-block text-left" x-data="{ open: false }">
                                            <button 
                                                @click="open = !open" 
                                                type="button" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[#2563EB] hover:bg-blue-50 rounded-[4px] border border-blue-200 transition-colors cursor-pointer"
                                            >
                                                <span>Kelola</span>
                                                <x-icon name="chevron-down" :size="11" />
                                            </button>

                                            <div 
                                                x-show="open" 
                                                @click.away="open = false" 
                                                x-cloak 
                                                class="absolute right-0 mt-1 w-44 bg-surface rounded-[8px] border border-line shadow-lg py-1.5 z-30 text-xs text-text text-left"
                                            >
                                                <a href="{{ route('dosen.tugas.edit', $item->id) }}" class="flex items-center gap-2 px-3 py-1.5 hover:bg-slate-50 transition-colors">
                                                    <x-icon name="edit" :size="13" class="text-text-muted" />
                                                    <span>Ubah / Edit</span>
                                                </a>

                                                @if ($item->status !== 'dipublikasikan')
                                                    <form method="POST" action="{{ route('dosen.tugas.publish', $item->id) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-emerald-700 hover:bg-emerald-50 transition-colors text-left cursor-pointer">
                                                            <x-icon name="check-circle" :size="13" class="text-emerald-600" />
                                                            <span>Publikasikan</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($item->status === 'dipublikasikan')
                                                    <form method="POST" action="{{ route('dosen.tugas.archive', $item->id) }}">
                                                        @csrf
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-slate-700 hover:bg-slate-50 transition-colors text-left cursor-pointer">
                                                            <x-icon name="archive" :size="13" class="text-text-muted" />
                                                            <span>Arsipkan</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                @if ($item->submissions->count() === 0 && $item->status === 'draft')
                                                    <hr class="my-1 border-line">
                                                    <form method="POST" action="{{ route('dosen.tugas.destroy', $item->id) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus assessment draft ini?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="w-full flex items-center gap-2 px-3 py-1.5 text-red-600 hover:bg-red-50 transition-colors text-left cursor-pointer">
                                                            <x-icon name="trash-2" :size="13" />
                                                            <span>Hapus</span>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-5 py-8 text-center text-xs text-text-muted">
                                        Tidak ada assessment yang sesuai dengan filter.
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
                            {{-- Previous Page Link --}}
                            @if ($assessments->onFirstPage())
                                <span class="w-7 h-7 rounded-[4px] border border-line bg-slate-100 text-text-subtle flex items-center justify-center cursor-not-allowed">
                                    <x-icon name="chevron-left" :size="13" />
                                </span>
                            @else
                                <a href="{{ $assessments->previousPageUrl() }}" class="w-7 h-7 rounded-[4px] border border-line bg-surface hover:bg-slate-100 text-text flex items-center justify-center transition-colors">
                                    <x-icon name="chevron-left" :size="13" />
                                </a>
                            @endif

                            {{-- Numeric Page Links --}}
                            @foreach ($assessments->getUrlRange(1, $assessments->lastPage()) as $page => $url)
                                <a 
                                    href="{{ $url }}" 
                                    class="w-7 h-7 rounded-[4px] border text-xs font-semibold flex items-center justify-center transition-colors {{ $page == $assessments->currentPage() ? 'bg-[#2563EB] text-white border-[#2563EB]' : 'bg-surface border-line text-text hover:bg-slate-100' }}"
                                >
                                    {{ $page }}
                                </a>
                            @endforeach

                            {{-- Next Page Link --}}
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
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Ringkasan Assessment -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Ringkasan Assessment</h2>
                    </div>

                    <!-- Mini Search Form -->
                    <form method="GET" action="{{ route('dosen.tugas.index') }}" class="relative w-36">
                        @if ($filters['jenis'] !== 'semua')
                            <input type="hidden" name="jenis" value="{{ $filters['jenis'] }}">
                        @endif
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ $filters['search'] }}" 
                            placeholder="Cari judul..." 
                            class="w-full pl-6 pr-2 py-1 text-[11px] rounded-[6px] border border-line bg-canvas text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                        >
                        <x-icon name="search" :size="11" class="absolute left-2 top-1/2 -translate-y-1/2 text-text-subtle pointer-events-none" />
                    </form>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Total -->
                    <div class="p-3 rounded-[8px] bg-canvas/60 border border-line">
                        <span class="text-[11px] font-medium text-text-muted block">Total</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-icon name="clipboard" :size="14" class="text-text-muted shrink-0" />
                            <span class="text-xl font-bold text-text tabular-nums leading-none">{{ $summary['total'] }}</span>
                        </div>
                        <span class="text-[10px] text-text-subtle block mt-1">assessment</span>
                    </div>

                    <!-- Tugas -->
                    <div class="p-3 rounded-[8px] bg-canvas/60 border border-line">
                        <span class="text-[11px] font-medium text-text-muted block">Tugas</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-icon name="file-text" :size="14" class="text-[#2563EB] shrink-0" />
                            <span class="text-xl font-bold text-text tabular-nums leading-none">{{ $summary['tugas'] }}</span>
                        </div>
                        <span class="text-[10px] text-text-subtle block mt-1">assessment</span>
                    </div>

                    <!-- Kuis / Ujian -->
                    <div class="p-3 rounded-[8px] bg-canvas/60 border border-line">
                        <span class="text-[11px] font-medium text-text-muted block">Kuis / Ujian</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-icon name="award" :size="14" class="text-purple-600 shrink-0" />
                            <span class="text-xl font-bold text-text tabular-nums leading-none">{{ $summary['kuis_ujian'] }}</span>
                        </div>
                        <span class="text-[10px] text-text-subtle block mt-1">assessment</span>
                    </div>

                    <!-- Menunggu Publikasi -->
                    <div class="p-3 rounded-[8px] bg-canvas/60 border border-line">
                        <span class="text-[11px] font-medium text-text-muted block">Menunggu Publikasi</span>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            <x-icon name="clock" :size="14" class="text-amber-600 shrink-0" />
                            <span class="text-xl font-bold text-text tabular-nums leading-none">{{ $summary['menunggu_publikasi'] }}</span>
                        </div>
                        <span class="text-[10px] text-text-subtle block mt-1">assessment</span>
                    </div>
                </div>
            </div>

            <!-- 2. Status Publikasi (Donut Chart) -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                <div class="flex items-center gap-2">
                    <x-icon name="bar-chart-2" :size="16" class="text-navy-800" />
                    <h2 class="text-sm font-bold text-text">Status Publikasi</h2>
                </div>

                <x-dosen.donut-chart 
                    :total="$pubStats['total']" 
                    :published="$pubStats['published']" 
                    :draft="$pubStats['draft']" 
                    :scheduled="$pubStats['scheduled']" 
                    :archived="$pubStats['archived']" 
                />
            </div>

            <!-- 3. Tugas & Ujian Terdekat -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Tugas &amp; Ujian Terdekat</h2>
                    </div>
                    <a href="{{ route('dosen.tugas.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <x-icon name="arrow-right" :size="12" />
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($upcoming as $item)
                        @php
                            $iconColor = match ($item->jenis) {
                                'tugas' => 'bg-blue-50 text-[#2563EB]',
                                'kuis' => 'bg-purple-50 text-purple-600',
                                'uts' => 'bg-rose-50 text-rose-600',
                                default => 'bg-amber-50 text-amber-600',
                            };
                            $badgeLabel = match ($item->status) {
                                'dipublikasikan' => 'Aktif',
                                'terjadwal' => 'Terjadwal',
                                'draft' => 'Draft',
                                default => ucfirst($item->status),
                            };
                            $badgeStyle = match ($item->status) {
                                'dipublikasikan' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'terjadwal' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'draft' => 'bg-amber-50 text-amber-700 border-amber-200',
                                default => 'bg-slate-100 text-slate-700 border-slate-200',
                            };
                        @endphp
                        <div class="p-3.5 px-5 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-start gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-[6px] {{ $iconColor }} flex items-center justify-center shrink-0 mt-0.5">
                                    @if ($item->jenis === 'uts' || $item->jenis === 'uas')
                                        <span class="text-[9px] font-bold uppercase">{{ $item->jenis }}</span>
                                    @else
                                        <x-icon name="clipboard" :size="14" />
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-text truncate" title="{{ $item->title }}">
                                        {{ $item->title }}
                                    </h4>
                                    <p class="text-[11px] text-text-muted truncate mt-0.5">
                                        {{ $item->due_at ? $item->due_at->locale('id')->isoFormat('D MMM Y, HH:mm') : '-' }} &bull; {{ $item->academicClass?->section ?? 'Kelas' }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full border {{ $badgeStyle }}">
                                    {{ $badgeLabel }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Tidak ada assessment mendatang.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
