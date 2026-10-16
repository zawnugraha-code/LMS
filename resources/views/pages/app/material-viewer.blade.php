@extends('layouts.app')

@section('title', $material->title . ' — Materi Pembelajaran Arunika')
@section('page_breadcrumb', 'Materi Pembelajaran / ' . $material->title)

@section('content')
<div 
    x-data="{
        watchPercentage: {{ (int) ($progress?->watch_percentage ?? 0) }},
        isCompleted: {{ ($progress?->status === 'completed') ? 'true' : 'false' }},
        isSaving: false,
        toastMessage: '',
        showToast: false,
        markCompleted() {
            this.isSaving = true;
            fetch('{{ route('app.materials.progress', $material) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ watch_percentage: 100 })
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                if (data.success) {
                    this.isCompleted = true;
                    this.watchPercentage = 100;
                    this.toastMessage = 'Materi berhasil diselesaikan! +' + data.xp_awarded + ' XP diberikan.';
                    this.showToast = true;
                    setTimeout(() => { this.showToast = false; }, 4000);
                }
            })
            .catch(() => {
                this.isSaving = false;
            });
        },
        updateVideoProgress(currentTime, duration) {
            if (!duration || duration <= 0) return;
            const pct = Math.min(100, Math.round((currentTime / duration) * 100));
            if (pct > this.watchPercentage) {
                this.watchPercentage = pct;
                if (pct >= 90 && !this.isCompleted) {
                    this.markCompleted();
                } else if (pct % 25 === 0) {
                    fetch('{{ route('app.materials.progress', $material) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ watch_percentage: pct })
                    });
                }
            }
        }
    }"
    class="space-y-6"
>
    <!-- Top Bar: Back Link, Title, Type Badge & Actions -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-line">
        <div class="space-y-1.5">
            <a 
                href="{{ route('app.materials', ['course' => $course?->code]) }}" 
                class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#2563EB] hover:underline"
            >
                <x-icon name="arrow-left" :size="14" />
                <span>Kembali ke Materi Pembelajaran</span>
            </a>
            <div class="flex items-center gap-2.5 flex-wrap">
                <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-[#17306B] text-white">
                    {{ $course?->code ?? 'MK' }}
                </span>
                <h1 class="text-xl sm:text-2xl font-bold text-text">
                    {{ $material->title }}
                </h1>
                <span 
                    class="px-2.5 py-0.5 rounded-full text-xs font-semibold"
                    :class="isCompleted ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-blue-50 text-blue-700 border border-blue-200'"
                    x-text="isCompleted ? '✓ Selesai' : 'Sedang Dipelajari'"
                ></span>
            </div>
            <p class="text-xs text-text-muted">
                {{ $course?->name ?? 'Mata Kuliah' }} &bull; Pertemuan {{ $meeting?->week ?? 1 }} &bull; Dosen: {{ $lecturer?->name ?? 'Dosen Pengampu' }}
            </p>
        </div>

        <!-- Mark as Completed Button -->
        <div class="flex items-center gap-3 shrink-0">
            <button 
                type="button" 
                @click="markCompleted()" 
                :disabled="isCompleted || isSaving"
                class="px-4 py-2 rounded-[6px] text-xs font-semibold inline-flex items-center gap-2 transition-colors shadow-2xs cursor-pointer"
                :class="isCompleted ? 'bg-emerald-100 text-emerald-800 cursor-default' : 'bg-[#2563EB] hover:bg-blue-700 text-white'"
            >
                <x-icon name="check" :size="14" />
                <span x-text="isCompleted ? 'Telah Selesai' : (isSaving ? 'Menyimpan...' : 'Tandai Selesai')"></span>
            </button>
        </div>
    </div>

    <!-- Toast Notification -->
    <div 
        x-show="showToast" 
        x-transition 
        class="fixed bottom-6 right-6 z-50 bg-surface border border-emerald-200 shadow-lg rounded-[8px] p-4 flex items-center gap-3 text-xs text-text"
        style="display: none;"
    >
        <div class="p-1.5 rounded-full bg-emerald-50 text-emerald-600">
            <x-icon name="check-circle" :size="16" />
        </div>
        <span x-text="toastMessage" class="font-medium"></span>
    </div>

    <!-- Viewer Area: Video or Document -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- Main Content Area (8 Cols) -->
        <div class="lg:col-span-8 space-y-5">
            <x-ui.card class="overflow-hidden">
                @if(strtolower($material->type) === 'video')
                    <!-- Video Player Container -->
                    <div class="bg-black aspect-video relative flex items-center justify-center">
                        <video 
                            controls 
                            class="w-full h-full object-contain"
                            @timeupdate="updateVideoProgress($el.currentTime, $el.duration)"
                        >
                            <source src="{{ $material->external_url ?? '/videos/demo-lesson.mp4' }}" type="video/mp4">
                            Browser Anda tidak mendukung tag video.
                        </video>
                    </div>
                @else
                    <!-- PDF Document Reader Container -->
                    <div class="p-6 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-line">
                            <div class="flex items-center gap-2 text-xs font-bold text-text">
                                <x-icon name="file-text" :size="16" class="text-[#2563EB]" />
                                <span>Dokumen Modul Kuliah (PDF)</span>
                            </div>
                            <a 
                                href="{{ $material->file_path ? asset($material->file_path) : '#' }}" 
                                download 
                                class="inline-flex items-center gap-1.5 text-xs text-[#2563EB] hover:underline font-semibold"
                            >
                                <x-icon name="download" :size="14" />
                                <span>Unduh PDF</span>
                            </a>
                        </div>
                        
                        <!-- Document Reader Simulation -->
                        <div class="p-6 rounded-[8px] bg-canvas border border-line space-y-4 text-xs leading-relaxed text-text">
                            <h2 class="text-sm font-bold text-text border-b border-line/60 pb-2">
                                {{ $material->title }} — Rencana Pembelajaran
                            </h2>
                            <p class="text-text-muted">
                                {{ $material->content ?? 'Materi perkuliahan ini mencakup fondasi teoritis, contoh implementasi kode bersih (clean code), dan latihan studi kasus terapan untuk menguji capaian kompetensi mahasiswa.' }}
                            </p>
                            <div class="p-4 rounded-[6px] bg-surface border border-line space-y-2">
                                <h3 class="font-bold text-xs text-text">Poin-Poin Utama Pembahasan:</h3>
                                <ul class="list-disc list-inside text-text-muted space-y-1">
                                    <li>Konsep abstraksi modular dan enkapsulasi enterprise.</li>
                                    <li>Penerapan prinsip SOLID dalam arsitektur perangkat lunak modern.</li>
                                    <li>Refaktorisasi pola desain (Design Patterns GoF) pada sistem terdistribusi.</li>
                                    <li>Standar evaluasi capaian CPMK perkuliahan.</li>
                                </ul>
                            </div>
                            <div class="pt-4 border-t border-line/60 flex items-center justify-between">
                                <span class="text-[11px] text-text-muted">Status Bacaan: Lengkap & Terverifikasi</span>
                                <button 
                                    type="button" 
                                    @click="markCompleted()" 
                                    class="text-xs font-semibold text-[#2563EB] hover:underline"
                                    x-show="!isCompleted"
                                >
                                    Tandai telah selesai membaca &rarr;
                                </button>
                            </div>
                        </div>
                    </div>
                @endif
            </x-ui.card>

            <!-- Bottom Navigation: Prev / Next Materials -->
            <div class="flex items-center justify-between gap-4 pt-2">
                @if($prevMaterial)
                    <a 
                        href="{{ route('app.materials.view', $prevMaterial) }}" 
                        class="px-4 py-2.5 rounded-[6px] border border-line bg-surface hover:bg-canvas text-xs font-semibold text-text inline-flex items-center gap-2 transition-colors shadow-2xs"
                    >
                        <x-icon name="chevron-left" :size="14" />
                        <span>Materi Sebelumnya: {{ Str::limit($prevMaterial->title, 25) }}</span>
                    </a>
                @else
                    <div></div>
                @endif

                @if($nextMaterial)
                    <a 
                        href="{{ route('app.materials.view', $nextMaterial) }}" 
                        class="px-4 py-2.5 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center gap-2 transition-colors shadow-2xs"
                    >
                        <span>Materi Berikutnya: {{ Str::limit($nextMaterial->title, 25) }}</span>
                        <x-icon name="chevron-right" :size="14" />
                    </a>
                @endif
            </div>
        </div>

        <!-- Right Sidebar Area (4 Cols) -->
        <div class="lg:col-span-4 space-y-4">
            
            <!-- Information Card -->
            <x-ui.card class="p-4 sm:p-5 space-y-3.5">
                <h3 class="text-xs font-bold text-text uppercase tracking-wider">
                    Informasi Pembelajaran
                </h3>
                <div class="space-y-2.5 text-xs divide-y divide-line/60">
                    <div class="flex justify-between py-1.5 first:pt-0">
                        <span class="text-text-muted">Jenis Modul</span>
                        <span class="font-semibold text-text capitalize">{{ $material->type }}</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-text-muted">Progres Selesai</span>
                        <span class="font-bold text-[#2563EB] tabular-nums" x-text="watchPercentage + '%'"></span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-text-muted">Penghargaan XP</span>
                        <span class="font-bold text-emerald-600">+25 XP</span>
                    </div>
                    <div class="flex justify-between py-1.5">
                        <span class="text-text-muted">Dosen</span>
                        <span class="font-semibold text-text truncate max-w-[160px]">{{ $lecturer?->name ?? 'Dosen' }}</span>
                    </div>
                </div>
            </x-ui.card>

            <!-- Ask AI Copilot Widget -->
            <div class="rounded-[12px] p-4 border border-indigo-100 bg-gradient-to-br from-[#EFF6FF] via-[#F5F3FF] to-[#FAF5FF] shadow-2xs space-y-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-white text-[#2563EB] shadow-2xs border border-blue-100 flex items-center justify-center shrink-0">
                        <x-icon name="bot" :size="16" class="text-[#2563EB]" />
                    </div>
                    <h4 class="text-xs font-bold text-text">Punya Pertanyaan Materi Ini?</h4>
                </div>
                <p class="text-xs text-text-muted leading-relaxed">
                    Ajukan pertanyaan seputar materi "{{ $material->title }}" kepada Arunika AI Copilot untuk penjelasan ringkas dan contoh soal.
                </p>
                <button 
                    type="button" 
                    @click="aiDrawerOpen = true; aiPrompt = 'Tolong jelaskan secara mendalam tentang materi {{ $material->title }} pada mata kuliah {{ $course?->name }} beserta contoh implementasinya.'"
                    class="w-full px-3.5 py-2 rounded-[6px] bg-[#2563EB] hover:bg-blue-700 text-white text-xs font-semibold inline-flex items-center justify-center gap-1.5 shadow-2xs transition-colors cursor-pointer"
                >
                    <span>Tanya AI Copilot</span>
                    <x-icon name="arrow-right" :size="13" />
                </button>
            </div>

        </div>

    </div>
</div>
@endsection
