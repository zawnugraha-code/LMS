@extends('layouts.admin')

@section('title', 'Katalog Mata Kuliah — LMS Arunika')
@section('page_title', 'Katalog Mata Kuliah')
@section('page_subtitle', 'Master Kurikulum Berbasis OBE (Outcome-Based Education) dan Distribusi Beban SKS')

@section('content')
<div x-data="{
    createModalOpen: false,
    editModalOpen: false,
    selectedCourse: null,
    editForm: {
        id: '',
        code: '',
        name: '',
        credits: 3,
        semester: 1,
        curriculum_id: '',
        description: '',
        syllabus: ''
    },
    openEdit(c) {
        this.selectedCourse = c;
        this.editForm = {
            id: c.id,
            code: c.code,
            name: c.name,
            credits: c.credits,
            semester: c.semester,
            curriculum_id: c.curriculum_id,
            description: c.description || '',
            syllabus: c.syllabus || ''
        };
        this.editModalOpen = true;
    }
}" class="space-y-6">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="text-xs font-semibold text-slate-500">Total Mata Kuliah:</span>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-blue-100 text-[#1A2E5E]">
                {{ $summary['totalMk'] }} MK ({{ $summary['totalSks'] }} SKS)
            </span>
        </div>

        <button 
            @click="createModalOpen = true"
            class="px-4 py-2.5 bg-[#1A2E5E] hover:bg-[#152449] text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center justify-center gap-2 cursor-pointer shrink-0"
        >
            <x-icon name="plus" :size="16" />
            <span>Tambah Mata Kuliah</span>
        </button>
    </div>

    <!-- Main Content Layout (8 Cols Table, 4 Cols Summary) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Table Column (8 Cols) -->
        <div class="lg:col-span-8 space-y-4">
            
            <!-- Filters & Search Bar -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                
                <!-- Filter Dropdown Program Studi -->
                <form method="GET" action="{{ route('admin.mata-kuliah.index') }}" class="flex items-center gap-2 w-full sm:w-auto">
                    @if ($searchQuery)
                        <input type="hidden" name="q" value="{{ $searchQuery }}">
                    @endif
                    <label class="text-xs text-slate-500 font-semibold shrink-0">Prodi:</label>
                    <select 
                        name="program_id" 
                        onchange="this.form.submit()"
                        class="px-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer w-full sm:w-56"
                    >
                        <option value="">Semua Program Studi</option>
                        @foreach ($summary['programs'] as $prog)
                            <option value="{{ $prog->id }}" {{ $selectedProgramId == $prog->id ? 'selected' : '' }}>
                                {{ $prog->name }}
                            </option>
                        @endforeach
                    </select>
                </form>

                <!-- Search Input Form -->
                <form method="GET" action="{{ route('admin.mata-kuliah.index') }}" class="relative w-full sm:w-64">
                    @if ($selectedProgramId)
                        <input type="hidden" name="program_id" value="{{ $selectedProgramId }}">
                    @endif
                    <input 
                        type="text" 
                        name="q" 
                        value="{{ $searchQuery }}" 
                        placeholder="Cari kode / nama MK..."
                        class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
                    />
                    <x-icon name="magnifying-glass" :size="14" class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400" />
                </form>

            </div>

            <!-- Table Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/80 border-b border-slate-200/80 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                <th class="py-3 px-4 w-12 text-center">No</th>
                                <th class="py-3 px-4">Kode MK</th>
                                <th class="py-3 px-4">Nama Mata Kuliah</th>
                                <th class="py-3 px-4 text-center">SKS</th>
                                <th class="py-3 px-4 text-center">Semester</th>
                                <th class="py-3 px-4 text-center">Kelas Dibuka</th>
                                <th class="py-3 px-4 text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            @forelse ($courses as $idx => $c)
                                <tr class="hover:bg-slate-50/60 transition-colors">
                                    <td class="py-3.5 px-4 text-center text-slate-400 font-mono">
                                        {{ $idx + 1 }}
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-800 border border-blue-200/60 font-mono">
                                            {{ $c->code }}
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4">
                                        <div class="font-bold text-slate-800 text-xs">
                                            {{ $c->name }}
                                        </div>
                                        <div class="text-[11px] text-slate-400 mt-0.5 truncate max-w-sm">
                                            {{ $c->curriculum?->program?->name ?? 'S1 Teknik Informatika' }}
                                        </div>
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-slate-100 text-[#1A2E5E] tabular-nums">
                                            {{ $c->credits }} SKS
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-center text-slate-600 font-semibold tabular-nums">
                                        Sem. {{ $c->semester }}
                                    </td>
                                    <td class="py-3.5 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded-full text-[11px] font-bold tabular-nums {{ $c->active_classes_count > 0 ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-400' }}">
                                            {{ $c->active_classes_count }} Kelas
                                        </span>
                                    </td>
                                    <td class="py-3.5 px-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button 
                                                @click="openEdit({{ json_encode($c) }})"
                                                class="px-2.5 py-1 text-xs font-semibold text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded-lg transition-colors cursor-pointer"
                                                title="Edit data mata kuliah"
                                            >
                                                Edit
                                            </button>

                                            @if ($c->total_classes_count === 0)
                                                <form method="POST" action="{{ route('admin.mata-kuliah.destroy', $c) }}" onsubmit="return confirm('Hapus mata kuliah {{ $c->code }} - {{ $c->name }}?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button 
                                                        type="submit" 
                                                        class="px-2 py-1 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer"
                                                        title="Hapus mata kuliah"
                                                    >
                                                        Hapus
                                                    </button>
                                                </form>
                                            @else
                                                <span 
                                                    class="px-2 py-1 text-[11px] text-slate-300 font-medium cursor-not-allowed" 
                                                    title="Mata kuliah tidak dapat dihapus karena telah memiliki {{ $c->total_classes_count }} kelas di sistem"
                                                >
                                                    Hapus
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-12 text-center text-slate-400">
                                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center mx-auto mb-2 text-slate-400">
                                            <x-icon name="book-open" :size="24" />
                                        </div>
                                        <p class="font-bold text-slate-700 text-xs">Mata Kuliah Tidak Ditemukan</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5">Coba sesuaikan kata kunci pencarian atau tambah mata kuliah baru.</p>
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
            
            <!-- Card Ringkasan Katalog -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 class="text-sm font-bold text-[#1A2E5E]">Ringkasan Kurikulum</h3>
                
                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-600">Total Mata Kuliah</span>
                        <span class="text-base font-extrabold text-[#1A2E5E] tabular-nums">{{ $summary['totalMk'] }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-blue-50 border border-blue-100">
                        <span class="text-blue-900 font-medium">Beban Kredit SKS</span>
                        <span class="text-base font-extrabold text-blue-700 tabular-nums">{{ $summary['totalSks'] }} SKS</span>
                    </div>
                    <div class="flex items-center justify-between p-3 rounded-xl bg-emerald-50 border border-emerald-100">
                        <span class="text-emerald-900 font-medium">Kelas Dibuka Semester Aktif</span>
                        <span class="text-base font-extrabold text-emerald-700 tabular-nums">{{ $summary['activeClassesCount'] }} Kelas</span>
                    </div>
                </div>
            </div>

            <!-- Card Distribusi per Prodi -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <h3 class="text-sm font-bold text-[#1A2E5E]">Distribusi Program Studi</h3>

                <div class="space-y-3 text-xs">
                    @foreach ($summary['prodiDistribusi'] as $dist)
                        <div class="space-y-1.5">
                            <div class="flex justify-between items-center text-[11px]">
                                <span class="font-bold text-slate-700">{{ $dist['name'] }}</span>
                                <span class="font-extrabold text-[#1A2E5E] tabular-nums">{{ $dist['count'] }} MK ({{ $dist['percentage'] }}%)</span>
                            </div>
                            <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                                <div class="bg-[#1A2E5E] h-full rounded-full" style="width: {{ $dist['percentage'] }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Petunjuk Kurikulum OBE Info Box -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 text-xs text-slate-700 space-y-2">
                <div class="flex items-center gap-2 font-bold text-[#1A2E5E]">
                    <x-icon name="information-circle" :size="18" class="text-blue-600 shrink-0" />
                    <span>Integritas Data Kurikulum</span>
                </div>
                <p class="text-[11px] text-slate-500 leading-relaxed">
                    Mata kuliah yang telah memiliki kelas perkuliahan aktif maupun selesai <strong>tidak dapat dihapus</strong> guna menjaga konsistensi transkrip akademik dan portofolio nilai mahasiswa.
                </p>
            </div>

        </div>

    </div>

    <!-- MODAL: Tambah Mata Kuliah -->
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
                    <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center">
                        <x-icon name="book-open" :size="18" />
                    </div>
                    <h3 class="text-base font-bold text-[#1A2E5E]">Tambah Mata Kuliah Baru</h3>
                </div>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <form method="POST" action="{{ route('admin.mata-kuliah.store') }}" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kurikulum & Program Studi <span class="text-rose-500">*</span></label>
                    <select 
                        name="curriculum_id" 
                        required 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        @foreach ($curriculums as $curr)
                            <option value="{{ $curr->id }}">
                                {{ $curr->program?->name }} ({{ $curr->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode MK <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="code" 
                            required 
                            placeholder="IF2105" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono uppercase"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">SKS <span class="text-rose-500">*</span></label>
                        <input 
                            type="number" 
                            name="credits" 
                            required 
                            min="1" 
                            max="6" 
                            value="3" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                        <input 
                            type="number" 
                            name="semester" 
                            required 
                            min="1" 
                            max="8" 
                            value="3" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Mata Kuliah <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        required 
                        placeholder="Contoh: Pemrograman Web Lanjut" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea 
                        name="description" 
                        rows="2" 
                        placeholder="Ringkasan ruang lingkup mata kuliah..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    ></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Silabus & Garis Besar Topik</label>
                    <textarea 
                        name="syllabus" 
                        rows="3" 
                        placeholder="Daftar pokok bahasan mingguan..." 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    ></textarea>
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
                        Simpan Mata Kuliah
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Mata Kuliah -->
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
                    <h3 class="text-base font-bold text-[#1A2E5E]">Edit Mata Kuliah</h3>
                </div>
                <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x" :size="18" />
                </button>
            </div>

            <form 
                method="POST" 
                :action="'/admin/mata-kuliah/' + editForm.id" 
                class="space-y-4 text-xs"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Kurikulum & Program Studi <span class="text-rose-500">*</span></label>
                    <select 
                        name="curriculum_id" 
                        x-model="editForm.curriculum_id" 
                        required 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    >
                        @foreach ($curriculums as $curr)
                            <option value="{{ $curr->id }}">
                                {{ $curr->program?->name }} ({{ $curr->name }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Kode MK <span class="text-rose-500">*</span></label>
                        <input 
                            type="text" 
                            name="code" 
                            x-model="editForm.code" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono uppercase"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">SKS <span class="text-rose-500">*</span></label>
                        <input 
                            type="number" 
                            name="credits" 
                            x-model="editForm.credits" 
                            required 
                            min="1" 
                            max="6" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Semester <span class="text-rose-500">*</span></label>
                        <input 
                            type="number" 
                            name="semester" 
                            x-model="editForm.semester" 
                            required 
                            min="1" 
                            max="8" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Nama Mata Kuliah <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="name" 
                        x-model="editForm.name" 
                        required 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi Singkat</label>
                    <textarea 
                        name="description" 
                        x-model="editForm.description" 
                        rows="2" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    ></textarea>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Silabus & Garis Besar Topik</label>
                    <textarea 
                        name="syllabus" 
                        x-model="editForm.syllabus" 
                        rows="3" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    ></textarea>
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

</div>
@endsection
