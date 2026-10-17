@extends('layouts.dosen')

@section('title', 'Dashboard Dosen — Arunika Learning System')
@section('page_breadcrumb', 'Dashboard Dosen')

@section('content')
<div class="space-y-6">

    <!-- Welcome Banner with Campus Image -->
    <div class="bg-surface border border-line rounded-[10px] p-6 flex flex-col md:flex-row md:items-center justify-between gap-6 shadow-2xs">
        <div class="min-w-0 flex-1">
            <h1 class="text-2xl font-bold text-text tracking-tight mb-1">
                Selamat datang, {{ $user->name ?? 'Dr. Budi Rahardjo' }}
            </h1>
            <p class="text-xs text-text-muted mb-4">
                Berikut ringkasan aktivitas mengajar Anda pada semester ini.
            </p>

            <div class="flex items-center gap-2.5 flex-wrap text-xs">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-line text-text-muted">
                    <x-icon name="graduation-cap" :size="14" class="text-navy-700" />
                    <span>{{ $user->department ?? 'S1 Teknik Informatika' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-line text-text-muted">
                    <x-icon name="calendar" :size="14" class="text-navy-700" />
                    <span>{{ $term->name ?? 'Semester Ganjil 2026/2027' }}</span>
                </span>
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-slate-50 border border-line text-text-muted">
                    <x-icon name="clock" :size="14" class="text-navy-700" />
                    <span>{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</span>
                </span>
            </div>
        </div>

        <div class="shrink-0 w-full md:w-80 h-36 rounded-[8px] overflow-hidden border border-line shadow-2xs relative">
            <img 
                src="/images/campus-hero.jpg" 
                alt="Kampus Universitas Nusantara Indonesia" 
                class="w-full h-full object-cover"
                onerror="this.onerror=null;this.src='/images/campus-hero.png';"
            >
        </div>
    </div>

    <!-- 4 Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <x-dosen.stat-card 
            title="Kelas Diampu" 
            :value="$stats['kelas_diampu']" 
            unit="kelas" 
            icon="book-open" 
            iconBg="bg-blue-50 text-[#2563EB]" 
        />
        <x-dosen.stat-card 
            title="Total Mahasiswa" 
            :value="$stats['total_mahasiswa']" 
            unit="mahasiswa" 
            icon="users" 
            iconBg="bg-blue-50 text-[#2563EB]" 
        />
        <x-dosen.stat-card 
            title="Tugas Aktif" 
            :value="$stats['tugas_aktif']" 
            unit="tugas" 
            icon="file-text" 
            iconBg="bg-blue-50 text-[#2563EB]" 
        />
        <x-dosen.stat-card 
            title="Perlu Dinilai" 
            :value="$stats['perlu_dinilai']" 
            :badge="$stats['perlu_dinilai']" 
            unit="pengumpulan" 
            icon="check-circle" 
            iconBg="bg-blue-50 text-[#2563EB]" 
        />
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Kolom Kiri (7 Kolom) -->
        <div class="lg:col-span-7 space-y-6">

            <!-- 1. Jadwal Mengajar Hari Ini -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Jadwal Mengajar Hari Ini</h2>
                    </div>
                    <a href="{{ route('dosen.kelas.index') }}" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($todaySchedule as $jadwal)
                        <div class="p-4 sm:px-5 flex items-center justify-between gap-4 hover:bg-slate-50/60 transition-colors">
                            <div class="flex items-start gap-4 min-w-0">
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-text tabular-nums shrink-0 mt-0.5">
                                    <x-icon name="clock" :size="14" class="text-text-muted" />
                                    <span>{{ substr($jadwal->start_time, 0, 5) }} - {{ substr($jadwal->end_time, 0, 5) }}</span>
                                </div>
                                <div class="min-w-0">
                                    <h4 class="text-xs font-bold text-text truncate">
                                        {{ $jadwal->course->code }} - {{ $jadwal->course->name }}
                                    </h4>
                                    <p class="text-[11px] text-text-muted mt-0.5 truncate">
                                        {{ $jadwal->section }} &bull; Ruang {{ $jadwal->room }}
                                    </p>
                                </div>
                            </div>
                            <div class="shrink-0">
                                <span class="px-2.5 py-1 text-[11px] font-medium rounded-full bg-blue-50 text-[#2563EB] border border-blue-200">
                                    Pertemuan {{ $jadwal->current_meeting_number ?? 5 }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-text-muted">
                            Tidak ada jadwal mengajar untuk hari ini.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Pengumpulan Terbaru (Perlu Dinilai) -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="file-text" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumpulan Terbaru (Perlu Dinilai)</h2>
                        @if ($stats['perlu_dinilai'] > 0)
                            <span class="px-1.5 py-0.2 text-[10px] font-bold rounded-full bg-red-500 text-white">
                                {{ $stats['perlu_dinilai'] }}
                            </span>
                        @endif
                    </div>
                    <a href="{{ route('dosen.penilaian.index') }}" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs divide-y divide-line/60">
                        <thead class="bg-slate-50 text-[11px] font-semibold text-text-muted uppercase">
                            <tr>
                                <th class="px-5 py-3">Nama Mahasiswa</th>
                                <th class="px-3 py-3">Mata Kuliah</th>
                                <th class="px-3 py-3">Tugas</th>
                                <th class="px-3 py-3">Waktu Submit</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/40">
                            @forelse ($pendingSubmissions as $sub)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="px-5 py-3.5 font-medium text-text whitespace-nowrap">
                                        {{ $sub->user->name ?? 'Mahasiswa' }}
                                    </td>
                                    <td class="px-3 py-3.5 text-text-muted whitespace-nowrap">
                                        {{ $sub->assignment->academicClass->course->code ?? '' }} - {{ $sub->assignment->academicClass->section ?? '' }}
                                    </td>
                                    <td class="px-3 py-3.5 text-text font-medium whitespace-nowrap">
                                        {{ $sub->assignment->title ?? 'Tugas' }}
                                    </td>
                                    <td class="px-3 py-3.5 text-text-muted whitespace-nowrap">
                                        {{ $sub->submitted_at ? $sub->submitted_at->locale('id')->isoFormat('D MMM Y, HH:mm') : '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-right whitespace-nowrap">
                                        <x-dosen.status-badge :status="$sub->status_waktu ?? 'Tepat Waktu'" />
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-5 py-6 text-center text-xs text-text-muted">
                                        Tidak ada tugas yang menunggu penilaian saat ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="px-5 py-3 bg-slate-50/50 border-t border-line/60">
                    <a href="{{ route('dosen.penilaian.index') }}" class="text-xs font-semibold text-[#2563EB] hover:underline inline-flex items-center gap-1.5">
                        <span>Lihat semua pengumpulan</span>
                        <x-icon name="arrow-right" :size="12" />
                    </a>
                </div>
            </div>

        </div>

        <!-- Kolom Kanan (5 Kolom) -->
        <div class="lg:col-span-5 space-y-6">

            <!-- 1. Pengumuman Terbaru -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="megaphone" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumuman Terbaru</h2>
                    </div>
                    <a href="{{ route('dosen.kelas.index') }}" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($recentAnnouncements as $announcement)
                        <div class="p-4 sm:px-5 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition-colors cursor-pointer">
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-medium text-text-subtle block mb-0.5">
                                    {{ $announcement->published_at ? $announcement->published_at->locale('id')->isoFormat('D MMM Y') : '' }}
                                </span>
                                <h4 class="text-xs font-bold text-text truncate">
                                    {{ $announcement->title }}
                                </h4>
                                <p class="text-[11px] text-text-muted truncate mt-0.5">
                                    {{ $announcement->content }}
                                </p>
                            </div>
                            <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Belum ada pengumuman kelas terbaru.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Tugas dengan Deadline Terdekat -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="clock" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Tugas dengan Deadline Terdekat</h2>
                    </div>
                    <a href="{{ route('dosen.tugas.index') }}" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    <div class="px-5 py-2.5 bg-slate-50 flex items-center justify-between text-[11px] font-semibold text-text-muted uppercase">
                        <span class="w-1/3">Mata Kuliah</span>
                        <span class="w-1/3">Judul Tugas</span>
                        <span class="w-1/3 text-right">Status</span>
                    </div>
                    @forelse ($upcomingDeadlines as $task)
                        <div class="p-3.5 px-5 flex items-center justify-between gap-2 hover:bg-slate-50/60 transition-colors text-xs">
                            <div class="w-1/3 min-w-0 pr-2">
                                <span class="font-medium text-text truncate block">
                                    {{ $task->academicClass->course->code ?? '' }} - {{ $task->academicClass->section ?? '' }}
                                </span>
                            </div>
                            <div class="w-1/3 min-w-0 pr-2">
                                <span class="text-text-muted truncate block">
                                    {{ $task->title }}
                                </span>
                            </div>
                            <div class="w-1/3 text-right shrink-0">
                                <span class="px-2 py-0.5 text-[11px] font-medium rounded-full bg-blue-50 text-[#2563EB] border border-blue-200">
                                    {{ $task->days_left_label }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Tidak ada tenggat tugas dalam waktu dekat.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 3. Pengumuman Akademik -->
            <div class="bg-surface border border-line rounded-[10px] overflow-hidden shadow-2xs">
                <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <x-icon name="landmark" :size="16" class="text-navy-800" />
                        <h2 class="text-sm font-bold text-text">Pengumuman Akademik</h2>
                    </div>
                    <a href="#" class="text-xs font-medium text-[#2563EB] hover:underline">
                        Lihat Semua
                    </a>
                </div>

                <div class="divide-y divide-line/60">
                    @forelse ($academicAnnouncements as $ann)
                        <div class="p-4 sm:px-5 flex items-center justify-between gap-3 hover:bg-slate-50/60 transition-colors cursor-pointer">
                            <div class="min-w-0 flex-1">
                                <span class="text-[10px] font-medium text-text-subtle block mb-0.5">
                                    {{ $ann->published_at ? $ann->published_at->locale('id')->isoFormat('D MMM Y') : '' }}
                                </span>
                                <h4 class="text-xs font-bold text-text truncate">
                                    {{ $ann->title }}
                                </h4>
                                <p class="text-[11px] text-text-muted truncate mt-0.5">
                                    {{ $ann->content }}
                                </p>
                            </div>
                            <x-icon name="chevron-right" :size="14" class="text-text-subtle shrink-0" />
                        </div>
                    @empty
                        <div class="p-5 text-center text-xs text-text-muted">
                            Belum ada pengumuman akademik.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
