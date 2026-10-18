@extends('layouts.admin')

@section('title', 'Manajemen Semester & Kalender Akademik — LMS Arunika')
@section('page_title', 'Semester & Kalender Akademik')
@section('page_subtitle', 'Pengaturan Siklus Perkuliahan, Status Semester Aktif, dan Masa Kunci Kelas')

@section('content')
<div x-data="{
    createModalOpen: false,
    editModalOpen: false,
    confirmActiveModalOpen: false,
    confirmCloseModalOpen: false,
    selectedTerm: null,
    editForm: {
        id: '',
        name: '',
        academic_year: '',
        semester_type: 'odd',
        start_date: '',
        end_date: ''
    },
    openEdit(term) {
        this.selectedTerm = term;
        this.editForm = {
            id: term.id,
            name: term.name,
            academic_year: term.academic_year,
            semester_type: term.semester_type,
            start_date: term.start_date ? term.start_date.substring(0, 10) : '',
            end_date: term.end_date ? term.end_date.substring(0, 10) : ''
        };
        this.editModalOpen = true;
    },
    openConfirmActive(term) {
        this.selectedTerm = term;
        this.confirmActiveModalOpen = true;
    },
    openConfirmClose(term) {
        this.selectedTerm = term;
        this.confirmCloseModalOpen = true;
    }
}" class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Total Semester:</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-[#1A2E5E]">
                {{ $summary['total'] }}
            </span>
        </div>

        <button 
            @click="createModalOpen = true"
            class="px-4 py-2.5 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
            <x-icon name="plus" :size="16" />
            <span>Tambah Semester Baru</span>
        </button>
    </div>

    <!-- Main Content Layout (8 Cols Table, 4 Cols Summary) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Table Column (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            
            <!-- Filters & Search Bar -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <!-- Status Filter Tabs -->
                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0 text-xs font-semibold">
                    <a 
                        href="{{ route('admin.semester.index', array_filter(['q' => $searchQuery])) }}"
                        class="px-3 py-1.5 rounded-xl transition-colors {{ empty($currentStatus) ? 'bg-[#1A2E5E] text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Semua ({{ $summary['total'] }})
                    </a>
                    <a 
                        href="{{ route('admin.semester.index', array_filter(['status' => 'aktif', 'q' => $searchQuery])) }}"
                        class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'aktif' ? 'bg-[#1A2E5E] text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Aktif ({{ $summary['aktif'] }})
                    </a>
                    <a 
                        href="{{ route('admin.semester.index', array_filter(['status' => 'belum_aktif', 'q' => $searchQuery])) }}"
                        class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'belum_aktif' ? 'bg-[#1A2E5E] text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Belum Aktif ({{ $summary['belum_aktif'] }})
                    </a>
                    <a 
                        href="{{ route('admin.semester.index', array_filter(['status' => 'selesai', 'q' => $searchQuery])) }}"
                        class="px-3 py-1.5 rounded-xl transition-colors {{ $currentStatus === 'selesai' ? 'bg-[#1A2E5E] text-white font-bold' : 'text-slate-600 hover:bg-slate-100' }}"
                    >
                        Selesai ({{ $summary['selesai'] }})
                    </a>
                </div>

                <!-- Search Input Form -->
                <form method="GET" action="{{ route('admin.semester.index') }}" class="relative sm:w-64">
                    @if ($currentStatus)
                        <input type="hidden" name="status" value="{{ $currentStatus }}">
                    @endif
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $searchQuery }}" 
                        placeholder="Cari tahun / nama..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    />
                    <x-icon name="magnifying-glass" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
                </form>
            </div>

            <!-- Semesters Table Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Nama Semester</th>
                                <th class="py-3 px-4">Tahun Ajaran</th>
                                <th class="py-3 px-4">Periode</th>
                                <th class="py-3 px-4 text-center">Kelas</th>
                                <th class="py-3 px-4 text-center">Status</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($semesters as $idx => $t)
                                @php
                                    $isCurrentlyActive = $t->status === 'aktif' || $t->is_active;
                                @endphp
                                <tr class="hover:bg-slate-50/60 transition-colors {{ $isCurrentlyActive ? 'bg-blue-50/20' : '' }}">
                                    <td class="py-3 px-4 text-center text-slate-400 font-mono">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3 px-4">
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $t->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 capitalize">
                                            Semester {{ $t->semester_type === 'odd' ? 'Ganjil' : ($t->semester_type === 'even' ? 'Genap' : 'Pendek') }}
                                        </div>
                                    </td>
                                    <td class="py-3 px-4 font-mono font-medium text-slate-600">
                                        {{ $t->academic_year }}
                                    </td>
                                    <td class="py-3 px-4 text-slate-500 text-[11px]">
                                        {{ $t->start_date?->format('d M Y') ?? '-' }} — {{ $t->end_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="py-3 px-4 text-center font-extrabold text-slate-700 tabular-nums">
                                        {{ $t->classes_count }}
                                    </td>
                                    <td class="py-3 px-4 text-center">
                                        <x-admin.status-badge :status="$t->status ?: ($t->is_active ? 'aktif' : 'belum_aktif')" />
                                    </td>
                                    <td class="py-3 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5" x-data="{ menuOpen: false }">
                                            
                                            <!-- Action Button: Edit -->
                                            <button 
                                                @click="openEdit({{ json_encode($t) }})"
                                                class="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit data semester"
                                            >
                                                Edit
                                            </button>

                                            <!-- Dropdown Aksi Lainnya -->
                                            <div class="relative">
                                                <button 
                                                    @click="menuOpen = !menuOpen" 
                                                    @click.outside="menuOpen = false"
                                                    class="p-1 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-lg transition-colors cursor-pointer"
                                                >
                                                    <x-icon name="ellipsis-vertical" :size="16" />
                                                </button>

                                                <div 
                                                    x-show="menuOpen" 
                                                    x-cloak 
                                                    x-transition 
                                                    class="absolute right-0 mt-1 w-44 bg-white rounded-xl shadow-lg border border-slate-200/80 py-1.5 z-30 text-left text-xs"
                                                >
                                                    @if (!$isCurrentlyActive && $t->status !== 'selesai')
                                                        <button 
                                                            @click="menuOpen = false; openConfirmActive({{ json_encode($t) }})"
                                                            class="w-full px-3 py-1.5 text-left text-emerald-700 hover:bg-emerald-50 font-semibold flex items-center gap-2 cursor-pointer"
                                                        >
                                                            <x-icon name="check-circle" :size="14" />
                                                            <span>Jadikan Aktif</span>
                                                        </button>
                                                    @endif

                                                    @if ($isCurrentlyActive)
                                                        <button 
                                                            @click="menuOpen = false; openConfirmClose({{ json_encode($t) }})"
                                                            class="w-full px-3 py-1.5 text-left text-amber-700 hover:bg-amber-50 font-semibold flex items-center gap-2 cursor-pointer"
                                                        >
                                                            <x-icon name="lock-closed" :size="14" />
                                                            <span>Tutup Semester</span>
                                                        </button>
                                                    @endif

                                                    @if ($t->classes_count === 0 && !$isCurrentlyActive)
                                                        <form method="POST" action="{{ route('admin.semester.destroy', $t) }}" onsubmit="return confirm('Hapus semester kosong {{ $t->name }}?');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button 
                                                                type="submit" 
                                                                class="w-full px-3 py-1.5 text-left text-rose-600 hover:bg-rose-50 font-semibold flex items-center gap-2 cursor-pointer"
                                                            >
                                                                <x-icon name="trash" :size="14" />
                                                                <span>Hapus Semester</span>
                                                            </button>
                                                        </form>
                                                    @elseif ($t->classes_count > 0)
                                                        <span class="px-3 py-1.5 text-slate-400 block text-[11px]" title="Tidak dapat dihapus karena memiliki kelas">
                                                            Terkunci (Ada Kelas)
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                            <x-icon name="calendar" :size="24" />
                                        </div>
                                        <p class="font-bold text-slate-700 text-xs">Tidak Ada Semester Ditemukan</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Coba gunakan filter lain atau buat semester baru.</p>
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
            
            <!-- Card Semester Aktif Terpilih -->
            @if ($summary['currentActive'])
                @php $curr = $summary['currentActive']; @endphp
                <div class="bg-[#1A2E5E] rounded-2xl p-5 text-white shadow-xs">
                    <div class="flex items-center justify-between pb-3 border-b border-white/10 mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-200">
                            Semester Aktif Utama
                        </span>
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-500 text-white">
                            LIVE
                        </span>
                    </div>

                    <h3 class="text-lg font-extrabold text-white leading-tight">
                        {{ $curr->name }}
                    </h3>
                    <p class="text-xs text-blue-200 mt-1">
                        Tahun Ajaran {{ $curr->academic_year }}
                    </p>

                    <div class="mt-4 p-3 rounded-xl bg-white/10 border border-white/10 text-xs space-y-2">
                        <div class="flex justify-between">
                            <span class="text-blue-200">Periode Perkuliahan</span>
                            <span class="font-bold text-white">{{ $curr->start_date?->format('d M Y') }} - {{ $curr->end_date?->format('d M Y') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-blue-200">Kelas Dibuka</span>
                            <span class="font-extrabold text-white tabular-nums">{{ $curr->classes()->count() }} Kelas</span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/10">
                        <button 
                            @click="openConfirmClose({{ json_encode($curr) }})"
                            class="w-full py-2 bg-amber-500 hover:bg-amber-600 text-slate-900 font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <x-icon name="lock-closed" :size="16" />
                            <span>Tutup Semester Ini</span>
                        </button>
                    </div>
                </div>
            @endif

            <!-- Ringkasan Statistik 2x2 Grid -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <h3 class="text-sm font-bold text-[#1A2E5E] mb-3">Ringkasan Kalender</h3>
                
                <div class="grid grid-cols-2 gap-3 text-center">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Total</span>
                        <span class="text-xl font-extrabold text-[#1A2E5E] tabular-nums mt-0.5 block">
                            {{ $summary['total'] }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <span class="text-[10px] uppercase font-bold text-emerald-600 block tracking-wider">Aktif</span>
                        <span class="text-xl font-extrabold text-emerald-700 tabular-nums mt-0.5 block">
                            {{ $summary['aktif'] }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-blue-50 border border-blue-100">
                        <span class="text-[10px] uppercase font-bold text-blue-600 block tracking-wider">Belum Aktif</span>
                        <span class="text-xl font-extrabold text-blue-700 tabular-nums mt-0.5 block">
                            {{ $summary['belum_aktif'] }}
                        </span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block tracking-wider">Selesai</span>
                        <span class="text-xl font-extrabold text-slate-600 tabular-nums mt-0.5 block">
                            {{ $summary['selesai'] }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Petunjuk & Aturan Akademik Info Box -->
            <div class="bg-blue-50/60 border border-blue-200/80 rounded-2xl p-4 text-xs text-blue-900 space-y-2">
                <div class="flex items-center gap-2 font-bold text-blue-900">
                    <x-icon name="information-circle" :size="18" class="text-blue-600 shrink-0" />
                    <span>Ketentuan Kalender Akademik</span>
                </div>
                <ul class="list-disc list-inside space-y-1 text-slate-600 text-[11px] leading-relaxed">
                    <li>Hanya boleh tepat <strong>1 semester berstatus aktif</strong> pada satu waktu.</li>
                    <li>Mengaktifkan semester baru otomatis menonaktifkan semester sebelumnya.</li>
                    <li>Aksi <strong>Tutup Semester</strong> mengunci seluruh nilai, presensi, dan kelas di dalamnya menjadi permanen (read-only).</li>
                </ul>
            </div>

        </div>

    </div>

    <!-- MODAL: Tambah Semester Baru -->
    <div 
        x-show="createModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
        <div 
            @click.outside="createModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <x-icon name="calendar" :size="18" />
                    </div>
                    <h3 class="text-base font-bold text-[#1A2E5E]">Tambah Semester Baru</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.semester.store') }}" class="space-y-4 text-xs">
                @csrf
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Semester <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="Contoh: Semester Ganjil 2026/2027" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="academic_year" 
                            required 
                            placeholder="2026/2027" 
                            pattern="^\d{4}\/\d{4}$"
                            title="Format: YYYY/YYYY (contoh: 2026/2027)"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Semester <span class="text-rose-500">*</span></label>
                        <select 
                            name="semester_type" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        >
                            <option value="odd">Ganjil</option>
                            <option value="even">Genap</option>
                            <option value="short">Pendek / Antara</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="start_date" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="end_date" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="pt-2">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" class="rounded text-blue-600 focus:ring-blue-500" />
                        <span class="text-slate-700 font-medium">Jadikan sebagai Semester Aktif utama sekarang</span>
                    </label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="createModalOpen = false" 
                        class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        Simpan Semester
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Semester -->
    <div 
        x-show="editModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
        <div 
            @click.outside="editModalOpen = false"
            class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5"
        >
            <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center">
                        <x-icon name="pencil" :size="18" />
                    </div>
                    <h3 class="text-base font-bold text-[#1A2E5E]">Edit Semester</h3>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <form 
                method="POST" 
                :action="'/admin/semester/' + editForm.id" 
                class="space-y-4 text-xs"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Semester <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="editForm.name" 
                        required 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tahun Ajaran <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="academic_year" 
                            x-model="editForm.academic_year" 
                            required 
                            pattern="^\d{4}\/\d{4}$"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jenis Semester <span class="text-rose-500">*</span></label>
                        <select 
                            name="semester_type" 
                            x-model="editForm.semester_type" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        >
                            <option value="odd">Ganjil</option>
                            <option value="even">Genap</option>
                            <option value="short">Pendek / Antara</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Mulai <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="start_date" 
                            x-model="editForm.start_date" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Selesai <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="end_date" 
                            x-model="editForm.end_date" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                        />
                    </div>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="editModalOpen = false" 
                        class="px-4 py-2 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                    >
                        Batal
                    </button>
                    <button 
                        type="submit" 
                        class="px-4 py-2 bg-[#1A2E5E] hover:bg-[#152449] text-white font-bold rounded-xl shadow-xs transition-colors cursor-pointer"
                    >
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Konfirmasi Jadikan Aktif -->
    <div 
        x-show="confirmActiveModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
        <div 
            @click.outside="confirmActiveModalOpen = false"
            class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
        >
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <x-icon name="check-circle" :size="24" />
            </div>
            <div>
                <h3 class="text-base font-bold text-[#1A2E5E]">Aktifkan Semester Ini?</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Anda akan menetapkan <strong x-text="selectedTerm?.name"></strong> sebagai Semester Aktif utama. Semester aktif saat ini akan dinonaktifkan secara otomatis.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button 
                    @click="confirmActiveModalOpen = false" 
                    class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                    Batal
                </button>
                <form :action="'/admin/semester/' + selectedTerm?.id + '/aktifkan'" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl shadow-xs"
                    >
                        Ya, Aktifkan Semester
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL: Konfirmasi Tutup Semester -->
    <div 
        x-show="confirmCloseModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs"
    >
        <div 
            @click.outside="confirmCloseModalOpen = false"
            class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 space-y-4"
        >
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center">
                <x-icon name="exclamation-triangle" :size="24" />
            </div>
            <div>
                <h3 class="text-base font-bold text-[#1A2E5E]">Tutup Semester Akademik?</h3>
                <p class="text-xs text-slate-600 mt-1 leading-relaxed">
                    Menutup <strong x-text="selectedTerm?.name"></strong> akan mengubah statusnya menjadi <strong>Selesai</strong> dan mengunci seluruh kelas di dalamnya secara permanen (read-only). Dosen tidak akan dapat lagi mengubah nilai atau presensi.
                </p>
            </div>
            <div class="flex items-center justify-end gap-2 pt-2">
                <button 
                    @click="confirmCloseModalOpen = false" 
                    class="px-4 py-2 text-xs font-bold text-slate-600 hover:bg-slate-100 rounded-xl"
                >
                    Batal
                </button>
                <form :action="'/admin/semester/' + selectedTerm?.id + '/tutup'" method="POST">
                    @csrf
                    <button 
                        type="submit" 
                        class="px-4 py-2 text-xs font-bold bg-amber-600 hover:bg-amber-700 text-white rounded-xl shadow-xs"
                    >
                        Ya, Tutup Semester
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
