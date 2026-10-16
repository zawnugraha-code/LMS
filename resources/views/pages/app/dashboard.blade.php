@extends('layouts.app')

@section('title', 'Dashboard — ' . Auth::user()?->role?->label())
@section('page_title', 'Dashboard ' . Auth::user()?->role?->label())

@section('content')

    <!-- ==========================================
         VIEW UNTUK MAHASISWA (PERSIS GAMBAR ACUAN)
         ========================================== -->
    @if(Auth::user()->isStudent())

        <div x-data="{ submitModalOpen: false, selectedAssignmentId: {{ $assignments->first()?->id ?? 1 }} }">
            
            <!-- Page Header: Sapaan & Subtitle Akademik -->
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-text tracking-tight">
                    Selamat datang, {{ Auth::user()->name }}
                </h1>
                <p class="text-[13px] text-text-muted mt-1">
                    Program Studi S1 Teknik Informatika &bull; Semester 3 &bull; Dosen: Pak Drs. Endang Lestari, M.Si.
                </p>
            </div>

            <!-- Grid Utama: 2 Kolom (1.48fr : 1fr) Sesuai Gambar Acuan -->
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1.48fr)_minmax(0,1fr)] gap-[18px] items-start">
                
                <!-- ==========================================
                     KOLOM KIRI (1.48fr)
                     ========================================== -->
                <div class="space-y-[18px]">
                    
                    <!-- Blok 1: Banner Sambutan dengan Foto Kampus -->
                    <div class="relative rounded-[8px] overflow-hidden bg-accent-soft border border-line min-h-[178px] flex items-center p-6 sm:p-7">
                        <!-- Teks Kiri -->
                        <div class="relative z-10 max-w-[360px] sm:max-w-[390px] space-y-2">
                            <h2 class="text-xl sm:text-[22px] font-bold text-text leading-tight">
                                Tetap semangat menempuh perkuliahan!
                            </h2>
                            <p class="text-[13px] text-text-muted leading-relaxed">
                                Progress hari ini lebih baik dari kemarin. Terus tingkatkan ilmu dan jangan lupa jaga kesehatan.
                            </p>
                            <div class="pt-2">
                                <a 
                                    href="#jadwal" 
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-[6px] bg-primary hover:bg-primary-hover active:bg-primary text-white text-xs font-semibold shadow-xs transition-colors"
                                >
                                    <span>Lihat Jadwal Kuliah</span>
                                    <x-icon name="chevron-right" :size="14" class="text-white" />
                                </a>
                            </div>
                        </div>

                        <!-- Foto Kampus Kanan dengan Gradasi Halus -->
                        <div class="absolute right-0 top-0 bottom-0 w-[55%] pointer-events-none overflow-hidden">
                            <img 
                                src="/images/campus-hero.jpg" 
                                alt="Kampus Universitas" 
                                class="w-full h-full object-cover object-center"
                                style="mask-image: linear-gradient(to left, black 55%, transparent 100%); -webkit-mask-image: linear-gradient(to left, black 55%, transparent 100%);"
                            />
                        </div>
                    </div>

                    <!-- Blok 2: Mata Kuliah Hari Ini -->
                    <x-ui.card>
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="calendar" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Mata Kuliah Hari Ini</h3>
                            </div>
                            <span class="text-xs text-text-muted">Kamis, 15 Okt 2026</span>
                        </div>

                        <div class="divide-y divide-line">
                            <!-- Sesi 1 -->
                            <div class="px-5 py-3.5 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-[#0EA5E9] shrink-0"></span>
                                    <span class="text-xs font-medium text-text tabular-nums w-24 shrink-0">
                                        08:00 &ndash; 10:00
                                    </span>
                                    <div class="min-w-0 space-y-0.5">
                                        <p class="text-[13px] font-semibold text-text truncate group-hover:text-accent transition-colors">
                                            Pemrograman Berorientasi Objek & Desain Pola
                                        </p>
                                        <div class="flex items-center gap-2 text-[11px] text-text-muted">
                                            <span>IF2101</span>
                                            <span>&bull;</span>
                                            <span>Ruang Lab 307 - Gedung B</span>
                                        </div>
                                    </div>
                                </div>
                                <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 group-hover:text-text transition-colors" />
                            </div>

                            <!-- Sesi 2 -->
                            <div class="px-5 py-3.5 flex items-center justify-between gap-4 hover:bg-canvas/50 transition-colors cursor-pointer group">
                                <div class="flex items-center gap-3.5 min-w-0">
                                    <span class="w-2 h-2 rounded-full bg-[#10B981] shrink-0"></span>
                                    <span class="text-xs font-medium text-text tabular-nums w-24 shrink-0">
                                        10:00 &ndash; 12:00
                                    </span>
                                    <div class="min-w-0 space-y-0.5">
                                        <p class="text-[13px] font-semibold text-text truncate group-hover:text-accent transition-colors">
                                            Basis Data & Sistem Terdistribusi
                                        </p>
                                        <div class="flex items-center gap-2 text-[11px] text-text-muted">
                                            <span>IF2102</span>
                                            <span>&bull;</span>
                                            <span>Ruang Gedung B 201 - Lab Basis Data</span>
                                        </div>
                                    </div>
                                </div>
                                <x-icon name="chevron-right" :size="16" class="text-text-subtle shrink-0 group-hover:text-text transition-colors" />
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Blok 3: Tugas Terbaru -->
                    <x-ui.card id="tugas-asesmen">
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="file-text" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Tugas Terbaru</h3>
                            </div>
                            <a href="#semua-tugas" class="text-xs text-accent font-medium hover:underline">Lihat Semua</a>
                        </div>

                        <div class="divide-y divide-line">
                            <!-- Tugas 1: Perlu Dikerjakan -->
                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 hover:bg-canvas/40 transition-colors">
                                <div class="space-y-1 min-w-0">
                                    <span class="text-[11px] text-text-muted font-medium block">
                                        IF2101 &ndash; Tugas 2
                                    </span>
                                    <h4 class="text-sm font-bold text-text leading-snug">
                                        Refactoring Monolithic Checkout ke SOLID Architecture
                                    </h4>
                                    <p class="text-[11px] text-text-muted">
                                        Batas pengumpulan: 01 Okt 2026, 10:00
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 self-start sm:self-center">
                                    <x-ui.badge variant="danger" :pill="true">
                                        <x-icon name="clock" :size="12" class="text-danger" />
                                        <span>2 hari lagi</span>
                                    </x-ui.badge>
                                    <x-ui.button 
                                        variant="primary" 
                                        size="md" 
                                        @click="selectedAssignmentId = {{ $assignments->first()?->id ?? 1 }}; submitModalOpen = true"
                                    >
                                        Kerjakan
                                    </x-ui.button>
                                </div>
                            </div>

                            <!-- Tugas 2: Sudah Dinilai -->
                            <div class="px-5 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3.5 hover:bg-canvas/40 transition-colors">
                                <div class="space-y-1 min-w-0">
                                    <span class="text-[11px] text-text-muted font-medium block">
                                        IF2101 &ndash; Tugas 1
                                    </span>
                                    <h4 class="text-sm font-bold text-text leading-snug">
                                        Rancang Bangun Mini Core Banking System (OOP Principles)
                                    </h4>
                                    <p class="text-[11px] text-text-muted">
                                        Batas pengumpulan: 10 Sep 2026, 10:00
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 self-start sm:self-center">
                                    <x-ui.badge variant="neutral" :pill="true">
                                        <x-icon name="check-circle" :size="12" class="text-text-muted" />
                                        <span>Sudah dinilai</span>
                                    </x-ui.badge>
                                    <x-ui.button variant="secondary" size="md">
                                        Lihat Nilai
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Blok 4: Mata Kuliah Terdaftar (5 Baris dengan Garis Warna) -->
                    <x-ui.card id="mata-kuliah">
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="file-text" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Mata Kuliah Terdaftar</h3>
                            </div>
                            <a href="#semua-mk" class="text-xs text-accent font-medium hover:underline">Lihat Semua</a>
                        </div>

                        <div class="divide-y divide-line">
                            @php
                                $registeredCourses = [
                                    ['color' => 'bg-[#10B981]', 'code' => 'IF2101', 'name' => 'Pemrograman Berorientasi Objek & Desain Pola', 'meta' => 'Kelas A / 3 SKS'],
                                    ['color' => 'bg-[#F59E0B]', 'code' => 'IF2102', 'name' => 'Basis Data & Sistem Terdistribusi', 'meta' => 'Kelas B / 3 SKS'],
                                    ['color' => 'bg-[#EF4444]', 'code' => 'IF2103', 'name' => 'Rancang Bangun Mini Core Banking System (OOP)', 'meta' => 'Kelas A / 3 SKS'],
                                    ['color' => 'bg-[#94A3B8]', 'code' => 'IF2104', 'name' => 'Jaringan Komputer', 'meta' => 'Kelas B / 3 SKS'],
                                    ['color' => 'bg-[#3B82F6]', 'code' => 'IF2105', 'name' => 'Arsitektur & Organisasi Komputer', 'meta' => 'Kelas A / 3 SKS'],
                                ];
                            @endphp

                            @foreach($registeredCourses as $course)
                                <div class="relative pl-6 pr-5 py-3.5 flex items-center justify-between gap-3 hover:bg-canvas/50 transition-colors">
                                    <!-- Bar Vertikal Warna di Sisi Kiri -->
                                    <div class="absolute left-3 top-3 bottom-3 w-[3px] {{ $course['color'] }} rounded-full"></div>
                                    
                                    <div class="flex items-center gap-3 min-w-0 flex-1">
                                        <span class="text-xs text-text-muted font-medium w-14 shrink-0">
                                            {{ $course['code'] }}
                                        </span>
                                        <p class="text-[13px] font-medium text-text truncate flex-1">
                                            {{ $course['name'] }}
                                        </p>
                                    </div>

                                    <div class="flex items-center gap-4 shrink-0">
                                        <span class="text-xs text-text-muted hidden sm:inline">
                                            {{ $course['meta'] }}
                                        </span>
                                        <x-ui.badge variant="success">
                                            Aktif
                                        </x-ui.badge>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-ui.card>

                </div>

                <!-- ==========================================
                     KOLOM KANAN (1fr)
                     ========================================== -->
                <div class="space-y-[18px]">
                    
                    <!-- Blok 5: Ringkasan Akademik -->
                    <x-ui.card>
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <h3 class="text-[15px] font-semibold text-text">Ringkasan Akademik</h3>
                            <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-[6px] border border-line bg-surface text-xs font-medium text-text cursor-pointer hover:bg-canvas transition-colors">
                                <span>Semester 3</span>
                                <x-icon name="chevron-down" :size="12" class="text-text-muted" />
                            </div>
                        </div>

                        <div class="p-5 space-y-5">
                            <!-- 3 Stat Tiles Sebaris -->
                            <div class="grid grid-cols-3 gap-2.5">
                                <!-- Tile 1: IPK -->
                                <x-ui.stat-tile 
                                    value="3.88" 
                                    label="IPK" 
                                    sub="&blacktriangle; 0.12" 
                                    subColor="text-success"
                                />

                                <!-- Tile 2: SKS Total -->
                                <x-ui.stat-tile 
                                    value="21" 
                                    label="SKS Total" 
                                    sub="Dari 24 SKS" 
                                />

                                <!-- Tile 3: Presensi -->
                                <x-ui.stat-tile 
                                    value="100%" 
                                    label="Presensi" 
                                    sub="14/14 Pertemuan" 
                                />
                            </div>

                            <!-- Progress Mata Kuliah Aktif -->
                            <div class="space-y-1.5 pt-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="font-medium text-text">Mata Kuliah Aktif</span>
                                    <span class="text-text-muted">7 Mata Kuliah</span>
                                </div>
                                <x-ui.progress :value="71" height="h-2" color="bg-accent" />
                                <p class="text-[11px] text-text-muted text-right font-medium">
                                    5/7 Selesai
                                </p>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Blok 6: Capaian Kompetensi -->
                    <x-ui.card id="obe-capaian">
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="target" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Capaian Kompetensi</h3>
                            </div>
                            <a href="#detail-cpl" class="text-xs text-accent font-medium hover:underline">Selengkapnya</a>
                        </div>

                        <div class="p-5 space-y-4">
                            <!-- CPL-01 -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs gap-2">
                                    <span class="font-bold text-text shrink-0">CPL-01</span>
                                    <span class="text-text-muted truncate flex-1 text-[11px]">
                                        Mampu menerapkan prinsip logika komputasi, algoritma...
                                    </span>
                                    <span class="font-semibold text-text tabular-nums shrink-0">95.00%</span>
                                </div>
                                <x-ui.progress :value="95" height="h-1.5" color="bg-accent" />
                            </div>

                            <!-- CPL-02 -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs gap-2">
                                    <span class="font-bold text-text shrink-0">CPL-02</span>
                                    <span class="text-text-muted truncate flex-1 text-[11px]">
                                        Mampu merancang, mengimplementasikan, dan mengevaluasi...
                                    </span>
                                    <span class="font-semibold text-text tabular-nums shrink-0">92.50%</span>
                                </div>
                                <x-ui.progress :value="92.5" height="h-1.5" color="bg-accent" />
                            </div>

                            <!-- CPL-03 -->
                            <div class="space-y-1.5">
                                <div class="flex items-center justify-between text-xs gap-2">
                                    <span class="font-bold text-text shrink-0">CPL-03</span>
                                    <span class="text-text-muted truncate flex-1 text-[11px]">
                                        Mampu berkomunikasi efektif secara lisan dan tulisan...
                                    </span>
                                    <span class="font-semibold text-text tabular-nums shrink-0">88.00%</span>
                                </div>
                                <x-ui.progress :value="88" height="h-1.5" color="bg-accent" />
                            </div>

                            <!-- Footer Standar Minimum -->
                            <div class="pt-3 border-t border-line flex items-center justify-between text-xs">
                                <span class="text-text-muted">
                                    Standar Minimum Kelulusan: <strong class="text-text font-bold">70%</strong>
                                </span>
                                <a href="#detail-cpl" class="text-accent font-medium hover:underline inline-flex items-center gap-1">
                                    <span>Lihat Detail</span>
                                    <span>&rarr;</span>
                                </a>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Blok 7: Sertifikat & Kompetensi -->
                    <x-ui.card>
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="award" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Sertifikat & Kompetensi</h3>
                            </div>
                            <span class="px-2 py-0.5 rounded-[4px] border border-line text-[11px] text-text font-medium bg-surface">
                                3 Tersedia
                            </span>
                        </div>

                        <div class="p-5">
                            <div class="grid grid-cols-3 gap-2.5">
                                <!-- Sertifikat 1 -->
                                <div class="bg-tile border border-line rounded-[8px] p-2.5 flex flex-col justify-between">
                                    <div>
                                        <x-icon name="award" :size="20" class="text-accent/80 mb-1.5" />
                                        <span class="text-[10px] text-text-muted block">Sertifikat</span>
                                        <h4 class="text-xs font-semibold text-text line-clamp-2 leading-tight mt-0.5">
                                            Pelatihan Web Development
                                        </h4>
                                    </div>
                                    <x-ui.button variant="primary" size="sm" class="w-full mt-3">
                                        Lihat
                                    </x-ui.button>
                                </div>

                                <!-- Sertifikat 2 -->
                                <div class="bg-tile border border-line rounded-[8px] p-2.5 flex flex-col justify-between">
                                    <div>
                                        <x-icon name="award" :size="20" class="text-accent/80 mb-1.5" />
                                        <span class="text-[10px] text-text-muted block">Sertifikat</span>
                                        <h4 class="text-xs font-semibold text-text line-clamp-2 leading-tight mt-0.5">
                                            Pemrograman OOP
                                        </h4>
                                    </div>
                                    <x-ui.button variant="primary" size="sm" class="w-full mt-3">
                                        Lihat
                                    </x-ui.button>
                                </div>

                                <!-- Sertifikat 3 -->
                                <div class="bg-tile border border-line rounded-[8px] p-2.5 flex flex-col justify-between">
                                    <div>
                                        <x-icon name="award" :size="20" class="text-accent/80 mb-1.5" />
                                        <span class="text-[10px] text-text-muted block">Sertifikat</span>
                                        <h4 class="text-xs font-semibold text-text line-clamp-2 leading-tight mt-0.5">
                                            UI/UX Dasar
                                        </h4>
                                    </div>
                                    <x-ui.button variant="primary" size="sm" class="w-full mt-3">
                                        Lihat
                                    </x-ui.button>
                                </div>
                            </div>
                        </div>
                    </x-ui.card>

                    <!-- Blok 8: Buku Besar XP -->
                    <x-ui.card id="gamifikasi">
                        <div class="px-5 py-3.5 border-b border-line flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2.5">
                                <x-icon name="book-text" :size="18" class="text-primary shrink-0" />
                                <h3 class="text-[15px] font-semibold text-text">Buku Besar XP</h3>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="#semua-xp" class="text-xs text-accent font-medium hover:underline">Lihat Semua</a>
                                <span class="text-text-subtle text-xs">|</span>
                                <span class="text-xs text-text-muted">Audit Log</span>
                            </div>
                        </div>

                        <div class="divide-y divide-line">
                            <!-- Entri 1 -->
                            <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-canvas/40 transition-colors">
                                <div class="space-y-0.5 min-w-0">
                                    <p class="text-xs font-medium text-text truncate">
                                        Presensi tepat waktu 5 minggu berturut-turut
                                    </p>
                                    <span class="text-[11px] text-text-muted block">
                                        24 Sep 2026, 10:25
                                    </span>
                                </div>
                                <span class="font-bold text-xs text-success tabular-nums shrink-0">
                                    +50 VP
                                </span>
                            </div>

                            <!-- Entri 2 -->
                            <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-canvas/40 transition-colors">
                                <div class="space-y-0.5 min-w-0">
                                    <p class="text-xs font-medium text-text truncate">
                                        Menyelesaikan Tugas 1 Mini Banking System
                                    </p>
                                    <span class="text-[11px] text-text-muted block">
                                        17 Sep 2026, 10:32
                                    </span>
                                </div>
                                <span class="font-bold text-xs text-success tabular-nums shrink-0">
                                    +100 VP
                                </span>
                            </div>

                            <!-- Entri 3 -->
                            <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-canvas/40 transition-colors">
                                <div class="space-y-0.5 min-w-0">
                                    <p class="text-xs font-medium text-text truncate">
                                        Skor Sempurna (100) pada Kuis OOP & Polimorfisme
                                    </p>
                                    <span class="text-[11px] text-text-muted block">
                                        27 Sep 2026, 10:26
                                    </span>
                                </div>
                                <span class="font-bold text-xs text-success tabular-nums shrink-0">
                                    +150 VP
                                </span>
                            </div>
                        </div>
                    </x-ui.card>

                </div>

            </div>

            <!-- Modal Pengumpulan Tugas (Test Compatibility) -->
            <div 
                x-show="submitModalOpen" 
                x-transition.opacity.duration.150ms 
                class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
                style="display: none;"
            >
                <div 
                    class="w-full max-w-lg bg-surface border border-line rounded-[8px] p-6 space-y-4 shadow-card" 
                    @click.away="submitModalOpen = false"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div>
                            <span class="text-[10px] uppercase font-semibold tracking-wider text-text-muted">Pengumpulan Berkas Asesmen</span>
                            <h3 class="font-semibold text-base text-text">Refactoring Monolithic Checkout ke SOLID</h3>
                        </div>
                        <button @click="submitModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                            <x-icon name="x" :size="18" />
                        </button>
                    </div>

                    <form :action="'/app/assignments/' + selectedAssignmentId + '/submit'" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-text mb-1.5">Ringkasan Pengerjaan / Tautan Kode *</label>
                            <textarea name="content" rows="3" required placeholder="Tuliskan dokumentasi arsitektur, tautan repositori Git, atau ringkasan solusi..." class="w-full px-3 py-2 rounded-[6px] bg-canvas border border-line text-text text-xs focus:outline-none focus:border-accent transition-colors"></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-text mb-1.5">URL Berkas Pendukung (Opsional)</label>
                            <input type="text" name="file_url" placeholder="https://github.com/... atau path arsip" class="w-full px-3 py-2 rounded-[6px] bg-canvas border border-line text-text text-xs focus:outline-none focus:border-accent transition-colors">
                        </div>
                        <div class="p-3 rounded-[6px] bg-tile border border-line text-xs text-text-muted flex items-center justify-between">
                            <span>Insentif Ketepatan Waktu:</span>
                            <span class="text-success font-semibold">+10 XP ke Ledger</span>
                        </div>
                        <div class="flex justify-end gap-2.5 pt-2 border-t border-line">
                            <x-ui.button type="button" variant="secondary" size="md" @click="submitModalOpen = false">
                                Batal
                            </x-ui.button>
                            <x-ui.button type="submit" variant="primary" size="md">
                                Kirimkan Tugas
                            </x-ui.button>
                        </div>
                    </form>
                </div>
            </div>

        </div>

    <!-- ==========================================
         VIEW UNTUK DOSEN PENGAMPU
         ========================================== -->
    @elseif(Auth::user()->isLecturer())

        <!-- Header Identitas Dosen -->
        <x-ui.card class="p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-line">
                <div class="space-y-1">
                    <div class="flex items-center gap-2 text-xs text-text-muted">
                        <x-ui.badge variant="success">DOSEN PENGAMPU</x-ui.badge>
                        <span>NIDN: {{ Auth::user()->identifier ?? '0012057801' }}</span>
                    </div>
                    <h1 class="text-2xl font-bold text-text">
                        Selamat datang, {{ Auth::user()->name }}
                    </h1>
                    <p class="text-xs text-text-muted">
                        Program Studi S1 Teknik Informatika &bull; Periode Akademik Ganjil 2026/2027
                    </p>
                </div>
                <div>
                    <x-ui.button 
                        variant="primary"
                        @click="aiDrawerOpen = true; aiPrompt = 'Tolong buatkan draf 3 soal kuis pilihan ganda berbasis taksonomi Bloom C4 untuk materi SOLID Principles.'; askAi()" 
                    >
                        <x-icon name="message-circle" :size="14" class="text-white" />
                        <span>Draf Soal Bloom (AI)</span>
                    </x-ui.button>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-xs pt-4">
                <x-ui.stat-tile value="{{ $classesTaught->count() }}" label="Kelas Diampu" sub="Kelas Paralel" />
                <x-ui.stat-tile 
                    value="{{ $pendingSubmissions->count() }}" 
                    label="Antrean Penilaian" 
                    sub="Berkas Masuk" 
                    subColor="{{ $pendingSubmissions->count() > 0 ? 'text-danger' : 'text-success' }}"
                />
                <x-ui.stat-tile value="91.8%" label="Rata-rata Capaian CPL" sub="Target 75%" subColor="text-success" />
                <x-ui.stat-tile value="100%" label="RPS Terverifikasi" sub="Sesuai OBE" subColor="text-success" />
            </div>
        </x-ui.card>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start" x-data="{ gradeModalOpen: false, activeSubmission: null }">
            
            <!-- Kolom Kiri: Antrean Penilaian -->
            <div class="lg:col-span-8 space-y-6">
                <x-ui.card>
                    <x-ui.card-header title="Antrean Penilaian Tugas" icon="clipboard-list">
                        <x-slot:action>
                            <span class="text-xs {{ $pendingSubmissions->count() > 0 ? 'text-danger font-semibold' : 'text-text-muted' }}">
                                {{ $pendingSubmissions->count() }} Menunggu Evaluasi
                            </span>
                        </x-slot:action>
                    </x-ui.card-header>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead>
                                <tr class="border-b border-line text-text-muted text-[11px] bg-canvas">
                                    <th class="py-2.5 px-4 font-semibold">MAHASISWA</th>
                                    <th class="py-2.5 px-4 font-semibold">TUGAS & KELAS</th>
                                    <th class="py-2.5 px-4 font-semibold">WAKTU KUMPUL</th>
                                    <th class="py-2.5 px-4 text-right font-semibold">AKSI</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-line">
                                @forelse($pendingSubmissions as $sub)
                                    <tr class="hover:bg-canvas/50 transition-colors">
                                        <td class="py-3 px-4 align-top">
                                            <p class="font-semibold text-text">{{ $sub->user->name }}</p>
                                            <span class="text-[11px] text-text-muted">NIM {{ $sub->user->identifier }}</span>
                                        </td>
                                        <td class="py-3 px-4 align-top space-y-0.5">
                                            <p class="font-medium text-text">{{ $sub->assignment->title }}</p>
                                            <p class="text-[11px] text-text-muted line-clamp-1">{{ $sub->content }}</p>
                                        </td>
                                        <td class="py-3 px-4 align-top text-text-muted whitespace-nowrap">
                                            {{ $sub->created_at->format('d M, H:i') }}
                                        </td>
                                        <td class="py-3 px-4 align-top text-right whitespace-nowrap">
                                            <x-ui.button 
                                                variant="primary" 
                                                size="sm"
                                                @click="activeSubmission = {{ json_encode($sub) }}; gradeModalOpen = true"
                                            >
                                                Nilai Rubrik
                                            </x-ui.button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-text-muted italic text-xs">
                                            Semua berkas pengumpulan tugas telah selesai dievaluasi.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-ui.card>

                <!-- Kelas Paralel -->
                <x-ui.card>
                    <x-ui.card-header title="Kelas yang Diampu Semester Ini" icon="book-open">
                        <x-slot:action>
                            <span class="text-xs text-text-muted">Ganjil 2026/2027</span>
                        </x-slot:action>
                    </x-ui.card-header>

                    <div class="divide-y divide-line">
                        @foreach($classesTaught as $class)
                            <div class="p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 text-xs hover:bg-canvas/40 transition-colors">
                                <div class="space-y-0.5">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-text">{{ $class->course->code }} &bull; Kelas {{ $class->section }}</span>
                                        <span class="text-text-muted text-[11px]">{{ $class->course->credits }} SKS</span>
                                    </div>
                                    <h4 class="font-semibold text-text text-sm">{{ $class->course->name }}</h4>
                                    <p class="text-[11px] text-text-muted">{{ $class->enrollments->count() }} Mahasiswa &bull; {{ $class->schedule_day }}, {{ $class->start_time }}</p>
                                </div>
                                <x-ui.badge variant="success">
                                    CPL Rata-rata: 91.8%
                                </x-ui.badge>
                            </div>
                        @endforeach
                    </div>
                </x-ui.card>
            </div>

            <!-- Kolom Kanan: Presensi QR Dinamis -->
            <div class="lg:col-span-4 space-y-6">
                <x-ui.card>
                    <x-ui.card-header title="Sesi Presensi QR Dinamis" icon="qr-code" />
                    
                    <div class="p-5 text-center space-y-4">
                        <div class="w-36 h-36 mx-auto bg-canvas border border-line rounded-[8px] p-3 flex items-center justify-center">
                            <x-icon name="qr-code" :size="96" class="text-primary" />
                        </div>

                        <div class="space-y-1.5 text-xs">
                            <div class="flex justify-between text-[11px] text-text-muted">
                                <span>Rotasi Token:</span>
                                <span class="text-danger font-semibold">12 Detik Tersisa</span>
                            </div>
                            <x-ui.progress :value="80" height="h-1.5" color="bg-danger" />
                            <p class="text-[11px] text-text-muted pt-1">
                                Sesi Lab 302 &bull; Radius Valid 50 Meter
                            </p>
                        </div>
                    </div>
                </x-ui.card>
            </div>

            <!-- Modal Penilaian Rubrik Dosen -->
            <div 
                x-show="gradeModalOpen" 
                x-transition.opacity.duration.150ms 
                class="fixed inset-0 bg-text/40 z-50 flex items-center justify-center p-4" 
                style="display: none;"
            >
                <div 
                    class="w-full max-w-2xl bg-surface border border-line rounded-[8px] p-6 space-y-4 shadow-card" 
                    @click.away="gradeModalOpen = false"
                >
                    <div class="flex items-center justify-between pb-3 border-b border-line">
                        <div>
                            <span class="text-[10px] uppercase font-semibold tracking-wider text-text-muted">Evaluasi Rubrik Berbobot</span>
                            <h3 class="font-semibold text-base text-text" x-text="activeSubmission ? activeSubmission.user.name + ' (' + activeSubmission.user.identifier + ')' : ''"></h3>
                        </div>
                        <button @click="gradeModalOpen = false" class="text-text-muted hover:text-text cursor-pointer p-1">
                            <x-icon name="x" :size="18" />
                        </button>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
                        <div class="p-3.5 rounded-[6px] bg-canvas border border-line space-y-2">
                            <span class="text-[10px] uppercase font-semibold text-text-muted block">Respons Mahasiswa</span>
                            <p class="font-semibold text-text" x-text="activeSubmission ? activeSubmission.assignment.title : ''"></p>
                            <div class="p-2.5 bg-surface border border-line rounded-[4px] text-[11px] text-text whitespace-pre-wrap leading-relaxed" x-text="activeSubmission ? activeSubmission.content : ''"></div>
                        </div>

                        <form :action="'/app/submissions/' + (activeSubmission ? activeSubmission.id : 1) + '/grade'" method="POST" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-xs font-semibold text-text mb-1">Skor Akhir (0 - 100) *</label>
                                <input type="number" step="0.1" name="score" required min="0" max="100" placeholder="Contoh: 92.5" class="w-full px-3 py-1.5 rounded-[6px] bg-canvas border border-line text-text text-sm focus:outline-none focus:border-accent">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-text mb-1">Umpan Balik Dosen *</label>
                                <textarea name="feedback" rows="3" required placeholder="Catatan evaluasi kode..." class="w-full px-3 py-1.5 rounded-[6px] bg-canvas border border-line text-text text-xs focus:outline-none focus:border-accent resize-none"></textarea>
                            </div>
                            <div class="flex justify-end gap-2 pt-2 border-t border-line">
                                <x-ui.button type="button" variant="secondary" size="md" @click="gradeModalOpen = false">
                                    Batal
                                </x-ui.button>
                                <x-ui.button type="submit" variant="primary" size="md">
                                    Simpan Nilai
                                </x-ui.button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    <!-- ==========================================
         VIEW UNTUK ADMIN KAMPUS & SUPER ADMIN
         ========================================== -->
    @elseif(Auth::user()->isCampusAdmin() || Auth::user()->isSuperAdmin())

        <!-- Header Institusi -->
        <x-ui.card class="p-6 mb-6">
            <div class="pb-4 border-b border-line space-y-1">
                <x-ui.badge variant="success">ADMINISTRASI AKADEMIK (BAAK)</x-ui.badge>
                <h1 class="text-2xl font-bold text-text mt-2">
                    Pemantauan Mutu & Kesiapan Akreditasi
                </h1>
                <p class="text-xs text-text-muted">
                    Rekapitulasi struktur akademik, kepatuhan kurikulum OBE, dan sinkronisasi Neo Feeder PDDikti.
                </p>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 pt-4">
                <x-ui.stat-tile value="{{ $stats['faculties'] }}" label="Fakultas" />
                <x-ui.stat-tile value="{{ $stats['programs'] }}" label="Prodi" />
                <x-ui.stat-tile value="{{ $stats['courses'] }}" label="Mata Kuliah" />
                <x-ui.stat-tile value="{{ $stats['classes'] }}" label="Kelas Aktif" />
                <x-ui.stat-tile value="{{ $stats['lecturers'] }}" label="Dosen" />
                <x-ui.stat-tile value="{{ $stats['students'] }}" label="Mahasiswa" subColor="text-success" />
            </div>
        </x-ui.card>

        <!-- Kepatuhan OBE Program Studi -->
        <x-ui.card>
            <x-ui.card-header title="Kepatuhan OBE & Kesiapan Akreditasi Prodi" icon="award">
                <x-slot:action>
                    <span class="text-xs text-success font-semibold">100% Terverifikasi</span>
                </x-slot:action>
            </x-ui.card-header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-line text-text-muted text-[11px] bg-canvas">
                            <th class="py-2.5 px-4 font-semibold">PROGRAM STUDI</th>
                            <th class="py-2.5 px-4 font-semibold">PERINGKAT</th>
                            <th class="py-2.5 px-4 font-semibold">CPL TERPETAKAN</th>
                            <th class="py-2.5 px-4 text-right font-semibold">DOKUMEN RPS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($programs as $prog)
                            <tr class="hover:bg-canvas/50 transition-colors">
                                <td class="py-3 px-4 font-semibold text-text">
                                    {{ $prog->name }}
                                </td>
                                <td class="py-3 px-4">
                                    <x-ui.badge variant="neutral">{{ $prog->accreditation }}</x-ui.badge>
                                </td>
                                <td class="py-3 px-4 text-text-muted">
                                    {{ $prog->cpls->count() }} Capaian Pembelajaran Lulusan
                                </td>
                                <td class="py-3 px-4 text-right text-success font-semibold">
                                    Lengkap &bull; Siap Audit
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    <!-- ==========================================
         VIEW UNTUK AUDITOR
         ========================================== -->
    @elseif(Auth::user()->isAuditor())

        <x-ui.card class="p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-line">
                <div>
                    <x-ui.badge variant="success">PORTAL AUDIT MUTU AKREDITASI</x-ui.badge>
                    <h1 class="text-2xl font-bold text-text mt-2">
                        Evaluasi Ketercapaian CPL & Bukti Asesmen
                    </h1>
                    <p class="text-xs text-text-muted mt-1">
                        Akses evaluasi read-only untuk asesor akreditasi LAM-INFOKOM, LAM-PT, dan BAN-PT.
                    </p>
                </div>
                <x-ui.button variant="secondary" size="md">
                    <x-icon name="download" :size="14" class="text-text" />
                    <span>Ekspor Laporan TSV</span>
                </x-ui.button>
            </div>
            <div class="text-xs text-text-muted flex items-center gap-3 pt-3">
                <span>Instrumen 9 Kriteria</span>
                <span>&bull;</span>
                <span>Status Bukti: Otentik dari Penilaian Kelas</span>
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header title="Matriks Capaian Pembelajaran Lulusan (CPL)" icon="target">
                <x-slot:action>
                    <span class="text-xs text-text-muted">Data Asesmen Teragregasi</span>
                </x-slot:action>
            </x-ui.card-header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="sticky top-0 bg-canvas">
                        <tr class="border-b border-line text-text-muted text-[11px]">
                            <th class="py-2.5 px-4 font-semibold">KODE CPL</th>
                            <th class="py-2.5 px-4 font-semibold">DESKRIPSI STANDAR KOMPETENSI</th>
                            <th class="py-2.5 px-4 text-right font-semibold">TARGET</th>
                            <th class="py-2.5 px-4 text-right font-semibold">CAPAIAN AKTUAL</th>
                            <th class="py-2.5 px-4 text-right font-semibold">STATUS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($cpls as $cpl)
                            <tr class="hover:bg-canvas/50 transition-colors">
                                <td class="py-3 px-4 font-bold text-text align-top">{{ $cpl->code }}</td>
                                <td class="py-3 px-4 text-text align-top leading-relaxed max-w-md">
                                    {{ $cpl->description }}
                                </td>
                                <td class="py-3 px-4 text-right text-text-muted align-top">{{ $cpl->target_score }}%</td>
                                <td class="py-3 px-4 text-right text-success font-semibold align-top">92.5%</td>
                                <td class="py-3 px-4 text-right align-top">
                                    <x-ui.badge variant="success">Tercapai</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    <!-- ==========================================
         VIEW UNTUK MENTOR INDUSTRI
         ========================================== -->
    @elseif(Auth::user()->isIndustryMentor())

        <x-ui.card class="p-6 mb-6">
            <div class="pb-4 border-b border-line">
                <x-ui.badge variant="success">KEMITRAAN INDUSTRI MBKM</x-ui.badge>
                <h1 class="text-2xl font-bold text-text mt-2">
                    Buku Jurnal Mahasiswa Magang Bersertifikat
                </h1>
                <p class="text-xs text-text-muted mt-1">
                    Verifikasi catatan kegiatan harian mahasiswa magang untuk konversi resmi 20 SKS.
                </p>
            </div>
            <div class="text-xs text-text-muted pt-3">
                Mitra Perusahaan: PT Tech Nusantara &bull; Software Engineering Track
            </div>
        </x-ui.card>

        <x-ui.card>
            <x-ui.card-header title="Daftar Mahasiswa Bimbingan" icon="users">
                <x-slot:action>
                    <span class="text-xs text-success font-semibold">Seluruh Log Terverifikasi</span>
                </x-slot:action>
            </x-ui.card-header>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="border-b border-line text-text-muted text-[11px] bg-canvas">
                            <th class="py-2.5 px-4 font-semibold">MAHASISWA</th>
                            <th class="py-2.5 px-4 font-semibold">POSISI & PROYEK</th>
                            <th class="py-2.5 px-4 font-semibold">PERIODE</th>
                            <th class="py-2.5 px-4 text-right font-semibold">STATUS VERIFIKASI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach($internStudents as $student)
                            <tr class="hover:bg-canvas/50 transition-colors">
                                <td class="py-3 px-4 align-top">
                                    <p class="font-semibold text-text">{{ $student->name }}</p>
                                    <span class="text-[11px] text-text-muted">NIM {{ $student->identifier }}</span>
                                </td>
                                <td class="py-3 px-4 text-text align-top">
                                    Backend Engineer Intern &bull; Proyek Payment Gateway API
                                </td>
                                <td class="py-3 px-4 text-text-muted align-top">
                                    Minggu ke-8 (Bulan 2)
                                </td>
                                <td class="py-3 px-4 text-right align-top">
                                    <x-ui.badge variant="success">Disetujui Mentor</x-ui.badge>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </x-ui.card>

    @endif

@endsection
