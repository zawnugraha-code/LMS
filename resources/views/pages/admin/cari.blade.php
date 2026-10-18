@extends('layouts.admin')

@section('title', 'Pencarian: ' . ($query ?: 'Global'))

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
        <div>
            <h1 class="text-2xl font-black text-[#1A2E5E] tracking-tight">Pencarian Global</h1>
            <p class="text-xs text-slate-500 mt-1">
                @if ($query)
                    Ditemukan <strong class="text-slate-800">{{ $totalResults }} hasil</strong> untuk kata kunci <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700 font-mono font-bold">"{{ $query }}"</span>
                @else
                    Masukkan kata kunci untuk mencari data mata kuliah, kelas, semester, dosen, atau mahasiswa.
                @endif
            </p>
        </div>
    </div>

    <!-- Search Input Bar -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs">
        <form method="GET" action="{{ route('admin.cari') }}" class="relative">
            <input 
                type="text" 
                name="q" 
                value="{{ $query }}" 
                placeholder="Ketik minimal 2 karakter untuk mencari di seluruh sistem..." 
                autofocus
                class="w-full pl-10 pr-24 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all placeholder:text-slate-400"
            />
            <x-icon name="magnifying-glass" :size="18" class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400" />
            <button 
                type="submit" 
                class="absolute right-1.5 top-1/2 -translate-y-1/2 px-4 py-1.5 bg-[#1A2E5E] text-white text-xs font-bold rounded-lg hover:bg-[#152449] transition-colors cursor-pointer"
            >
                Cari
            </button>
        </form>
    </div>

    @if ($query && $totalResults > 0)

        <!-- Group 1: Kelas Perkuliahan -->
        @if ($classes->isNotEmpty())
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-2">
                        <x-icon name="academic-cap" :size="16" class="text-blue-600" />
                        <span>Kelas Perkuliahan ({{ $classes->count() }})</span>
                    </h3>
                    <a href="{{ route('admin.kelas.index', ['q' => $query]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                        Buka di Modul Kelas &gt;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @foreach ($classes as $c)
                        <a 
                            href="{{ route('admin.kelas.show', $c) }}" 
                            class="p-3 rounded-xl border border-slate-200 hover:border-blue-400 hover:bg-slate-50/70 transition-colors block space-y-1"
                        >
                            <div class="flex items-center justify-between">
                                <span class="font-mono font-bold text-[#1A2E5E]">{{ $c->course->code }} - {{ $c->section }}</span>
                                <x-admin.status-badge :status="$c->status" />
                            </div>
                            <div class="font-bold text-slate-800 truncate">{{ $c->course->name }}</div>
                            <div class="text-[11px] text-slate-500 flex items-center justify-between">
                                <span>Dosen: {{ $c->lecturer?->name ?? 'Belum Ditunjuk' }}</span>
                                <span>{{ $c->room ?? 'Ruang -' }}</span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Group 2: Mata Kuliah -->
        @if ($courses->isNotEmpty())
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-2">
                        <x-icon name="book-open" :size="16" class="text-blue-600" />
                        <span>Mata Kuliah ({{ $courses->count() }})</span>
                    </h3>
                    <a href="{{ route('admin.mata-kuliah.index', ['q' => $query]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                        Buka di Katalog MK &gt;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @foreach ($courses as $mk)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <div class="flex items-center gap-1.5 font-bold text-slate-800">
                                    <span class="font-mono text-blue-700 bg-blue-50 px-1.5 py-0.2 rounded">{{ $mk->code }}</span>
                                    <span>{{ $mk->name }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    {{ $mk->credits }} SKS &bull; Semester {{ $mk->semester }} &bull; {{ $mk->curriculum?->program?->name ?? 'Teknik Informatika' }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Group 3: Semester -->
        @if ($terms->isNotEmpty())
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-2">
                        <x-icon name="calendar" :size="16" class="text-blue-600" />
                        <span>Semester Akademik ({{ $terms->count() }})</span>
                    </h3>
                    <a href="{{ route('admin.semester.index', ['q' => $query]) }}" class="text-[11px] font-bold text-blue-600 hover:underline">
                        Buka di Semester &gt;
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                    @foreach ($terms as $t)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 flex items-center justify-between">
                            <div>
                                <div class="font-bold text-slate-800">{{ $t->name }}</div>
                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">
                                    Tahun Ajaran: {{ $t->academic_year }} ({{ ucfirst($t->semester_type) }})
                                </div>
                            </div>
                            <x-admin.status-badge :status="$t->status ?? ($t->is_active ? 'aktif' : 'belum_aktif')" />
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Group 4: Dosen Pengajar -->
        @if ($lecturers->isNotEmpty())
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-2">
                        <x-icon name="user" :size="16" class="text-blue-600" />
                        <span>Dosen Pengajar ({{ $lecturers->count() }})</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    @foreach ($lecturers as $d)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                            <div class="font-bold text-slate-800 truncate" title="{{ $d->name }}">{{ $d->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $d->identifier ?? 'NIP: -' }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $d->email }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Group 5: Mahasiswa -->
        @if ($students->isNotEmpty())
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-2">
                        <x-icon name="user-group" :size="16" class="text-blue-600" />
                        <span>Mahasiswa ({{ $students->count() }})</span>
                    </h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                    @foreach ($students as $s)
                        <div class="p-3 rounded-xl border border-slate-200 bg-slate-50/50 space-y-1">
                            <div class="font-bold text-slate-800 truncate" title="{{ $s->name }}">{{ $s->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">{{ $s->identifier ?? 'NIM: -' }}</div>
                            <div class="text-[10px] text-slate-400 truncate">{{ $s->email }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    @elseif ($query)
        <div class="bg-white border border-slate-200/80 rounded-3xl p-12 text-center text-slate-400 space-y-2">
            <x-icon name="magnifying-glass" :size="40" class="mx-auto text-slate-300" />
            <h3 class="text-sm font-bold text-slate-700">Tidak Ada Hasil Ditemukan</h3>
            <p class="text-xs text-slate-400">
                Tidak ditemukan data yang cocok dengan kata kunci <span class="font-bold text-slate-600">"{{ $query }}"</span>.
            </p>
        </div>
    @endif

</div>
@endsection
