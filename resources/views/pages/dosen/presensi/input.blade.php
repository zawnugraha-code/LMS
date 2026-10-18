@extends('layouts.dosen')

@section('title', 'Input Presensi Perkuliahan — Portal Dosen Arunika')
@section('page_breadcrumb', 'Input Presensi')

@section('content')
<div class="space-y-6" x-data="inputPresensiApp()">
    
    <!-- 1. Header Halaman & Breadcrumb -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 text-xs text-text-muted mb-1">
                <a href="{{ route('dosen.presensi.index') }}" class="hover:text-text transition-colors flex items-center gap-1">
                    <x-icon name="arrow-left" :size="12" />
                    <span>Kembali ke Presensi</span>
                </a>
            </div>
            <h1 class="text-2xl font-bold text-text tracking-tight">
                Input Presensi Perkuliahan
            </h1>
            <p class="text-xs sm:text-[13px] text-text-muted mt-0.5 leading-relaxed">
                Pilih kelas dan pertemuan untuk mencatat atau memperbarui status kehadiran mahasiswa.
            </p>
        </div>
    </div>

    <!-- 2. Form Selector Kelas & Pertemuan -->
    <div class="bg-surface border border-line rounded-[8px] p-5 shadow-xs">
        <form method="GET" action="{{ route('dosen.presensi.input') }}" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
            
            <!-- Pilihan Kelas -->
            <div class="md:col-span-5">
                <label for="class_id_select" class="block text-xs font-semibold text-text mb-1.5">
                    Pilih Kelas Perkuliahan
                </label>
                <div class="relative">
                    <select 
                        id="class_id_select"
                        name="class_id" 
                        onchange="this.form.submit()" 
                        class="w-full h-9 pl-3 pr-8 text-xs bg-canvas/40 border border-line rounded-[6px] text-text font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer appearance-none"
                    >
                        @foreach ($classes as $cls)
                            <option value="{{ $cls->id }}" {{ $selectedClass?->id === $cls->id ? 'selected' : '' }}>
                                {{ $cls->course?->code }} - {{ $cls->course?->name }} ({{ $cls->section }})
                            </option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" :size="14" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
                </div>
            </div>

            <!-- Pilihan Pertemuan -->
            <div class="md:col-span-5">
                <label for="meeting_id_select" class="block text-xs font-semibold text-text mb-1.5">
                    Pilih Sesi Pertemuan
                </label>
                <div class="relative">
                    <select 
                        id="meeting_id_select"
                        name="meeting_id" 
                        onchange="this.form.submit()" 
                        class="w-full h-9 pl-3 pr-8 text-xs bg-canvas/40 border border-line rounded-[6px] text-text font-medium focus:outline-none focus:border-[#2563EB] cursor-pointer appearance-none"
                    >
                        @foreach ($meetings as $m)
                            <option value="{{ $m->id }}" {{ $selectedMeeting?->id === $m->id ? 'selected' : '' }}>
                                Pertemuan {{ $m->week }}: {{ $m->title }} ({{ $m->date ? \Carbon\Carbon::parse($m->date)->locale('id')->isoFormat('D MMM Y') : 'Jadwal Mingguan' }})
                            </option>
                        @endforeach
                    </select>
                    <x-icon name="chevron-down" :size="14" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
                </div>
            </div>

            <!-- Tombol Refresh / Terapkan -->
            <div class="md:col-span-2">
                <button type="submit" class="w-full h-9 px-3 rounded-[6px] bg-slate-100 hover:bg-slate-200 text-text text-xs font-semibold transition-colors flex items-center justify-center gap-1.5 cursor-pointer">
                    <x-icon name="rotate-cw" :size="14" />
                    <span>Muat Sesi</span>
                </button>
            </div>

        </form>

        <!-- Informasi Pertemuan Aktif Banner -->
        @if ($selectedMeeting)
            <div class="mt-4 pt-4 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs">
                <div class="flex items-center gap-2">
                    <span class="px-2 py-0.5 rounded-full font-bold bg-blue-50 text-[#2563EB] border border-blue-200">
                        Pertemuan ke-{{ $selectedMeeting->week }}
                    </span>
                    <span class="font-bold text-text">
                        {{ $selectedMeeting->title }}
                    </span>
                    <span class="text-text-muted">
                        • {{ $selectedMeeting->date ? \Carbon\Carbon::parse($selectedMeeting->date)->locale('id')->isoFormat('dddd, D MMMM Y') : 'Senin, 19 Oktober 2026' }}
                    </span>
                    <span class="text-text-muted">
                        • Ruang {{ $selectedClass?->room ?? 'Lab 307' }}
                    </span>
                </div>
                <div class="text-[11px] text-text-muted">
                    Total: <strong class="text-text">{{ count($students) }}</strong> mahasiswa terdaftar
                </div>
            </div>
        @endif
    </div>

    <!-- 3. Form Input Presensi Mahasiswa -->
    <form method="POST" action="{{ route('dosen.presensi.store') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="meeting_id" value="{{ $selectedMeeting?->id }}">

        <div class="bg-surface border border-line rounded-[8px] shadow-xs overflow-hidden">
            
            <!-- Toolbar Atas: Aksi Massal "Hadir Semua" & Search Mahasiswa -->
            <div class="p-4 border-b border-line bg-slate-50/50 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                
                <div class="flex items-center gap-2.5">
                    <!-- Tombol Aksi Massal: Hadir Semua -->
                    <button 
                        type="button" 
                        @click="markAllHadir()" 
                        class="h-8 px-3 rounded-[6px] bg-white border border-line hover:border-[#2563EB] text-[#2563EB] text-xs font-semibold flex items-center gap-1.5 shadow-xs transition-colors cursor-pointer"
                    >
                        <x-icon name="check-check" :size="14" />
                        <span>Hadir Semua</span>
                    </button>

                    <!-- Filter Status Cepat -->
                    <div class="hidden sm:inline-flex items-center gap-1 text-[11px] text-text-muted">
                        <span>Status:</span>
                        <span class="px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-medium">Hadir: <strong x-text="countStatus('hadir')"></strong></span>
                        <span class="px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 font-medium">Izin: <strong x-text="countStatus('izin')"></strong></span>
                        <span class="px-2 py-0.5 rounded-full bg-purple-50 text-purple-700 font-medium">Sakit: <strong x-text="countStatus('sakit')"></strong></span>
                        <span class="px-2 py-0.5 rounded-full bg-red-50 text-red-700 font-medium">Tidak Hadir: <strong x-text="countStatus('tidak_hadir')"></strong></span>
                    </div>
                </div>

                <!-- Live Search Mahasiswa -->
                <div class="relative w-full sm:w-64">
                    <input 
                        type="text" 
                        x-model="searchQuery" 
                        placeholder="Cari nama atau NIM..." 
                        class="w-full h-8 pl-8 pr-3 text-xs bg-surface border border-line rounded-[6px] text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                    >
                    <x-icon name="search" :size="13" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-text-muted pointer-events-none" />
                </div>

            </div>

            <!-- Tabel Baris Mahasiswa -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50 border-b border-line text-text-muted font-medium text-[11px]">
                            <th class="py-3 px-4 w-12 text-center">No</th>
                            <th class="py-3 px-4 min-w-[220px]">Mahasiswa</th>
                            <th class="py-3 px-4 min-w-[340px]">Status Kehadiran</th>
                            <th class="py-3 px-4 min-w-[200px]">Catatan / Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line/60">
                        @foreach ($students as $idx => $s)
                            <tr 
                                x-show="matchesSearch('{{ strtolower($s['name']) }}', '{{ strtolower($s['identifier']) }}')"
                                class="hover:bg-slate-50/50 transition-colors"
                            >
                                <!-- No -->
                                <td class="py-3 px-4 text-center text-text-muted font-medium">
                                    {{ $idx + 1 }}
                                </td>

                                <!-- Identitas Mahasiswa -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-7 h-7 rounded-full bg-blue-50 text-[#2563EB] font-bold text-xs flex items-center justify-center shrink-0 border border-blue-200">
                                            {{ substr($s['name'], 0, 1) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-text leading-tight truncate">
                                                {{ $s['name'] }}
                                            </p>
                                            <p class="text-[11px] text-text-muted mt-0.5">
                                                {{ $s['identifier'] }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Radio Pills: Hadir | Izin | Sakit | Tidak Hadir -->
                                <td class="py-3 px-4">
                                    <div class="inline-flex items-center gap-2">
                                        
                                        <!-- Hadir -->
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border cursor-pointer transition-colors text-xs select-none"
                                            :class="records[{{ $s['id'] }}] === 'hadir' ? 'bg-emerald-50 border-emerald-300 text-emerald-800 font-semibold' : 'bg-surface border-line text-text-muted hover:bg-slate-50'"
                                        >
                                            <input 
                                                type="radio" 
                                                name="mahasiswa[{{ $s['id'] }}][status]" 
                                                value="hadir" 
                                                x-model="records[{{ $s['id'] }}]"
                                                class="sr-only"
                                            >
                                            <span class="w-2 h-2 rounded-full" :class="records[{{ $s['id'] }}] === 'hadir' ? 'bg-emerald-600' : 'bg-slate-300'"></span>
                                            <span>Hadir</span>
                                        </label>

                                        <!-- Izin -->
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border cursor-pointer transition-colors text-xs select-none"
                                            :class="records[{{ $s['id'] }}] === 'izin' ? 'bg-amber-50 border-amber-300 text-amber-800 font-semibold' : 'bg-surface border-line text-text-muted hover:bg-slate-50'"
                                        >
                                            <input 
                                                type="radio" 
                                                name="mahasiswa[{{ $s['id'] }}][status]" 
                                                value="izin" 
                                                x-model="records[{{ $s['id'] }}]"
                                                class="sr-only"
                                            >
                                            <span class="w-2 h-2 rounded-full" :class="records[{{ $s['id'] }}] === 'izin' ? 'bg-amber-600' : 'bg-slate-300'"></span>
                                            <span>Izin</span>
                                        </label>

                                        <!-- Sakit -->
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border cursor-pointer transition-colors text-xs select-none"
                                            :class="records[{{ $s['id'] }}] === 'sakit' ? 'bg-purple-50 border-purple-300 text-purple-800 font-semibold' : 'bg-surface border-line text-text-muted hover:bg-slate-50'"
                                        >
                                            <input 
                                                type="radio" 
                                                name="mahasiswa[{{ $s['id'] }}][status]" 
                                                value="sakit" 
                                                x-model="records[{{ $s['id'] }}]"
                                                class="sr-only"
                                            >
                                            <span class="w-2 h-2 rounded-full" :class="records[{{ $s['id'] }}] === 'sakit' ? 'bg-purple-600' : 'bg-slate-300'"></span>
                                            <span>Sakit</span>
                                        </label>

                                        <!-- Tidak Hadir -->
                                        <label class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border cursor-pointer transition-colors text-xs select-none"
                                            :class="records[{{ $s['id'] }}] === 'tidak_hadir' ? 'bg-red-50 border-red-300 text-red-800 font-semibold' : 'bg-surface border-line text-text-muted hover:bg-slate-50'"
                                        >
                                            <input 
                                                type="radio" 
                                                name="mahasiswa[{{ $s['id'] }}][status]" 
                                                value="tidak_hadir" 
                                                x-model="records[{{ $s['id'] }}]"
                                                class="sr-only"
                                            >
                                            <span class="w-2 h-2 rounded-full" :class="records[{{ $s['id'] }}] === 'tidak_hadir' ? 'bg-red-600' : 'bg-slate-300'"></span>
                                            <span>Tidak Hadir</span>
                                        </label>

                                    </div>
                                </td>

                                <!-- Catatan / Keterangan -->
                                <td class="py-3 px-4">
                                    <input 
                                        type="text" 
                                        name="mahasiswa[{{ $s['id'] }}][catatan]" 
                                        value="{{ $s['catatan'] }}" 
                                        placeholder="Catatan tambahan (opsional)..." 
                                        class="w-full h-8 px-2.5 text-xs bg-canvas/30 border border-line rounded-[4px] text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                                    >
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Simpan Aksi Bar -->
            <div class="p-4 bg-slate-50/70 border-t border-line flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="text-xs text-text-muted flex items-center gap-2">
                    <x-icon name="info" :size="14" class="text-text-subtle shrink-0" />
                    <span>Presensi yang disimpan akan otomatis memperbarui rekap kehadiran mahasiswa.</span>
                </div>
                <div class="flex items-center gap-2.5 justify-end">
                    <a 
                        href="{{ route('dosen.presensi.index') }}" 
                        class="h-9 px-4 rounded-[6px] bg-white border border-line text-text hover:bg-slate-100 text-xs font-semibold transition-colors flex items-center justify-center cursor-pointer"
                    >
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="h-9 px-5 rounded-[6px] bg-[#1A2E5E] hover:bg-[#2C4A8F] text-white text-xs font-semibold flex items-center gap-2 shadow-xs transition-colors cursor-pointer"
                    >
                        <x-icon name="save" :size="14" />
                        <span>Simpan Presensi</span>
                    </button>
                </div>
            </div>

        </div>
    </form>

</div>

<script>
function inputPresensiApp() {
    return {
        searchQuery: '',
        records: {
            @foreach ($students as $s)
                {{ $s['id'] }}: '{{ $s['status'] }}',
            @endforeach
        },
        markAllHadir() {
            for (let id in this.records) {
                this.records[id] = 'hadir';
            }
        },
        countStatus(status) {
            let count = 0;
            for (let id in this.records) {
                if (this.records[id] === status) count++;
            }
            return count;
        },
        matchesSearch(name, nim) {
            if (!this.searchQuery.trim()) return true;
            let q = this.searchQuery.toLowerCase().trim();
            return name.includes(q) || nim.includes(q);
        }
    }
}
</script>
@endsection
