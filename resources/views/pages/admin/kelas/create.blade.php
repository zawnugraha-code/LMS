@extends('layouts.admin')

@section('title', 'Buka Kelas Baru')

@section('content')
<div 
    class="max-w-5xl mx-auto space-y-6"
    x-data="{
        term_id: '{{ old('term_id', $activeTerm?->id) }}',
        schedule_day: '{{ old('schedule_day', 'Senin') }}',
        start_time: '{{ old('start_time', '08:00') }}',
        end_time: '{{ old('end_time', '10:30') }}',
        room: '{{ old('room', 'Lab Multimedia 1') }}',
        lecturer_id: '{{ old('lecturer_id', '') }}',
        conflictStatus: 'idle', // idle, checking, safe, conflict
        conflictMessage: '',

        async checkConflict() {
            if (!this.term_id || !this.schedule_day || !this.start_time || !this.end_time) {
                this.conflictStatus = 'idle';
                return;
            }

            this.conflictStatus = 'checking';

            try {
                const res = await fetch('{{ route('admin.kelas.check-conflict') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        term_id: this.term_id,
                        schedule_day: this.schedule_day,
                        start_time: this.start_time,
                        end_time: this.end_time,
                        room: this.room,
                        lecturer_id: this.lecturer_id
                    })
                });

                const data = await res.json();
                if (data.has_conflict) {
                    this.conflictStatus = 'conflict';
                    this.conflictMessage = data.room_conflict || data.lecturer_conflict || 'Bentrok jadwal terdeteksi.';
                } else {
                    this.conflictStatus = 'safe';
                    this.conflictMessage = 'Jadwal ruangan dan dosen tersedia (Tidak ada bentrok).';
                }
            } catch (err) {
                this.conflictStatus = 'idle';
            }
        }
    }"
    x-init="checkConflict()"
>

    <!-- Header Breadcrumb & Title -->
    <div class="flex items-center justify-between pb-2 border-b border-slate-200/80">
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                <a href="{{ route('admin.kelas.index') }}" class="hover:text-blue-600 font-semibold transition-colors">
                    Kelas Perkuliahan
                </a>
                <span>&bull;</span>
                <span class="text-slate-800 font-bold">Buka Kelas Baru</span>
            </div>
            <h1 class="text-2xl font-black text-[#1A2E5E] tracking-tight">Buka Seksi / Kelas Perkuliahan</h1>
        </div>

        <a 
            href="{{ route('admin.kelas.index') }}"
            class="px-3.5 py-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-bold transition-colors cursor-pointer"
        >
            Kembali
        </a>
    </div>

    <!-- 2 Column Layout (Form + Live Schedule Checker) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Form Column (8 Cols) -->
        <div class="lg:col-span-8 bg-white border border-slate-200/80 rounded-2xl p-6 shadow-xs">
            <form method="POST" action="{{ route('admin.kelas.store') }}" class="space-y-5 text-xs">
                @csrf

                <!-- Pilihan Mata Kuliah & Semester -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Mata Kuliah <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="course_id" 
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="">-- Pilih Mata Kuliah --</option>
                            @foreach ($courses as $c)
                                <option value="{{ $c->id }}" {{ old('course_id') == $c->id ? 'selected' : '' }}>
                                    [{{ $c->code }}] {{ $c->name }} ({{ $c->credits }} SKS)
                                </option>
                            @endforeach
                        </select>
                        @error('course_id')
                            <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Semester Akademik <span class="text-rose-500">*</span>
                        </label>
                        <select 
                            name="term_id" 
                            x-model="term_id"
                            @change="checkConflict()"
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            @foreach ($terms as $t)
                                <option value="{{ $t->id }}">
                                    {{ $t->name }} {{ $t->isCurrentActive() ? '(Aktif Saat Ini)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        @error('term_id')
                            <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Nama Seksi & Dosen Pengampu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Nama Seksi / Kelas <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            name="section" 
                            value="{{ old('section', 'IF-201-A') }}" 
                            placeholder="Misal: IF-201-A atau Seksi 01"
                            required 
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono font-bold uppercase"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Harus unik untuk mata kuliah dalam semester yang sama.</p>
                        @error('section')
                            <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1.5">
                            Dosen Pengampu Utama
                        </label>
                        <select 
                            name="lecturer_id" 
                            x-model="lecturer_id"
                            @change="checkConflict()"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                        >
                            <option value="">-- Belum Ditunjuk (Admin Tunjuk Nanti) --</option>
                            @foreach ($lecturers as $l)
                                <option value="{{ $l->id }}">
                                    {{ $l->name }} ({{ $l->identifier ?? $l->email }})
                                </option>
                            @endforeach
                        </select>
                        @error('lecturer_id')
                            <p class="text-[11px] text-rose-500 font-semibold mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Jadwal Perkuliahan: Hari, Jam Mulai, Jam Selesai -->
                <div class="p-4 bg-slate-50 border border-slate-200/80 rounded-2xl space-y-3">
                    <div class="font-bold text-[#1A2E5E] flex items-center gap-1.5">
                        <x-icon name="calendar" :size="16" class="text-blue-600" />
                        <span>Jadwal Perkuliahan Mingguan</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Hari Perkuliahan</label>
                            <select 
                                name="schedule_day" 
                                x-model="schedule_day"
                                @change="checkConflict()"
                                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 cursor-pointer"
                            >
                                <option value="Senin">Senin</option>
                                <option value="Selasa">Selasa</option>
                                <option value="Rabu">Rabu</option>
                                <option value="Kamis">Kamis</option>
                                <option value="Jumat">Jumat</option>
                                <option value="Sabtu">Sabtu</option>
                            </select>
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jam Mulai</label>
                            <input 
                                type="time" 
                                name="start_time" 
                                x-model="start_time"
                                @change="checkConflict()"
                                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">Jam Selesai</label>
                            <input 
                                type="time" 
                                name="end_time" 
                                x-model="end_time"
                                @change="checkConflict()"
                                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center"
                            />
                        </div>
                    </div>

                    <!-- Ruangan & Kapasitas Kursi -->
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-2">
                        <div class="sm:col-span-2">
                            <label class="block font-bold text-slate-700 mb-1">Ruang Kuliah / Laboratorium</label>
                            <input 
                                type="text" 
                                name="room" 
                                x-model="room"
                                @input.debounce.500ms="checkConflict()"
                                placeholder="Misal: Lab Multimedia 1, R.302"
                                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500"
                            />
                        </div>

                        <div>
                            <label class="block font-bold text-slate-700 mb-1">
                                Kuota Kursi <span class="text-rose-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                name="capacity" 
                                value="{{ old('capacity', 40) }}" 
                                required 
                                min="5" 
                                max="150"
                                class="w-full px-3 py-1.5 bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono text-center font-bold"
                            />
                        </div>
                    </div>
                </div>

                <!-- Konfigurasi Pertemuan Otomatis -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Jumlah Pertemuan Mingguan</label>
                        <input 
                            type="number" 
                            name="meetings_count" 
                            value="{{ old('meetings_count', 16) }}" 
                            min="1" 
                            max="32"
                            class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 font-mono"
                        />
                        <p class="text-[10px] text-slate-400 mt-1">Standar akademik: 16 pekan termasuk UTS (Pekan 8) & UAS (Pekan 16).</p>
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Komponen Penilaian BAAK</label>
                        <div class="p-2.5 rounded-xl bg-slate-50 border border-slate-200 text-[11px] text-slate-600 flex items-center justify-between">
                            <span>Tugas (30%), Kuis (20%), UTS (25%), UAS (25%)</span>
                            <span class="font-extrabold text-[#1A2E5E]">100%</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">Dibuat otomatis dan dapat disesuaikan dosen di buku nilai.</p>
                    </div>
                </div>

                <!-- Submit Button Bar -->
                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a 
                        href="{{ route('admin.kelas.index') }}"
                        class="px-4 py-2.5 rounded-xl text-slate-600 hover:bg-slate-100 font-bold transition-colors cursor-pointer"
                    >
                        Batal
                    </a>
                    <button 
                        type="submit" 
                        class="px-6 py-2.5 bg-[#1A2E5E] hover:bg-[#152449] text-white font-bold rounded-xl shadow-xs transition-colors flex items-center gap-2 cursor-pointer"
                    >
                        <x-icon name="check" :size="16" />
                        <span>Buka Kelas & Generate Pertemuan</span>
                    </button>
                </div>
            </form>
        </div>

        <!-- Right Side: Live Schedule Checker Panel (4 Cols) -->
        <div class="lg:col-span-4 space-y-4">
            
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs space-y-3">
                <div class="flex items-center justify-between border-b border-slate-100 pb-2">
                    <h3 class="text-sm font-bold text-[#1A2E5E] flex items-center gap-1.5">
                        <x-icon name="clock" :size="16" class="text-blue-600" />
                        <span>Live Schedule Checker</span>
                    </h3>
                    <button 
                        type="button" 
                        @click="checkConflict()"
                        class="text-[10px] font-bold text-blue-600 hover:underline cursor-pointer"
                    >
                        Cek Ulang
                    </button>
                </div>

                <!-- Status Banner -->
                <div>
                    <!-- Idle -->
                    <template x-if="conflictStatus === 'idle'">
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-500">
                            Pilih hari, jam, ruang, dan dosen pengampu untuk memeriksa ketersediaan secara langsung.
                        </div>
                    </template>

                    <!-- Checking -->
                    <template x-if="conflictStatus === 'checking'">
                        <div class="p-3 rounded-xl bg-blue-50 border border-blue-200 text-xs text-blue-700 flex items-center gap-2">
                            <span class="animate-spin inline-block w-4 h-4 border-2 border-current border-t-transparent rounded-full"></span>
                            <span>Memeriksa ketersediaan jadwal...</span>
                        </div>
                    </template>

                    <!-- Safe -->
                    <template x-if="conflictStatus === 'safe'">
                        <div class="p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <x-icon name="check-circle" :size="16" class="text-emerald-600 shrink-0" />
                                <span>Jadwal Aman & Tersedia</span>
                            </div>
                            <p class="text-[11px] text-emerald-700" x-text="conflictMessage"></p>
                        </div>
                    </template>

                    <!-- Conflict -->
                    <template x-if="conflictStatus === 'conflict'">
                        <div class="p-3 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 space-y-1">
                            <div class="font-bold flex items-center gap-1.5">
                                <x-icon name="exclamation-triangle" :size="16" class="text-rose-600 shrink-0" />
                                <span>Bentrok Jadwal Terdeteksi!</span>
                            </div>
                            <p class="text-[11px] text-rose-700 leading-relaxed" x-text="conflictMessage"></p>
                        </div>
                    </template>
                </div>

                <!-- Info Aturan BAAK -->
                <div class="pt-2 text-[11px] text-slate-500 space-y-1.5 border-t border-slate-100">
                    <p class="font-semibold text-slate-700">Validasi Otomatis BAAK:</p>
                    <ul class="list-disc list-inside space-y-1 text-slate-500">
                        <li>Ruangan tidak dapat dipakai bersamaan di jam yang tumpang tindih.</li>
                        <li>Dosen tidak dapat mengajar 2 kelas berbeda di jam yang sama.</li>
                        <li>Generasi 16 pertemuan otomatis disinkronkan ke tanggal mulai semester.</li>
                    </ul>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
