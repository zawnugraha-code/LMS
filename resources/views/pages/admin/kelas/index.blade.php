@extends('layouts.admin')

@section('title', 'Kelas Perkuliahan')

@section('content')
<div class="space-y-6" x-data="{ openDropdownId: null }">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-[#1A2E5E] tracking-tight">Kelas Perkuliahan</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola seksi paralel, alokasi dosen pengampu, jadwal ruang, dan pantau keterisian kuota mahasiswa.
            </p>
        </div>

        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2">
                <span class="text-xs font-semibold text-slate-500">Total Kelas:</span>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-[#1A2E5E]">
                    {{ $summary['totalClasses'] }} Kelas
                </span>
            </div>

            <a 
                href="{{ route('admin.kelas.create') }}"
                class="px-4 py-2.5 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer shrink-0"
            >
                <x-icon name="plus" :size="16" />
                <span>Buka Kelas Baru</span>
            </a>
        </div>
    </div>

    <!-- Main Content Layout (8 Cols Table, 4 Cols Summary) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Table Column (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            
            <!-- Filter & Search Bar Controls -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs space-y-3">
                
                <!-- Status Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-100 pb-3 overflow-x-auto text-xs">
                    <a 
                        href="{{ route('admin.kelas.index', array_merge(request()->query(), ['status' => 'semua'])) }}"
                        class="px-3 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap {{ $selectedStatus === 'semua' || !$selectedStatus ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Semua Status
                    </a>
                    <a 
                        href="{{ route('admin.kelas.index', array_merge(request()->query(), ['status' => 'aktif'])) }}"
                        class="px-3 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $selectedStatus === 'aktif' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        <span>Aktif</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $selectedStatus === 'aktif' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                            {{ $summary['activeClasses'] }}
                        </span>
                    </a>
                    <a 
                        href="{{ route('admin.kelas.index', array_merge(request()->query(), ['status' => 'selesai'])) }}"
                        class="px-3 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $selectedStatus === 'selesai' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        <span>Selesai</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $selectedStatus === 'selesai' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                            {{ $summary['finishedClasses'] }}
                        </span>
                    </a>
                </div>

                <!-- Dropdowns & Search Input -->
                <form method="GET" action="{{ route('admin.kelas.index') }}" class="grid grid-cols-1 sm:grid-cols-12 gap-3 text-xs">
                    <input type="hidden" name="status" value="{{ $selectedStatus }}">

                    <!-- Filter Semester -->
                    <div class="sm:col-span-3">
                        <select 
                            name="term_id" 
                            onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="semua">Semua Semester</option>
                            @foreach ($terms as $t)
                                <option value="{{ $t->id }}" {{ $selectedTermId == $t->id ? 'selected' : '' }}>
                                    {{ $t->name }} {{ $t->isCurrentActive() ? '★' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Mata Kuliah -->
                    <div class="sm:col-span-3">
                        <select 
                            name="course_id" 
                            onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="">Semua Mata Kuliah</option>
                            @foreach ($courses as $c)
                                <option value="{{ $c->id }}" {{ $selectedCourseId == $c->id ? 'selected' : '' }}>
                                    {{ $c->code }} - {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Dosen -->
                    <div class="sm:col-span-3">
                        <select 
                            name="lecturer_id" 
                            onchange="this.form.submit()"
                            class="w-full px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="">Semua Dosen</option>
                            @foreach ($lecturers as $l)
                                <option value="{{ $l->id }}" {{ $selectedLecturerId == $l->id ? 'selected' : '' }}>
                                    {{ $l->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Search Input -->
                    <div class="sm:col-span-3 relative">
                        <input 
                            type="text" 
                            name="q" 
                            value="{{ $searchQuery }}" 
                            placeholder="Cari kelas, ruang, kode..."
                            class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                        />
                        <x-icon name="magnifying-glass" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
                    </div>
                </form>

            </div>

            <!-- Table Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs text-slate-600">
                        <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] tracking-wider font-extrabold border-b border-slate-200/80">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">No</th>
                                <th class="py-3 px-4">Mata Kuliah</th>
                                <th class="py-3 px-4">Kelas</th>
                                <th class="py-3 px-4">Dosen Pengampu</th>
                                <th class="py-3 px-4">Jadwal & Ruang</th>
                                <th class="py-3 px-4 w-32">Terisi</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($classes as $idx => $cls)
                                @php
                                    $enrolledCount = $cls->active_enrollments_count ?? $cls->activeEnrollments->count();
                                    $capacity = (int) $cls->capacity;
                                    $pct = $capacity > 0 ? min(100, round(($enrolledCount / $capacity) * 100)) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="py-3 px-4 text-center font-mono text-slate-400">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3 px-4 font-bold text-slate-800">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-mono text-[11px] px-1.5 py-0.5 rounded bg-blue-50 text-blue-700 font-extrabold">
                                                {{ $cls->course->code }}
                                            </span>
                                            <span class="truncate max-w-[160px]" title="{{ $cls->course->name }}">
                                                {{ $cls->course->name }}
                                            </span>
                                        </div>
                                        <div class="text-[11px] text-slate-400 font-normal mt-0.5">
                                            {{ $cls->course->credits }} SKS &bull; Semester {{ $cls->course->semester }} &bull; {{ $cls->term->name }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4">
                                        <span class="font-extrabold text-[#1A2E5E] font-mono text-xs">
                                            {{ $cls->section }}
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        @if ($cls->lecturer)
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-blue-100 text-[#1A2E5E] font-bold flex items-center justify-center text-[10px] shrink-0">
                                                    {{ substr($cls->lecturer->name, 0, 1) }}
                                                </div>
                                                <span class="font-medium text-slate-700 truncate max-w-[130px]" title="{{ $cls->lecturer->name }}">
                                                    {{ $cls->lecturer->name }}
                                                </span>
                                            </div>
                                        @else
                                            <span class="inline-flex items-center gap-1 text-amber-600 font-semibold text-[11px]">
                                                <x-icon name="exclamation-circle" :size="12" />
                                                <span>Belum Ditunjuk</span>
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4 text-[11px]">
                                        @if ($cls->schedule_day && $cls->start_time)
                                            <div class="font-semibold text-slate-700">
                                                {{ $cls->schedule_day }}, {{ substr($cls->start_time, 0, 5) }} - {{ substr($cls->end_time, 0, 5) }}
                                            </div>
                                            <div class="text-slate-400 flex items-center gap-1 mt-0.5">
                                                <x-icon name="map-pin" :size="11" />
                                                <span>{{ $cls->room ?? 'Ruang Belum Diatur' }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-400 italic">Jadwal belum diatur</span>
                                        @endif
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="space-y-1">
                                            <div class="flex items-center justify-between text-[11px]">
                                                <span class="font-extrabold text-slate-800 tabular-nums">
                                                    {{ $enrolledCount }} / {{ $capacity }}
                                                </span>
                                                <span class="font-semibold text-slate-400 text-[10px]">
                                                    {{ $pct }}%
                                                </span>
                                            </div>
                                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                                <div 
                                                    class="h-full rounded-full transition-all duration-300 {{ $pct >= 100 ? 'bg-rose-500' : ($pct >= 85 ? 'bg-amber-500' : 'bg-[#1A2E5E]') }}" 
                                                    style="width: {{ $pct }}%"
                                                ></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <x-admin.status-badge :status="$cls->status" />
                                    </td>
                                    <td class="py-3 px-4 text-right relative">
                                        <div class="inline-block text-left" @click.away="if (openDropdownId === {{ $cls->id }}) openDropdownId = null">
                                            <button 
                                                type="button"
                                                @click="openDropdownId = (openDropdownId === {{ $cls->id }} ? null : {{ $cls->id }})"
                                                class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 font-semibold text-[11px] inline-flex items-center gap-1 cursor-pointer transition-colors"
                                            >
                                                <span>Kelola</span>
                                                <x-icon name="chevron-down" :size="12" />
                                            </button>

                                            <!-- Dropdown Menu -->
                                            <div 
                                                x-show="openDropdownId === {{ $cls->id }}"
                                                x-cloak
                                                class="absolute right-0 mt-1 w-44 bg-white border border-slate-200 rounded-xl shadow-lg z-20 py-1 text-xs"
                                            >
                                                <a 
                                                    href="{{ route('admin.kelas.show', $cls) }}"
                                                    class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold"
                                                >
                                                    <x-icon name="eye" :size="14" class="text-blue-600" />
                                                    <span>Lihat Detail</span>
                                                </a>

                                                <a 
                                                    href="{{ route('admin.kelas.edit', $cls) }}"
                                                    class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold"
                                                >
                                                    <x-icon name="pencil-square" :size="14" class="text-amber-600" />
                                                    <span>Edit Konfigurasi</span>
                                                </a>

                                                <a 
                                                    href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $cls->id]) }}"
                                                    class="flex items-center gap-2 px-3 py-2 text-slate-700 hover:bg-slate-50 font-semibold"
                                                >
                                                    <x-icon name="user-group" :size="14" class="text-emerald-600" />
                                                    <span>Kelola Peserta</span>
                                                </a>

                                                @if ($cls->status === 'aktif')
                                                    <form method="POST" action="{{ route('admin.kelas.finish', $cls) }}" onsubmit="return confirm('Apakah Anda yakin ingin menyelesaikan kelas ini? Kelas akan dikunci menjadi read-only.');">
                                                        @csrf
                                                        <button 
                                                            type="submit"
                                                            class="w-full text-left flex items-center gap-2 px-3 py-2 text-slate-600 hover:bg-slate-50 font-semibold cursor-pointer"
                                                        >
                                                            <x-icon name="lock-closed" :size="14" class="text-slate-400" />
                                                            <span>Selesaikan Kelas</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                <div class="border-t border-slate-100 my-1"></div>

                                                @if ($enrolledCount === 0)
                                                    <form method="POST" action="{{ route('admin.kelas.destroy', $cls) }}" onsubmit="return confirm('Hapus kelas {{ $cls->course->code }} {{ $cls->section }} secara permanen?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button 
                                                            type="submit"
                                                            class="w-full text-left flex items-center gap-2 px-3 py-2 text-rose-600 hover:bg-rose-50 font-semibold cursor-pointer"
                                                        >
                                                            <x-icon name="trash" :size="14" class="text-rose-500" />
                                                            <span>Hapus Kelas</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button 
                                                        disabled 
                                                        title="Kelas yang telah memiliki mahasiswa terdaftar tidak dapat dihapus."
                                                        class="w-full text-left flex items-center gap-2 px-3 py-2 text-slate-400 cursor-not-allowed font-medium opacity-60"
                                                    >
                                                        <x-icon name="trash" :size="14" class="text-slate-300" />
                                                        <span>Hapus (Terkunci)</span>
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="py-12 text-center text-slate-400">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <x-icon name="academic-cap" :size="32" class="text-slate-300" />
                                            <p class="font-semibold text-xs text-slate-500">Tidak ada kelas yang sesuai dengan kriteria filter.</p>
                                            <a href="{{ route('admin.kelas.index') }}" class="text-xs text-blue-600 hover:underline font-bold mt-1">
                                                Reset Semua Filter
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Right Side Widgets (4 Cols) -->
        <div class="lg:col-span-4 space-y-6">
            
            <!-- Card Ringkasan Kelas -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-[#1A2E5E]">Ringkasan Kelas</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Total Kelas</span>
                        <span class="text-base font-extrabold text-[#1A2E5E] tabular-nums">{{ $summary['totalClasses'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <span class="text-emerald-900 font-medium">Kelas Aktif Berjalan</span>
                        <span class="text-base font-extrabold text-emerald-700 tabular-nums">{{ $summary['activeClasses'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-100">
                        <span class="text-blue-900 font-medium">Mahasiswa Terdaftar</span>
                        <span class="text-base font-extrabold text-blue-700 tabular-nums">{{ $summary['totalEnrolled'] }} Mhs</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Kelas Selesai</span>
                        <span class="text-base font-extrabold text-slate-600 tabular-nums">{{ $summary['finishedClasses'] }}</span>
                    </div>
                </div>
            </div>

            <!-- Card Donut Chart Kapasitas Kelas -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-[#1A2E5E]">Kapasitas Kolektif Kelas</h3>

                <div class="flex flex-col items-center justify-center pt-2">
                    <x-admin.donut-chart 
                        :percentage="$summary['capacityPercentage']" 
                        :size="130" 
                        strokeColor="#1A2E5E" 
                        label="Terisi" 
                    />
                </div>

                <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-3 text-center text-xs">
                    <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                        <div class="text-[10px] uppercase font-bold text-blue-800">Kursi Terisi</div>
                        <div class="text-base font-black text-[#1A2E5E] mt-0.5 tabular-nums">
                            {{ $summary['totalEnrolled'] }}
                        </div>
                        <div class="text-[10px] text-blue-600 font-semibold">{{ $summary['capacityPercentage'] }}% kuota</div>
                    </div>

                    <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100/80">
                        <div class="text-[10px] uppercase font-bold text-emerald-800">Sisa Kursi</div>
                        <div class="text-base font-black text-emerald-700 mt-0.5 tabular-nums">
                            {{ $summary['remainingCapacity'] }}
                        </div>
                        <div class="text-[10px] text-emerald-600 font-semibold">{{ $summary['remainingPercentage'] }}% tersedia</div>
                    </div>
                </div>
            </div>

            <!-- Card Pedoman Penugasan BAAK -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-xs text-slate-700 space-y-2">
                <div class="flex items-center gap-2 font-bold text-[#1A2E5E]">
                    <x-icon name="shield-check" :size="18" class="text-blue-600 shrink-0" />
                    <span>Integritas Penugasan Dosen</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Sistem mendeteksi bentrok jadwal dosen dan pemakaian ruang secara langsung saat pembukaan kelas baru. Jika dosen pengampu diganti, seluruh hak kelola tugas dan buku nilai otomatis berpindah ke dosen baru.
                </p>
            </div>

        </div>

    </div>

</div>
@endsection
