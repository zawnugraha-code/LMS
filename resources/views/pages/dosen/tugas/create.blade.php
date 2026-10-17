@extends('layouts.dosen')

@section('title', 'Buat Tugas & Ujian Baru — Arunika Learning System')
@section('page_breadcrumb')
    <a href="{{ route('dosen.tugas.index') }}" class="hover:underline">Tugas &amp; Ujian</a>
    <x-icon name="chevron-right" :size="12" class="inline text-text-subtle mx-1" />
    <span>Buat Assessment Baru</span>
@endsection

@section('content')
<div class="space-y-6" x-data="{
    jenis: '{{ old('jenis', $defaultJenis ?? 'tugas') }}',
    mode: '{{ old('mode', 'individu') }}',
    metode: '{{ old('metode', 'unggah_berkas') }}',
    status: '{{ old('status', 'dipublikasikan') }}',
    enableLate: true
}">

    <!-- Header Judul Halaman -->
    <div>
        <a href="{{ route('dosen.tugas.index') }}" class="inline-flex items-center gap-1.5 text-xs text-text-muted hover:text-text font-medium mb-2 transition-colors">
            <x-icon name="arrow-left" :size="14" />
            <span>Kembali ke Daftar Assessment</span>
        </a>
        <h1 class="text-xl sm:text-2xl font-bold text-text tracking-tight">
            Buat Tugas / Ujian Baru
        </h1>
        <p class="text-xs text-text-muted mt-0.5">
            Tentukan kelas perkuliahan, jenis assessment, panduan soal, dan jadwal tenggat pengumpulan.
        </p>
    </div>

    <!-- Error Summary Alerts -->
    @if ($errors->any())
        <div class="p-4 rounded-[8px] bg-red-50 border border-red-200 text-red-800 text-xs shadow-2xs">
            <div class="flex items-center gap-2 font-bold mb-1">
                <x-icon name="alert-circle" :size="15" class="text-red-600" />
                <span>Harap periksa kembali isian formulir:</span>
            </div>
            <ul class="list-disc list-inside space-y-0.5 text-red-700 pl-4">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('dosen.tugas.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

            <!-- Kolom Kiri: Form Detail Assessment (8 Kolom) -->
            <div class="lg:col-span-8 space-y-5">

                <!-- 1. Pilihan Mata Kuliah & Kelas -->
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        <x-icon name="book-open" :size="16" class="text-navy-800" />
                        <span>Mata Kuliah &amp; Kelas</span>
                    </h3>

                    <div>
                        <label for="class_id" class="block text-xs font-semibold text-text mb-1">
                            Pilih Kelas yang Diampu <span class="text-red-500">*</span>
                        </label>
                        <select 
                            id="class_id" 
                            name="class_id" 
                            required 
                            class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                        >
                            <option value="">-- Pilih Kelas Perkuliahan --</option>
                            @foreach ($classes as $cls)
                                <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>
                                    {{ $cls->course->code }} - {{ $cls->course->name }} ({{ $cls->section }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Jenis Assessment Pills -->
                    <div>
                        <label class="block text-xs font-semibold text-text mb-1.5">
                            Jenis Assessment <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
                            @foreach (['tugas' => 'Tugas', 'kuis' => 'Kuis', 'uts' => 'UTS', 'uas' => 'UAS'] as $val => $lbl)
                                <label 
                                    @click="jenis = '{{ $val }}'"
                                    :class="jenis === '{{ $val }}' ? 'border-[#2563EB] bg-blue-50/50 text-[#2563EB] font-bold' : 'border-line bg-surface text-text hover:bg-slate-50'"
                                    class="p-2.5 rounded-[6px] border text-center text-xs cursor-pointer transition-all flex flex-col items-center justify-center gap-1"
                                >
                                    <input type="radio" name="jenis" value="{{ $val }}" class="sr-only" :checked="jenis === '{{ $val }}'">
                                    <span>{{ $lbl }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <!-- Mode & Metode -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label class="block text-xs font-semibold text-text mb-1.5">
                                Mode Pengerjaan <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <label 
                                    @click="mode = 'individu'"
                                    :class="mode === 'individu' ? 'border-[#2563EB] bg-blue-50/50 text-[#2563EB] font-bold' : 'border-line text-text'"
                                    class="flex-1 p-2 rounded-[6px] border text-center text-xs cursor-pointer transition-all"
                                >
                                    <input type="radio" name="mode" value="individu" class="sr-only" :checked="mode === 'individu'">
                                    <span>Individu</span>
                                </label>
                                <label 
                                    @click="mode = 'kelompok'"
                                    :class="mode === 'kelompok' ? 'border-[#2563EB] bg-blue-50/50 text-[#2563EB] font-bold' : 'border-line text-text'"
                                    class="flex-1 p-2 rounded-[6px] border text-center text-xs cursor-pointer transition-all"
                                >
                                    <input type="radio" name="mode" value="kelompok" class="sr-only" :checked="mode === 'kelompok'">
                                    <span>Kelompok</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-text mb-1.5">
                                Metode Pengumpulan <span class="text-red-500">*</span>
                            </label>
                            <div class="flex items-center gap-2">
                                <label 
                                    @click="metode = 'unggah_berkas'"
                                    :class="metode === 'unggah_berkas' ? 'border-[#2563EB] bg-blue-50/50 text-[#2563EB] font-bold' : 'border-line text-text'"
                                    class="flex-1 p-2 rounded-[6px] border text-center text-xs cursor-pointer transition-all"
                                >
                                    <input type="radio" name="metode" value="unggah_berkas" class="sr-only" :checked="metode === 'unggah_berkas'">
                                    <span>Unggah Berkas</span>
                                </label>
                                <label 
                                    @click="metode = 'input_manual'"
                                    :class="metode === 'input_manual' ? 'border-[#2563EB] bg-blue-50/50 text-[#2563EB] font-bold' : 'border-line text-text'"
                                    class="flex-1 p-2 rounded-[6px] border text-center text-xs cursor-pointer transition-all"
                                >
                                    <input type="radio" name="metode" value="input_manual" class="sr-only" :checked="metode === 'input_manual'">
                                    <span>Nilai Manual</span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. Konten Judul & Instruksi -->
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        <x-icon name="file-text" :size="16" class="text-navy-800" />
                        <span>Detail Assessment</span>
                    </h3>

                    <div>
                        <label for="title" class="block text-xs font-semibold text-text mb-1">
                            Judul Assessment <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="title" 
                            name="title" 
                            value="{{ old('title') }}" 
                            required 
                            placeholder="Contoh: Tugas 3 - Polimorfisme dan Desain Pola" 
                            class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                        >
                    </div>

                    <div>
                        <label for="description" class="block text-xs font-semibold text-text mb-1">
                            Instruksi &amp; Panduan Pengerjaan
                        </label>
                        <textarea 
                            id="description" 
                            name="description" 
                            rows="5" 
                            placeholder="Tuliskan petunjuk pengerjaan, format laporan, kriteria penilaian, atau tautan referensi..." 
                            class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text placeholder:text-text-muted focus:outline-none focus:border-[#2563EB]"
                        >{{ old('description') }}</textarea>
                    </div>

                    <!-- Upload Berkas Soal Panduan -->
                    <div>
                        <label for="lampiran" class="block text-xs font-semibold text-text mb-1">
                            Unggah Berkas Panduan / Soal (Opsional)
                        </label>
                        <div class="border-2 border-dashed border-line hover:border-slate-300 rounded-[8px] p-4 text-center cursor-pointer bg-slate-50/50">
                            <input type="file" id="lampiran" name="lampiran" class="w-full text-xs text-text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded-[4px] file:border-0 file:text-xs file:font-semibold file:bg-[#2563EB] file:text-white hover:file:bg-blue-700">
                            <p class="text-[11px] text-text-subtle mt-1.5">Mendukung file PDF, ZIP, DOCX hingga 10MB.</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Pengaturan Waktu & Skor -->
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4">
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        <x-icon name="clock" :size="16" class="text-navy-800" />
                        <span>Pengaturan Waktu &amp; Nilai</span>
                    </h3>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label for="mulai_at" class="block text-xs font-semibold text-text mb-1">
                                Waktu Mulai Pengerjaan
                            </label>
                            <input 
                                type="datetime-local" 
                                id="mulai_at" 
                                name="mulai_at" 
                                value="{{ old('mulai_at', now()->format('Y-m-d\TH:i')) }}" 
                                class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                            >
                        </div>

                        <div>
                            <label for="due_at" class="block text-xs font-semibold text-text mb-1">
                                Tenggat Waktu (Deadline) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="datetime-local" 
                                id="due_at" 
                                name="due_at" 
                                value="{{ old('due_at', now()->addDays(7)->format('Y-m-d\T23:59')) }}" 
                                required 
                                class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                            >
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                        <div>
                            <label for="max_score" class="block text-xs font-semibold text-text mb-1">
                                Nilai Maksimum (Skor Penuh) <span class="text-red-500">*</span>
                            </label>
                            <input 
                                type="number" 
                                id="max_score" 
                                name="max_score" 
                                min="1" 
                                max="100" 
                                value="{{ old('max_score', 100) }}" 
                                required 
                                class="w-full px-3 py-2 text-xs rounded-[6px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                            >
                        </div>

                        <div class="flex items-center pt-5">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" name="allow_late" value="1" checked class="rounded-[4px] border-line text-[#2563EB] focus:ring-0">
                                <span class="text-xs font-medium text-text">Izinkan pengumpulan terlambat</span>
                            </label>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Kolom Kanan: Status & Aksi Publikasi (4 Kolom) -->
            <div class="lg:col-span-4 space-y-5">

                <!-- Panel Publikasi -->
                <div class="bg-surface border border-line rounded-[10px] p-5 shadow-2xs space-y-4 sticky top-16">
                    <h3 class="text-sm font-bold text-text flex items-center gap-2">
                        <x-icon name="send" :size="16" class="text-navy-800" />
                        <span>Status Publikasi</span>
                    </h3>

                    <div class="space-y-2">
                        <!-- Option 1: Publikasikan Sekarang -->
                        <label 
                            @click="status = 'dipublikasikan'"
                            :class="status === 'dipublikasikan' ? 'border-[#2563EB] bg-blue-50/40 text-text' : 'border-line text-text-muted hover:bg-slate-50'"
                            class="p-3 rounded-[8px] border block cursor-pointer transition-colors"
                        >
                            <div class="flex items-start gap-2.5">
                                <input type="radio" name="status" value="dipublikasikan" class="mt-0.5" :checked="status === 'dipublikasikan'">
                                <div>
                                    <span class="text-xs font-bold text-text block">Publikasikan Sekarang</span>
                                    <span class="text-[11px] text-text-muted mt-0.5 block leading-normal">
                                        Langsung terlihat dan dapat diakses oleh seluruh mahasiswa di kelas.
                                    </span>
                                </div>
                            </div>
                        </label>

                        <!-- Option 2: Jadwalkan Publikasi -->
                        <label 
                            @click="status = 'terjadwal'"
                            :class="status === 'terjadwal' ? 'border-[#2563EB] bg-blue-50/40 text-text' : 'border-line text-text-muted hover:bg-slate-50'"
                            class="p-3 rounded-[8px] border block cursor-pointer transition-colors"
                        >
                            <div class="flex items-start gap-2.5">
                                <input type="radio" name="status" value="terjadwal" class="mt-0.5" :checked="status === 'terjadwal'">
                                <div class="w-full">
                                    <span class="text-xs font-bold text-text block">Terjadwal (Otomatis)</span>
                                    <span class="text-[11px] text-text-muted mt-0.5 block leading-normal">
                                        Sistem akan merilis assessment otomatis pada waktu yang ditentukan.
                                    </span>

                                    <!-- Tanggal Jadwal -->
                                    <div x-show="status === 'terjadwal'" x-cloak class="mt-3 pt-2 border-t border-line/60">
                                        <label for="publish_at" class="block text-[10px] font-semibold text-text mb-1">
                                            Rilis Pada Tanggal &amp; Jam:
                                        </label>
                                        <input 
                                            type="datetime-local" 
                                            id="publish_at" 
                                            name="publish_at" 
                                            value="{{ old('publish_at', now()->addDays(2)->format('Y-m-d\T08:00')) }}" 
                                            class="w-full px-2.5 py-1.5 text-xs rounded-[4px] border border-line bg-surface text-text focus:outline-none focus:border-[#2563EB]"
                                        >
                                    </div>
                                </div>
                            </div>
                        </label>

                        <!-- Option 3: Simpan sebagai Draft -->
                        <label 
                            @click="status = 'draft'"
                            :class="status === 'draft' ? 'border-[#2563EB] bg-blue-50/40 text-text' : 'border-line text-text-muted hover:bg-slate-50'"
                            class="p-3 rounded-[8px] border block cursor-pointer transition-colors"
                        >
                            <div class="flex items-start gap-2.5">
                                <input type="radio" name="status" value="draft" class="mt-0.5" :checked="status === 'draft'">
                                <div>
                                    <span class="text-xs font-bold text-text block">Simpan sebagai Draft</span>
                                    <span class="text-[11px] text-text-muted mt-0.5 block leading-normal">
                                        Hanya tersimpan di dashboard dosen, mahasiswa tidak dapat melihatnya.
                                    </span>
                                </div>
                            </div>
                        </label>
                    </div>

                    <!-- Tombol Aksi -->
                    <div class="pt-3 border-t border-line/60 flex items-center justify-between gap-3">
                        <a 
                            href="{{ route('dosen.tugas.index') }}" 
                            class="px-3.5 py-2 rounded-[6px] border border-line hover:bg-slate-50 text-xs font-semibold text-text transition-colors"
                        >
                            Batal
                        </a>
                        <button 
                            type="submit" 
                            class="flex-1 px-4 py-2 rounded-[6px] bg-[#1A2E5E] hover:bg-navy-800 text-white text-xs font-semibold shadow-xs transition-colors flex items-center justify-center gap-1.5 cursor-pointer"
                        >
                            <x-icon name="check" :size="14" />
                            <span>Simpan Assessment</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>

    </form>

</div>
@endsection
