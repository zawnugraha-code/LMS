@extends('layouts.dosen')

@section('title', 'Kelas Saya — Arunika Learning System')
@section('page_breadcrumb', 'Kelas Saya')

@section('content')
<div class="space-y-6">

    <!-- Header Judul Halaman -->
    <div class="flex items-start gap-3.5">
        <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
            <x-icon name="users" :size="22" />
        </div>
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight">
                Kelas Saya
            </h1>
            <p class="text-xs text-text-muted mt-0.5">
                Kelola kelas yang Anda ampu, pantau progres mahasiswa, dan akses fitur pembelajaran.
            </p>
        </div>
    </div>

    <!-- Filter Tab & Semester Selector -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <!-- Filter Tabs -->
        <div class="inline-flex items-center gap-1.5 p-1 rounded-[8px] bg-surface border border-line text-xs font-medium">
            <a 
                href="{{ route('dosen.kelas.index', ['status' => 'semua']) }}" 
                class="px-3.5 py-1.5 rounded-[6px] transition-colors {{ $statusFilter === 'semua' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-50' }}"
            >
                Semua Kelas
            </a>
            <a 
                href="{{ route('dosen.kelas.index', ['status' => 'aktif']) }}" 
                class="px-3.5 py-1.5 rounded-[6px] transition-colors {{ $statusFilter === 'aktif' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-50' }}"
            >
                Aktif ({{ $summary['active_classes'] }})
            </a>
            <a 
                href="{{ route('dosen.kelas.index', ['status' => 'selesai']) }}" 
                class="px-3.5 py-1.5 rounded-[6px] transition-colors {{ $statusFilter === 'selesai' ? 'bg-[#2563EB] text-white shadow-2xs font-semibold' : 'text-text-muted hover:text-text hover:bg-slate-50' }}"
            >
                Selesai ({{ $summary['completed_classes'] }})
            </a>
        </div>

        <!-- Semester Selector -->
        <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-[8px] bg-surface border border-line text-xs font-medium text-text cursor-default">
            <x-icon name="graduation-cap" :size="14" class="text-text-muted" />
            <span>Semester 3 ({{ $term->name ?? 'Ganjil 2026/2027' }})</span>
            <x-icon name="chevron-down" :size="12" class="text-text-subtle" />
        </div>
    </div>

    <!-- Main Grid: Left Courses List, Right Widgets -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri: Daftar Mata Kuliah & Kelas (8 Kolom) -->
        <div class="lg:col-span-8 space-y-4">
            @forelse ($courses as $course)
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4 hover:border-slate-300 transition-colors">
                    
                    <!-- Header Mata Kuliah -->
                    <div class="flex items-start gap-4">
                        <!-- Thumbnail Visual Sesuai Kode MK -->
                        <div class="w-14 h-14 rounded-[8px] bg-navy-900 text-white flex items-center justify-center shrink-0 p-2 shadow-2xs">
                            @if (str_contains($course->code, 'IF2101'))
                                <span class="font-mono text-sm font-bold tracking-tight text-blue-400">&lt;/&gt;</span>
                            @elseif (str_contains($course->code, 'IF2103'))
                                <x-icon name="database" :size="22" class="text-emerald-400" />
                            @elseif (str_contains($course->code, 'IF2105'))
                                <x-icon name="cpu" :size="22" class="text-cyan-400" />
                            @else
                                <x-icon name="shield" :size="22" class="text-amber-400" />
                            @endif
                        </div>

                        <div class="min-w-0 flex-1">
                            <h3 class="text-sm sm:text-base font-bold text-text truncate">
                                {{ $course->code }} - {{ $course->name }}
                            </h3>
                            <div class="flex items-center gap-2.5 mt-1">
                                <span class="text-xs text-text-muted">
                                    {{ $course->curriculum?->program?->name ?? 'S1 Teknik Informatika' }}
                                </span>
                                <span class="px-2 py-0.5 text-[10px] font-semibold rounded-full bg-blue-50 text-[#2563EB] border border-blue-200">
                                    {{ $course->taught_classes->count() }} Kelas
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Kelas yang Diampu pada MK Ini -->
                    <div class="space-y-3 pt-1 border-t border-line/60">
                        @foreach ($course->taught_classes as $class)
                            @php
                                $circleColor = match ($class->section) {
                                    'Kelas A' => 'bg-[#2563EB]',
                                    'Kelas B' => 'bg-purple-600',
                                    default => 'bg-amber-600',
                                };
                                $letter = substr(str_replace('Kelas ', '', $class->section), 0, 1);
                            @endphp
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 p-3 rounded-[8px] bg-canvas/40 border border-line/60 hover:bg-canvas transition-colors">
                                
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-7 h-7 rounded-full {{ $circleColor }} text-white text-xs font-bold flex items-center justify-center shrink-0">
                                        {{ $letter }}
                                    </div>
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <h4 class="text-xs font-bold text-text truncate">{{ $class->section }}</h4>
                                        </div>
                                        <div class="flex items-center gap-3 text-[11px] text-text-muted mt-0.5">
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="calendar" :size="12" class="text-text-subtle" />
                                                <span>{{ $class->schedule_day }}, {{ substr($class->start_time, 0, 5) }} - {{ substr($class->end_time, 0, 5) }}</span>
                                            </span>
                                            <span class="inline-flex items-center gap-1">
                                                <x-icon name="map-pin" :size="12" class="text-text-subtle" />
                                                <span>Ruang {{ $class->room }}</span>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between sm:justify-end gap-4 shrink-0 pt-2 sm:pt-0 border-t sm:border-t-0 border-line/40">
                                    <div class="flex items-center gap-1.5 text-xs text-text-muted shrink-0">
                                        <x-icon name="users" :size="13" class="text-text-subtle" />
                                        <span>{{ $class->student_count }} mahasiswa</span>
                                    </div>

                                    <div class="shrink-0 w-32 sm:w-36">
                                        <div class="flex items-center justify-between text-[10px] text-text-muted mb-1">
                                            <span>Progress Materi</span>
                                            <span class="font-bold text-text tabular-nums">{{ $class->progress_percentage }}%</span>
                                        </div>
                                        <div class="w-full h-1.5 rounded-full bg-slate-200 overflow-hidden">
                                            <div class="h-full bg-[#2563EB] rounded-full transition-all duration-300" style="width: {{ $class->progress_percentage }}%"></div>
                                        </div>
                                    </div>

                                    <a 
                                        href="{{ route('dosen.kelas.show', $class->id) }}" 
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-slate-50 text-xs font-semibold text-text shadow-2xs hover:border-slate-300 transition-colors shrink-0"
                                    >
                                        <span>Lihat Kelas</span>
                                        <x-icon name="chevron-right" :size="12" class="text-text-subtle" />
                                    </a>
                                </div>

                            </div>
                        @endforeach
                    </div>

                </div>
            @empty
                <x-dosen.empty-state 
                    icon="users"
                    title="Tidak ada kelas yang ditemukan"
                    description="Anda belum memiliki kelas yang terdaftar dengan status ini pada semester aktif."
                />
            @endforelse
        </div>

        <!-- Kolom Kanan: Widget Samping (4 Kolom) -->
        <div class="lg:col-span-4 space-y-6">

            <!-- 1. Jadwal Hari Ini -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Jadwal Hari Ini</h2>
                    </div>
                    <span class="text-xs text-text-muted font-medium">
                        Senin, 19 Okt 2026
                    </span>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($todaySchedule as $index => $jadwal)
                        @php
                            $dotColor = match ($index % 3) {
                                0 => 'bg-[#2563EB]',
                                1 => 'bg-purple-600',
                                default => 'bg-amber-600',
                            };
                        @endphp
                        <a 
                            href="{{ route('dosen.kelas.show', $jadwal->id) }}" 
                            class="p-4 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition-colors group"
                        >
                            <div class="flex items-start gap-3 min-w-0">
                                <span class="w-2 h-2 rounded-full {{ $dotColor }} mt-1.5 shrink-0"></span>
                                <div class="min-w-0">
                                    <span class="text-[11px] font-semibold text-text-muted tabular-nums block">
                                        {{ substr($jadwal->start_time, 0, 5) }} - {{ substr($jadwal->end_time, 0, 5) }}
                                    </span>
                                    <h4 class="text-xs font-bold text-text group-hover:text-[#2563EB] transition-colors truncate">
                                        {{ $jadwal->course->code }} - {{ $jadwal->section }}
                                    </h4>
                                    <p class="text-[11px] text-text-muted truncate mt-0.5">
                                        {{ $jadwal->course->name }}
                                    </p>
                                    <p class="text-[10px] text-text-subtle mt-0.5 flex items-center gap-1">
                                        <x-icon name="map-pin" :size="10" />
                                        <span>Ruang {{ $jadwal->room }}</span>
                                    </p>
                                </div>
                            </div>
                            <x-icon name="chevron-right" :size="14" class="text-text-subtle group-hover:text-text shrink-0" />
                        </a>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Tidak ada jadwal hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Ringkasan Kelas (2x2 Grid) -->
            <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs">
                <div class="flex items-center gap-2 mb-4">
                    <x-icon name="briefcase" :size="16" class="text-navy-800" />
                    <h2 class="text-sm font-bold text-text">Ringkasan Kelas</h2>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <!-- Total Kelas -->
                    <div class="p-3.5 rounded-[8px] bg-canvas/60 border border-line flex items-center gap-3">
                        <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                            <x-icon name="book-open" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-text tabular-nums leading-none">{{ $summary['total_classes'] }}</p>
                            <p class="text-[11px] text-text-muted mt-1 truncate">Total Kelas</p>
                        </div>
                    </div>

                    <!-- Total Mahasiswa -->
                    <div class="p-3.5 rounded-[8px] bg-canvas/60 border border-line flex items-center gap-3">
                        <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                            <x-icon name="users" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-text tabular-nums leading-none">{{ $summary['total_students'] }}</p>
                            <p class="text-[11px] text-text-muted mt-1 truncate">Total Mahasiswa</p>
                        </div>
                    </div>

                    <!-- Kelas Aktif -->
                    <div class="p-3.5 rounded-[8px] bg-canvas/60 border border-line flex items-center gap-3">
                        <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                            <x-icon name="calendar" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-text tabular-nums leading-none">{{ $summary['active_classes'] }}</p>
                            <p class="text-[11px] text-text-muted mt-1 truncate">Kelas Aktif</p>
                        </div>
                    </div>

                    <!-- Kelas Selesai -->
                    <div class="p-3.5 rounded-[8px] bg-canvas/60 border border-line flex items-center gap-3">
                        <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0">
                            <x-icon name="check-circle" :size="16" />
                        </div>
                        <div class="min-w-0">
                            <p class="text-lg font-bold text-text tabular-nums leading-none">{{ $summary['completed_classes'] }}</p>
                            <p class="text-[11px] text-text-muted mt-1 truncate">Kelas Selesai</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Pengumuman Terbaru -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="megaphone" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumuman Terbaru</h2>
                    </div>
                    <a href="{{ route('dosen.dashboard') }}" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($recentAnnouncements as $index => $ann)
                        @php
                            $iconColor = match ($index % 3) {
                                0 => 'bg-blue-50 text-[#2563EB]',
                                1 => 'bg-red-50 text-red-600',
                                default => 'bg-amber-50 text-amber-600',
                            };
                        @endphp
                        <div class="p-4 flex items-start gap-3 hover:bg-slate-50/60 transition-colors">
                            <div class="w-8 h-8 rounded-full {{ $iconColor }} flex items-center justify-center shrink-0 mt-0.5">
                                <x-icon name="file-text" :size="15" />
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-text truncate">
                                    {{ $ann->title }}
                                </h4>
                                <p class="text-[11px] text-text-muted truncate mt-0.5">
                                    {{ $ann->content }}
                                </p>
                                <span class="text-[10px] text-text-subtle mt-1 block">
                                    {{ $ann->published_at ? $ann->published_at->diffForHumans() : '' }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Belum ada pengumuman kelas terbaru.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
