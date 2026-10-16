@extends('layouts.app')

@section('title', $course->name . ' (' . $course->code . ') — Detail Mata Kuliah')
@section('page_breadcrumb', 'Mata Kuliah / ' . $course->code)

@section('content')

    <div class="space-y-6">
        
        <!-- Header & Back Navigation -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <a href="{{ route('app.courses') }}" class="w-9 h-9 rounded-[6px] bg-surface hover:bg-canvas border border-line flex items-center justify-center text-text-muted hover:text-text transition-colors shadow-2xs">
                    <x-icon name="arrow-left" :size="16" />
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-semibold text-text-muted">{{ $course->code }}</span>
                        <x-ui.badge variant="primary">{{ $course->credits }} SKS</x-ui.badge>
                        @if($class->isLocked())
                            <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-[4px] bg-accent-soft text-accent border border-accent/20">
                                Akan Datang
                            </span>
                        @else
                            <x-ui.badge variant="success">Kelas Aktif</x-ui.badge>
                        @endif
                    </div>
                    <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight mt-1">
                        {{ $course->name }}
                    </h1>
                </div>
            </div>

            <!-- Action Button -->
            <div>
                @if($class->isLocked())
                    <button type="button" disabled class="px-4 py-2 rounded-[6px] bg-canvas border border-line text-text-muted text-xs font-medium inline-flex items-center gap-2 cursor-not-allowed opacity-80 shadow-2xs">
                        <x-icon name="lock" :size="14" class="text-text-muted" />
                        <span>Ruang Kelas Terkunci</span>
                    </button>
                @else
                    <a href="{{ route('app.courses.classroom', $class) }}" class="px-4 py-2 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-2 shadow-xs transition-colors">
                        <span>Masuk Ruang Kelas</span>
                        <x-icon name="chevron-right" :size="14" class="text-white" />
                    </a>
                @endif
            </div>
        </div>

        <!-- Main Layout: 2 Columns (3:1) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
            
            <!-- Left Column: RPS, CPMK, Bobot Penilaian (2 Cols) -->
            <div class="lg:col-span-2 space-y-6">
                
                <!-- Deskripsi & Silabus Perkuliahan -->
                <x-ui.card class="p-6">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-4">
                        <div class="p-2 rounded-[6px] bg-blue-50 text-primary">
                            <x-icon name="book-open" :size="18" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-text">Deskripsi & Rencana Pembelajaran Semester (RPS)</h2>
                            <p class="text-[11px] text-text-muted">Cakupan kompetensi dan materi utama perkuliahan</p>
                        </div>
                    </div>
                    
                    <div class="text-xs text-text leading-relaxed space-y-3">
                        <p>{{ $course->description ?? 'Mata kuliah ini dirancang untuk membekali mahasiswa dengan penguasaan konsep fundamental dan aplikatif di bidang teknologi informasi sesuai dengan kebutuhan industri modern.' }}</p>
                    </div>

                    <!-- Ringkasan Silabus Topik -->
                    <div class="mt-6 pt-5 border-t border-line">
                        <h3 class="text-xs font-semibold text-text mb-3">Struktur Silabus (14 Pertemuan)</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 text-xs">
                            <div class="p-3 rounded-[6px] bg-canvas border border-line/60">
                                <span class="font-bold text-primary block mb-0.5">Paruh Semester 1 (Minggu 1-7)</span>
                                <span class="text-text-muted text-[11px] block">Fondasi teori, paradigma arsitektur, modul dasar, dan studi kasus.</span>
                            </div>
                            <div class="p-3 rounded-[6px] bg-canvas border border-line/60">
                                <span class="font-bold text-accent block mb-0.5">Paruh Semester 2 (Minggu 8-14)</span>
                                <span class="text-text-muted text-[11px] block">Penerapan studi kasus industri, proyek mini tim, evaluasi UTS & UAS.</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Capaian Pembelajaran Mata Kuliah (CPMK) -->
                <x-ui.card class="p-6">
                    <div class="flex items-center justify-between pb-4 border-b border-line mb-4">
                        <div class="flex items-center gap-2.5">
                            <div class="p-2 rounded-[6px] bg-emerald-50 text-emerald-600">
                                <x-icon name="award" :size="18" />
                            </div>
                            <div>
                                <h2 class="text-sm font-bold text-text">Capaian Pembelajaran Mata Kuliah (CPMK)</h2>
                                <p class="text-[11px] text-text-muted">Standar kompetensi OBE yang dinilai selama perkuliahan</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-text-muted">{{ $course->cpmks->count() }} Capaian</span>
                    </div>

                    <div class="divide-y divide-line/70">
                        @forelse($course->cpmks as $cpmk)
                            <div class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-4">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-text">{{ $cpmk->code }}</span>
                                        <span class="text-[10px] font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 px-1.5 py-0.2 rounded">
                                            Bobot {{ (int) $cpmk->weight }}%
                                        </span>
                                    </div>
                                    <p class="text-xs text-text-muted leading-snug">{{ $cpmk->description }}</p>
                                </div>
                            </div>
                        @empty
                            <p class="text-xs text-text-muted py-3">Belum ada data CPMK untuk mata kuliah ini.</p>
                        @endforelse
                    </div>
                </x-ui.card>

                <!-- Bobot Komponen Penilaian -->
                <x-ui.card class="p-6">
                    <div class="flex items-center gap-2.5 pb-4 border-b border-line mb-4">
                        <div class="p-2 rounded-[6px] bg-amber-50 text-amber-600">
                            <x-icon name="bar-chart-2" :size="18" />
                        </div>
                        <div>
                            <h2 class="text-sm font-bold text-text">Komposisi Bobot Penilaian</h2>
                            <p class="text-[11px] text-text-muted">Distribusi persentase komponen nilai akhir mahasiswa</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 text-center">
                        <div class="p-3 rounded-[6px] bg-canvas border border-line">
                            <span class="text-[11px] text-text-muted block">Tugas</span>
                            <span class="text-lg font-bold text-text block mt-0.5">25%</span>
                        </div>
                        <div class="p-3 rounded-[6px] bg-canvas border border-line">
                            <span class="text-[11px] text-text-muted block">Kuis</span>
                            <span class="text-lg font-bold text-text block mt-0.5">15%</span>
                        </div>
                        <div class="p-3 rounded-[6px] bg-canvas border border-line">
                            <span class="text-[11px] text-text-muted block">Presensi</span>
                            <span class="text-lg font-bold text-text block mt-0.5">10%</span>
                        </div>
                        <div class="p-3 rounded-[6px] bg-canvas border border-line">
                            <span class="text-[11px] text-text-muted block">UTS</span>
                            <span class="text-lg font-bold text-text block mt-0.5">25%</span>
                        </div>
                        <div class="p-3 rounded-[6px] bg-canvas border border-line col-span-2 sm:col-span-1">
                            <span class="text-[11px] text-text-muted block">UAS</span>
                            <span class="text-lg font-bold text-text block mt-0.5">25%</span>
                        </div>
                    </div>
                </x-ui.card>

            </div>

            <!-- Right Column: Dosen, Jadwal & Info Tambahan (1 Col) -->
            <div class="space-y-6">
                
                <!-- Dosen Pengampu -->
                <x-ui.card class="p-5">
                    <span class="text-[11px] font-semibold text-text-subtle uppercase tracking-wider block mb-3.5">Dosen Pengampu</span>
                    <div class="flex items-center gap-3.5 pb-4 border-b border-line">
                        <x-ui.avatar :name="$lecturer->name ?? 'Dosen'" :src="$lecturer->avatar_path ?? null" size="lg" />
                        <div class="min-w-0">
                            <h3 class="text-xs font-bold text-text truncate">{{ $lecturer->name ?? 'Dosen Pengampu' }}</h3>
                            <p class="text-[11px] text-text-muted truncate mt-0.5">NIDN: {{ $lecturer->identifier ?? '0012057801' }}</p>
                        </div>
                    </div>
                    <div class="pt-3 text-xs text-text-muted space-y-2">
                        <div class="flex items-center gap-2">
                            <x-icon name="mail" :size="13" class="text-text-subtle" />
                            <span class="truncate">{{ $lecturer->email ?? 'dosen@unus.ac.id' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="map-pin" :size="13" class="text-text-subtle" />
                            <span class="truncate">Gedung Fakultas Ilmu Komputer Lt. 3</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Jadwal & Lokasi -->
                <x-ui.card class="p-5">
                    <span class="text-[11px] font-semibold text-text-subtle uppercase tracking-wider block mb-3.5">Jadwal & Ruang Kuliah</span>
                    <div class="space-y-3 text-xs">
                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-[6px] bg-tile border border-line shrink-0">
                                <x-icon name="calendar" :size="15" class="text-text-muted" />
                            </div>
                            <div>
                                <span class="font-bold text-text block">{{ $class->schedule_day ?? 'Belum Terjadwal' }}</span>
                                <span class="text-text-muted text-[11px] block mt-0.5">{{ substr($class->start_time ?? '08:00', 0, 5) }} - {{ substr($class->end_time ?? '10:00', 0, 5) }} WIB</span>
                            </div>
                        </div>

                        <div class="flex items-start gap-3">
                            <div class="p-2 rounded-[6px] bg-tile border border-line shrink-0">
                                <x-icon name="map-pin" :size="15" class="text-text-muted" />
                            </div>
                            <div>
                                <span class="font-bold text-text block">{{ $class->room ?? 'Lab Komputer' }}</span>
                                <span class="text-text-muted text-[11px] block mt-0.5">Kelas Paralel: {{ $class->section ?? 'IF-3A' }}</span>
                            </div>
                        </div>
                    </div>
                </x-ui.card>

                <!-- Informasi Akademik -->
                <x-ui.card class="p-5">
                    <span class="text-[11px] font-semibold text-text-subtle uppercase tracking-wider block mb-3">Informasi Kurikulum</span>
                    <div class="space-y-2.5 text-xs divide-y divide-line/60">
                        <div class="flex justify-between py-1.5 first:pt-0">
                            <span class="text-text-muted">Semester</span>
                            <span class="font-medium text-text">{{ $class->term?->name ?? 'Semester 3 (Ganjil 2026/2027)' }}</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-text-muted">Beban Kredit</span>
                            <span class="font-medium text-text">{{ $course->credits }} SKS</span>
                        </div>
                        <div class="flex justify-between py-1.5">
                            <span class="text-text-muted">Program Studi</span>
                            <span class="font-medium text-text">S1 Teknik Informatika</span>
                        </div>
                        <div class="flex justify-between py-1.5 last:pb-0">
                            <span class="text-text-muted">Fakultas</span>
                            <span class="font-medium text-text">Ilmu Komputer</span>
                        </div>
                    </div>
                </x-ui.card>

            </div>

        </div>

    </div>

@endsection
