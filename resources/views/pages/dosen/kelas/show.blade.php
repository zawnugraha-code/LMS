@extends('layouts.dosen')

@section('title', $class->course->code . ' ' . $class->section . ' — Arunika Learning System')
@section('page_breadcrumb', 'Kelas Saya / ' . $class->course->code . ' - ' . $class->section)

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'silabus' }">

    <!-- Tombol Kembali -->
    <div>
        <a href="{{ route('dosen.kelas.index') }}" class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-text font-medium transition-colors">
            <x-icon name="arrow-left" :size="14" />
            <span>Kembali ke Daftar Kelas</span>
        </a>
    </div>

    <!-- Header Kelas -->
    <div class="bg-surface border border-line rounded-[10px] p-6 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-start justify-between gap-4">
            <div class="min-w-0 flex-1">
                <div class="flex items-center gap-2.5 flex-wrap mb-2">
                    <span class="px-2.5 py-0.5 text-xs font-bold rounded-full bg-blue-50 text-[#2563EB] border border-blue-200">
                        {{ $class->course->code }}
                    </span>
                    <span class="px-2.5 py-0.5 text-xs font-semibold rounded-full bg-slate-100 text-text border border-line">
                        {{ $class->section }}
                    </span>
                    <x-dosen.status-badge :status="$class->status" />
                </div>

                <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight mb-2">
                    {{ $class->course->name }}
                </h1>

                <p class="text-xs text-text-muted mb-4 max-w-3xl">
                    {{ $class->course->description ?? 'Mata kuliah terdaftar pada kurikulum aktif program studi ' . ($class->course->curriculum?->program?->name ?? 'S1 Teknik Informatika') . '.' }}
                </p>

                <!-- Metadata Row -->
                <div class="flex items-center gap-4 flex-wrap text-xs text-text-muted pt-3 border-t border-line/60">
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="award" :size="14" class="text-navy-700" />
                        <span>{{ $class->course->credits ?? 3 }} SKS</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="calendar" :size="14" class="text-navy-700" />
                        <span>{{ $class->schedule_day }}, {{ substr($class->start_time, 0, 5) }} - {{ substr($class->end_time, 0, 5) }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="map-pin" :size="14" class="text-navy-700" />
                        <span>Ruang {{ $class->room }}</span>
                    </span>
                    <span class="inline-flex items-center gap-1.5">
                        <x-icon name="users" :size="14" class="text-navy-700" />
                        <span>{{ $class->students->count() }} Mahasiswa Terdaftar</span>
                    </span>
                </div>
            </div>

            <!-- Aksi Cepat -->
            <div class="flex items-center gap-2 shrink-0 pt-2 md:pt-0">
                <a 
                    href="{{ route('dosen.materi.index') }}" 
                    class="px-3 py-1.5 rounded-[6px] border border-line hover:border-slate-300 bg-surface hover:bg-slate-50 text-xs font-semibold text-text shadow-2xs transition-colors inline-flex items-center gap-1.5"
                >
                    <x-icon name="folder-open" :size="14" />
                    <span>Materi</span>
                </a>
                <a 
                    href="{{ route('dosen.tugas.index') }}" 
                    class="px-3.5 py-1.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-xs font-semibold text-white shadow-2xs transition-colors inline-flex items-center gap-1.5"
                >
                    <x-icon name="plus" :size="14" />
                    <span>Assessment</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Tab Bar -->
    <div class="border-b border-line flex items-center gap-6 text-xs font-semibold">
        <button 
            @click="activeTab = 'silabus'" 
            :class="activeTab === 'silabus' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-text-muted hover:text-text'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-2"
        >
            <x-icon name="book-open" :size="15" />
            <span>Silabus &amp; Pertemuan ({{ $class->meetings->count() }})</span>
        </button>

        <button 
            @click="activeTab = 'mahasiswa'" 
            :class="activeTab === 'mahasiswa' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-text-muted hover:text-text'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-2"
        >
            <x-icon name="users" :size="15" />
            <span>Daftar Mahasiswa ({{ $class->students->count() }})</span>
        </button>

        <button 
            @click="activeTab = 'komponen'" 
            :class="activeTab === 'komponen' ? 'border-[#2563EB] text-[#2563EB]' : 'border-transparent text-text-muted hover:text-text'"
            class="py-3 border-b-2 transition-colors cursor-pointer flex items-center gap-2"
        >
            <x-icon name="bar-chart-3" :size="15" />
            <span>Komponen Penilaian ({{ $class->assessmentComponents->count() }})</span>
        </button>
    </div>

    <!-- Konten Tab 1: Silabus & Pertemuan -->
    <div x-show="activeTab === 'silabus'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @forelse ($class->meetings as $meeting)
                <div class="bg-surface border border-line rounded-[10px] p-4 shadow-2xs hover:border-slate-300 transition-colors flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2 py-0.5 text-[11px] font-bold rounded-full bg-blue-50 text-[#2563EB] border border-blue-200">
                                Pertemuan {{ $meeting->week }}
                            </span>
                            <span class="text-[11px] text-text-muted">
                                {{ $meeting->date ? \Carbon\Carbon::parse($meeting->date)->locale('id')->isoFormat('D MMM Y') : 'Jadwal Reguler' }}
                            </span>
                        </div>

                        <h3 class="text-xs font-bold text-text mb-1">
                            {{ $meeting->topic ?? 'Materi Pertemuan ke-' . $meeting->week }}
                        </h3>
                        <p class="text-[11px] text-text-muted line-clamp-2 leading-relaxed">
                            {{ $meeting->description ?? 'Rincian capaian pembelajaran mata kuliah dan latihan terstruktur.' }}
                        </p>
                    </div>

                    <!-- Lampiran Materi / Tugas -->
                    <div class="pt-3 mt-3 border-t border-line/60 space-y-1.5">
                        <div class="flex items-center justify-between text-[11px] text-text-muted">
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="folder-open" :size="13" class="text-text-subtle" />
                                <span>{{ $meeting->materials->count() }} Materi Tersedia</span>
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <x-icon name="clipboard-list" :size="13" class="text-text-subtle" />
                                <span>{{ $meeting->assignments->count() }} Assessment</span>
                            </span>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-2">
                    <x-dosen.empty-state 
                        icon="book-open"
                        title="Belum ada silabus pertemuan"
                        description="Pertemuan perkuliahan untuk kelas ini belum disusun atau belum disinkronkan."
                    />
                </div>
            @endforelse
        </div>
    </div>

    <!-- Konten Tab 2: Daftar Mahasiswa -->
    <div x-show="activeTab === 'mahasiswa'" x-cloak class="space-y-4">
        <x-dosen.data-table :headers="[
            'No',
            'NIM',
            'Nama Mahasiswa',
            'Program Studi',
            'Angkatan',
            ['label' => 'Status KRS', 'align' => 'right']
        ]">
            @forelse ($class->students as $idx => $student)
                <tr class="hover:bg-slate-50/60 transition-colors">
                    <td class="px-4 py-3 text-text-muted tabular-nums">{{ $idx + 1 }}</td>
                    <td class="px-4 py-3 font-mono font-medium text-text">{{ $student->identifier ?? '-' }}</td>
                    <td class="px-4 py-3 font-medium text-text">
                        <div class="flex items-center gap-2.5">
                            <img 
                                src="{{ $student->avatar_path ?? '/images/avatar-arya.jpg' }}" 
                                alt="{{ $student->name }}" 
                                class="w-6 h-6 rounded-full object-cover border border-line"
                                onerror="this.onerror=null;this.src='https://ui-avatars.com/api/?name={{ urlencode($student->name) }}&background=E2E8F0&color=1A2E5E';"
                            >
                            <span>{{ $student->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-text-muted">{{ $class->course->curriculum?->program?->name ?? 'S1 Teknik Informatika' }}</td>
                    <td class="px-4 py-3 text-text-muted tabular-nums">2024</td>
                    <td class="px-4 py-3 text-right">
                        <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Aktif
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-xs text-text-muted">
                        Belum ada mahasiswa yang terdaftar di kelas ini.
                    </td>
                </tr>
            @endforelse
        </x-dosen.data-table>
    </div>

    <!-- Konten Tab 3: Komponen Penilaian -->
    <div x-show="activeTab === 'komponen'" x-cloak class="space-y-4">
        <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-sm font-bold text-text">Bobot Komponen Penilaian</h3>
                    <p class="text-xs text-text-muted mt-0.5">Struktur persentase akhir untuk penentuan nilai mutu mahasiswa.</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">
                    <x-icon name="check-circle" :size="13" />
                    <span>Total Bobot: 100%</span>
                </div>
            </div>

            <x-dosen.data-table :headers="['Komponen', 'Jenis Assessment', 'Bobot (%)', 'Visualisasi Bobot']">
                @forelse ($class->assessmentComponents as $comp)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="px-4 py-3 font-semibold text-text uppercase">{{ $comp->jenis }}</td>
                        <td class="px-4 py-3 text-text-muted capitalize">{{ $comp->jenis }} Terstruktur</td>
                        <td class="px-4 py-3 font-bold text-text tabular-nums">{{ $comp->bobot }}%</td>
                        <td class="px-4 py-3 w-1/3">
                            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
                                <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $comp->bobot }}%"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-8 text-center text-xs text-text-muted">
                            Belum ada komponen penilaian yang diatur untuk kelas ini.
                        </td>
                    </tr>
                @endforelse
            </x-dosen.data-table>
        </div>
    </div>

</div>
@endsection
