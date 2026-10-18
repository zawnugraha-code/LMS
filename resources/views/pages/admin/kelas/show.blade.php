@extends('layouts.admin')

@section('title', '[' . $class->course->code . '] ' . $class->section . ' - Detail Kelas')

@section('content')
@php
    $enrolledCount = $class->activeEnrollments->count();
    $capacity = (int) $class->capacity;
    $capacityPct = $capacity > 0 ? min(100, round(($enrolledCount / $capacity) * 100)) : 0;
    $currentTab = request()->query('tab', 'peserta');
@endphp

<div 
    class="space-y-6"
    x-data="{
        activeTab: '{{ $currentTab }}',
        editMeetingModalOpen: false,
        meetingForm: {
            id: null,
            week: 1,
            title: '',
            date: '',
            mode: 'offline',
            start_time: '',
            end_time: '',
            description: ''
        },
        openEditMeeting(m) {
            this.meetingForm = {
                id: m.id,
                week: m.week,
                title: m.title,
                date: m.date ? m.date.substring(0, 10) : '',
                mode: m.mode || 'offline',
                start_time: m.start_time ? m.start_time.substring(0, 5) : '',
                end_time: m.end_time ? m.end_time.substring(0, 5) : '',
                description: m.description || ''
            };
            this.editMeetingModalOpen = true;
        }
    }"
>

    <!-- Header Breadcrumb & Back -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
        <div class="flex items-center gap-2 text-xs text-slate-500">
            <a href="{{ route('admin.kelas.index') }}" class="hover:text-blue-600 font-semibold transition-colors">
                Kelas Perkuliahan
            </a>
            <span>&bull;</span>
            <span class="text-slate-800 font-bold">[{{ $class->course->code }}] {{ $class->section }}</span>
        </div>

        <a 
            href="{{ route('admin.kelas.index') }}"
            class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-colors cursor-pointer"
        >
            Kembali ke Daftar Kelas
        </a>
    </div>

    <!-- Hero Card Banner Kelas -->
    <div class="bg-white border border-slate-200/80 rounded-3xl p-6 shadow-xs relative overflow-hidden">
        <div class="absolute right-0 top-0 w-80 h-full bg-gradient-to-l from-blue-50/70 to-transparent pointer-events-none"></div>

        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6 relative z-10">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="font-mono text-xs px-2.5 py-1 rounded-lg bg-blue-100 text-[#1A2E5E] font-black">
                        {{ $class->course->code }}
                    </span>
                    <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 font-mono font-bold">
                        Seksi {{ $class->section }}
                    </span>
                    <x-admin.status-badge :status="$class->status" />
                    <span class="text-xs text-slate-400 font-semibold">
                        &bull; {{ $class->term->name }}
                    </span>
                </div>

                <h1 class="text-2xl lg:text-3xl font-black text-[#1A2E5E] tracking-tight">
                    {{ $class->course->name }}
                </h1>

                <div class="flex flex-wrap items-center gap-4 text-xs text-slate-500 pt-1">
                    <div class="flex items-center gap-1.5 font-medium">
                        <x-icon name="user" :size="15" class="text-blue-600" />
                        <span>Dosen:</span>
                        <strong class="text-slate-800">
                            {{ $class->lecturer ? $class->lecturer->name : 'Belum Ditunjuk' }}
                        </strong>
                    </div>

                    <div class="flex items-center gap-1.5 font-medium">
                        <x-icon name="clock" :size="15" class="text-blue-600" />
                        <span>{{ $class->schedule_day ?? 'Hari -' }}, {{ substr((string)$class->start_time, 0, 5) }} - {{ substr((string)$class->end_time, 0, 5) }}</span>
                    </div>

                    <div class="flex items-center gap-1.5 font-medium">
                        <x-icon name="map-pin" :size="15" class="text-blue-600" />
                        <span>{{ $class->room ?? 'Ruang belum diatur' }}</span>
                    </div>

                    <div class="flex items-center gap-1.5 font-medium">
                        <x-icon name="academic-cap" :size="15" class="text-blue-600" />
                        <span>{{ $class->course->credits }} SKS (Semester {{ $class->course->semester }})</span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons Bar -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a 
                    href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $class->id]) }}"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <x-icon name="user-plus" :size="15" />
                    <span>Kelola Peserta</span>
                </a>

                <a 
                    href="{{ route('admin.kelas.edit', $class) }}"
                    class="px-4 py-2 bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 text-xs font-bold rounded-xl shadow-xs transition-colors flex items-center gap-1.5 cursor-pointer"
                >
                    <x-icon name="pencil-square" :size="15" class="text-amber-600" />
                    <span>Edit Konfigurasi</span>
                </a>

                @if ($class->status === 'aktif')
                    <form method="POST" action="{{ route('admin.kelas.finish', $class) }}" onsubmit="return confirm('Selesaikan kelas ini? Kelas akan dikunci menjadi read-only.');">
                        @csrf
                        <button 
                            type="submit"
                            class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5 cursor-pointer"
                        >
                            <x-icon name="lock-closed" :size="14" class="text-slate-500" />
                            <span>Selesaikan</span>
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <!-- Progress Bar Kapasitas Kursi -->
        <div class="mt-6 pt-4 border-t border-slate-100 grid grid-cols-1 sm:grid-cols-4 gap-4 items-center text-xs">
            <div class="sm:col-span-3 space-y-1.5">
                <div class="flex justify-between items-center text-[11px]">
                    <span class="text-slate-500 font-semibold">Keterisian Kuota Mahasiswa:</span>
                    <span class="font-extrabold text-[#1A2E5E] tabular-nums">
                        {{ $enrolledCount }} dari {{ $capacity }} Kursi Terisi ({{ $capacityPct }}%)
                    </span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div 
                        class="h-full rounded-full transition-all duration-300 {{ $capacityPct >= 100 ? 'bg-rose-500' : ($capacityPct >= 85 ? 'bg-amber-500' : 'bg-[#1A2E5E]') }}" 
                        style="width: {{ $capacityPct }}%"
                    ></div>
                </div>
            </div>

            <div class="text-right sm:border-l sm:border-slate-100 sm:pl-4">
                <span class="text-[11px] text-slate-400 block font-medium">Sisa Kursi:</span>
                <span class="text-lg font-black text-emerald-700 tabular-nums">
                    {{ max(0, $capacity - $enrolledCount) }} Kursi
                </span>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs (4 Tabs) -->
    <div class="bg-white border border-slate-200/80 rounded-2xl p-1.5 shadow-xs flex items-center gap-1 overflow-x-auto text-xs font-bold">
        <button 
            type="button"
            @click="activeTab = 'peserta'"
            :class="activeTab === 'peserta' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer"
        >
            <x-icon name="user-group" :size="16" />
            <span>Peserta Kelas ({{ $enrolledCount }})</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'pertemuan'"
            :class="activeTab === 'pertemuan' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer"
        >
            <x-icon name="calendar" :size="16" />
            <span>Jadwal &amp; Pertemuan ({{ $class->meetings->count() }})</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'komponen'"
            :class="activeTab === 'komponen' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer"
        >
            <x-icon name="calculator" :size="16" />
            <span>Komponen Penilaian (OBE)</span>
        </button>

        <button 
            type="button"
            @click="activeTab = 'aktivitas'"
            :class="activeTab === 'aktivitas' ? 'bg-[#1A2E5E] text-white shadow-xs' : 'text-slate-600 hover:bg-slate-100'"
            class="px-4 py-2 rounded-xl transition-colors flex items-center gap-2 whitespace-nowrap cursor-pointer"
        >
            <x-icon name="chart-bar" :size="16" />
            <span>Ringkasan Aktivitas</span>
        </button>
    </div>

    <!-- TAB 1: PESERTA KELAS -->
    <div x-show="activeTab === 'peserta'" x-cloak class="space-y-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-xs overflow-hidden">
            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-bold text-[#1A2E5E]">Daftar Mahasiswa Terdaftar</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Mahasiswa resmi yang mengambil kelas ini pada semester aktif.</p>
                </div>

                <a 
                    href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $class->id]) }}"
                    class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold rounded-xl transition-colors cursor-pointer"
                >
                    + Daftarkan / Keluarkan Mahasiswa
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-slate-600">
                    <thead class="bg-slate-50/80 text-slate-400 uppercase text-[10px] tracking-wider font-extrabold border-b border-slate-200/80">
                        <tr>
                            <th class="py-3 px-4 w-10 text-center">No</th>
                            <th class="py-3 px-4">Mahasiswa</th>
                            <th class="py-3 px-4">NIM</th>
                            <th class="py-3 px-4">Program Studi</th>
                            <th class="py-3 px-4">Tanggal Bergabung</th>
                            <th class="py-3 px-4 text-center">Status</th>
                            <th class="py-3 px-4 text-center">Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($class->activeEnrollments as $i => $enr)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 text-center font-mono text-slate-400">
                                    {{ $i + 1 }}
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <div class="w-7 h-7 rounded-full bg-slate-100 text-[#1A2E5E] font-extrabold flex items-center justify-center text-[10px] shrink-0">
                                            {{ substr($enr->user->name, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-slate-800">{{ $enr->user->name }}</div>
                                            <div class="text-[11px] text-slate-400">{{ $enr->user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3 px-4 font-mono font-semibold text-slate-700">
                                    {{ $enr->user->identifier ?? '-' }}
                                </td>
                                <td class="py-3 px-4 text-slate-600">
                                    {{ $enr->user->prodi ?? ($class->course->curriculum->program->name ?? 'S1 Teknik Informatika') }}
                                </td>
                                <td class="py-3 px-4 text-slate-500 text-[11px]">
                                    {{ $enr->created_at ? $enr->created_at->format('d M Y') : '-' }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800">
                                        Terdaftar
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-center font-mono font-bold text-slate-700">
                                    @if ($enr->letter_grade)
                                        <span class="px-2 py-0.5 rounded bg-blue-50 text-blue-700">
                                            {{ $enr->letter_grade }} ({{ $enr->final_score }})
                                        </span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-10 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <x-icon name="user-group" :size="28" class="text-slate-300" />
                                        <p class="font-semibold text-xs text-slate-500">Belum ada mahasiswa yang didaftarkan ke kelas ini.</p>
                                        <a 
                                            href="{{ route('admin.pendaftaran-mahasiswa.index', ['class_id' => $class->id]) }}" 
                                            class="text-xs text-blue-600 hover:underline font-bold mt-1"
                                        >
                                            Buka Halaman Pendaftaran Mahasiswa
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

    <!-- TAB 2: JADWAL & PERTEMUAN -->
    <div x-show="activeTab === 'pertemuan'" x-cloak class="space-y-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs flex items-center justify-between text-xs">
            <div>
                <h3 class="font-bold text-[#1A2E5E]">Linimasa Pertemuan Perkuliahan (16 Pekan)</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">
                    Admin memiliki wewenang menyesuaikan tanggal atau topik pertemuan jika bertepatan dengan hari libur nasional.
                </p>
            </div>
            <div class="text-[11px] text-slate-500 font-semibold">
                {{ $class->meetings->count() }} Pertemuan Tercatat
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach ($class->meetings as $meeting)
                <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-xs space-y-2 hover:border-blue-200 transition-colors">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 rounded-lg bg-[#1A2E5E] text-white font-black text-[11px] flex items-center justify-center shrink-0">
                                {{ $meeting->week }}
                            </span>
                            <span class="font-bold text-slate-800 text-xs">
                                {{ $meeting->title }}
                            </span>
                        </div>

                        <button 
                            type="button" 
                            @click="openEditMeeting({{ json_encode($meeting) }})"
                            class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-blue-600 transition-colors cursor-pointer"
                            title="Sesuaikan Tanggal / Topik"
                        >
                            <x-icon name="pencil-square" :size="14" />
                        </button>
                    </div>

                    <p class="text-[11px] text-slate-500 line-clamp-2">
                        {{ $meeting->description ?? 'Tidak ada rincian silabus khusus untuk pekan ini.' }}
                    </p>

                    <div class="pt-2 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400 font-medium">
                        <div class="flex items-center gap-1.5 text-slate-700 font-semibold">
                            <x-icon name="calendar" :size="13" class="text-blue-600" />
                            <span>{{ $meeting->date ? $meeting->date->format('d M Y') : 'Tanggal belum diatur' }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.2 rounded-full uppercase text-[9px] font-bold {{ $meeting->mode === 'online' ? 'bg-amber-100 text-amber-800' : 'bg-blue-50 text-blue-700' }}">
                                {{ $meeting->mode }}
                            </span>
                            <span>{{ substr((string)$meeting->start_time, 0, 5) }} - {{ substr((string)$meeting->end_time, 0, 5) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- TAB 3: KOMPONEN PENILAIAN (OBE) -->
    <div x-show="activeTab === 'komponen'" x-cloak class="space-y-4">
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs space-y-4">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div>
                    <h3 class="text-sm font-bold text-[#1A2E5E]">Struktur Komponen Penilaian Akademik</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Bobot penilaian OBE yang terintegrasi pada modul Buku Nilai Dosen.</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-extrabold bg-blue-100 text-[#1A2E5E]">
                    Total Bobot: 100%
                </span>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @foreach ($class->assessmentComponents as $comp)
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 text-center space-y-1">
                        <div class="text-[11px] uppercase font-bold text-slate-500 tracking-wider">
                            {{ ucfirst($comp->jenis) }}
                        </div>
                        <div class="text-2xl font-black text-[#1A2E5E] tabular-nums">
                            {{ $comp->bobot }}%
                        </div>
                        <div class="text-[10px] text-slate-400">Bobot Nilai Akhir</div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs text-blue-900 flex items-start gap-3">
                <x-icon name="information-circle" :size="20" class="text-blue-600 shrink-0 mt-0.5" />
                <div>
                    <strong class="font-bold">Otoritas Penyesuaian Bobot:</strong>
                    <p class="mt-0.5 text-[11px] text-blue-800 leading-relaxed">
                        Dosen pengampu dapat menyesuaikan porsi bobot antar-komponen ini melalui portal Buku Nilai Dosen asalkan akumulasi total bobot tetap tepat berjumlah 100%.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- TAB 4: RINGKASAN AKTIVITAS (READ-ONLY) -->
    <div x-show="activeTab === 'aktivitas'" x-cloak class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs text-center space-y-1">
                <div class="text-slate-400 text-xs font-semibold">Materi Pembelajaran</div>
                <div class="text-2xl font-black text-[#1A2E5E] tabular-nums">{{ $class->materials->count() }}</div>
                <div class="text-[10px] text-slate-400">Dokumen / Slide</div>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs text-center space-y-1">
                <div class="text-slate-400 text-xs font-semibold">Tugas & Penilaian</div>
                <div class="text-2xl font-black text-[#1A2E5E] tabular-nums">{{ $class->assignments->count() }}</div>
                <div class="text-[10px] text-slate-400">Tugas / Kuis / Ujian</div>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs text-center space-y-1">
                <div class="text-slate-400 text-xs font-semibold">Pertemuan Terjadwal</div>
                <div class="text-2xl font-black text-emerald-700 tabular-nums">{{ $class->meetings->count() }}</div>
                <div class="text-[10px] text-slate-400">Sesi Mingguan</div>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs text-center space-y-1">
                <div class="text-slate-400 text-xs font-semibold">Utas Diskusi</div>
                <div class="text-2xl font-black text-[#1A2E5E] tabular-nums">{{ $class->discussionThreads->count() }}</div>
                <div class="text-[10px] text-slate-400">Pertanyaan & Jawaban</div>
            </div>
        </div>
    </div>

    <!-- MODAL: Sesuaikan Jadwal / Topik Pertemuan -->
    <div 
        x-show="editMeetingModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/40 backdrop-blur-xs"
    >
        <div 
            @click.away="editMeetingModalOpen = false" 
            class="bg-white border border-slate-200 rounded-3xl p-6 shadow-xl max-w-lg w-full space-y-4"
        >
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <span class="w-7 h-7 rounded-lg bg-[#1A2E5E] text-white font-black text-xs flex items-center justify-center" x-text="meetingForm.week"></span>
                    <h3 class="text-base font-bold text-[#1A2E5E]">Sesuaikan Pertemuan Mingguan</h3>
                </div>
                <button @click="editMeetingModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1 cursor-pointer">
                    <x-icon name="x-mark" :size="18" />
                </button>
            </div>

            <form 
                method="POST" 
                :action="'/admin/kelas/{{ $class->id }}/pertemuan/' + meetingForm.id" 
                class="space-y-4 text-xs"
            >
                @csrf
                @method('PUT')

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Judul / Topik Pembahasan <span class="text-rose-500">*</span></label>
                    <input 
                        type="text" 
                        name="title" 
                        x-model="meetingForm.title" 
                        required 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Tanggal Perkuliahan <span class="text-rose-500">*</span></label>
                        <input 
                            type="date" 
                            name="date" 
                            x-model="meetingForm.date" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mode Perkuliahan <span class="text-rose-500">*</span></label>
                        <select 
                            name="mode" 
                            x-model="meetingForm.mode" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="offline">Tatap Muka (Offline)</option>
                            <option value="online">Daring (Online)</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Mulai</label>
                        <input 
                            type="time" 
                            name="start_time" 
                            x-model="meetingForm.start_time" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jam Selesai</label>
                        <input 
                            type="time" 
                            name="end_time" 
                            x-model="meetingForm.end_time" 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                        />
                    </div>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">Deskripsi / Catatan Tambahan</label>
                    <textarea 
                        name="description" 
                        x-model="meetingForm.description" 
                        rows="2" 
                        class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                    ></textarea>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-2">
                    <button 
                        type="button" 
                        @click="editMeetingModalOpen = false" 
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
