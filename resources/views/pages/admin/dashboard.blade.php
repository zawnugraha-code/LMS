@extends('layouts.admin')

@section('title', 'Beranda Administrator — LMS Arunika')
@section('page_title', 'Beranda Administrator')
@section('page_subtitle', 'Pusat Kontrol & Monitoring Operasional Akademik Kampus')

@section('content')
<div class="space-y-6">

    <!-- 1. Hero Welcome Banner (Navy Academic Theme) -->
    <div class="relative overflow-hidden bg-gradient-to-r from-[#1A2E5E] via-[#1E3A8A] to-[#2563EB] rounded-3xl p-6 sm:p-8 text-white shadow-md">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="max-w-2xl space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold bg-white/15 text-blue-100 border border-white/20">
                        Administrator Kampus
                    </span>
                    @if ($activeTerm)
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-400/30">
                            {{ $activeTerm->name }}
                        </span>
                    @endif
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-white">
                    Selamat Datang, {{ auth()->user()->name ?? 'Administrator' }}
                </h2>
                <p class="text-xs sm:text-sm text-blue-100/90 leading-relaxed">
                    Biro Administrasi Akademik & Kemahasiswaan (BAAK) Universitas Nusantara Indonesia. Pantau kapasitas perkuliahan, penugasan dosen, dan pendaftaran mahasiswa secara terpadu.
                </p>
            </div>

            <!-- Quick Action Shortcut -->
            <div class="flex flex-wrap items-center gap-3 shrink-0">
                <a 
                    href="{{ route('admin.kelas.index') }}" 
                    class="px-4 py-2.5 bg-white text-[#1A2E5E] hover:bg-blue-50 font-bold text-xs rounded-xl shadow-xs transition-colors flex items-center gap-2"
                >
                    <x-icon name="plus" :size="16" />
                    <span>Buka Kelas Baru</span>
                </a>
                <a 
                    href="{{ route('admin.pendaftaran-mahasiswa.index') }}" 
                    class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl border border-white/20 transition-colors flex items-center gap-2"
                >
                    <x-icon name="user-plus" :size="16" />
                    <span>Pendaftaran Mahasiswa</span>
                </a>
            </div>
        </div>

        <!-- Decorative Background Element -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/5 rounded-full blur-2xl pointer-events-none"></div>
    </div>

    <!-- 2. 4 Stat Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-5">
        <x-admin.stat-card 
            title="Total Dosen" 
            :value="$totalDosen" 
            :subtitle="$dosenMengampuCount . ' dosen aktif mengampu kelas'" 
            icon="users"
            iconBg="bg-blue-50 text-blue-700"
        />

        <x-admin.stat-card 
            title="Total Mahasiswa" 
            :value="$totalMahasiswa" 
            :subtitle="$mahasiswaTerdaftarCount . ' terdaftar pada semester ini'" 
            icon="academic-cap"
            iconBg="bg-emerald-50 text-emerald-700"
        />

        <x-admin.stat-card 
            title="Kelas Semester Aktif" 
            :value="$kelasSemesterAktifCount" 
            :subtitle="'Rata-rata keterisian ' . $rataRataKeterisian . '%'" 
            icon="book-open"
            iconBg="bg-indigo-50 text-indigo-700"
        />

        <x-admin.stat-card 
            title="Mata Kuliah" 
            :value="$totalMataKuliah" 
            subtitle="Katalog kurikulum OBE aktif" 
            icon="clipboard-list"
            iconBg="bg-purple-50 text-purple-700"
        />
    </div>

    <!-- 3. Main Operational Grid: 3 Kolom Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
        
        <!-- KOLOM 1: Panel Perlu Perhatian (4 Kolom pada LG) -->
        <div class="lg:col-span-4 space-y-6">
            
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-700 flex items-center justify-center">
                            <x-icon name="exclamation-triangle" :size="18" />
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-[#1A2E5E]">Perlu Perhatian</h3>
                            <p class="text-[11px] text-slate-500">Peringatan operasional semester</p>
                        </div>
                    </div>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($perluPerhatian as $item)
                        <div class="py-3.5 first:pt-0 last:pb-0 flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1">
                                <h4 class="text-xs font-bold text-slate-800 leading-snug">{{ $item['judul'] }}</h4>
                                <p class="text-[11px] text-slate-500 mt-0.5 leading-relaxed">{{ $item['keterangan'] }}</p>
                                @if ($item['tautan'] !== '#')
                                    <a href="{{ $item['tautan'] }}" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-600 hover:text-blue-800 mt-1">
                                        <span>Tinjau sekarang</span>
                                        <x-icon name="chevron-right" :size="12" />
                                    </a>
                                @endif
                            </div>
                            <x-admin.status-badge :status="$item['status']" class="shrink-0 text-[10px]" />
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- Card Kapasitas Kelas Donut -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-[#1A2E5E]">Kapasitas Kelas</h3>
                        <p class="text-[11px] text-slate-500">Semester Ganjil 2026/2027</p>
                    </div>
                    <span class="text-xs font-bold text-blue-600 tabular-nums">
                        {{ $totalTerisi }}/{{ $totalKapasitas }}
                    </span>
                </div>

                <x-admin.donut-chart 
                    :terisi="$totalTerisi" 
                    :tersisa="$totalTersisa" 
                    labelTerisi="Mahasiswa Terdaftar" 
                    labelTersisa="Kursi Tersedia"
                    unit="kursi"
                />
            </div>

        </div>

        <!-- KOLOM 2: Kelas Semester Aktif (5 Kolom pada LG) -->
        <div class="lg:col-span-5 space-y-6">
            
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-slate-100 mb-4">
                    <div>
                        <h3 class="text-sm font-bold text-[#1A2E5E]">Kelas Semester Aktif</h3>
                        <p class="text-[11px] text-slate-500">Monitoring jadwal dan keterisian kursi</p>
                    </div>
                    <a href="{{ route('admin.kelas.index') }}" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <span>Lihat Semua</span>
                        <x-icon name="chevron-right" :size="12" />
                    </a>
                </div>

                @if ($daftarKelasAktif->isEmpty())
                    <div class="text-center py-8">
                        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center mb-3">
                            <x-icon name="book-open" :size="24" />
                        </div>
                        <p class="text-xs font-bold text-slate-700">Belum Ada Kelas di Semester Ini</p>
                        <p class="text-[11px] text-slate-400 mt-1 max-w-xs mx-auto">
                            Buka kelas baru dan tetapkan dosen pengampu untuk memulai aktivitas perkuliahan.
                        </p>
                        <a href="{{ route('admin.kelas.index') }}" class="inline-block mt-3 px-3 py-1.5 bg-[#1A2E5E] text-white text-xs font-semibold rounded-lg">
                            Buka Kelas Baru
                        </a>
                    </div>
                @else
                    <div class="space-y-3.5">
                        @foreach ($daftarKelasAktif as $cls)
                            <div class="p-3.5 rounded-xl border border-slate-100 hover:border-blue-200 bg-slate-50/50 transition-colors">
                                <div class="flex items-start justify-between gap-3">
                                    <div class="min-w-0 flex-1">
                                        <div class="flex items-center gap-2">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-800 font-mono">
                                                {{ $cls['course_code'] }}
                                            </span>
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-200 text-slate-800">
                                                Kelas {{ $cls['section'] }}
                                            </span>
                                        </div>
                                        <h4 class="text-xs font-bold text-slate-800 truncate mt-1">
                                            {{ $cls['course_name'] }}
                                        </h4>
                                        <p class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-2">
                                            <span>{{ $cls['lecturer_name'] }}</span>
                                            <span>•</span>
                                            <span>{{ $cls['room'] }} ({{ $cls['schedule_day'] }} {{ $cls['start_time'] }})</span>
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <span class="text-xs font-extrabold text-[#1A2E5E] tabular-nums">
                                            {{ $cls['terisi'] }}/{{ $cls['capacity'] }}
                                        </span>
                                        <span class="text-[10px] text-slate-400 block font-medium">Mahasiswa</span>
                                    </div>
                                </div>

                                <!-- Progress Bar Keterisian -->
                                <div class="w-full bg-slate-200 rounded-full h-1.5 mt-3 overflow-hidden">
                                    <div 
                                        class="h-full rounded-full transition-all duration-500 {{ $cls['percentage'] >= 100 ? 'bg-rose-500' : ($cls['percentage'] >= 80 ? 'bg-amber-500' : 'bg-blue-600') }}"
                                        style="width: {{ min(100, $cls['percentage']) }}%"
                                    ></div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

        <!-- KOLOM 3: Aksi Cepat & Aktivitas Terbaru (3 Kolom pada LG) -->
        <div class="lg:col-span-3 space-y-6">
            
            <!-- Card Aksi Cepat -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <h3 class="text-sm font-bold text-[#1A2E5E] mb-1">Aksi Cepat</h3>
                <p class="text-[11px] text-slate-500 mb-4">Pintasan tugas administratif</p>

                <div class="space-y-2">
                    <a 
                        href="{{ route('admin.kelas.index') }}" 
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/80 transition-colors"
                    >
                        <x-icon name="plus" :size="16" class="text-blue-600" />
                        <span>Buka Kelas Baru</span>
                    </a>

                    <a 
                        href="{{ route('admin.mata-kuliah.index') }}" 
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/80 transition-colors"
                    >
                        <x-icon name="book-open" :size="16" class="text-purple-600" />
                        <span>Tambah Mata Kuliah</span>
                    </a>

                    <a 
                        href="{{ route('admin.pendaftaran-mahasiswa.index') }}#import" 
                        class="w-full flex items-center gap-2.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-700 bg-slate-50 hover:bg-blue-50 hover:text-blue-700 border border-slate-200/80 transition-colors"
                    >
                        <x-icon name="arrow-up-tray" :size="16" class="text-emerald-600" />
                        <span>Import Mahasiswa</span>
                    </a>

                    <div 
                        class="w-full flex items-center justify-between px-3 py-2 rounded-xl text-xs font-semibold text-slate-400 bg-slate-100/60 border border-slate-200/50 cursor-not-allowed"
                        title="Manajemen Pengguna lengkap akan hadir di Batch 2"
                    >
                        <div class="flex items-center gap-2.5">
                            <x-icon name="user-plus" :size="16" class="text-slate-400" />
                            <span>Tambah Pengguna</span>
                        </div>
                        <span class="text-[10px] px-1.5 py-0.5 rounded bg-slate-200 text-slate-600 font-bold">B2</span>
                    </div>
                </div>
            </div>

            <!-- Card Aktivitas Terbaru -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-3">
                    <h3 class="text-sm font-bold text-[#1A2E5E]">Aktivitas Terbaru</h3>
                    <a href="{{ route('admin.log-aktivitas.index') }}" class="text-[11px] font-semibold text-blue-600 hover:text-blue-800">
                        Lihat Semua
                    </a>
                </div>

                @if ($aktivitasTerbaru->isEmpty())
                    <p class="text-xs text-slate-400 py-4 text-center">Belum ada riwayat aktivitas tercatat.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($aktivitasTerbaru as $log)
                            <div class="text-xs">
                                <div class="flex items-center justify-between gap-1 text-[10px] text-slate-400 mb-0.5">
                                    <span class="font-bold text-slate-600 truncate">{{ $log->user?->name ?? 'Sistem' }}</span>
                                    <span class="shrink-0">{{ $log->created_at?->diffForHumans() }}</span>
                                </div>
                                <p class="text-slate-700 leading-snug font-medium">
                                    {{ $log->deskripsi }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>

    </div>

</div>
@endsection
