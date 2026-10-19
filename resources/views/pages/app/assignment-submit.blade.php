@extends('layouts.app')

@section('title', 'Pengerjaan Tugas — ' . $assignment->title . ' — Arunika')
@section('page_breadcrumb', 'Tugas & Ujian / ' . $assignment->title)

@section('content')
<div class="space-y-6">

    <!-- Top Action / Back Button & Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <a 
                href="{{ route('app.assignments') }}" 
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563EB] hover:underline mb-2"
            >
                <x-icon name="arrow-left" :size="14" />
                <span>Kembali ke Tugas & Ujian</span>
            </a>
            <div class="flex items-center gap-2.5">
                <span class="px-2.5 py-0.5 rounded text-[11px] font-bold bg-blue-50 text-blue-700">
                    {{ $assignment->academicClass?->course?->code ?? 'IF2101' }}
                </span>
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    {{ $assignment->title }}
                </h1>
            </div>
            <p class="text-xs text-text-muted mt-1">
                {{ $assignment->academicClass?->course?->name ?? 'Mata Kuliah' }} • Dosen Pengampu: {{ $assignment->academicClass?->lecturer?->name ?? 'Dosen Arunika' }}
            </p>
        </div>

        <!-- Badges: Due date, Max score, Reward XP -->
        <div class="flex items-center gap-2.5 shrink-0 flex-wrap">
            <div class="px-3 py-1.5 rounded-[8px] bg-surface border border-line flex items-center gap-2 text-xs">
                <x-icon name="calendar" :size="14" class="text-text-muted" />
                <span class="text-text font-medium">Batas: {{ $assignment->due_at ? $assignment->due_at->translatedFormat('d M Y, H:i') : '23:59 WIB' }}</span>
            </div>
            <div class="px-3 py-1.5 rounded-[8px] bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-1.5">
                <x-icon name="award" :size="14" />
                <span>Maks. {{ number_format($assignment->max_score, 0) }} Poin</span>
            </div>
            <div class="px-3 py-1.5 rounded-[8px] bg-amber-50 border border-amber-200 text-amber-800 text-xs font-bold flex items-center gap-1.5">
                <x-icon name="zap" :size="14" />
                <span>+10 XP</span>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Workspace -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">

        <!-- Left Column: Deskripsi, Lampiran, Rubrik, Riwayat -->
        <div class="lg:col-span-7 space-y-5">
            
            <!-- 1. Deskripsi Tugas -->
            <x-ui.card class="p-6">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-line">
                    <h2 class="text-sm font-bold text-text flex items-center gap-2">
                        <x-icon name="file-text" :size="16" class="text-[#2563EB]" />
                        <span>Instruksi & Deskripsi Tugas</span>
                    </h2>
                    <a 
                        href="{{ route('assignments.download', $assignment) }}" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text shadow-2xs transition-colors"
                    >
                        <x-icon name="download" :size="13" class="text-text-muted" />
                        <span>Unduh Panduan PDF</span>
                    </a>
                </div>

                <div class="prose max-w-none text-xs text-text leading-relaxed space-y-3">
                    <p>{{ $assignment->description }}</p>
                    <div class="p-4 rounded-[8px] bg-slate-50 border border-line">
                        <h4 class="font-bold text-text mb-2">Ketentuan Pengumpulan:</h4>
                        <ul class="list-disc list-inside space-y-1 text-text-muted">
                            <li>Format berkas yang diterima: PDF laporan atau arsip ZIP/RAR source code proyek.</li>
                            <li>Sertakan link repository GitHub/GitLab jika tugas berbasis implementasi kode.</li>
                            <li>Cantumkan identitas lengkap (NIM, Nama, Kelas) pada baris komentar pertama berkas utama.</li>
                            <li>Batas toleransi keterlambatan 3 hari dengan penalti akumulasi nilai -10% per hari.</li>
                        </ul>
                    </div>
                </div>
            </x-ui.card>

            <!-- 2. Rubrik Penilaian -->
            <x-ui.card class="p-6">
                <h2 class="text-sm font-bold text-text flex items-center gap-2 mb-4">
                    <x-icon name="check-square" :size="16" class="text-[#2563EB]" />
                    <span>Kriteria & Rubrik Penilaian (CPMK)</span>
                </h2>

                <div class="border border-line rounded-[8px] overflow-hidden text-xs">
                    <table class="w-full text-left divide-y divide-line">
                        <thead class="bg-slate-50 font-semibold text-text">
                            <tr>
                                <th class="py-2.5 px-3.5">Kriteria</th>
                                <th class="py-2.5 px-3.5 text-center w-20">Bobot</th>
                                <th class="py-2.5 px-3.5">Standar Capaian Sangat Memuaskan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-line/70 text-text-muted">
                            <tr>
                                <td class="py-2.5 px-3.5 font-medium text-text">Arsitektur & Desain Pola</td>
                                <td class="py-2.5 px-3.5 text-center font-bold text-text">40%</td>
                                <td class="py-2.5 px-3.5">Menerapkan prinsip SOLID secara konsisten dan tanpa antipattern.</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-3.5 font-medium text-text">Kerapihan & Dokumentasi Kode</td>
                                <td class="py-2.5 px-3.5 text-center font-bold text-text">30%</td>
                                <td class="py-2.5 px-3.5">Kode bersih, clean architecture, komentar inline terstruktur, dan README informatif.</td>
                            </tr>
                            <tr>
                                <td class="py-2.5 px-3.5 font-medium text-text">Implementasi Automated Testing</td>
                                <td class="py-2.5 px-3.5 text-center font-bold text-text">30%</td>
                                <td class="py-2.5 px-3.5">Unit test mencakup skenario happy path, corner cases, dan code coverage > 85%.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-ui.card>

            <!-- 3. Riwayat / Status Pengumpulan -->
            @if($submission)
                <x-ui.card class="p-6 border-blue-200 bg-blue-50/20">
                    <div class="flex items-center justify-between pb-3 border-b border-line mb-3">
                        <h2 class="text-sm font-bold text-text flex items-center gap-2">
                            <x-icon name="clock" :size="16" class="text-[#2563EB]" />
                            <span>Riwayat Pengumpulan Terakhir</span>
                        </h2>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $submission->status === 'graded' ? 'bg-emerald-100 text-emerald-800' : ($submission->status === 'submitted' ? 'bg-blue-100 text-blue-800' : 'bg-amber-100 text-amber-800') }}">
                            {{ strtoupper($submission->status) }}
                        </span>
                    </div>

                    <div class="space-y-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="text-text-muted">Waktu Submit:</span>
                            <span class="font-medium text-text">{{ $submission->submitted_at ? $submission->submitted_at->translatedFormat('d M Y, H:i') : 'Draft' }}</span>
                        </div>
                        @if($submission->file_path)
                            <div class="flex items-center justify-between">
                                <span class="text-text-muted">Berkas Terlampir:</span>
                                <a href="{{ route('berkas.pengumpulan', $submission) }}" target="_blank" class="font-semibold text-[#2563EB] hover:underline truncate max-w-xs inline-flex items-center gap-1" title="Unduh Berkas Saya">
                                    <x-icon name="paperclip" :size="12" />
                                    <span class="truncate">{{ basename($submission->file_path) }}</span>
                                </a>
                            </div>
                        @endif
                        @if($submission->score !== null)
                            <div class="p-3 mt-3 rounded-[6px] bg-emerald-50 border border-emerald-200 flex items-center justify-between">
                                <span class="font-semibold text-emerald-900">Nilai Akhir:</span>
                                <span class="text-lg font-bold text-emerald-700">{{ number_format($submission->score, 1) }} / 100</span>
                            </div>
                            @if($submission->feedback)
                                <div class="mt-2 text-text p-3 rounded-[6px] bg-surface border border-line">
                                    <span class="font-semibold block text-text-muted text-[11px] mb-1">Catatan Evaluasi Dosen:</span>
                                    <p class="italic text-text">"{{ $submission->feedback }}"</p>
                                </div>
                            @endif
                        @endif
                    </div>
                </x-ui.card>
            @endif

        </div>

        <!-- Right Column: Form Lembar Kerja Pengumpulan -->
        <div class="lg:col-span-5">
            <x-ui.card class="p-6">
                <h2 class="text-sm font-bold text-text flex items-center gap-2 mb-4">
                    <x-icon name="send" :size="16" class="text-[#2563EB]" />
                    <span>Lembar Kerja Pengumpulan</span>
                </h2>

                <form 
                    action="{{ route('assignments.submit', $assignment) }}" 
                    method="POST" 
                    enctype="multipart/form-data" 
                    class="space-y-4 text-xs"
                >
                    @csrf

                    <div>
                        <label class="block font-medium text-text mb-1">
                            Catatan Jawaban / Solusi / Tautan Repository
                        </label>
                        <textarea 
                            name="content" 
                            rows="4" 
                            placeholder="Tuliskan ringkasan jawaban, penjelasan arsitektur, atau masukkan URL GitHub/GitLab repository..."
                            class="w-full rounded-[6px] border border-line bg-canvas p-3 text-xs text-text focus:outline-none focus:border-accent focus:bg-surface transition-colors leading-relaxed"
                        >{{ old('content', $submission?->content) }}</textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">
                            Tautan Alternatif (Google Drive / GitHub)
                        </label>
                        <input 
                            type="text" 
                            name="file_url" 
                            value="{{ old('file_url', filter_var($submission?->file_path, FILTER_VALIDATE_URL) ? $submission->file_path : '') }}"
                            placeholder="https://github.com/username/project-repo" 
                            class="w-full rounded-[6px] border border-line bg-canvas px-3 py-2 text-xs text-text focus:outline-none focus:border-accent focus:bg-surface transition-colors"
                        />
                    </div>

                    <div>
                        <label class="block font-medium text-text mb-1">
                            Unggah Berkas Pendukung (PDF, ZIP, DOCX - Maks. 25MB)
                        </label>
                        <div class="border-2 border-dashed border-line rounded-[8px] p-5 text-center hover:border-accent bg-canvas/40 transition-colors">
                            <x-icon name="upload-cloud" :size="24" class="text-text-muted mx-auto mb-2" />
                            <p class="text-xs font-medium text-text">Pilih berkas dari komputer Anda</p>
                            <p class="text-[10px] text-text-subtle mt-0.5">Format: .pdf, .zip, .rar, .docx</p>
                            <input type="file" name="file" class="mt-3 block w-full text-xs text-text-muted file:mr-3 file:py-1.5 file:px-3 file:rounded file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-[#2563EB] hover:file:bg-blue-100 cursor-pointer">
                        </div>
                    </div>

                    <div class="p-3 rounded-[6px] bg-slate-50 border border-line">
                        <label class="inline-flex items-start gap-2 cursor-pointer">
                            <input type="checkbox" required class="mt-0.5 rounded border-line text-[#2563EB] focus:ring-accent">
                            <span class="text-[11px] text-text-muted leading-tight">
                                Saya menyatakan bahwa seluruh dokumen dan source code yang saya kumpulkan adalah orisinal karya saya sendiri sesuai Kode Etik Akademik Arunika.
                            </span>
                        </label>
                    </div>

                    <div class="pt-3 border-t border-line flex items-center justify-between gap-3">
                        <button 
                            type="submit" 
                            name="action" 
                            value="draft"
                            class="px-4 py-2 rounded-[6px] border border-line text-text font-medium hover:bg-canvas transition-colors shadow-2xs"
                        >
                            Simpan Sebagai Draf
                        </button>
                        <button 
                            type="submit" 
                            name="action" 
                            value="submit"
                            class="px-5 py-2 rounded-[6px] bg-[#2563EB] hover:bg-[#1D4ED8] text-white font-semibold transition-colors flex items-center gap-1.5 shadow-2xs"
                        >
                            <x-icon name="send" :size="13" />
                            <span>Kumpulkan Sekarang (+10 XP)</span>
                        </button>
                    </div>
                </form>
            </x-ui.card>
        </div>

    </div>

</div>
@endsection
