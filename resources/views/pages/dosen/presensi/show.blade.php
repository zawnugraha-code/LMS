@extends('layouts.dosen')

@section('title', 'Detail Presensi Kelas ' . ($class->course?->code ?? '') . ' — Portal Dosen Arunika')
@section('page_breadcrumb', 'Detail Presensi')

@section('content')
<div class="space-y-6" x-data="{ activeTab: 'matriks' }">
    
    <!-- 1. Header Halaman & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
                <a href="{{ route('dosen.presensi.index') }}" class="hover:text-text transition-colors flex items-center gap-1">
                    <x-icon name="arrow-left" :size="12" />
                    <span>Kembali ke Presensi</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold text-text tracking-tight flex items-center gap-3">
                <span>{{ $class->course?->name }}</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-50 text-[#2563EB] border border-blue-200">
                    {{ $class->section }}
                </span>
            </h1>
            <p class="text-xs sm:text-[13px] text-text-muted mt-0.5">
                {{ $class->course?->code }} • {{ $class->schedule_day }}, {{ substr($class->start_time ?? '08:00', 0, 5) }} - {{ substr($class->end_time ?? '10:00', 0, 5) }} • Ruang {{ $class->room }}
            </p>
        </div>

        <!-- Tombol Aksi Kanan -->
        <div class="flex items-center gap-2.5">
            <a 
                href="{{ route('dosen.presensi.export', $class->id) }}" 
                class="h-9 px-3.5 rounded-[6px] bg-white border border-line text-text hover:bg-slate-50 text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors"
            >
                <x-icon name="download" :size="14" />
                <span>Ekspor CSV</span>
            </a>

            <a 
                href="{{ route('dosen.presensi.input', ['class_id' => $class->id]) }}" 
                class="h-9 px-4 rounded-[6px] bg-[#1A2E5E] hover:bg-[#2C4A8F] text-white text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors"
            >
                <x-icon name="plus" :size="14" />
                <span>Input Presensi</span>
            </a>
        </div>
    </div>

    <!-- 2. 4 Stat Cards Kelas -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted">Rata-rata Kehadiran</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['avg_rate'] }}%</span>
                </div>
                <div class="w-28 h-1.5 bg-slate-100 rounded-full overflow-hidden mt-2">
                    <div class="h-full bg-[#2563EB] rounded-full" style="width: {{ $stats['avg_rate'] }}%"></div>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="chart-pie" :size="18" />
            </div>
        </div>

        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted">Pertemuan Terlaksana</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['meetings_held'] }}</span>
                    <span class="text-xs text-text-muted">/ 16 sesi</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="calendar-check" :size="18" />
            </div>
        </div>

        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted">Mahasiswa Terdaftar</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['total_students'] }}</span>
                    <span class="text-xs text-text-muted">mahasiswa</span>
                </div>
            </div>
            <div class="w-10 h-10 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center shrink-0 border border-blue-100">
                <x-icon name="users" :size="18" />
            </div>
        </div>

        <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs flex items-center justify-between">
            <div class="min-w-0">
                <p class="text-xs text-text-muted">Perlu Perhatian</p>
                <div class="mt-1 flex items-baseline gap-2">
                    <span class="text-2xl font-bold text-text tracking-tight">{{ $stats['under_threshold_count'] }}</span>
                    <span class="text-xs text-text-muted">mhs &lt; 75%</span>
                </div>
                <p class="text-[10px] text-text-subtle mt-1">Batas syarat ujian 75%</p>
            </div>
            <div class="w-10 h-10 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 border border-amber-200">
                <x-icon name="triangle-alert" :size="18" />
            </div>
        </div>

    </div>

    <!-- 3. Tab Navigasi: Matriks Kehadiran vs Riwayat Pertemuan -->
    <div class="border-b border-line flex items-center gap-6 text-xs font-semibold">
        <button 
            type="button" 
            @click="activeTab = 'matriks'" 
            class="py-3 relative transition-colors cursor-pointer"
            :class="activeTab === 'matriks' ? 'text-[#2563EB]' : 'text-text-muted hover:text-text'"
        >
            <span>Matriks Kehadiran Mahasiswa</span>
            <span 
                x-show="activeTab === 'matriks'" 
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#2563EB] rounded-full"
            ></span>
        </button>

        <button 
            type="button" 
            @click="activeTab = 'riwayat'" 
            class="py-3 relative transition-colors cursor-pointer"
            :class="activeTab === 'riwayat' ? 'text-[#2563EB]' : 'text-text-muted hover:text-text'"
        >
            <span>Riwayat per Pertemuan (1–16)</span>
            <span 
                x-show="activeTab === 'riwayat'" 
                class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#2563EB] rounded-full"
            ></span>
        </button>
    </div>

    <!-- TAB 1: Matriks Kehadiran Mahasiswa -->
    <div x-show="activeTab === 'matriks'" class="space-y-4">
        
        <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
            
            <div class="p-4 border-b border-line flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-3">
                    <span class="font-bold text-text">Legenda Status:</span>
                    <span class="inline-flex items-center gap-1 text-[11px]"><span class="w-5 h-5 rounded-[4px] bg-emerald-50 text-emerald-700 font-bold border border-emerald-200 flex items-center justify-center text-[10px]">H</span> Hadir</span>
                    <span class="inline-flex items-center gap-1 text-[11px]"><span class="w-5 h-5 rounded-[4px] bg-amber-50 text-amber-700 font-bold border border-amber-200 flex items-center justify-center text-[10px]">I</span> Izin</span>
                    <span class="inline-flex items-center gap-1 text-[11px]"><span class="w-5 h-5 rounded-[4px] bg-purple-50 text-purple-700 font-bold border border-purple-200 flex items-center justify-center text-[10px]">S</span> Sakit</span>
                    <span class="inline-flex items-center gap-1 text-[11px]"><span class="w-5 h-5 rounded-[4px] bg-red-50 text-red-700 font-bold border border-red-200 flex items-center justify-center text-[10px]">A</span> Tidak Hadir (Alfa)</span>
                    <span class="inline-flex items-center gap-1 text-[11px]"><span class="w-5 h-5 rounded-[4px] bg-slate-50 text-slate-400 font-bold border border-slate-200 flex items-center justify-center text-[10px]">-</span> Belum Terlaksana</span>
                </div>
            </div>

            <!-- Matriks Table with horizontal scroll -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50 border-b border-line text-text-muted font-medium text-[11px]">
                            <th class="py-3 px-3 w-10 text-center sticky left-0 bg-slate-50 z-10">No</th>
                            <th class="py-3 px-3 min-w-[90px] sticky left-10 bg-slate-50 z-10">NIM</th>
                            <th class="py-3 px-4 min-w-[180px] sticky left-32 bg-slate-50 z-10 border-r border-line">Nama Mahasiswa</th>
                            
                            <!-- Pertemuan 1 - 16 columns -->
                            @foreach ($meetings as $m)
                                <th class="py-3 px-1.5 text-center w-8">
                                    <span class="block font-bold">P{{ $m->week }}</span>
                                </th>
                            @endforeach

                            <th class="py-3 px-3 text-center w-20 border-l border-line">Hadir/Sesi</th>
                            <th class="py-3 px-3 text-center w-16">%</th>
                            <th class="py-3 px-4 text-center w-36">Kelayakan Ujian</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/60">
                        @foreach ($matrix as $idx => $row)
                            <tr class="hover:bg-slate-50/50 transition-colors">
                                <!-- No -->
                                <td class="py-2.5 px-3 text-center text-text-muted font-medium sticky left-0 bg-surface z-10">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- NIM -->
                                <td class="py-2.5 px-3 font-mono text-text-muted text-[11px] sticky left-10 bg-surface z-10">
                                    {{ $row['identifier'] }}
                                </td>

                                <!-- Nama Mahasiswa -->
                                <td class="py-2.5 px-4 font-semibold text-text sticky left-32 bg-surface z-10 border-r border-line">
                                    {{ $row['name'] }}
                                </td>

                                <!-- Sesi Pertemuan 1 - 16 -->
                                @foreach ($meetings as $m)
                                    @php
                                        $rec = $row['records'][$m->week] ?? null;
                                        $status = $rec ? $rec['status'] : null;
                                    @endphp
                                    <td class="py-2.5 px-1.5 text-center">
                                        @if ($status === 'hadir')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-[3px] bg-emerald-50 text-emerald-700 font-bold text-[10px] border border-emerald-200" title="Hadir">
                                                H
                                            </span>
                                        @elseif ($status === 'izin')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-[3px] bg-amber-50 text-amber-700 font-bold text-[10px] border border-amber-200" title="Izin">
                                                I
                                            </span>
                                        @elseif ($status === 'sakit')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-[3px] bg-purple-50 text-purple-700 font-bold text-[10px] border border-purple-200" title="Sakit">
                                                S
                                            </span>
                                        @elseif ($status === 'tidak_hadir')
                                            <span class="inline-flex items-center justify-center w-5 h-5 rounded-[3px] bg-red-50 text-red-700 font-bold text-[10px] border border-red-200" title="Tidak Hadir">
                                                A
                                            </span>
                                        @else
                                            <span class="inline-flex items-center justify-center w-5 h-5 text-slate-300 font-medium text-[10px]">
                                                -
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                <!-- Hadir / Sesi -->
                                <td class="py-2.5 px-3 text-center font-medium text-text border-l border-line">
                                    {{ $row['hadir_count'] }} / {{ $row['total_recorded'] }}
                                </td>

                                <!-- % Kehadiran -->
                                <td class="py-2.5 px-3 text-center font-bold {{ $row['attendance_rate'] >= 75 ? 'text-text' : 'text-red-600' }}">
                                    {{ $row['attendance_rate'] }}%
                                </td>

                                <!-- Kelayakan Ujian -->
                                <td class="py-2.5 px-4 text-center">
                                    @if ($row['is_eligible'])
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Memenuhi Syarat
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                            Peringatan (&lt; 75%)
                                        </span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <!-- TAB 2: Riwayat per Pertemuan -->
    <div x-show="activeTab === 'riwayat'" class="space-y-3">
        
        @foreach ($meetings as $m)
            @php
                $presensiList = $m->presensi;
                $isRecorded = $presensiList->isNotEmpty();
                $hCount = $presensiList->where('status', 'hadir')->count();
                $iCount = $presensiList->where('status', 'izin')->count();
                $sCount = $presensiList->where('status', 'sakit')->count();
                $aCount = $presensiList->where('status', 'tidak_hadir')->count();
            @endphp
            <div class="bg-surface border border-line rounded-[8px] p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-3.5">
                    <div class="w-10 h-10 rounded-[6px] {{ $isRecorded ? 'bg-blue-50 text-[#2563EB] border-blue-200' : 'bg-slate-50 text-slate-400 border-line' }} border flex flex-col items-center justify-center shrink-0">
                        <span class="text-[9px] font-semibold uppercase leading-tight">P</span>
                        <span class="text-xs font-bold leading-none">{{ $m->week }}</span>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-text">
                            Pertemuan ke-{{ $m->week }}: {{ $m->title }}
                        </h4>
                        <p class="text-[11px] text-text-muted mt-0.5">
                            {{ $m->date ? \Carbon\Carbon::parse($m->date)->locale('id')->isoFormat('dddd, D MMMM Y') : 'Jadwal Mingguan' }} • {{ substr($m->start_time ?? '08:00', 0, 5) }} - {{ substr($m->end_time ?? '10:00', 0, 5) }} WIB
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-4 text-xs">
                    @if ($isRecorded)
                        <div class="flex items-center gap-2 text-[11px]">
                            <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold">{{ $hCount }} Hadir</span>
                            <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-semibold">{{ $iCount }} Izin</span>
                            <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-semibold">{{ $sCount }} Sakit</span>
                            <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-semibold">{{ $aCount }} Alpa</span>
                        </div>
                    @else
                        <span class="text-[11px] text-text-muted italic">
                            Belum ada catatan presensi
                        </span>
                    @endif

                    <a 
                        href="{{ route('dosen.presensi.input', ['class_id' => $class->id, 'meeting_id' => $m->id]) }}" 
                        class="px-3 py-1.5 rounded-[6px] bg-[#1A2E5E] hover:bg-[#2C4A8F] text-white text-xs font-semibold shadow-xs transition-colors shrink-0"
                    >
                        {{ $isRecorded ? 'Ubah Presensi' : 'Input Presensi' }}
                    </a>
                </div>
            </div>
        @endforeach

    </div>

</div>
@endsection
