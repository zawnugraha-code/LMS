@extends('layouts.dosen')

@section('title', 'Matriks Buku Nilai: ' . $class->course?->code . ' ' . $class->section . ' — Portal Dosen Arunika')
@section('page_breadcrumb', 'Buku Nilai')

@section('content')
<div class="space-y-6" x-data="{ modalKomponen: window.location.hash === '#pengaturan' }">
    
    <!-- 1. Header & Navigasi Kembali -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <a 
                href="{{ route('dosen.buku-nilai.index') }}" 
                class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-[#2563EB] font-medium transition-colors mb-2"
            >
                <x-icon name="arrow-left" :size="14" />
                <span>Kembali ke Buku Nilai</span>
            </a>
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-[8px] bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                    <x-icon name="graduation-cap" :size="20" />
                </div>
                <div>
                    <h1 class="text-xl font-bold text-text tracking-tight flex items-center gap-2">
                        <span>{{ $class->course?->code }} - {{ $class->course?->name }}</span>
                        <span class="px-2 py-0.5 rounded-[4px] text-xs font-semibold bg-blue-50 text-[#2563EB] border border-blue-100">
                            {{ $class->section }}
                        </span>
                    </h1>
                    <p class="text-xs text-text-muted mt-0.5">
                        {{ $class->term?->name ?? 'Semester Ganjil 2026/2027' }} • {{ $class->course?->credits ?? 3 }} SKS • {{ $summary['total_mahasiswa'] }} Mahasiswa Terdaftar
                    </p>
                </div>
            </div>
        </div>

        <!-- Action Buttons: Export CSV & Pengaturan Penilaian -->
        <div class="flex items-center gap-2.5">
            <button 
                type="button" 
                @click="modalKomponen = true"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-surface hover:bg-slate-50 border border-line rounded-[6px] text-xs font-medium text-text shadow-xs cursor-pointer transition-colors"
            >
                <x-icon name="sliders" :size="14" class="text-text-muted" />
                <span>Pengaturan Penilaian</span>
            </button>

            <a 
                href="{{ route('dosen.buku-nilai.export', $class->id) }}"
                class="inline-flex items-center gap-1.5 px-3.5 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white rounded-[6px] text-xs font-medium shadow-xs transition-colors"
            >
                <x-icon name="download" :size="14" />
                <span>Ekspor CSV</span>
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-check" :size="16" class="text-emerald-600 shrink-0" />
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-emerald-600 hover:text-emerald-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-200 text-red-800 text-xs px-4 py-3 rounded-[6px] flex items-center justify-between shadow-xs">
            <div class="flex items-center gap-2">
                <x-icon name="circle-alert" :size="16" class="text-red-600 shrink-0" />
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button type="button" @click="$el.parentElement.remove()" class="text-red-600 hover:text-red-900 cursor-pointer">
                <x-icon name="x" :size="14" />
            </button>
        </div>
    @endif

    <!-- 2. 4 Stat Cards Kelas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Stat 1: Rata-rata Kelas -->
        <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Rata-rata Kelas</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ number_format($summary['rata_rata'], 1) }}</span>
                    <span class="px-2 py-0.5 rounded-[4px] text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                        {{ $summary['letter'] }}
                    </span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="bar-chart-2" :size="18" />
            </div>
        </div>

        <!-- Stat 2: Nilai Tertinggi -->
        <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Nilai Tertinggi</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ number_format($summary['nilai_tertinggi'], 1) }}</span>
                    <span class="text-xs text-text-muted font-normal">skor maks</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 border border-emerald-100">
                <x-icon name="trending-up" :size="18" />
            </div>
        </div>

        <!-- Stat 3: Nilai Terendah -->
        <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Nilai Terendah</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ number_format($summary['nilai_terendah'], 1) }}</span>
                    <span class="text-xs text-text-muted font-normal">skor min</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-100">
                <x-icon name="trending-down" :size="18" />
            </div>
        </div>

        <!-- Stat 4: Tingkat Kelulusan -->
        <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted font-normal">Tingkat Kelulusan</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $summary['tingkat_kelulusan'] }}%</span>
                </div>
                <p class="text-[11px] text-text-muted mt-0.5">{{ $summary['lulus_count'] }} dari {{ $summary['total_mahasiswa'] }} mhs lulus</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-purple-50 text-purple-600 flex items-center justify-center shrink-0 border border-purple-100">
                <x-icon name="award" :size="18" />
            </div>
        </div>
    </div>

    <!-- 3. Filter Bar (Status & Pencarian Mahasiswa) -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        
        <!-- Left Filter Tabs -->
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
            <a 
                href="{{ route('dosen.buku-nilai.show', [$class->id, 'status' => 'semua', 'search' => $search]) }}"
                class="px-3 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeStatus === 'semua' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line' }}"
            >
                Semua Mahasiswa ({{ $summary['total_mahasiswa'] }})
            </a>
            <a 
                href="{{ route('dosen.buku-nilai.show', [$class->id, 'status' => 'lulus', 'search' => $search]) }}"
                class="px-3 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeStatus === 'lulus' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line' }}"
            >
                Lulus ({{ $summary['lulus_count'] }})
            </a>
            <a 
                href="{{ route('dosen.buku-nilai.show', [$class->id, 'status' => 'belum_lengkap', 'search' => $search]) }}"
                class="px-3 py-1.5 rounded-[6px] text-xs font-medium transition-colors shrink-0 {{ $activeStatus === 'belum_lengkap' ? 'bg-[#2563EB] text-white shadow-xs' : 'bg-surface text-text-muted hover:text-text border border-line' }}"
            >
                Belum Lengkap ({{ $summary['belum_lengkap_count'] }})
            </a>
        </div>

        <!-- Search Input -->
        <form action="{{ route('dosen.buku-nilai.show', $class->id) }}" method="GET" class="relative sm:w-64">
            <input type="hidden" name="status" value="{{ $activeStatus }}">
            <span class="absolute inset-y-0 left-0 pl-2.5 flex items-center pointer-events-none text-text-muted">
                <x-icon name="search" :size="14" />
            </span>
            <input 
                type="text" 
                name="search" 
                value="{{ $search }}"
                placeholder="Cari NIM atau Nama..." 
                class="w-full pl-8 pr-3 py-1.5 bg-surface border border-line rounded-[6px] text-xs text-text placeholder:text-text-muted/60 focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB] shadow-xs"
            >
        </form>
    </div>

    <!-- 4. Tabel Matriks Nilai Komprehensif -->
    <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
        
        <!-- Table Header Info -->
        <div class="px-5 py-3.5 border-b border-line flex flex-col sm:flex-row sm:items-center justify-between gap-2 bg-slate-50/50">
            <div class="flex items-center gap-2">
                <x-icon name="table" :size="16" class="text-text-muted" />
                <h2 class="text-xs font-bold text-text uppercase tracking-wider">Matriks Nilai Akhir OBE</h2>
            </div>
            <div class="text-[11px] text-text-muted flex items-center gap-3">
                @foreach ($components as $c)
                    <span>{{ ucfirst($c->jenis) }}: <strong class="text-text">{{ $c->bobot }}%</strong></span>
                @endforeach
            </div>
        </div>

        <!-- Table Responsive -->
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/70 border-b border-line text-[11px] font-semibold text-text-muted uppercase tracking-wider">
                        <th class="py-3 px-3 text-center w-12">No</th>
                        <th class="py-3 px-3 w-28">NIM</th>
                        <th class="py-3 px-4 min-w-[180px]">Nama Mahasiswa</th>
                        <th class="py-3 px-3 text-center w-24">Tugas ({{ $components->where('jenis', 'tugas')->first()?->bobot ?? 30 }}%)</th>
                        <th class="py-3 px-3 text-center w-24">Kuis ({{ $components->where('jenis', 'kuis')->first()?->bobot ?? 20 }}%)</th>
                        <th class="py-3 px-3 text-center w-24">UTS ({{ $components->where('jenis', 'uts')->first()?->bobot ?? 25 }}%)</th>
                        <th class="py-3 px-3 text-center w-24">UAS ({{ $components->where('jenis', 'uas')->first()?->bobot ?? 25 }}%)</th>
                        <th class="py-3 px-3 text-center w-24">Nilai Akhir</th>
                        <th class="py-3 px-3 text-center w-20">Huruf</th>
                        <th class="py-3 px-4 text-center w-28">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($students as $row)
                        <tr class="hover:bg-slate-50/60 transition-colors {{ $row['has_pending'] ? 'bg-amber-50/20' : '' }}">
                            
                            <!-- No -->
                            <td class="py-3 px-3 text-center text-text-muted font-medium">
                                {{ $row['no'] }}
                            </td>

                            <!-- NIM -->
                            <td class="py-3 px-3 font-mono text-[11px] text-text-muted">
                                {{ $row['nim'] }}
                            </td>

                            <!-- Nama Mahasiswa -->
                            <td class="py-3 px-4">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-6 h-6 rounded-full bg-slate-200 text-slate-700 flex items-center justify-center font-bold text-[10px] shrink-0 overflow-hidden">
                                        @if (!empty($row['avatar_path']))
                                            <img src="{{ $row['avatar_path'] }}" alt="{{ $row['name'] }}" class="w-full h-full object-cover">
                                        @else
                                            {{ strtoupper(substr($row['name'], 0, 1)) }}
                                        @endif
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-semibold text-text text-xs truncate leading-snug">
                                            {{ $row['name'] }}
                                        </p>
                                    </div>
                                </div>
                            </td>

                            <!-- Komponen: Tugas -->
                            <td class="py-3 px-3 text-center font-mono text-xs text-text">
                                {{ number_format($row['scores']['tugas'], 1) }}
                            </td>

                            <!-- Komponen: Kuis -->
                            <td class="py-3 px-3 text-center font-mono text-xs text-text">
                                {{ number_format($row['scores']['kuis'], 1) }}
                            </td>

                            <!-- Komponen: UTS -->
                            <td class="py-3 px-3 text-center font-mono text-xs text-text">
                                {{ number_format($row['scores']['uts'], 1) }}
                            </td>

                            <!-- Komponen: UAS -->
                            <td class="py-3 px-3 text-center font-mono text-xs text-text">
                                {{ number_format($row['scores']['uas'], 1) }}
                            </td>

                            <!-- Nilai Akhir -->
                            <td class="py-3 px-3 text-center font-bold text-text text-xs bg-slate-50/40">
                                {{ number_format($row['final_score'], 1) }}
                            </td>

                            <!-- Huruf Mutu -->
                            <td class="py-3 px-3 text-center">
                                <span class="px-2 py-0.5 rounded-[4px] text-[10px] font-bold {{ in_array($row['letter'], ['A', 'A-']) ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : (in_array($row['letter'], ['B', 'B+', 'B-']) ? 'bg-blue-50 text-[#2563EB] border border-blue-200' : (in_array($row['letter'], ['C', 'C+']) ? 'bg-amber-50 text-amber-700 border border-amber-200' : 'bg-red-50 text-red-700 border border-red-200')) }}">
                                    {{ $row['letter'] }}
                                </span>
                            </td>

                            <!-- Status -->
                            <td class="py-3 px-4 text-center">
                                @if ($row['status'] === 'Lulus')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <x-icon name="check" :size="10" />
                                        <span>Lulus</span>
                                    </span>
                                @elseif ($row['status'] === 'Belum Lengkap')
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200" title="Terdapat tugas/ujian yang belum dinilai dosen">
                                        <x-icon name="clock" :size="10" />
                                        <span>Belum Lengkap</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-50 text-red-700 border border-red-200">
                                        <x-icon name="alert-circle" :size="10" />
                                        <span>Remidi</span>
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="py-12 text-center text-text-muted">
                                <x-icon name="users" :size="28" class="mx-auto mb-2 text-text-muted/60" />
                                <p class="font-medium text-xs">Tidak ada mahasiswa yang sesuai dengan filter pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Table Footer -->
        <div class="px-5 py-3 border-t border-line flex items-center justify-between text-xs text-text-muted bg-slate-50/30">
            <p>Menampilkan {{ count($students) }} mahasiswa</p>
            <p class="text-[11px]">Nilai Akhir dihitung otomatis: &sum; (Nilai Komponen &times; Bobot / 100)</p>
        </div>
    </div>

    <!-- 5. Modal Pengaturan Komponen Penilaian (Alpine.js) -->
    <div 
        x-show="modalKomponen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-black/50 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.away="modalKomponen = false"
            x-data="{
                tugas: {{ $components->where('jenis', 'tugas')->first()?->bobot ?? 30 }},
                kuis: {{ $components->where('jenis', 'kuis')->first()?->bobot ?? 20 }},
                uts: {{ $components->where('jenis', 'uts')->first()?->bobot ?? 25 }},
                uas: {{ $components->where('jenis', 'uas')->first()?->bobot ?? 25 }},
                get total() {
                    return (parseInt(this.tugas) || 0) + 
                           (parseInt(this.kuis) || 0) + 
                           (parseInt(this.uts) || 0) + 
                           (parseInt(this.uas) || 0);
                },
                get isValid() {
                    return this.total === 100;
                }
            }"
            class="bg-surface border border-line rounded-[8px] max-w-md w-full shadow-lg overflow-hidden animate-in fade-in zoom-in-95 duration-150"
        >
            <!-- Modal Header -->
            <div class="px-5 py-4 border-b border-line flex items-center justify-between">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-[6px] bg-blue-50 text-[#2563EB] flex items-center justify-center border border-blue-100">
                        <x-icon name="sliders" :size="16" />
                    </div>
                    <div>
                        <h3 class="text-sm font-bold text-text tracking-tight">Pengaturan Penilaian</h3>
                        <p class="text-[11px] text-text-muted">Kelas {{ $class->course?->code }} - {{ $class->section }}</p>
                    </div>
                </div>
                <button 
                    type="button" 
                    @click="modalKomponen = false"
                    class="text-text-muted hover:text-text cursor-pointer"
                >
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <!-- Modal Form -->
            <form action="{{ route('dosen.buku-nilai.components.update', $class->id) }}" method="POST">
                @csrf
                <div class="p-5 space-y-4 text-xs">
                    
                    <p class="text-text-muted leading-relaxed">
                        Atur persentase bobot setiap komponen penilaian kelas. Total bobot penilaian secara keseluruhan <strong>wajib tepat 100%</strong> sesuai standar kurikulum OBE.
                    </p>

                    <!-- Input 4 Komponen -->
                    <div class="space-y-3 bg-slate-50/70 p-3.5 rounded-[6px] border border-line">
                        
                        <!-- Tugas -->
                        <div class="flex items-center justify-between gap-3">
                            <label for="comp_tugas" class="font-medium text-text flex items-center gap-1.5">
                                <span>Tugas Terstruktur</span>
                            </label>
                            <div class="flex items-center gap-1.5 w-24">
                                <input 
                                    type="number" 
                                    id="comp_tugas"
                                    name="components[tugas]" 
                                    x-model="tugas" 
                                    min="0" 
                                    max="100" 
                                    required
                                    class="w-full text-right px-2.5 py-1.5 bg-surface border border-line rounded-[4px] font-mono text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                                >
                                <span class="text-text-muted font-bold">%</span>
                            </div>
                        </div>

                        <!-- Kuis -->
                        <div class="flex items-center justify-between gap-3">
                            <label for="comp_kuis" class="font-medium text-text flex items-center gap-1.5">
                                <span>Kuis / Praktikum</span>
                            </label>
                            <div class="flex items-center gap-1.5 w-24">
                                <input 
                                    type="number" 
                                    id="comp_kuis"
                                    name="components[kuis]" 
                                    x-model="kuis" 
                                    min="0" 
                                    max="100" 
                                    required
                                    class="w-full text-right px-2.5 py-1.5 bg-surface border border-line rounded-[4px] font-mono text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                                >
                                <span class="text-text-muted font-bold">%</span>
                            </div>
                        </div>

                        <!-- UTS -->
                        <div class="flex items-center justify-between gap-3">
                            <label for="comp_uts" class="font-medium text-text flex items-center gap-1.5">
                                <span>Ujian Tengah Semester (UTS)</span>
                            </label>
                            <div class="flex items-center gap-1.5 w-24">
                                <input 
                                    type="number" 
                                    id="comp_uts"
                                    name="components[uts]" 
                                    x-model="uts" 
                                    min="0" 
                                    max="100" 
                                    required
                                    class="w-full text-right px-2.5 py-1.5 bg-surface border border-line rounded-[4px] font-mono text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                                >
                                <span class="text-text-muted font-bold">%</span>
                            </div>
                        </div>

                        <!-- UAS -->
                        <div class="flex items-center justify-between gap-3">
                            <label for="comp_uas" class="font-medium text-text flex items-center gap-1.5">
                                <span>Ujian Akhir Semester (UAS)</span>
                            </label>
                            <div class="flex items-center gap-1.5 w-24">
                                <input 
                                    type="number" 
                                    id="comp_uas"
                                    name="components[uas]" 
                                    x-model="uas" 
                                    min="0" 
                                    max="100" 
                                    required
                                    class="w-full text-right px-2.5 py-1.5 bg-surface border border-line rounded-[4px] font-mono text-xs text-text focus:outline-hidden focus:border-[#2563EB] focus:ring-1 focus:ring-[#2563EB]"
                                >
                                <span class="text-text-muted font-bold">%</span>
                            </div>
                        </div>

                    </div>

                    <!-- Total Live Indicator -->
                    <div 
                        class="p-3 rounded-[6px] border flex items-center justify-between"
                        :class="isValid ? 'bg-emerald-50/80 border-emerald-200 text-emerald-800' : 'bg-red-50/80 border-red-200 text-red-800'"
                    >
                        <div class="flex items-center gap-2">
                            <x-icon ::name="isValid ? 'check-circle' : 'alert-circle'" :size="16" />
                            <span class="font-semibold" x-text="isValid ? 'Total Bobot: 100% (Sesuai Standar OBE)' : 'Total Bobot: ' + total + '% (Harus tepat 100%)'"></span>
                        </div>
                        <span class="font-mono font-bold" x-text="total + '%'"></span>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-5 py-3 border-t border-line bg-slate-50/50 flex items-center justify-end gap-2.5">
                    <button 
                        type="button" 
                        @click="modalKomponen = false"
                        class="px-3.5 py-2 bg-surface hover:bg-slate-100 border border-line rounded-[6px] text-xs font-medium text-text transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        :disabled="!isValid"
                        :class="isValid ? 'bg-[#1A2E5E] hover:bg-[#152449] cursor-pointer' : 'bg-slate-300 text-slate-500 cursor-not-allowed'"
                        class="px-4 py-2 text-white rounded-[6px] text-xs font-medium transition-colors shadow-xs"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
