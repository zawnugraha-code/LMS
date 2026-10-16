@extends('layouts.app')

@section('title', 'Mata Kuliah — Arunika')
@section('page_breadcrumb', 'Mata Kuliah')

@section('content')

    <div x-data="{ activeTab: 'semua', searchQuery: '' }">
        
        <!-- ==========================================
             1. PAGE HEADER: ICON, JUDUL, DESKRIPSI, DROPDOWN SEMESTER
             ========================================== -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3.5">
                <div class="shrink-0 text-primary">
                    <x-icon name="book-open" :size="32" :strokeWidth="2" class="text-primary" />
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-text tracking-tight">
                        Mata Kuliah
                    </h1>
                    <p class="text-[13px] text-text-muted mt-0.5">
                        Kelola dan akses semua mata kuliah yang kamu ambil di semester ini.
                    </p>
                </div>
            </div>

            <!-- Dropdown Semester (Kanan) -->
            <div class="inline-flex items-center gap-2 px-3.5 py-2 rounded-[6px] border border-line bg-surface text-xs font-medium text-text shadow-2xs cursor-pointer hover:bg-canvas transition-colors self-start md:self-auto">
                <x-icon name="calendar" :size="15" class="text-text-muted" />
                <span>Semester 3 (Ganjil 2026/2027)</span>
                <x-icon name="chevron-down" :size="13" class="text-text-muted" />
            </div>
        </div>

        <!-- ==========================================
             2. RINGKASAN STATISTIK (5 SEGMEN)
             ========================================== -->
        <x-ui.card class="p-5 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 lg:gap-0 divide-y sm:divide-y-0 lg:divide-x divide-line">
                <!-- Segmen 1: Total Mata Kuliah (Dengan Avatar Ikon Topi Wisuda) -->
                <div class="flex items-center gap-4 lg:pr-6 pb-3 sm:pb-0">
                    <div class="w-12 h-12 rounded-full bg-accent-soft flex items-center justify-center shrink-0">
                        <x-icon name="graduation-cap" :size="24" class="text-accent" />
                    </div>
                    <div>
                        <span class="text-[11px] font-medium text-text-muted block">Total Mata Kuliah</span>
                        <span class="text-lg font-bold text-text mt-0.5 block leading-tight">6 Mata Kuliah</span>
                    </div>
                </div>

                <!-- Segmen 2: SKS Total -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="landmark" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">18</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">SKS Total</span>
                    </div>
                </div>

                <!-- Segmen 3: SKS Diambil -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="folder-open" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">12</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">SKS Diambil</span>
                    </div>
                </div>

                <!-- Segmen 4: Mata Kuliah Aktif -->
                <div class="flex items-center gap-3.5 lg:px-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="users" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">6</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">Mata Kuliah Aktif</span>
                    </div>
                </div>

                <!-- Segmen 5: Mata Kuliah Selesai -->
                <div class="flex items-center gap-3.5 lg:pl-6 pt-3 sm:pt-0">
                    <div class="p-2 rounded-[6px] bg-tile border border-line/60 shrink-0">
                        <x-icon name="check-circle" :size="16" class="text-text-muted" />
                    </div>
                    <div>
                        <span class="text-base font-bold text-text tabular-nums block leading-tight">0</span>
                        <span class="text-[11px] font-medium text-text-muted block mt-0.5">Mata Kuliah Selesai</span>
                    </div>
                </div>
            </div>
        </x-ui.card>

        <!-- ==========================================
             3. TOOLBAR: TAB FILTER, PENCARIAN & FILTER BUTTON
             ========================================== -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <!-- Tab Filter Pills -->
            <div class="inline-flex items-center gap-1 bg-surface border border-line rounded-[8px] p-1 shadow-2xs self-start">
                <button 
                    type="button"
                    @click="activeTab = 'semua'"
                    :class="activeTab === 'semua' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Semua
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'aktif'"
                    :class="activeTab === 'aktif' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Aktif
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'selesai'"
                    :class="activeTab === 'selesai' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Selesai
                </button>
                <button 
                    type="button"
                    @click="activeTab = 'arsip'"
                    :class="activeTab === 'arsip' ? 'bg-accent text-white font-medium shadow-xs' : 'text-text-muted hover:text-text hover:bg-canvas'"
                    class="rounded-[6px] px-4 py-1.5 text-xs transition-colors cursor-pointer"
                >
                    Arsip
                </button>
            </div>

            <!-- Input Pencarian & Tombol Filter (Kanan) -->
            <div class="flex items-center gap-3">
                <div class="relative">
                    <input 
                        x-model="searchQuery" 
                        type="text" 
                        placeholder="Cari nama mata kuliah..." 
                        class="w-64 h-9 pl-8 pr-3 rounded-[6px] bg-surface border border-line text-text text-xs placeholder:text-text-subtle focus:outline-none focus:border-accent shadow-2xs transition-colors"
                    >
                    <x-icon name="search" :size="14" class="text-text-subtle absolute left-2.5 top-2.5" />
                </div>

                <button 
                    type="button"
                    class="h-9 px-3.5 rounded-[6px] bg-surface hover:bg-canvas border border-line text-text text-xs font-medium inline-flex items-center gap-2 shadow-2xs transition-colors cursor-pointer"
                >
                    <x-icon name="filter" :size="14" class="text-text-muted" />
                    <span>Filter</span>
                </button>
            </div>
        </div>

        <!-- ==========================================
             4. GRID 6 KARTU MATA KULIAH (PERSIS GAMBAR ACUAN)
             ========================================== -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 items-stretch">
            
            <!-- Kartu 1: IF2101 - Pemrograman Berorientasi Objek & Desain Pola -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <!-- Kode MK & Badge Status -->
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2101</span>
                        <x-ui.badge variant="success" class="font-semibold">3 Aktif</x-ui.badge>
                    </div>

                    <!-- Judul Mata Kuliah & Chevron -->
                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Pemrograman Berorientasi Objek & Desain Pola
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <!-- Info Dosen & Jadwal -->
                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Dr. Rudi Rahardjo, S.T., M.Kom.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Senin, 08:00 - 10:00 &bull; Lab Pemrograman</span>
                        </div>
                    </div>

                    <!-- Progres Materi -->
                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">7 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="50" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <!-- Footer Aksi -->
                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <a href="{{ route('app.dashboard') }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>Masuk Kelas</span>
                        <x-icon name="chevron-right" :size="13" class="text-white" />
                    </a>
                </div>
            </x-ui.card>

            <!-- Kartu 2: IF2102 - Basis Data & Sistem Terdistribusi -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2102</span>
                        <x-ui.badge variant="success" class="font-semibold">3 Aktif</x-ui.badge>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Basis Data & Sistem Terdistribusi
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="map-pin" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Ruang Gedung B 201 - Lab Basis Data</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Selasa, 10:00 - 12:00 &bull; Lab Basis Data</span>
                        </div>
                    </div>

                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">5 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="36" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <a href="{{ route('app.dashboard') }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>Masuk Kelas</span>
                        <x-icon name="chevron-right" :size="13" class="text-white" />
                    </a>
                </div>
            </x-ui.card>

            <!-- Kartu 3: IF2103 - Rancang Bangun Mini Core Banking System (OOP Principles) -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2103</span>
                        <x-ui.badge variant="success" class="font-semibold">3 Aktif</x-ui.badge>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Rancang Bangun Mini Core Banking System (OOP Principles)
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Budi Raharjo, S.Kom., M.T.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Rabu, 08:00 - 10:00 &bull; Lab Pemrograman</span>
                        </div>
                    </div>

                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">6 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="43" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <a href="{{ route('app.dashboard') }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>Masuk Kelas</span>
                        <x-icon name="chevron-right" :size="13" class="text-white" />
                    </a>
                </div>
            </x-ui.card>

            <!-- Kartu 4: IF2104 - Jaringan Komputer -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2104</span>
                        <x-ui.badge variant="success" class="font-semibold">3 Aktif</x-ui.badge>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Jaringan Komputer
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Andi Saputra, S.T., M.Eng.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Kamis, 10:00 - 12:00 &bull; Lab Jaringan</span>
                        </div>
                    </div>

                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">3 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="21" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <a href="{{ route('app.dashboard') }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>Masuk Kelas</span>
                        <x-icon name="chevron-right" :size="13" class="text-white" />
                    </a>
                </div>
            </x-ui.card>

            <!-- Kartu 5: IF2105 - Arsitektur & Organisasi Komputer -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2105</span>
                        <x-ui.badge variant="success" class="font-semibold">3 SKS</x-ui.badge>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Arsitektur & Organisasi Komputer
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Dewi Lestari, S.Kom., M.T.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Jumat, 08:00 - 10:00 &bull; Lab Komputer</span>
                        </div>
                    </div>

                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">4 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="28" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <a href="{{ route('app.dashboard') }}" class="px-4 py-1.5 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold inline-flex items-center gap-1.5 shadow-xs transition-colors">
                        <span>Masuk Kelas</span>
                        <x-icon name="chevron-right" :size="13" class="text-white" />
                    </a>
                </div>
            </x-ui.card>

            <!-- Kartu 6: IF2106 - Keamanan Sistem Informasi (Akan Datang / Locked) -->
            <x-ui.card class="p-5 flex flex-col justify-between hover:border-line-strong transition-all group">
                <div class="space-y-3.5">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-xs font-medium text-text-muted">IF2106</span>
                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold px-2 py-0.5 rounded-[4px] bg-accent-soft text-accent border border-accent/20">
                            3 Akan Datang
                        </span>
                    </div>

                    <div class="flex items-start justify-between gap-2">
                        <h3 class="text-sm font-bold text-text leading-snug group-hover:text-accent transition-colors">
                            Keamanan Sistem Informasi
                        </h3>
                        <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 mt-0.5 group-hover:text-text transition-colors" />
                    </div>

                    <div class="space-y-1.5 pt-1 text-xs text-text-muted">
                        <div class="flex items-center gap-2">
                            <x-icon name="user" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Fajar Nugroho, S.Kom., M.Kom.</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <x-icon name="calendar" :size="14" class="text-text-subtle shrink-0" />
                            <span class="truncate">Jumat, 10:00 - 12:00 &bull; Lab Keamanan</span>
                        </div>
                    </div>

                    <div class="pt-3 space-y-1.5">
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-text">Progres Materi</span>
                            <span class="text-text-muted tabular-nums">0 / 14 Pertemuan</span>
                        </div>
                        <x-ui.progress :value="0" height="h-1.5" color="bg-accent" />
                    </div>
                </div>

                <div class="pt-5 mt-4 border-t border-line flex items-center justify-between gap-3">
                    <button type="button" class="px-3.5 py-1.5 rounded-[6px] border border-line hover:bg-canvas text-text text-xs font-medium inline-flex items-center gap-1.5 transition-colors cursor-pointer">
                        <x-icon name="file-text" :size="14" class="text-text-muted" />
                        <span>Lihat Detail</span>
                    </button>
                    <button type="button" disabled class="px-4 py-1.5 rounded-[6px] bg-canvas border border-line text-text-muted text-xs font-medium inline-flex items-center gap-1.5 cursor-not-allowed opacity-80">
                        <x-icon name="lock" :size="13" class="text-text-muted" />
                        <span>Masuk Kelas</span>
                    </button>
                </div>
            </x-ui.card>

        </div>

    </div>

@endsection
