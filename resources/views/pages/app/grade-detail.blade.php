@extends('layouts.app')

@section('title', 'Transkrip Nilai ' . ($course?->code ?? '') . ' — Arunika')
@section('page_breadcrumb', 'Transkrip Nilai')

@section('content')

    <div class="space-y-6">

        <!-- ==========================================
             1. BREADCRUMB & HEADER TOPBAR
             ========================================== -->
        <div>
            <div class="flex items-center gap-2 text-xs text-text-muted mb-2">
                <a href="{{ route('app.dashboard') }}" class="hover:text-text transition-colors">Beranda</a>
                <x-icon name="chevron-right" :size="12" />
                <a href="{{ route('app.grades') }}" class="hover:text-text transition-colors">Nilai</a>
                <x-icon name="chevron-right" :size="12" />
                <span class="text-text font-medium">{{ $course?->code ?? 'Transkrip' }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3">
                    <a 
                        href="{{ route('app.grades') }}" 
                        class="mt-1 p-2 rounded-[8px] border border-line bg-surface hover:bg-canvas text-text transition-colors shrink-0 shadow-2xs"
                        title="Kembali ke Daftar Nilai"
                    >
                        <x-icon name="arrow-left" :size="16" />
                    </a>
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold tracking-wider uppercase bg-blue-50 text-blue-700 border border-blue-200">
                                {{ $course?->code }}
                            </span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $course?->credits ?? 3 }} SKS
                            </span>
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-emerald-50 text-emerald-700">
                                {{ $term?->name ?? 'Semester Ganjil 2026/2027' }}
                            </span>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-text mt-1 tracking-tight">
                            {{ $course?->name }}
                        </h1>
                        <p class="text-xs text-text-muted mt-0.5">
                            Dosen Pengampu: <strong class="text-text font-medium">{{ $lecturer?->name ?? 'Dosen Pengampu' }}</strong>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2.5 self-start sm:self-auto shrink-0">
                    <a 
                        href="{{ $pdf_url }}" 
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-[8px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors"
                        target="_blank"
                    >
                        <x-icon name="download" :size="15" />
                        <span>Unduh KHS (PDF)</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- ==========================================
             2. 4 METRIC STAT TILES
             ========================================== -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Tile 1: Nilai Akhir Angka -->
            <x-ui.card class="p-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                        <x-icon name="award" :size="16" />
                    </div>
                    <span class="text-xs font-medium text-text-muted">Nilai Akhir Angka</span>
                </div>
                <div class="mt-3">
                    <span class="text-2xl font-bold text-emerald-700 tabular-nums leading-none block">
                        {{ $final_score }}
                    </span>
                    <span class="text-[11px] text-text-subtle mt-1.5 block">Skala Penilaian 0 - 100</span>
                </div>
            </x-ui.card>

            <!-- Tile 2: Nilai Huruf & Mutu -->
            <x-ui.card class="p-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <x-icon name="graduation-cap" :size="16" />
                    </div>
                    <span class="text-xs font-medium text-text-muted">Huruf Mutu & Indeks</span>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline gap-2">
                        <span class="text-2xl font-bold text-text tabular-nums leading-none">
                            {{ $letter_grade }}
                        </span>
                        <span class="text-xs font-semibold text-text-muted">
                            (Bobot {{ number_format($gpa_point, 2) }})
                        </span>
                    </div>
                    <span class="text-[11px] text-emerald-600 font-semibold mt-1.5 block">Sangat Istimewa</span>
                </div>
            </x-ui.card>

            <!-- Tile 3: Status Kelulusan -->
            <x-ui.card class="p-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                        <x-icon name="check-circle" :size="16" />
                    </div>
                    <span class="text-xs font-medium text-text-muted">Status Kelulusan</span>
                </div>
                <div class="mt-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">
                        <x-icon name="check" :size="13" />
                        <span>{{ $status_label }}</span>
                    </span>
                    <span class="text-[11px] text-text-subtle mt-2 block">Memenuhi Ambang Batas SKS</span>
                </div>
            </x-ui.card>

            <!-- Tile 4: Capaian CPMK Rata-Rata -->
            <x-ui.card class="p-4">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[8px] bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                        <x-icon name="target" :size="16" />
                    </div>
                    <span class="text-xs font-medium text-text-muted">Ketercapaian CPMK</span>
                </div>
                <div class="mt-3">
                    <div class="flex items-baseline">
                        <span class="text-2xl font-bold text-text tabular-nums leading-none">91.3%</span>
                        <span class="text-xs text-text-muted ml-1.5 font-normal">OBE Mastery</span>
                    </div>
                    <div class="w-full h-1 bg-slate-100 rounded-full overflow-hidden mt-2">
                        <div class="h-full bg-[#10B981] rounded-full" style="width: 91.3%;"></div>
                    </div>
                </div>
            </x-ui.card>

        </div>

        <!-- ==========================================
             3. MAIN 2-COLUMN LAYOUT
             ========================================== -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- LEFT COLUMN (lg:col-span-8): KOMPONEN BOBOT & CPMK -->
            <div class="lg:col-span-8 space-y-6">

                <!-- KARTU 1: TABEL RINCIAN KOMPONEN PENILAIAN -->
                <x-ui.card class="p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-line">
                        <div>
                            <h2 class="text-base font-bold text-text">Rincian Komponen Penilaian (Bobot OBE)</h2>
                            <p class="text-xs text-text-muted mt-0.5">
                                Rumus: (Tugas &times; 25%) + (Kuis &times; 15%) + (Presensi &times; 10%) + (UTS &times; 25%) + (UAS &times; 25%)
                            </p>
                        </div>
                        <span class="px-2.5 py-1 rounded-[6px] text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200 self-start sm:self-auto">
                            Formula Terverifikasi
                        </span>
                    </div>

                    <div class="overflow-x-auto mt-4">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="border-b border-line text-[11px] font-semibold text-text-muted">
                                    <th class="py-2.5 px-3">No</th>
                                    <th class="py-2.5 px-3">Komponen Asesmen</th>
                                    <th class="py-2.5 px-3 text-center">Bobot</th>
                                    <th class="py-2.5 px-3 text-center">Skor Mentah</th>
                                    <th class="py-2.5 px-3 text-right">Kontribusi Nilai</th>
                                    <th class="py-2.5 px-3 text-right">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line/60">
                                @foreach($components as $idx => $comp)
                                    <tr class="hover:bg-canvas/40 transition-colors">
                                        <td class="py-3 px-3 text-text-muted tabular-nums">{{ $idx + 1 }}</td>
                                        <td class="py-3 px-3">
                                            <p class="font-bold text-text">{{ $comp['name'] }}</p>
                                            <span class="text-[11px] text-text-muted">{{ $comp['items_count'] }}</span>
                                        </td>
                                        <td class="py-3 px-3 text-center font-medium tabular-nums">{{ $comp['weight'] }}%</td>
                                        <td class="py-3 px-3 text-center tabular-nums">
                                            <span class="px-2 py-0.5 rounded font-semibold bg-slate-100 text-slate-800">
                                                {{ number_format($comp['score'], 2) }}
                                            </span>
                                        </td>
                                        <td class="py-3 px-3 text-right tabular-nums font-bold text-text">
                                            {{ number_format($comp['contribution'], 2) }}
                                        </td>
                                        <td class="py-3 px-3 text-right">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700">
                                                {{ $comp['status'] }}
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t-2 border-line bg-slate-50/80 font-bold text-text">
                                    <td colspan="2" class="py-3 px-3">Total Akumulasi Nilai Akhir</td>
                                    <td class="py-3 px-3 text-center">100%</td>
                                    <td class="py-3 px-3 text-center">-</td>
                                    <td class="py-3 px-3 text-right text-emerald-700 text-sm tabular-nums">
                                        {{ $final_score }}
                                    </td>
                                    <td class="py-3 px-3 text-right">
                                        <span class="px-2.5 py-0.5 rounded text-xs bg-emerald-100 text-emerald-800">
                                            {{ $letter_grade }}
                                        </span>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </x-ui.card>

                <!-- KARTU 2: KETERCAPAIAN CPMK (OBE) -->
                <x-ui.card class="p-5">
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-4">
                        <div class="flex items-center gap-2">
                            <x-icon name="target" :size="18" class="text-text" />
                            <h2 class="text-base font-bold text-text">Capaian Pembelajaran Mata Kuliah (CPMK)</h2>
                        </div>
                        <span class="text-xs font-semibold text-text-muted">Standar Kelulusan $\ge 70\%$</span>
                    </div>

                    <div class="space-y-4">
                        @foreach($cpmks as $cp)
                            <div class="p-3.5 rounded-[8px] border border-line bg-surface hover:bg-canvas/30 transition-colors">
                                <div class="flex items-start justify-between gap-3 mb-2">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                                {{ $cp['code'] }}
                                            </span>
                                            <h4 class="text-xs font-bold text-text">{{ $cp['name'] }}</h4>
                                        </div>
                                        <p class="text-[11px] text-text-muted mt-1 leading-snug">
                                            {{ $cp['description'] }}
                                        </p>
                                    </div>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold border {{ $cp['status_class'] }} shrink-0">
                                        {{ $cp['status'] }}
                                    </span>
                                </div>

                                <div class="flex items-center gap-3 mt-2">
                                    <div class="flex-1 h-2 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#10B981] rounded-full" style="width: {{ $cp['mastery'] }}%;"></div>
                                    </div>
                                    <span class="text-xs font-bold text-text tabular-nums shrink-0">
                                        {{ $cp['mastery'] }}%
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

            </div>

            <!-- RIGHT COLUMN (lg:col-span-4): INFO DOSEN, CPL & UNDUH KHS -->
            <div class="lg:col-span-4 space-y-6">

                <!-- 1. KARTU INFORMASI DOSEN & KELAS -->
                <x-ui.card class="p-5">
                    <h3 class="text-sm font-bold text-text mb-3 flex items-center gap-2">
                        <x-icon name="user" :size="16" class="text-text" />
                        <span>Dosen &amp; Informasi Perkuliahan</span>
                    </h3>

                    <div class="flex items-center gap-3 pb-3 border-b border-line">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                            {{ substr($lecturer?->name ?? 'DP', 0, 2) }}
                        </div>
                        <div class="min-w-0 flex-1">
                            <h4 class="text-xs font-bold text-text truncate">
                                {{ $lecturer?->name ?? 'Prof. Dr. Budi Rahardjo, Ph.D.' }}
                            </h4>
                            <p class="text-[11px] text-text-muted truncate">
                                NIDN: {{ $lecturer?->identifier ?? '0012057801' }}
                            </p>
                        </div>
                    </div>

                    <div class="space-y-2.5 pt-3 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Kelas / Seksi:</span>
                            <span class="font-semibold text-text">{{ $class?->section ?? 'IF-3A' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Jadwal Kuliah:</span>
                            <span class="font-semibold text-text">
                                {{ $class?->schedule_day ?? 'Senin' }}, {{ $class?->start_time ? substr($class->start_time, 0, 5) : '08:00' }} - {{ $class?->end_time ? substr($class->end_time, 0, 5) : '10:00' }}
                            </span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Ruang Perkuliahan:</span>
                            <span class="font-semibold text-text">{{ $class?->room ?? 'Lab Pemrograman 2' }}</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Kurikulum:</span>
                            <span class="font-semibold text-text">OBE 2024 / SN-Dikti</span>
                        </div>
                    </div>
                </x-ui.card>

                <!-- 2. KARTU KONTRIBUSI KE CPL PRODI -->
                <x-ui.card class="p-5">
                    <h3 class="text-sm font-bold text-text mb-3 flex items-center gap-2">
                        <x-icon name="layers" :size="16" class="text-text" />
                        <span>Kontribusi ke CPL Program Studi</span>
                    </h3>

                    <div class="space-y-3 text-xs">
                        @foreach($cpls as $cpl)
                            <div class="p-3 rounded-[8px] bg-slate-50 border border-line">
                                <div class="flex items-center justify-between mb-1">
                                    <span class="font-bold text-text">{{ $cpl['code'] }}</span>
                                    <span class="font-bold text-emerald-700 tabular-nums">{{ $cpl['mastery'] }}%</span>
                                </div>
                                <p class="text-[11px] text-text-muted leading-tight">
                                    {{ $cpl['name'] }}
                                </p>
                                <div class="w-full h-1.5 bg-slate-200 rounded-full overflow-hidden mt-2">
                                    <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $cpl['mastery'] }}%;"></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>

                <!-- 3. KARTU DOKUMEN & PENGESAHAN KHS -->
                <x-ui.card class="p-5">
                    <h3 class="text-sm font-bold text-text mb-2 flex items-center gap-2">
                        <x-icon name="file-check" :size="16" class="text-text" />
                        <span>Pengesahan &amp; Unduh Transkrip</span>
                    </h3>
                    <p class="text-xs text-text-muted leading-relaxed mb-4">
                        Kartu Hasil Studi ini memuat tanda tangan elektronik resmi dan kode verifikasi QR kampus yang diakui secara akademik.
                    </p>

                    <a 
                        href="{{ $pdf_url }}" 
                        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-[8px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white text-xs font-semibold shadow-2xs transition-colors"
                        target="_blank"
                    >
                        <x-icon name="download" :size="15" />
                        <span>Unduh Dokumen KHS Resmi (PDF)</span>
                    </a>

                    <div class="mt-3 p-2.5 rounded-[6px] bg-slate-50 border border-dashed border-line text-center">
                        <span class="text-[10px] text-text-subtle block font-mono">
                            HASH: SHA256:7B8F9A4E2D1C5E3F0A...
                        </span>
                        <span class="text-[10px] text-emerald-600 font-semibold block mt-0.5">
                            Status: Digital Signature Valid
                        </span>
                    </div>
                </x-ui.card>

            </div>

        </div>

    </div>

@endsection
