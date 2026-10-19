@extends('layouts.dosen')

@section('title', 'Materi Pembelajaran — Arunika Learning System')
@section('page_breadcrumb', 'Materi Pembelajaran')

@section('content')
<div class="space-y-6">

    <!-- Header Judul Halaman & Tombol Aksi -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-start gap-3.5">
            <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <x-icon name="book-open" :size="22" />
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight">
                    Materi Pembelajaran
                </h1>
                <p class="text-xs text-text-muted mt-0.5">
                    Kelola silabus, slide presentasi, modul praktikum, dan video pembelajaran per pertemuan.
                </p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button 
                type="button" 
                onclick="openAnnouncementModal()"
                class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-semibold text-text hover:text-[#2563EB] bg-surface hover:bg-slate-50 border border-line rounded-[8px] transition-colors shadow-2xs cursor-pointer"
            >
                <x-icon name="megaphone" :size="14" class="text-text-muted" />
                <span>+ Buat Pengumuman</span>
            </button>

            <button 
                type="button" 
                onclick="openUploadModal()"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[8px] shadow-2xs transition-colors cursor-pointer"
            >
                <x-icon name="plus" :size="15" />
                <span>+ Unggah Materi Baru</span>
            </button>
        </div>
    </div>

    <!-- Alert Flash Messages -->
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
                <span>Terjadi kesalahan:</span>
            </div>
            <ul class="list-disc list-inside pl-4 space-y-0.5 text-rose-700">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Selector Kelas & Top Bar Filter -->
    <div class="bg-surface border border-line rounded-[10px] p-4 shadow-2xs flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <!-- Pilihan Kelas Dosen -->
        <div class="flex items-center gap-3 flex-wrap">
            <span class="text-xs font-semibold text-text-muted">Pilih Kelas:</span>
            <div class="flex items-center gap-1.5 flex-wrap">
                @foreach ($classes as $cl)
                    <a 
                        href="{{ route('dosen.materi.index', ['class_id' => $cl->id]) }}" 
                        class="px-3 py-1.5 rounded-[8px] text-xs font-semibold border transition-all {{ ($activeClass?->id == $cl->id) ? 'bg-[#1A2E5E] text-white border-[#1A2E5E] shadow-2xs' : 'bg-surface border-line text-text hover:bg-slate-50' }}"
                    >
                        {{ $cl->course->code }} - {{ $cl->section }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Filter Tipe, Status, & Live Search -->
        <form method="GET" action="{{ route('dosen.materi.index') }}" class="flex items-center gap-2.5 flex-wrap">
            <input type="hidden" name="class_id" value="{{ $activeClass?->id }}">

            <!-- Filter Tipe -->
            <select 
                name="type" 
                onchange="this.form.submit()" 
                class="px-2.5 py-1.5 text-xs font-medium rounded-[8px] bg-surface border border-line text-text focus:outline-none focus:border-[#2563EB] cursor-pointer"
            >
                <option value="semua" {{ ($filters['type'] ?? 'semua') === 'semua' ? 'selected' : '' }}>Semua Tipe</option>
                <option value="pdf" {{ ($filters['type'] ?? '') === 'pdf' ? 'selected' : '' }}>PDF / Dokumen</option>
                <option value="slide" {{ ($filters['type'] ?? '') === 'slide' ? 'selected' : '' }}>Slide Presentasi</option>
                <option value="video" {{ ($filters['type'] ?? '') === 'video' ? 'selected' : '' }}>Video Pembelajaran</option>
                <option value="tautan" {{ ($filters['type'] ?? '') === 'tautan' ? 'selected' : '' }}>Tautan / Link</option>
                <option value="zip" {{ ($filters['type'] ?? '') === 'zip' ? 'selected' : '' }}>Berkas ZIP</option>
            </select>

            <!-- Filter Status -->
            <select 
                name="status" 
                onchange="this.form.submit()" 
                class="px-2.5 py-1.5 text-xs font-medium rounded-[8px] bg-surface border border-line text-text focus:outline-none focus:border-[#2563EB] cursor-pointer"
            >
                <option value="semua" {{ ($filters['status'] ?? 'semua') === 'semua' ? 'selected' : '' }}>Semua Status</option>
                <option value="published" {{ ($filters['status'] ?? '') === 'published' ? 'selected' : '' }}>Terpublikasi</option>
                <option value="draft" {{ ($filters['status'] ?? '') === 'draft' ? 'selected' : '' }}>Draft</option>
            </select>

            <!-- Search input -->
            <div class="relative w-44">
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $filters['search'] ?? '' }}" 
                    placeholder="Cari materi..." 
                    class="w-full pl-7 pr-2 py-1.5 text-xs rounded-[8px] border border-line bg-surface text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                >
                <x-icon name="search" :size="12" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-subtle pointer-events-none" />
            </div>

            @if (!empty($filters['search']) || $filters['type'] !== 'semua' || $filters['status'] !== 'semua')
                <a 
                    href="{{ route('dosen.materi.index', ['class_id' => $activeClass?->id]) }}" 
                    class="p-1.5 text-text-muted hover:text-text text-xs" 
                    title="Reset Filter"
                >
                    <x-icon name="x" :size="14" />
                </a>
            @endif
        </form>
    </div>

    <!-- 4 Top Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- 1. Total Materi -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                <x-icon name="folder-open" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Total Materi</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['total'] }}
                </span>
                <span class="text-[11px] text-text-subtle block mt-0.5">
                    Di kelas {{ $activeClass?->section ?? 'terpilih' }}
                </span>
            </div>
        </div>

        <!-- 2. Terpublikasi -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <x-icon name="check-circle" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Terpublikasi</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['published'] }}
                </span>
                <span class="text-[11px] text-emerald-600 font-medium block mt-0.5">
                    {{ $summary['percent_published'] }}% materi aktif
                </span>
            </div>
        </div>

        <!-- 3. Draft -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <x-icon name="clock" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Materi Draft</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['draft'] }}
                </span>
                <span class="text-[11px] text-text-subtle block mt-0.5">
                    Belum terlihat mahasiswa
                </span>
            </div>
        </div>

        <!-- 4. Pertemuan Terisi -->
        <div class="bg-surface border border-line rounded-[10px] p-4 flex items-center gap-4 shadow-2xs">
            <div class="w-11 h-11 rounded-[8px] bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <x-icon name="calendar" :size="20" />
            </div>
            <div class="min-w-0 flex-1">
                <span class="text-xs font-medium text-text-muted truncate block">Pertemuan Terisi</span>
                <span class="text-2xl font-bold text-text tabular-nums leading-tight block mt-0.5">
                    {{ $summary['meetings_filled'] }} <span class="text-sm font-normal text-text-muted">/ 16</span>
                </span>
                <span class="text-[11px] text-indigo-600 font-medium block mt-0.5">
                    Silabus semester ganjil
                </span>
            </div>
        </div>
    </div>

    <!-- Main Grid: Pertemuan & Materi (Kiri 8 col), Widget & Pengumuman (Kanan 4 col) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Daftar Pertemuan 1 s/d 16 -->
        <div class="lg:col-span-8 space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-bold text-text">
                    Daftar Pertemuan Perkuliahan (16 Pertemuan)
                </h2>
                <span class="text-xs text-text-muted">
                    {{ $activeClass?->course?->code }} &bull; {{ $activeClass?->course?->name }} ({{ $activeClass?->section }})
                </span>
            </div>

            <div class="space-y-3">
                @forelse ($meetings as $meeting)
                    @php
                        $matCount = $meeting->meeting_materials->count();
                        $isUts = ($meeting->week === 8);
                        $isUas = ($meeting->week === 16);
                    @endphp
                    <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs transition-all">
                        <!-- Meeting Header Bar -->
                        <div class="p-4 bg-slate-50/70 border-b border-line/60 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 rounded-[6px] text-xs font-bold {{ $isUts || $isUas ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-[#1A2E5E]' }}">
                                    Minggu {{ $meeting->week }}
                                </span>
                                <div>
                                    <h3 class="text-xs sm:text-sm font-bold text-text">
                                        {{ $meeting->title }}
                                    </h3>
                                    <span class="text-[11px] text-text-subtle tabular-nums">
                                        {{ $meeting->date ? Carbon\Carbon::parse($meeting->date)->locale('id')->isoFormat('dddd, D MMMM Y') : 'Jadwal belum ditentukan' }}
                                    </span>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <span class="text-[11px] font-medium text-text-muted px-2 py-0.5 rounded-full bg-white border border-line">
                                    {{ $matCount }} Materi
                                </span>
                                <button 
                                    type="button" 
                                    onclick="openUploadModal({{ $meeting->week }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold text-[#2563EB] bg-blue-50/60 hover:bg-blue-100/70 border border-blue-200 rounded-[6px] transition-colors cursor-pointer"
                                >
                                    <x-icon name="plus" :size="12" />
                                    <span>Tambah</span>
                                </button>
                            </div>
                        </div>

                        <!-- Materials List inside this Meeting -->
                        @if ($matCount > 0)
                            <div class="divide-y divide-line/40">
                                @foreach ($meeting->meeting_materials as $mat)
                                    @php
                                        $typeInfo = match(strtolower($mat->type)) {
                                            'pdf', 'dokumen' => ['icon' => 'file-text', 'bg' => 'bg-rose-50 text-rose-600 border-rose-200', 'label' => 'PDF'],
                                            'ppt', 'slide' => ['icon' => 'pie-chart', 'bg' => 'bg-amber-50 text-amber-600 border-amber-200', 'label' => 'Slide'],
                                            'video' => ['icon' => 'video', 'bg' => 'bg-purple-50 text-purple-600 border-purple-200', 'label' => 'Video'],
                                            'tautan', 'link' => ['icon' => 'link', 'bg' => 'bg-emerald-50 text-emerald-600 border-emerald-200', 'label' => 'Tautan'],
                                            'zip' => ['icon' => 'folder-open', 'bg' => 'bg-blue-50 text-blue-600 border-blue-200', 'label' => 'ZIP'],
                                            default => ['icon' => 'file-text', 'bg' => 'bg-slate-50 text-slate-600 border-slate-200', 'label' => strtoupper($mat->type)],
                                        };
                                    @endphp
                                    <div class="p-3.5 sm:px-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-slate-50/50 transition-colors">
                                        <div class="flex items-start gap-3 min-w-0">
                                            <div class="w-9 h-9 rounded-[8px] {{ $typeInfo['bg'] }} border flex items-center justify-center shrink-0 mt-0.5">
                                                <x-icon :name="$typeInfo['icon']" :size="16" />
                                            </div>
                                            <div class="min-w-0">
                                                <div class="flex items-center gap-2 flex-wrap">
                                                    <h4 class="text-xs font-bold text-text truncate max-w-sm" title="{{ $mat->title }}">
                                                        {{ $mat->title }}
                                                    </h4>
                                                    <x-dosen.status-badge :status="$mat->status" size="xs" />
                                                </div>
                                                <p class="text-[11px] text-text-muted mt-0.5 truncate max-w-md">
                                                    {{ $mat->content ?? 'Materi perkuliahan resmi dari dosen pengampu.' }}
                                                </p>
                                                <div class="flex items-center gap-3 text-[10px] text-text-subtle mt-1">
                                                    <span class="font-medium text-text-muted">{{ $typeInfo['label'] }}</span>
                                                    <span>&bull;</span>
                                                    <span class="tabular-nums">Diunggah: {{ $mat->created_at ? $mat->created_at->locale('id')->isoFormat('D MMM Y, HH:mm') : '-' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Actions per Material -->
                                        <div class="flex items-center gap-1.5 self-end sm:self-center shrink-0">
                                            @if ($mat->file_path)
                                                <a 
                                                    href="{{ route('berkas.materi', $mat) }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-text-muted hover:text-text bg-surface hover:bg-slate-100 border border-line rounded-[6px] transition-colors"
                                                    title="Unduh / Lihat Berkas"
                                                >
                                                    <x-icon name="download" :size="12" />
                                                    <span>Unduh</span>
                                                </a>
                                            @elseif ($mat->external_url)
                                                <a 
                                                    href="{{ $mat->external_url }}" 
                                                    target="_blank" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium text-text-muted hover:text-text bg-surface hover:bg-slate-100 border border-line rounded-[6px] transition-colors"
                                                    title="Buka Tautan"
                                                >
                                                    <x-icon name="external-link" :size="12" />
                                                    <span>Buka URL</span>
                                                </a>
                                            @endif

                                            <!-- Toggle Publish / Draft -->
                                            <form action="{{ route('dosen.materi.toggle_publish', $mat->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="submit" 
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-medium {{ $mat->status === 'published' ? 'text-amber-700 bg-amber-50 hover:bg-amber-100 border-amber-200' : 'text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border-emerald-200' }} border rounded-[6px] transition-colors cursor-pointer"
                                                    title="{{ $mat->status === 'published' ? 'Jadikan Draft' : 'Publikasikan' }}"
                                                >
                                                    <x-icon :name="$mat->status === 'published' ? 'eye' : 'check'" :size="12" />
                                                    <span>{{ $mat->status === 'published' ? 'Draftkan' : 'Publikasikan' }}</span>
                                                </button>
                                            </form>

                                            <!-- Edit Material Trigger -->
                                            <button 
                                                type="button" 
                                                onclick='openEditModal(@json($mat), {{ $meeting->week }})'
                                                class="p-1.5 text-text-muted hover:text-text hover:bg-slate-100 border border-line rounded-[6px] transition-colors cursor-pointer"
                                                title="Ubah Materi"
                                            >
                                                <x-icon name="pencil" :size="12" />
                                            </button>

                                            <!-- Delete Material -->
                                            <form 
                                                action="{{ route('dosen.materi.destroy', $mat->id) }}" 
                                                method="POST" 
                                                class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi \'{{ $mat->title }}\'?');"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="p-1.5 text-rose-600 hover:text-rose-700 hover:bg-rose-50 border border-rose-200 rounded-[6px] transition-colors cursor-pointer"
                                                    title="Hapus Materi"
                                                >
                                                    <x-icon name="x" :size="12" />
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="p-4 text-center text-xs text-text-subtle">
                                <span>Belum ada materi pembelajaran pada pertemuan ini.</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center bg-surface border border-line rounded-[10px] text-xs text-text-muted">
                        Tidak ada pertemuan perkuliahan yang ditemukan.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Kolom Kanan: Widgets & Pengumuman (4 col) -->
        <div class="lg:col-span-4 space-y-5">

            <!-- 1. Widget Pengumuman Kelas -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="megaphone" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumuman Kelas</h2>
                    </div>
                    <button 
                        type="button" 
                        onclick="openAnnouncementModal()"
                        class="text-xs font-semibold text-[#2563EB] hover:underline cursor-pointer"
                    >
                        + Tambah
                    </button>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($announcements as $ann)
                        <div class="p-4 hover:bg-slate-50/60 transition-colors space-y-1.5">
                            <div class="flex items-start justify-between gap-2">
                                <h4 class="text-xs font-bold text-text leading-snug">
                                    {{ $ann->title }}
                                </h4>
                                @if ($ann->author_id === $user->id || $ann->academicClass?->lecturer_id === $user->id)
                                    <form 
                                        action="{{ route('dosen.pengumuman.destroy', $ann->id) }}" 
                                        method="POST" 
                                        onsubmit="return confirm('Hapus pengumuman ini?');"
                                    >
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-text-subtle hover:text-rose-600 transition-colors cursor-pointer" title="Hapus">
                                            <x-icon name="x" :size="12" />
                                        </button>
                                    </form>
                                @endif
                            </div>
                            <p class="text-[11px] text-text-muted leading-relaxed whitespace-pre-line">
                                {{ Str::limit($ann->content, 120) }}
                            </p>
                            <div class="flex items-center justify-between text-[10px] text-text-subtle pt-1">
                                <span>{{ $ann->academicClass ? $ann->academicClass->section : 'Semua Kelas' }}</span>
                                <span class="tabular-nums">{{ $ann->published_at ? $ann->published_at->locale('id')->isoFormat('D MMM Y, HH:mm') : '' }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-text-muted">
                            Belum ada pengumuman kelas yang aktif.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Widget Panduan RPS & Kalender Perkuliahan -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-3">
                <div class="flex items-center gap-2">
                    <x-icon name="info" :size="16" class="text-navy-800" />
                    <h3 class="text-xs font-bold text-text uppercase tracking-wider text-text-muted">
                        Panduan Silabus &amp; RPS
                    </h3>
                </div>

                <p class="text-xs text-text-muted leading-relaxed">
                    Setiap semester terdiri dari <strong>16 pertemuan perkuliahan</strong>. Pastikan seluruh berkas slide, modul, dan video siap sebelum tanggal pertemuan dimulai.
                </p>

                <div class="space-y-2 pt-1 text-xs">
                    <div class="flex items-center justify-between p-2 rounded-[6px] bg-canvas border border-line">
                        <span class="font-medium text-text">Pertemuan 1 - 7</span>
                        <span class="text-[11px] text-emerald-600 font-semibold">Materi Pra-UTS</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-[6px] bg-amber-50/70 border border-amber-200 text-amber-800">
                        <span class="font-bold">Pertemuan 8</span>
                        <span class="text-[11px] font-semibold">Ujian Tengah Semester (UTS)</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-[6px] bg-canvas border border-line">
                        <span class="font-medium text-text">Pertemuan 9 - 15</span>
                        <span class="text-[11px] text-emerald-600 font-semibold">Materi Pasca-UTS</span>
                    </div>
                    <div class="flex items-center justify-between p-2 rounded-[6px] bg-rose-50/70 border border-rose-200 text-rose-800">
                        <span class="font-bold">Pertemuan 16</span>
                        <span class="text-[11px] font-semibold">Ujian Akhir Semester (UAS)</span>
                    </div>
                </div>
            </div>

            <!-- 3. Tautan Cepat -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-3">
                <h3 class="text-xs font-bold text-text uppercase tracking-wider text-text-muted">
                    Tautan Cepat
                </h3>

                <div class="space-y-2">
                    <a 
                        href="{{ route('dosen.kelas.show', $activeClass?->id) }}" 
                        class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center justify-between transition-colors"
                    >
                        <div class="flex items-center gap-2">
                            <x-icon name="users" :size="14" class="text-text-muted" />
                            <span>Lihat Detail Kelas &amp; Mahasiswa</span>
                        </div>
                        <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                    </a>

                    <a 
                        href="{{ route('dosen.tugas.index') }}" 
                        class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center justify-between transition-colors"
                    >
                        <div class="flex items-center gap-2">
                            <x-icon name="file-text" :size="14" class="text-text-muted" />
                            <span>Kelola Tugas &amp; Ujian</span>
                        </div>
                        <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                    </a>

                    <a 
                        href="{{ route('dosen.penilaian.index') }}" 
                        class="p-2.5 rounded-[6px] border border-line hover:border-slate-300 bg-canvas/40 hover:bg-canvas text-xs font-medium text-text flex items-center justify-between transition-colors"
                    >
                        <div class="flex items-center gap-2">
                            <x-icon name="clipboard-check" :size="14" class="text-text-muted" />
                            <span>Buka Lembar Penilaian</span>
                        </div>
                        <x-icon name="chevron-right" :size="13" class="text-text-subtle" />
                    </a>
                </div>
            </div>

        </div>

    </div>

</div>

<!-- Modal 1: Unggah Materi Baru -->
<div 
    id="uploadModal" 
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4"
>
    <div class="bg-surface border border-line rounded-[12px] max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-line">
            <h3 class="text-sm font-bold text-text flex items-center gap-2">
                <x-icon name="upload-cloud" :size="18" class="text-[#2563EB]" />
                <span>Unggah Materi Pembelajaran Baru</span>
            </h3>
            <button type="button" onclick="closeUploadModal()" class="text-text-muted hover:text-text cursor-pointer">
                <x-icon name="x" :size="16" />
            </button>
        </div>

        <form action="{{ route('dosen.materi.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            <input type="hidden" name="class_id" value="{{ $activeClass?->id }}">

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Target Kelas</label>
                <input 
                    type="text" 
                    value="{{ $activeClass?->course?->code }} - {{ $activeClass?->course?->name }} ({{ $activeClass?->section }})" 
                    disabled 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-slate-100 text-text font-medium cursor-not-allowed"
                >
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Pertemuan Ke-</label>
                    <select name="week" id="uploadWeekSelect" required class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        @for ($w = 1; $w <= 16; $w++)
                            <option value="{{ $w }}">Pertemuan {{ $w }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Tipe Materi</label>
                    <select name="type" required class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        <option value="pdf">Dokumen / PDF</option>
                        <option value="slide">Slide Presentasi (PPT/PDF)</option>
                        <option value="video">Video Pembelajaran</option>
                        <option value="tautan">Tautan Eksternal / Link</option>
                        <option value="zip">Berkas ZIP / Proyek</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Judul Materi <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    required 
                    placeholder="Contoh: Modul 5 - Polymorphism & Interface" 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Deskripsi / Ringkasan</label>
                <textarea 
                    name="content" 
                    rows="2" 
                    placeholder="Deskripsi singkat mengenai materi ini..." 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Berkas Lampiran (Maks 50MB)</label>
                <input 
                    type="file" 
                    name="file" 
                    class="w-full px-3 py-1.5 text-xs rounded-[6px] border border-line bg-surface text-text file:mr-3 file:py-1 file:px-2.5 file:rounded-[4px] file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Atau Tautan Eksternal (URL)</label>
                <input 
                    type="url" 
                    name="external_url" 
                    placeholder="https://youtu.be/... atau https://drive.google.com/..." 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                >
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Status Publikasi</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        <option value="published">Publikasikan Sekarang</option>
                        <option value="draft">Simpan sebagai Draft</option>
                    </select>
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center gap-2 text-xs text-text cursor-pointer">
                        <input type="checkbox" name="is_downloadable" value="1" checked class="rounded border-line text-[#2563EB] focus:ring-0">
                        <span>Boleh diunduh mahasiswa</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                <button 
                    type="button" 
                    onclick="closeUploadModal()"
                    class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text border border-line rounded-[6px] transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[6px] shadow-2xs transition-colors cursor-pointer"
                >
                    Simpan Materi
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 2: Edit Materi -->
<div 
    id="editModal" 
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4"
>
    <div class="bg-surface border border-line rounded-[12px] max-w-lg w-full p-6 shadow-xl space-y-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between pb-3 border-b border-line">
            <h3 class="text-sm font-bold text-text flex items-center gap-2">
                <x-icon name="pencil" :size="16" class="text-[#2563EB]" />
                <span>Ubah Materi Pembelajaran</span>
            </h3>
            <button type="button" onclick="closeEditModal()" class="text-text-muted hover:text-text cursor-pointer">
                <x-icon name="x" :size="16" />
            </button>
        </div>

        <form id="editMaterialForm" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Pertemuan Ke-</label>
                    <select name="week" id="editWeek" required class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        @for ($w = 1; $w <= 16; $w++)
                            <option value="{{ $w }}">Pertemuan {{ $w }}</option>
                        @endfor
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Tipe Materi</label>
                    <select name="type" id="editType" required class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        <option value="pdf">Dokumen / PDF</option>
                        <option value="slide">Slide Presentasi (PPT/PDF)</option>
                        <option value="video">Video Pembelajaran</option>
                        <option value="tautan">Tautan Eksternal / Link</option>
                        <option value="zip">Berkas ZIP / Proyek</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Judul Materi <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    id="editTitle" 
                    required 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Deskripsi / Ringkasan</label>
                <textarea 
                    name="content" 
                    id="editContent" 
                    rows="2" 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                ></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Ganti Berkas (Opsional, Maks 50MB)</label>
                <input 
                    type="file" 
                    name="file" 
                    class="w-full px-3 py-1.5 text-xs rounded-[6px] border border-line bg-surface text-text file:mr-3 file:py-1 file:px-2.5 file:rounded-[4px] file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Tautan Eksternal (URL)</label>
                <input 
                    type="url" 
                    name="external_url" 
                    id="editUrl" 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                >
            </div>

            <div class="grid grid-cols-2 gap-3 pt-2">
                <div>
                    <label class="block text-xs font-semibold text-text mb-1">Status Publikasi</label>
                    <select name="status" id="editStatus" class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                        <option value="published">Publikasikan</option>
                        <option value="draft">Draft (Sembunyikan)</option>
                    </select>
                </div>

                <div class="flex items-center pt-5">
                    <label class="inline-flex items-center gap-2 text-xs text-text cursor-pointer">
                        <input type="checkbox" name="is_downloadable" id="editDownloadable" value="1" class="rounded border-line text-[#2563EB] focus:ring-0">
                        <span>Boleh diunduh mahasiswa</span>
                    </label>
                </div>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                <button 
                    type="button" 
                    onclick="closeEditModal()"
                    class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text border border-line rounded-[6px] transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[6px] shadow-2xs transition-colors cursor-pointer"
                >
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal 3: Buat Pengumuman Baru -->
<div 
    id="announcementModal" 
    class="fixed inset-0 z-50 bg-black/50 backdrop-blur-xs hidden flex items-center justify-center p-4"
>
    <div class="bg-surface border border-line rounded-[12px] max-w-lg w-full p-6 shadow-xl space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-line">
            <h3 class="text-sm font-bold text-text flex items-center gap-2">
                <x-icon name="megaphone" :size="16" class="text-[#2563EB]" />
                <span>Buat Pengumuman Kelas Baru</span>
            </h3>
            <button type="button" onclick="closeAnnouncementModal()" class="text-text-muted hover:text-text cursor-pointer">
                <x-icon name="x" :size="16" />
            </button>
        </div>

        <form action="{{ route('dosen.pengumuman.store') }}" method="POST" class="space-y-4">
            @csrf

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Target Kelas</label>
                <select name="class_id" class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]">
                    <option value="{{ $activeClass?->id }}">Kelas Ini: {{ $activeClass?->course?->code }} - {{ $activeClass?->section }}</option>
                    <option value="semua">Semua Kelas yang Saya Ampu</option>
                    @foreach ($classes as $c)
                        @if ($c->id !== $activeClass?->id)
                            <option value="{{ $c->id }}">{{ $c->course->code }} - {{ $c->section }}</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Judul Pengumuman <span class="text-rose-500">*</span></label>
                <input 
                    type="text" 
                    name="title" 
                    required 
                    placeholder="Contoh: Perubahan Jadwal Pertemuan 7" 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                >
            </div>

            <div>
                <label class="block text-xs font-semibold text-text mb-1">Isi Pengumuman <span class="text-rose-500">*</span></label>
                <textarea 
                    name="content" 
                    rows="4" 
                    required 
                    placeholder="Tuliskan isi pengumuman untuk mahasiswa..." 
                    class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-2.5 pt-4 border-t border-line">
                <button 
                    type="button" 
                    onclick="closeAnnouncementModal()"
                    class="px-4 py-2 text-xs font-medium text-text-muted hover:text-text border border-line rounded-[6px] transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button 
                    type="submit" 
                    class="px-4 py-2 text-xs font-semibold text-white bg-[#1A2E5E] hover:bg-navy-800 rounded-[6px] shadow-2xs transition-colors cursor-pointer"
                >
                    Kirim Pengumuman
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Control Scripts -->
<script>
    function openUploadModal(week = 1) {
        document.getElementById('uploadWeekSelect').value = week;
        document.getElementById('uploadModal').classList.remove('hidden');
    }

    function closeUploadModal() {
        document.getElementById('uploadModal').classList.add('hidden');
    }

    function openEditModal(material, week) {
        document.getElementById('editMaterialForm').action = '/dosen/materi/' + material.id;
        document.getElementById('editWeek').value = week;
        document.getElementById('editTitle').value = material.title;
        document.getElementById('editType').value = material.type;
        document.getElementById('editContent').value = material.content || '';
        document.getElementById('editUrl').value = material.external_url || '';
        document.getElementById('editStatus').value = material.status;
        document.getElementById('editDownloadable').checked = material.is_downloadable;

        document.getElementById('editModal').classList.remove('hidden');
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.add('hidden');
    }

    function openAnnouncementModal() {
        document.getElementById('announcementModal').classList.remove('hidden');
    }

    function closeAnnouncementModal() {
        document.getElementById('announcementModal').classList.add('hidden');
    }
</script>
@endsection
