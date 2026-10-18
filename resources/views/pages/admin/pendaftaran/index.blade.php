@extends('layouts.admin')

@section('title', 'Pendaftaran Mahasiswa')

@section('content')
<div 
    class="space-y-6"
    x-data="{
        switchClassModalOpen: false,
        addStudentModalOpen: false,
        removeModalOpen: false,
        studentToRemove: { id: null, name: '', nim: '' },
        selectedIds: [],
        selectAll: false,

        toggleSelectAll() {
            if (this.selectAll) {
                this.selectedIds = Array.from(document.querySelectorAll('.row-checkbox')).map(cb => cb.value);
            } else {
                this.selectedIds = [];
            }
        },

        openRemoveModal(id, name, nim) {
            this.studentToRemove = { id: id, name: name, nim: nim };
            this.removeModalOpen = true;
        }
    }"
>

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-[#1A2E5E] tracking-tight">Pendaftaran Mahasiswa</h1>
            <p class="text-xs text-slate-500 mt-1">
                Kelola pendaftaran mahasiswa ke seksi kelas perkuliahan, alokasi kapasitas kursi, dan impor/ekspor peserta.
            </p>
        </div>

        @if ($selectedClass)
            <div class="flex items-center gap-2.5 shrink-0">
                <a 
                    href="{{ route('admin.pendaftaran-mahasiswa.export', $selectedClass) }}"
                    class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <x-icon name="arrow-down-tray" :size="15" class="text-slate-500" />
                    <span>Ekspor CSV</span>
                </a>

                <button 
                    type="button" 
                    @click="addStudentModalOpen = true"
                    class="px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <x-icon name="plus" :size="16" />
                    <span>Tambah Mahasiswa</span>
                </button>
            </div>
        @endif
    </div>

    @if ($selectedClass)

        <!-- Kartu Kelas Terpilih (Featured Class Card) -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs relative overflow-hidden">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="space-y-1.5">
                    <div class="flex items-center gap-2">
                        <span class="font-mono text-xs px-2.5 py-0.5 rounded-lg bg-blue-100 text-[#1A2E5E] font-extrabold">
                            {{ $selectedClass->course->code }}
                        </span>
                        <span class="text-xs px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold">
                            Kelas {{ $selectedClass->section }}
                        </span>
                        <x-admin.status-badge :status="$selectedClass->status" />
                    </div>

                    <h2 class="text-lg font-black text-[#1A2E5E]">
                        {{ $selectedClass->course->name }}
                    </h2>

                    <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-0.5">
                        <div class="flex items-center gap-1">
                            <x-icon name="user" :size="14" class="text-blue-600" />
                            <span>Dosen:</span>
                            <strong class="text-slate-700">{{ $selectedClass->lecturer?->name ?? 'Belum Ditunjuk' }}</strong>
                        </div>

                        <div class="flex items-center gap-1">
                            <x-icon name="clock" :size="14" class="text-blue-600" />
                            <span>{{ $selectedClass->schedule_day ?? 'Hari -' }}, {{ substr((string)$selectedClass->start_time, 0, 5) }} - {{ substr((string)$selectedClass->end_time, 0, 5) }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <x-icon name="map-pin" :size="14" class="text-blue-600" />
                            <span>{{ $selectedClass->room ?? 'Ruang belum diatur' }}</span>
                        </div>

                        <div class="flex items-center gap-1">
                            <x-icon name="user-group" :size="14" class="text-blue-600" />
                            <strong class="text-[#1A2E5E]">{{ $countTerdaftar }} Mahasiswa</strong>
                            <span>(Kapasitas: {{ $capacity }})</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <button 
                        type="button" 
                        @click="switchClassModalOpen = true"
                        class="px-4 py-2 rounded-xl bg-blue-50 text-[#1A2E5E] hover:bg-blue-100 text-xs font-bold transition-colors flex items-center gap-1.5 cursor-pointer"
                    >
                        <span>Ganti Kelas</span>
                        <x-icon name="chevron-right" :size="14" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Layout (8 Cols Table, 4 Cols Widgets) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            <!-- Table Column (8 Cols) -->
            <div class="lg:col-span-8 space-y-4">
                
                <!-- Tab Filter & Search -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs space-y-3">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        
                        <!-- Tabs -->
                        <div class="flex items-center gap-2 overflow-x-auto text-xs">
                            <a 
                                href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $selectedClass->id, 'tab' => 'terdaftar']) }}"
                                class="px-3.5 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $tab === 'terdaftar' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                            >
                                <span>Terdaftar</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'terdaftar' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                                    {{ $countTerdaftar }}
                                </span>
                            </a>

                            <a 
                                href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $selectedClass->id, 'tab' => 'dikeluarkan']) }}"
                                class="px-3.5 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $tab === 'dikeluarkan' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                            >
                                <span>Dikeluarkan</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'dikeluarkan' ? 'bg-white/20 text-white' : 'bg-rose-100 text-rose-800' }}">
                                    {{ $countDikeluarkan }}
                                </span>
                            </a>

                            <a 
                                href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $selectedClass->id, 'tab' => 'belum_terdaftar']) }}"
                                class="px-3.5 py-1.5 rounded-xl font-bold transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $tab === 'belum_terdaftar' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100' }}"
                            >
                                <span>Belum Terdaftar</span>
                                <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $tab === 'belum_terdaftar' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $countBelumTerdaftar }}
                                </span>
                            </a>
                        </div>

                        <!-- Search -->
                        <form method="GET" action="{{ route('admin.pendaftaran-mahasiswa.index') }}" class="relative w-full sm:w-64">
                            <input type="hidden" name="class_id" value="{{ $selectedClass->id }}">
                            <input type="hidden" name="tab" value="{{ $tab }}">
                            <input 
                                type="text" 
                                name="q" 
                                value="{{ $searchQuery }}" 
                                placeholder="Cari nama, NIM, email..."
                                class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                            />
                            <x-icon name="magnifying-glass" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
                        </form>

                    </div>
                </div>

                <!-- Banner Informasi Status Pengeluaran -->
                <div class="p-3.5 rounded-2xl bg-blue-50/70 border border-blue-100/90 text-xs text-blue-900 flex items-center gap-2.5">
                    <x-icon name="information-circle" :size="18" class="text-blue-600 shrink-0" />
                    <span>
                        Mengeluarkan mahasiswa dari kelas <strong>tidak menghapus</strong> nilai tugas, presensi, maupun riwayat pengumpulan tugas yang sudah tercatat.
                    </span>
                </div>

                <!-- Sticky Bulk Action Bar (muncul jika ada yang dicentang di tab belum_terdaftar) -->
                @if ($tab === 'belum_terdaftar')
                    <form 
                        method="POST" 
                        action="{{ route('admin.pendaftaran-mahasiswa.bulk-store', $selectedClass) }}" 
                        x-show="selectedIds.length > 0" 
                        x-cloak
                        class="p-3 rounded-2xl bg-[#1A2E5E] text-white flex items-center justify-between shadow-md text-xs transition-all"
                    >
                        @csrf
                        <div class="flex items-center gap-2 font-bold pl-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                            <span x-text="selectedIds.length + ' mahasiswa dipilih'"></span>
                        </div>

                        <template x-for="id in selectedIds" :key="id">
                            <input type="hidden" name="user_ids[]" :value="id">
                        </template>

                        <button 
                            type="submit" 
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl transition-colors cursor-pointer"
                        >
                            + Daftarkan Sekaligus ke Kelas
                        </button>
                    </form>
                @endif

                <!-- Table Card -->
                <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs text-slate-600">
                            <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] tracking-wider font-extrabold border-b border-slate-200/80">
                                <tr>
                                    @if ($tab === 'belum_terdaftar')
                                        <th class="py-3 px-3 w-8 text-center">
                                            <input 
                                                type="checkbox" 
                                                x-model="selectAll" 
                                                @change="toggleSelectAll()"
                                                class="rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                            />
                                        </th>
                                    @endif
                                    <th class="py-3 px-4 w-10 text-center">No</th>
                                    <th class="py-3 px-4">Mahasiswa</th>
                                    <th class="py-3 px-4">NIM</th>
                                    <th class="py-3 px-4">Program Studi</th>
                                    <th class="py-3 px-4">
                                        {{ $tab === 'dikeluarkan' ? 'Dikeluarkan Pada' : 'Tanggal Bergabung' }}
                                    </th>
                                    <th class="py-3 px-4 text-center">Status</th>
                                    <th class="py-3 px-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @forelse ($items as $idx => $item)
                                    @php
                                        $user = ($tab === 'belum_terdaftar') ? $item : $item->user;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        @if ($tab === 'belum_terdaftar')
                                            <td class="py-3 px-3 text-center">
                                                <input 
                                                    type="checkbox" 
                                                    value="{{ $user->id }}" 
                                                    x-model="selectedIds"
                                                    class="row-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer"
                                                />
                                            </td>
                                        @endif
                                        <td class="py-3 px-4 text-center font-mono text-slate-400">
                                            {{ $idx + 1 }}
                                        </td>
                                        <td class="py-3 px-4">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-7 h-7 rounded-full bg-blue-100 text-[#1A2E5E] font-extrabold flex items-center justify-center text-[10px] shrink-0">
                                                    {{ substr($user->name, 0, 1) }}
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800">{{ $user->name }}</div>
                                                    <div class="text-[11px] text-slate-400">{{ $user->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="py-3 px-4 font-mono font-bold text-slate-700">
                                            {{ $user->identifier ?? '-' }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-600">
                                            {{ $user->prodi ?? ($selectedClass->course->curriculum?->program?->name ?? 'S1 Teknik Informatika') }}
                                        </td>
                                        <td class="py-3 px-4 text-slate-500 text-[11px]">
                                            @if ($tab === 'dikeluarkan')
                                                {{ $item->dikeluarkan_at ? $item->dikeluarkan_at->format('d M Y H:i') : '-' }}
                                            @elseif ($tab === 'terdaftar')
                                                {{ $item->created_at ? $item->created_at->format('d M Y') : '-' }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            @if ($tab === 'dikeluarkan')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                                    Dikeluarkan
                                                </span>
                                            @elseif ($tab === 'terdaftar')
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                                    Terdaftar
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                                    Belum Terdaftar
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-4 text-right">
                                            @if ($tab === 'terdaftar')
                                                <button 
                                                    type="button" 
                                                    @click="openRemoveModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->identifier }}')"
                                                    class="text-rose-600 hover:text-rose-800 font-bold hover:underline cursor-pointer"
                                                >
                                                    Keluarkan &gt;
                                                </button>
                                            @elseif ($tab === 'dikeluarkan')
                                                <form method="POST" action="{{ route('admin.pendaftaran-mahasiswa.store', $selectedClass) }}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <button type="submit" class="text-blue-600 hover:text-blue-800 font-bold hover:underline cursor-pointer">
                                                        + Daftarkan Kembali
                                                    </button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ route('admin.pendaftaran-mahasiswa.store', $selectedClass) }}">
                                                    @csrf
                                                    <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                    <button type="submit" class="text-emerald-600 hover:text-emerald-800 font-bold hover:underline cursor-pointer">
                                                        + Daftarkan &gt;
                                                    </button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $tab === 'belum_terdaftar' ? 8 : 7 }}" class="py-12 text-center text-slate-400">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <x-icon name="user-group" :size="32" class="text-slate-300" />
                                                <p class="font-semibold text-xs text-slate-500">
                                                    Tidak ada data mahasiswa pada tab ini.
                                                </p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Right Column Widgets (4 Cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Card Donut Kapasitas Kelas -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                    <h3 class="text-sm font-bold text-[#1A2E5E]">Kapasitas Kelas</h3>

                    <div class="flex flex-col items-center justify-center pt-2">
                        <x-admin.donut-chart 
                            :percentage="$capacityPercentage" 
                            :size="130" 
                            strokeColor="#1A2E5E" 
                            label="Terisi" 
                        />
                    </div>

                    <div class="pt-2 border-t border-slate-100 grid grid-cols-2 gap-3 text-center text-xs">
                        <div class="p-2.5 rounded-xl bg-blue-50/70 border border-blue-100/80">
                            <div class="text-[10px] uppercase font-bold text-blue-800">Kursi Terisi</div>
                            <div class="text-base font-black text-[#1A2E5E] mt-0.5 tabular-nums">
                                {{ $enrolledCount }} / {{ $capacity }}
                            </div>
                            <div class="text-[10px] text-blue-600 font-semibold">{{ $capacityPercentage }}% kuota</div>
                        </div>

                        <div class="p-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100/80">
                            <div class="text-[10px] uppercase font-bold text-emerald-800">Sisa Kuota</div>
                            <div class="text-base font-black text-emerald-700 mt-0.5 tabular-nums">
                                {{ $remainingCapacity }}
                            </div>
                            <div class="text-[10px] text-emerald-600 font-semibold">{{ $remainingPercentage }}% kursi</div>
                        </div>
                    </div>
                </div>

                <!-- Widget Belum Terdaftar di Kelas Ini -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-bold text-[#1A2E5E]">Mahasiswa Belum Terdaftar</h3>
                        <a 
                            href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $selectedClass->id, 'tab' => 'belum_terdaftar']) }}"
                            class="text-[11px] font-bold text-blue-600 hover:underline"
                        >
                            Lihat Semua &gt;
                        </a>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        @forelse ($quickAddStudents as $qStudent)
                            <div class="flex items-center justify-between p-2 rounded-xl bg-slate-50 border border-slate-100">
                                <div class="truncate max-w-[170px]">
                                    <div class="font-bold text-slate-800 truncate" title="{{ $qStudent->name }}">
                                        {{ $qStudent->name }}
                                    </div>
                                    <div class="text-[10px] text-slate-400 font-mono">
                                        {{ $qStudent->identifier ?? $qStudent->email }}
                                    </div>
                                </div>

                                <form method="POST" action="{{ route('admin.pendaftaran-mahasiswa.store', $selectedClass) }}">
                                    @csrf
                                    <input type="hidden" name="user_id" value="{{ $qStudent->id }}">
                                    <button 
                                        type="submit" 
                                        class="px-2.5 py-1 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg font-bold text-[11px] transition-colors cursor-pointer"
                                    >
                                        + Daftarkan
                                    </button>
                                </form>
                            </div>
                        @empty
                            <p class="text-slate-400 text-xs italic py-2 text-center">
                                Seluruh mahasiswa telah terdaftar di kelas ini.
                            </p>
                        @endforelse
                    </div>
                </div>

                <!-- Widget Impor CSV Peserta -->
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                        <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-1.5">
                            <x-icon name="arrow-up-tray" :size="16" class="text-blue-600" />
                            <span>Impor Mahasiswa (CSV)</span>
                        </h3>
                        <a 
                            href="{{ route('admin.pendaftaran-mahasiswa.template') }}"
                            class="text-[11px] font-bold text-blue-600 hover:underline flex items-center gap-1"
                        >
                            <x-icon name="document-arrow-down" :size="13" />
                            <span>Unduh Template</span>
                        </a>
                    </div>

                    <p class="text-[11px] text-slate-500 leading-relaxed">
                        Unggah berkas CSV berisi daftar NIM atau Email mahasiswa untuk didaftarkan sekaligus ke kelas ini.
                    </p>

                    <form 
                        method="POST" 
                        action="{{ route('admin.pendaftaran-mahasiswa.import', $selectedClass) }}" 
                        enctype="multipart/form-data" 
                        class="space-y-3"
                    >
                        @csrf
                        <input 
                            type="file" 
                            name="file" 
                            accept=".csv,text/csv" 
                            required 
                            class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-[#1A2E5E] hover:file:bg-blue-100 cursor-pointer"
                        />

                        <button 
                            type="submit" 
                            class="w-full py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer flex items-center justify-center gap-1.5"
                        >
                            <x-icon name="arrow-up-tray" :size="14" />
                            <span>Unggah &amp; Impor Peserta</span>
                        </button>
                    </form>
                </div>

            </div>

        </div>

    @else
        <!-- State jika belum ada kelas sama sekali -->
        <div class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-400 space-y-3">
            <x-icon name="academic-cap" :size="48" class="mx-auto text-slate-300" />
            <h3 class="text-base font-bold text-slate-700">Belum Ada Kelas Perkuliahan</h3>
            <p class="text-xs text-slate-500 max-w-md mx-auto">
                Silakan buka kelas perkuliahan terlebih dahulu pada modul Kelas sebelum mendaftarkan mahasiswa.
            </p>
            <a 
                href="{{ route('admin.kelas.create') }}" 
                class="inline-flex items-center gap-2 px-5 py-2.5 bg-[#1A2E5E] text-white text-xs font-bold rounded-xl shadow-xs hover:bg-[#152449] transition-colors"
            >
                <x-icon name="plus" :size="16" />
                <span>Buka Kelas Baru</span>
            </a>
        </div>
    @endif

    <!-- MODAL 1: Ganti Kelas Terpilih -->
    <div 
        x-show="switchClassModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    >
        <div 
            @click.away="switchClassModalOpen = false" 
            class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl max-w-md w-full space-y-4 text-xs"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-[#1A2E5E]">Pilih Kelas Perkuliahan</h3>
                <button @click="switchClassModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x-mark" :size="18" />
                </button>
            </div>

            <div class="space-y-2 max-h-80 overflow-y-auto pr-1">
                @foreach ($allClasses as $cItem)
                    <a 
                        href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $cItem->id]) }}"
                        class="block p-3 rounded-2xl border transition-colors {{ $selectedClass && $selectedClass->id === $cItem->id ? 'border-[#1A2E5E] bg-blue-50/50' : 'border-slate-200 hover:border-blue-300 hover:bg-slate-50' }}"
                    >
                        <div class="flex items-center justify-between">
                            <span class="font-mono font-bold text-[#1A2E5E]">{{ $cItem->course->code }} - Seksi {{ $cItem->section }}</span>
                            <x-admin.status-badge :status="$cItem->status" />
                        </div>
                        <div class="text-slate-800 font-semibold mt-0.5 truncate">{{ $cItem->course->name }}</div>
                        <div class="text-[11px] text-slate-400 mt-1 flex items-center justify-between">
                            <span>Dosen: {{ $cItem->lecturer?->name ?? 'Belum Ditunjuk' }}</span>
                            <span>{{ $cItem->term->name }}</span>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- MODAL 2: Tambah Mahasiswa ke Kelas -->
    <div 
        x-show="addStudentModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    >
        <div 
            @click.away="addStudentModalOpen = false" 
            class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl max-w-lg w-full space-y-4 text-xs"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <x-icon name="user-plus" :size="18" />
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-[#1A2E5E]">Daftarkan Mahasiswa</h3>
                        <p class="text-[11px] text-slate-400">Pilih mahasiswa untuk dimasukkan ke kelas {{ $selectedClass?->section }}</p>
                    </div>
                </div>
                <button @click="addStudentModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x-mark" :size="18" />
                </button>
            </div>

            @if ($selectedClass)
                <form 
                    method="POST" 
                    action="{{ route('admin.pendaftaran-mahasiswa.store', $selectedClass) }}" 
                    class="space-y-4"
                >
                    @csrf
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">
                            Pilih Mahasiswa <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="user_id" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="">-- Pilih Mahasiswa Aktif --</option>
                            @foreach ($quickAddStudents as $mhs)
                                <option value="{{ $mhs->id }}">
                                    {{ $mhs->name }} (NIM: {{ $mhs->identifier ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-slate-400 mt-1">
                            Menampilkan mahasiswa aktif yang belum mengambil kelas ini.
                        </p>
                    </div>

                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                        <button 
                            type="button" 
                            @click="addStudentModalOpen = false" 
                            class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            class="px-5 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                        >
                            Daftarkan ke Kelas
                        </button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    <!-- MODAL 3: Konfirmasi Keluarkan Mahasiswa -->
    <div 
        x-show="removeModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    >
        <div 
            @click.away="removeModalOpen = false" 
            class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl max-w-md w-full space-y-4 text-xs"
        >
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                    <x-icon name="exclamation-triangle" :size="22" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-900">Keluarkan Mahasiswa?</h3>
                    <p class="text-[11px] text-slate-500 mt-0.5">Konfirmasi pengeluaran peserta dari seksi kelas perkuliahan.</p>
                </div>
            </div>

            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-200 space-y-1">
                <div class="font-bold text-slate-800" x-text="studentToRemove.name"></div>
                <div class="font-mono text-slate-500 text-[11px]" x-text="'NIM: ' + studentToRemove.nim"></div>
                <div class="text-blue-700 text-[11px] font-semibold pt-1">
                    Seluruh nilai tugas, presensi, dan pengumpulan tugas tetap disimpan aman di database.
                </div>
            </div>

            @if ($selectedClass)
                <form 
                    method="POST" 
                    :action="'/admin/pendaftaran-mahasiswa/{{ $selectedClass->id }}/keluarkan/' + studentToRemove.id" 
                    class="pt-2 flex items-center justify-end gap-2"
                >
                    @csrf
                    @method('DELETE')
                    <button 
                        type="button" 
                        @click="removeModalOpen = false" 
                        class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-rose-600 hover:bg-rose-700 text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        Ya, Keluarkan Mahasiswa
                    </button>
                </form>
            @endif
        </div>
    </div>

</div>
@endsection
