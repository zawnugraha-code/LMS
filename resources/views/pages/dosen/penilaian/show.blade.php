@extends('layouts.dosen')

@section('title', 'Penilaian: ' . $assignment->title . ' — Arunika Learning System')
@section('page_breadcrumb', 'Penilaian Detail')

@section('content')
<div class="space-y-6">

    <!-- Tombol Kembali & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <a 
            href="{{ route('dosen.penilaian.index') }}" 
            class="inline-flex items-center gap-1.5 text-xs font-semibold text-text-muted hover:text-text transition-colors group"
        >
            <x-icon name="chevron-left" :size="15" class="transition-transform group-hover:-translate-x-0.5" />
            <span>Kembali ke Daftar Penilaian</span>
        </a>

        <div class="inline-flex items-center gap-2 text-xs text-text-muted">
            <a href="{{ route('dosen.dashboard') }}" class="hover:text-text transition-colors">Beranda</a>
            <span class="text-text-subtle">/</span>
            <a href="{{ route('dosen.penilaian.index') }}" class="hover:text-text transition-colors">Penilaian</a>
            <span class="text-text-subtle">/</span>
            <span class="text-text font-semibold truncate max-w-[220px]">{{ $assignment->title }}</span>
        </div>
    </div>

    <!-- Alert Notifikasi / Flash Messages -->
    @if (session('success'))
        <div class="flex items-center justify-between p-4 rounded-[10px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs shadow-2xs">
            <div class="flex items-center gap-2.5">
                <x-icon name="check-circle" :size="18" class="text-emerald-600 shrink-0" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif

    @if ($errors->any())
        <div class="p-4 rounded-[10px] bg-rose-50 border border-rose-200 text-rose-800 text-xs shadow-2xs space-y-1">
            <div class="flex items-center gap-2 font-semibold">
                <x-icon name="alert-triangle" :size="16" class="text-rose-600 shrink-0" />
                <span>Terjadi kesalahan pada input penilaian:</span>
            </div>
            <ul class="list-disc list-inside pl-4 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Banner Assessment Info -->
    <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
        <div class="flex flex-col lg:flex-row lg:items-start justify-between gap-4">
            <div class="space-y-1.5 min-w-0">
                <div class="flex items-center gap-2.5 flex-wrap">
                    @php
                        $jenisBadgeColor = match ($assignment->jenis) {
                            'tugas' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'kuis' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'uts' => 'bg-rose-50 text-rose-700 border-rose-200',
                            'uas' => 'bg-orange-50 text-orange-700 border-orange-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase border {{ $jenisBadgeColor }}">
                        {{ $assignment->jenis }}
                    </span>
                    <x-dosen.status-badge :status="$assignment->status" size="xs" />
                    <span class="text-xs text-text-muted">
                        Mode: <span class="font-semibold text-text capitalize">{{ $assignment->mode }}</span>
                    </span>
                    <span class="text-xs text-text-muted">&bull;</span>
                    <span class="text-xs text-text-muted">
                        Metode: <span class="font-semibold text-text capitalize">{{ $assignment->metode }}</span>
                    </span>
                </div>

                <h1 class="text-lg sm:text-xl font-bold text-text tracking-tight">
                    {{ $assignment->title }}
                </h1>

                <p class="text-xs text-text-muted">
                    {{ $assignment->academicClass?->course?->code }} - {{ $assignment->academicClass?->course?->name }} &bull; Kelas {{ $assignment->academicClass?->section ?? 'A' }}
                </p>
            </div>

            <!-- Quick Action: Quick Zeros -->
            <div class="flex items-center gap-2.5 shrink-0">
                <form 
                    action="{{ route('dosen.penilaian.quick_zeros', $assignment->id) }}" 
                    method="POST" 
                    onsubmit="return confirm('Apakah Anda yakin ingin memberikan nilai 0 kepada semua mahasiswa yang belum mengumpulkan tugas?');"
                >
                    @csrf
                    <button 
                        type="submit" 
                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-rose-700 bg-rose-50/70 hover:bg-rose-100/80 border border-rose-200 rounded-[8px] transition-colors shadow-2xs cursor-pointer"
                        title="Beri nilai 0 otomatis kepada mahasiswa tanpa submission"
                    >
                        <x-icon name="check-circle" :size="14" class="text-rose-600" />
                        <span>Isi Nilai 0 Tanpa Submission</span>
                    </button>
                </form>

                <a 
                    href="{{ route('dosen.tugas.edit', $assignment->id) }}" 
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-text-muted hover:text-text bg-surface hover:bg-slate-50 border border-line rounded-[8px] transition-colors shadow-2xs"
                >
                    <x-icon name="pencil" :size="13" />
                    <span>Edit Tugas</span>
                </a>
            </div>
        </div>

        <!-- Meta Grid & Info -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-line/60 text-xs">
            <div>
                <span class="text-text-muted text-[11px] block">Tenggat Waktu</span>
                <span class="font-semibold text-text mt-0.5 block tabular-nums">
                    {{ $assignment->due_at ? $assignment->due_at->locale('id')->isoFormat('D MMM Y, HH:mm') . ' WIB' : '-' }}
                </span>
            </div>

            <div>
                <span class="text-text-muted text-[11px] block">Nilai Maksimum</span>
                <span class="font-semibold text-text mt-0.5 block tabular-nums">
                    {{ number_format($assignment->max_score, 0) }} Poin
                </span>
            </div>

            <div>
                <span class="text-text-muted text-[11px] block">Kebijakan Terlambat</span>
                <span class="font-semibold text-text mt-0.5 block capitalize">
                    {{ str_replace('_', ' ', $assignment->late_policy ?? 'Tolak Terlambat') }}
                </span>
            </div>

            <div>
                <span class="text-text-muted text-[11px] block">Pengiriman Ulang</span>
                <span class="font-semibold text-text mt-0.5 block">
                    {{ $assignment->allow_resubmit ? 'Diizinkan' : 'Hanya Sekali' }}
                </span>
            </div>
        </div>

        @if ($assignment->description)
            <div class="pt-2 text-xs text-text-muted bg-slate-50/70 p-3 rounded-[6px] border border-line/50">
                <span class="font-semibold text-text block mb-1">Instruksi Tugas:</span>
                <p class="whitespace-pre-line leading-relaxed">{{ $assignment->description }}</p>
            </div>
        @endif
    </div>

    <!-- 4 Mini Stat Cards & Progress Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Mahasiswa -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-10 h-10 rounded-[8px] bg-slate-100 text-slate-700 flex items-center justify-center shrink-0">
                <x-icon name="users" :size="18" />
            </div>
            <div>
                <span class="text-xs font-medium text-text-muted block">Total Mahasiswa</span>
                <span class="text-xl font-bold text-text tabular-nums block mt-0.5">
                    {{ $total_students }}
                </span>
            </div>
        </div>

        <!-- 2. Sudah Mengumpulkan -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <x-icon name="upload-cloud" :size="18" />
            </div>
            <div>
                <span class="text-xs font-medium text-text-muted block">Mengumpulkan</span>
                <span class="text-xl font-bold text-text tabular-nums block mt-0.5">
                    {{ $submitted_count }} <span class="text-xs font-normal text-text-muted">/ {{ $total_students }}</span>
                </span>
            </div>
        </div>

        <!-- 3. Sudah Dinilai -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-3.5 shadow-2xs">
            <div class="w-10 h-10 rounded-[8px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <x-icon name="check-circle" :size="18" />
            </div>
            <div>
                <span class="text-xs font-medium text-text-muted block">Sudah Dinilai</span>
                <span class="text-xl font-bold text-text tabular-nums block mt-0.5">
                    {{ $graded_count }} <span class="text-xs font-normal text-text-muted">/ {{ $submitted_count }}</span>
                </span>
            </div>
        </div>

        <!-- 4. Progress Penilaian -->
        <div class="bg-surface border border-line rounded-[10px] p-4 shadow-2xs space-y-1.5 flex flex-col justify-center">
            <div class="flex items-center justify-between text-xs">
                <span class="font-medium text-text-muted">Progress Penilaian</span>
                <span class="font-bold text-text tabular-nums">{{ $progress_percent }}%</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                <div class="bg-[#10B981] h-2 rounded-full transition-all duration-300" style="width: {{ $progress_percent }}%"></div>
            </div>
            <span class="text-[10px] text-text-subtle">
                {{ $graded_count }} dari {{ $total_students }} mahasiswa selesai
            </span>
        </div>
    </div>

    <!-- Filter Tab & Table Card -->
    <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs space-y-0">
        
        <!-- Filter Bar -->
        <div class="p-4 border-b border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <!-- Filter Tabs -->
            <div class="inline-flex items-center gap-1 p-1 rounded-[8px] bg-canvas border border-line text-xs font-medium overflow-x-auto max-w-full">
                <button 
                    type="button" 
                    onclick="filterGradingTable('all', this)"
                    class="filter-btn px-3 py-1.5 rounded-[6px] transition-colors bg-[#2563EB] text-white shadow-2xs font-semibold cursor-pointer whitespace-nowrap"
                >
                    Semua ({{ $total_students }})
                </button>
                <button 
                    type="button" 
                    onclick="filterGradingTable('submitted', this)"
                    class="filter-btn px-3 py-1.5 rounded-[6px] transition-colors text-text-muted hover:text-text hover:bg-slate-100 cursor-pointer whitespace-nowrap"
                >
                    Mengumpulkan ({{ $submitted_count }})
                </button>
                <button 
                    type="button" 
                    onclick="filterGradingTable('unsubmitted', this)"
                    class="filter-btn px-3 py-1.5 rounded-[6px] transition-colors text-text-muted hover:text-text hover:bg-slate-100 cursor-pointer whitespace-nowrap"
                >
                    Belum Kumpul ({{ $total_students - $submitted_count }})
                </button>
                <button 
                    type="button" 
                    onclick="filterGradingTable('needs_grade', this)"
                    class="filter-btn px-3 py-1.5 rounded-[6px] transition-colors text-text-muted hover:text-text hover:bg-slate-100 cursor-pointer whitespace-nowrap"
                >
                    Perlu Dinilai ({{ max(0, $submitted_count - $graded_count) }})
                </button>
                <button 
                    type="button" 
                    onclick="filterGradingTable('graded', this)"
                    class="filter-btn px-3 py-1.5 rounded-[6px] transition-colors text-text-muted hover:text-text hover:bg-slate-100 cursor-pointer whitespace-nowrap"
                >
                    Sudah Dinilai ({{ $graded_count }})
                </button>
            </div>

            <!-- Search input -->
            <div class="relative w-full sm:w-64">
                <input 
                    type="text" 
                    id="gradingSearchInput" 
                    onkeyup="searchGradingTable()" 
                    placeholder="Cari nama atau NIM..." 
                    class="w-full pl-8 pr-3 py-1.5 text-xs rounded-[8px] border border-line bg-surface text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                >
                <x-icon name="search" :size="13" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-subtle pointer-events-none" />
            </div>
        </div>

        <!-- Table of Students & Grading -->
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs divide-y divide-line/60" id="gradingTable">
                <thead class="bg-slate-50 text-[11px] font-semibold text-text-muted uppercase">
                    <tr>
                        <th class="px-4 py-3 w-10 text-center">No</th>
                        <th class="px-4 py-3">Mahasiswa</th>
                        <th class="px-3 py-3 text-center">Status &amp; Waktu</th>
                        <th class="px-3 py-3">Berkas &amp; Jawaban</th>
                        <th class="px-3 py-3 w-28 text-center">Nilai (0-100)</th>
                        <th class="px-3 py-3">Feedback / Catatan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line/40">
                    @forelse ($rows as $row)
                        @php
                            $student = $row['student'];
                            $submission = $row['submission'];
                            $hasSubmitted = $row['has_submitted'];
                            $isGraded = $row['is_graded'];
                            $needsGrade = $hasSubmitted && ! $isGraded;

                            // Form action
                            $formAction = $submission 
                                ? route('dosen.penilaian.grade', $submission->id) 
                                : route('dosen.penilaian.grade_student', [$assignment->id, $student->id]);

                            $initial = strtoupper(substr($student->name, 0, 1));
                        @endphp
                        <tr 
                            class="grading-row hover:bg-slate-50/60 transition-colors"
                            data-has-submitted="{{ $hasSubmitted ? '1' : '0' }}"
                            data-is-graded="{{ $isGraded ? '1' : '0' }}"
                            data-needs-grade="{{ $needsGrade ? '1' : '0' }}"
                            data-search="{{ strtolower($student->name . ' ' . ($student->identifier ?? '')) }}"
                        >
                            <td class="px-4 py-3.5 text-center text-text-muted tabular-nums">
                                {{ $loop->iteration }}
                            </td>

                            <!-- Info Mahasiswa -->
                            <td class="px-4 py-3.5 min-w-[200px]">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-blue-50 text-[#2563EB] font-bold text-xs flex items-center justify-center shrink-0 border border-blue-100">
                                        {{ $initial }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-text truncate">
                                            {{ $student->name }}
                                        </p>
                                        <p class="text-[11px] text-text-muted tabular-nums">
                                            {{ $student->identifier ?? 'NIM -' }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Status & Waktu Pengumpulan -->
                            <td class="px-3 py-3.5 text-center whitespace-nowrap">
                                @if ($hasSubmitted)
                                    <x-dosen.status-badge :status="$row['status_waktu']" size="xs" />
                                    <span class="text-[10px] text-text-subtle block mt-1 tabular-nums">
                                        {{ $row['submitted_at'] ? $row['submitted_at']->locale('id')->isoFormat('D MMM, HH:mm') : '-' }}
                                    </span>
                                @else
                                    <x-dosen.status-badge status="belum_kumpul" size="xs" />
                                    <span class="text-[10px] text-text-subtle block mt-1">
                                        Belum ada berkas
                                    </span>
                                @endif
                            </td>

                            <!-- Berkas / Jawaban -->
                            <td class="px-3 py-3.5 min-w-[180px] max-w-[240px]">
                                @if ($row['file_path'])
                                    <a 
                                        href="{{ asset('storage/' . $row['file_path']) }}" 
                                        target="_blank" 
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium text-[#2563EB] bg-blue-50/60 hover:bg-blue-100/70 rounded-[6px] border border-blue-200 transition-colors truncate max-w-full"
                                        title="Unduh / Buka Berkas: {{ basename($row['file_path']) }}"
                                    >
                                        <x-icon name="paperclip" :size="12" class="shrink-0" />
                                        <span class="truncate">{{ basename($row['file_path']) }}</span>
                                    </a>
                                @elseif ($submission && ! empty($submission->content))
                                    <span class="text-xs text-text truncate block italic text-text-muted" title="{{ $submission->content }}">
                                        "{{ Str::limit($submission->content, 40) }}"
                                    </span>
                                @else
                                    <span class="text-xs text-text-subtle">-</span>
                                @endif
                            </td>

                            <!-- Form Grading Input (Nilai, Feedback, Submit) -->
                            <form action="{{ $formAction }}" method="POST" class="contents">
                                @csrf
                                <td class="px-3 py-3.5 text-center">
                                    <div class="relative inline-block w-24">
                                        <input 
                                            type="number" 
                                            name="score" 
                                            min="0" 
                                            max="100" 
                                            step="0.5" 
                                            value="{{ old('score', $row['score'] !== null ? (float) $row['score'] : '') }}" 
                                            placeholder="0 - 100"
                                            required
                                            class="w-full px-2.5 py-1 text-xs font-semibold text-center rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB] tabular-nums"
                                        >
                                    </div>
                                    @if ($isGraded)
                                        <span class="text-[10px] text-emerald-600 block mt-0.5 font-medium">
                                            Tersimpan
                                        </span>
                                    @endif
                                </td>

                                <td class="px-3 py-3.5 min-w-[200px]">
                                    <input 
                                        type="text" 
                                        name="feedback" 
                                        value="{{ old('feedback', $row['feedback'] ?? '') }}" 
                                        placeholder="Tulis umpan balik atau catatan..." 
                                        class="w-full px-2.5 py-1 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB] placeholder:text-text-muted"
                                    >
                                </td>

                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <button 
                                        type="submit" 
                                        class="inline-flex items-center gap-1 px-3 py-1 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[6px] shadow-2xs transition-colors cursor-pointer"
                                    >
                                        <x-icon name="check" :size="12" />
                                        <span>Simpan</span>
                                    </button>
                                </td>
                            </form>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-5 py-8 text-center text-xs text-text-muted">
                                Tidak ada mahasiswa yang terdaftar di kelas ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Footer Ringkasan -->
        <div class="px-5 py-3 bg-slate-50/60 border-t border-line/60 flex items-center justify-between text-xs text-text-muted">
            <span>
                Total Mahasiswa: <strong class="text-text">{{ $total_students }}</strong>
            </span>
            <span class="inline-flex items-center gap-3">
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Sudah Dinilai: <strong class="text-text">{{ $graded_count }}</strong></span>
                </span>
                <span class="inline-flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Perlu Dinilai: <strong class="text-text">{{ max(0, $submitted_count - $graded_count) }}</strong></span>
                </span>
            </span>
        </div>

    </div>

</div>

<!-- Filter and Search Script -->
<script>
    let activeFilter = 'all';

    function filterGradingTable(filter, element) {
        activeFilter = filter;

        // Update button styles
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-[#2563EB]', 'text-white', 'shadow-2xs', 'font-semibold');
            btn.classList.add('text-text-muted', 'hover:text-text', 'hover:bg-slate-100');
        });
        element.classList.add('bg-[#2563EB]', 'text-white', 'shadow-2xs', 'font-semibold');
        element.classList.remove('text-text-muted', 'hover:text-text', 'hover:bg-slate-100');

        applyFilters();
    }

    function searchGradingTable() {
        applyFilters();
    }

    function applyFilters() {
        const query = (document.getElementById('gradingSearchInput')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('.grading-row');

        rows.forEach(row => {
            const hasSubmitted = row.getAttribute('data-has-submitted') === '1';
            const isGraded = row.getAttribute('data-is-graded') === '1';
            const needsGrade = row.getAttribute('data-needs-grade') === '1';
            const searchData = row.getAttribute('data-search') || '';

            // Check Tab condition
            let matchTab = true;
            if (activeFilter === 'submitted') matchTab = hasSubmitted;
            else if (activeFilter === 'unsubmitted') matchTab = !hasSubmitted;
            else if (activeFilter === 'needs_grade') matchTab = needsGrade;
            else if (activeFilter === 'graded') matchTab = isGraded;

            // Check Search query
            let matchSearch = true;
            if (query.length > 0) {
                matchSearch = searchData.includes(query);
            }

            if (matchTab && matchSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
</script>
@endsection
