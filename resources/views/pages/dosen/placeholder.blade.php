@extends('layouts.dosen')

@section('title', ($menuTitle ?? 'Fitur') . ' — Portal Dosen Arunika')
@section('page_breadcrumb', $menuTitle ?? 'Fitur')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-text tracking-tight flex items-center gap-3">
                <span>{{ $menuTitle ?? 'Fitur Dosen' }}</span>
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-blue-50 text-[#2563EB] border border-blue-200">
                    {{ $badgeBatch ?? 'Batch 2' }}
                </span>
            </h1>
            <p class="text-xs sm:text-[13px] text-text-muted mt-1 leading-relaxed">
                {{ $menuDescription ?? 'Halaman ini sedang disiapkan untuk tahap implementasi berikutnya.' }}
            </p>
        </div>
    </div>

    <!-- Empty State Card -->
    <div class="bg-surface border border-line rounded-[8px] p-12 text-center shadow-xs flex flex-col items-center justify-center max-w-2xl mx-auto my-8">
        <div class="w-16 h-16 rounded-full bg-blue-50 text-[#2563EB] flex items-center justify-center mb-4">
            <x-icon name="clock" :size="32" />
        </div>
        <h2 class="text-base font-bold text-text mb-1">
            Halaman ini sedang disiapkan
        </h2>
        <p class="text-xs text-text-muted max-w-md leading-relaxed mb-6">
            Fitur <strong>{{ $menuTitle ?? 'modul ini' }}</strong> dijadwalkan hadir pada tahap rilis Batch 2 (Presensi, Buku Nilai, Diskusi & Pengaturan). Skema database dan kontrak relasi telah disiapkan secara penuh.
        </p>
        <div class="flex items-center gap-3">
            <a 
                href="{{ route('dosen.dashboard') }}" 
                class="px-4 py-2 rounded-[6px] bg-[#1A2E5E] hover:bg-[#2C4A8F] text-white text-xs font-semibold shadow-xs transition-colors"
            >
                Kembali ke Beranda Dosen
            </a>
            <a 
                href="{{ route('dosen.tugas.index') }}" 
                class="px-4 py-2 rounded-[6px] bg-white border border-line text-text hover:bg-slate-50 text-xs font-semibold transition-colors"
            >
                Buka Tugas & Ujian
            </a>
        </div>
    </div>
</div>
@endsection
