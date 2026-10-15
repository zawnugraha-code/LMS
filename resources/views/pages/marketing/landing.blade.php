@extends('layouts.marketing')

@section('title', 'Arunika — Learning Operating System untuk Perguruan Tinggi Indonesia')

@section('content')

    <!-- Flash message for demo request -->
    @if(session('success'))
        <div class="max-w-5xl mx-auto my-6 px-4">
            <div class="p-4 rounded-[4px] bg-brand-tint border border-brand text-brand flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <x-icon name="check-circle" class="w-5 h-5 text-brand shrink-0" />
                    <p class="text-sm font-medium">{{ session('success') }}</p>
                </div>
                <a href="{{ route('app.dashboard') }}" class="px-3 py-1.5 rounded-[2px] bg-brand text-paper font-medium text-xs hover:opacity-90 transition-editorial">
                    Buka Demo &rarr;
                </a>
            </div>
        </div>
    @endif

    <!-- ==========================================
         1. HERO SECTION (2 KOLOM ASIMETRIS EDITORIAL)
         ========================================== -->
    <section class="border-b border-line bg-paper py-16 sm:py-24">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
                
                <!-- Kolom Kiri (7 Kolom): Tipografi & Aksi -->
                <div class="lg:col-span-7 space-y-6">
                    <div class="inline-flex items-center gap-2 font-mono text-[11px] uppercase tracking-wider text-ink-muted border-b border-line pb-1">
                        <span class="w-2 h-2 rounded-full bg-brand"></span>
                        <span>Learning Operating System &middot; Native OBE &middot; Terakreditasi</span>
                    </div>

                    <h1 class="font-display font-medium text-4xl sm:text-5xl lg:text-6xl text-ink leading-[1.08] tracking-tight">
                        Nilai tugas jadi capaian CPL.<br>
                        Capaian CPL jadi laporan akreditasi.
                    </h1>

                    <p class="text-base sm:text-lg text-ink-muted leading-relaxed font-normal max-w-2xl">
                        Arunika menghitung ketercapaian CPL/CPMK langsung dari penilaian di kelas, sehingga tim prodi tidak perlu rekap manual menjelang akreditasi. Dirancang khusus untuk siklus mutu perguruan tinggi Indonesia dan kepatuhan standar Kemendikbudristek No. 53/2023.
                    </p>

                    <!-- CTA Buttons (Editorial: Ink Primer, Line Sekunder) -->
                    <div class="pt-2 flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                        <a href="{{ route('app.dashboard') }}" class="px-6 py-3 rounded-[4px] bg-ink text-paper hover:bg-black font-medium text-sm text-center transition-editorial flex items-center justify-center gap-2">
                            <span>Eksplorasi Live Demo Sekarang</span>
                            <x-icon name="arrow-right" class="w-4 h-4 text-paper" />
                        </a>
                        <a href="#demo-form" class="px-6 py-3 rounded-[4px] border border-line hover:border-ink text-ink font-medium text-sm text-center transition-editorial">
                            Jadwalkan Presentasi Kampus
                        </a>
                    </div>

                    <!-- Indikator Data Riil Kampus (Mono) -->
                    <div class="pt-8 border-t border-line grid grid-cols-2 sm:grid-cols-4 gap-4 font-mono text-xs">
                        <div>
                            <span class="block text-xl font-semibold text-ink">100%</span>
                            <span class="text-[11px] text-ink-muted">Native OBE Engine</span>
                        </div>
                        <div>
                            <span class="block text-xl font-semibold text-ink">0 Jam</span>
                            <span class="text-[11px] text-ink-muted">Rekap Nilai Manual</span>
                        </div>
                        <div>
                            <span class="block text-xl font-semibold text-ink">15 Detik</span>
                            <span class="text-[11px] text-ink-muted">Rotasi Token Presensi</span>
                        </div>
                        <div>
                            <span class="block text-xl font-semibold text-ink">UU PDP</span>
                            <span class="text-[11px] text-ink-muted">Data Center Indonesia</span>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan (5 Kolom): Render UI Produk Asli (Tabel Capaian CPL Seed) -->
                <div class="lg:col-span-5">
                    <div class="border border-line rounded-[4px] bg-surface p-5 space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-line text-xs font-mono">
                            <span class="text-ink font-semibold">PORTOFOLIO CPL &middot; PRODI IF</span>
                            <span class="px-1.5 py-0.5 border border-brand text-brand rounded-[2px] text-[10px]">VERIFIKASI AKTIF</span>
                        </div>

                        <div class="text-xs space-y-1">
                            <p class="font-medium text-ink">S1 Teknik Informatika &middot; Kurikulum 2024</p>
                            <p class="text-ink-muted text-[11px]">Contoh data agregasi kelas IF-3A (38 Mahasiswa)</p>
                        </div>

                        <!-- Tabel Capaian CPL Riil -->
                        <div class="border-t border-line pt-3">
                            <table class="w-full text-left text-xs font-mono">
                                <thead>
                                    <tr class="text-ink-muted border-b border-line pb-1 text-[10px]">
                                        <th class="py-1.5 font-normal">KODE</th>
                                        <th class="py-1.5 font-normal">KOMPETENSI</th>
                                        <th class="py-1.5 text-right font-normal">TARGET</th>
                                        <th class="py-1.5 text-right font-normal">CAPAIAN</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line/60">
                                    <tr>
                                        <td class="py-2 text-ink font-semibold">CPL-01</td>
                                        <td class="py-2 text-ink-muted font-sans text-[11px]">Etika Profesi & Tanggung Jawab</td>
                                        <td class="py-2 text-right text-ink-muted">75%</td>
                                        <td class="py-2 text-right text-brand font-semibold">84%</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-ink font-semibold">CPL-02</td>
                                        <td class="py-2 text-ink-muted font-sans text-[11px]">Desain Pola & Pemrograman Objek</td>
                                        <td class="py-2 text-right text-ink-muted">70%</td>
                                        <td class="py-2 text-right text-brand font-semibold">78%</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-ink font-semibold">CPL-03</td>
                                        <td class="py-2 text-ink-muted font-sans text-[11px]">Arsitektur Software Berkelanjutan</td>
                                        <td class="py-2 text-right text-ink-muted">70%</td>
                                        <td class="py-2 text-right text-signal font-semibold">66%</td>
                                    </tr>
                                    <tr>
                                        <td class="py-2 text-ink font-semibold">CPL-04</td>
                                        <td class="py-2 text-ink-muted font-sans text-[11px]">Analisis Masalah Kompleks</td>
                                        <td class="py-2 text-right text-ink-muted">65%</td>
                                        <td class="py-2 text-right text-brand font-semibold">72%</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Bar Horizontal Ketercapaian Tunggal -->
                        <div class="pt-3 border-t border-line space-y-1.5">
                            <div class="flex justify-between text-[11px] font-mono">
                                <span class="text-ink">Rata-rata Ketercapaian Angkatan</span>
                                <span class="text-brand font-semibold">75.0%</span>
                            </div>
                            <div class="w-full bg-paper h-2 rounded-[2px] border border-line relative overflow-hidden">
                                <div class="bg-brand h-full" style="width: 75%;"></div>
                            </div>
                            <div class="flex justify-between text-[10px] font-mono text-ink-muted">
                                <span>Ambang batas minimum: 70%</span>
                                <span class="text-brand">Tercapai &middot; Lolos Standar</span>
                            </div>
                        </div>

                        <div class="pt-2 text-[11px] text-ink-muted flex items-center justify-between border-t border-line">
                            <span>Sumber: Asesmen Tugas 1 & Kuis 1</span>
                            <span class="font-mono text-ink">Terekam di SIAKAD</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================
         2. MASALAH VS SOLUSI (BUKU BESAR / LEDGER DUA KOLOM)
         ========================================== -->
    <section id="masalah-solusi" class="py-20 border-b border-line bg-surface">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-2xl mb-12">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Evaluasi Kebutuhan Kampus</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Perbandingan Praktik Pengelolaan Pembelajaran
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Bagaimana transformasi dari cara lama berbasis penumpukan dokumen ke sistem operasi pembelajaran berbasis luaran.
                </p>
            </div>

            <!-- Ledger Table Dua Kolom Bergaris 1px -->
            <div class="border border-line rounded-[4px] bg-paper overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-line">
                    
                    <!-- Kolom Kiri: Cara Konvensional -->
                    <div class="p-6 sm:p-8 space-y-6">
                        <div class="pb-3 border-b border-line flex items-center justify-between">
                            <h3 class="font-display font-medium text-lg text-ink">Praktik Konvensional</h3>
                            <span class="font-mono text-[11px] uppercase tracking-wider text-signal">Beban Manual Tinggi</span>
                        </div>

                        <div class="space-y-5 text-sm">
                            <div class="space-y-1">
                                <span class="font-mono text-xs text-ink-muted uppercase">01 &middot; Rekapitulasi Nilai</span>
                                <p class="text-ink font-medium">Excel terpisah di akhir semester</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Dosen dan tim akreditasi lembur berminggu-minggu menginput manual nilai dari beragam spreadsheet untuk menghitung ketercapaian CPMK dan CPL.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-ink-muted uppercase">02 &middot; Kehadiran di Kelas</span>
                                <p class="text-ink font-medium">Presensi tanda tangan atau kode statis</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Tanda tangan mudah dipalsukan, dan tautan absen dapat dibagikan keluar ruangan oleh mahasiswa yang tidak hadir secara fisik.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-ink-muted uppercase">03 &middot; Bantuan Kecerdasan Buatan</span>
                                <p class="text-ink font-medium">AI tanpa kendali pedagogis</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Mahasiswa menyalin jawaban langsung dari chatbot eksternal tanpa memahami konsep, sementara dosen tidak memiliki sistem verifikasi konteks.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-ink-muted uppercase">04 &middot; Ketergantungan Jaringan</span>
                                <p class="text-ink font-medium">Halaman gagal muat saat internet drop</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Mahasiswa di jaringan kampus yang padat atau lokasi pelosok terhambat mengunduh materi dan mengumpulkan tugas.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Kolom Kanan: Dengan Arunika -->
                    <div class="p-6 sm:p-8 space-y-6 bg-surface">
                        <div class="pb-3 border-b border-line flex items-center justify-between">
                            <h3 class="font-display font-medium text-lg text-brand">Dengan Arunika</h3>
                            <span class="font-mono text-[11px] uppercase tracking-wider text-brand">Terkoneksi Otomatis</span>
                        </div>

                        <div class="space-y-5 text-sm">
                            <div class="space-y-1">
                                <span class="font-mono text-xs text-brand uppercase">01 &middot; Native OBE Engine</span>
                                <p class="text-ink font-medium">Kalkulasi real-time tiap tugas dinilai</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Setiap rubrik tugas, kuis Bloom, dan asesmen langsung mengalir ke grafik radar CPL dan matriks pemetaan prodi tanpa rekap lembar kerja manual.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-brand uppercase">02 &middot; Presensi QR Dinamis & Radius</span>
                                <p class="text-ink font-medium">Rotasi kode 15 detik dengan batas geofence</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Kode QR berganti terus di layar dosen dan memverifikasi koordinat GPS mahasiswa berada di dalam radius ruang kuliah yang sah.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-brand uppercase">03 &middot; Pendamping Belajar Sokrates</span>
                                <p class="text-ink font-medium">Dialog penalaran dengan rujukan modul kuliah</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    AI menuntun mahasiswa berpikir langkah demi langkah sesuai silabus pengampu, disertai catatan kaki sumber materi asli.
                                </p>
                            </div>

                            <div class="space-y-1">
                                <span class="font-mono text-xs text-brand uppercase">04 &middot; Offline-First PWA</span>
                                <p class="text-ink font-medium">Modul tersimpan lokal, auto-sinkron saat online</p>
                                <p class="text-xs text-ink-muted leading-relaxed">
                                    Materi dapat dibaca dan draft tugas dapat disimpan secara luring, lalu disinkronkan otomatis begitu perangkat mendeteksi koneksi stabil.
                                </p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================
         3. FITUR BERNOBOR (01–06 EDITORIAL LIST)
         ========================================== -->
    <section id="fitur-unggulan" class="py-20 border-b border-line bg-paper">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-2xl mb-14">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Spesifikasi Fungsional</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Enam Instrumen Utama Pembelajaran
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Setiap modul dirancang selaras dengan instrumen 9 kriteria akreditasi LAM-PT, LAM-INFOKOM, dan BAN-PT.
                </p>
            </div>

            <!-- Daftar Fitur Berindeks 01 - 06 -->
            <div class="divide-y divide-line border-y border-line">
                
                <!-- 01. Native OBE Engine -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">01</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Mesin OBE & Matriks Ketercapaian CPL/CPMK</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Menghubungkan tugas kuliah, kuis, dan ujian ke bobot CPMK tertentu, yang kemudian diagregasikan ke profil CPL lulusan secara otomatis tanpa intervensi data ganda.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>Sesuai KKNI Level 6</span>
                            <span>&middot;</span>
                            <span>Audit Trail Lengkap</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 font-mono text-xs space-y-2">
                        <div class="flex justify-between text-[11px] text-ink-muted border-b border-line pb-1.5">
                            <span>ALUR DATA ASESMEN</span>
                            <span class="text-brand">OTOMATIS TERSINKRONISASI</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-ink">
                            <p>Tugas 1: Desain Arsitektur OOP &rarr; CPMK-02 (Bobot 40%)</p>
                            <p class="text-ink-muted">&rdquor; Mahasiswa: Arya Pratama (NIM 22001001) &middot; Skor 96.5 / 100</p>
                            <p class="text-brand">&rarr; Kontribusi ke CPL-02: +38.6 poin kompetensi</p>
                        </div>
                    </div>
                </div>

                <!-- 02. Pendamping Belajar (AI) -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">02</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Pendamping Belajar Kontekstual (Metode Sokrates)</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Bantuan kecerdasan buatan terarah yang bertindak sebagai tutor pribadi. Menuntun mahasiswa melalui pertanyaan kritis berjenjang dan rujukan slide materi, bukan pembagi kunci jawaban.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>RAG Silabus Kuliah</span>
                            <span>&middot;</span>
                            <span>Catatan Kaki Materi [1]</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 text-xs space-y-2">
                        <div class="flex justify-between text-[11px] font-mono text-ink-muted border-b border-line pb-1.5">
                            <span>CATATAN PINGGIR MAHASISWA</span>
                            <span class="text-ink">MODE SOKRATES</span>
                        </div>
                        <div class="pl-2.5 border-l-2 border-line text-xs space-y-1">
                            <p class="font-medium text-ink">"Mengapa sebuah class tidak boleh menangani parsing data sekaligus koneksi database?"</p>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                Bila satu class memiliki dua alasan untuk berubah, modifikasi pada skema data akan berisiko merusak logika koneksi [1].
                            </p>
                            <p class="font-mono text-[10px] text-ink-muted pt-1">
                                [1] Modul 03: Prinsip SOLID &middot; Pertemuan ke-5, Hal. 14
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 03. Gamifikasi Edukatif -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">03</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Buku Besar Pengalaman (XP Ledger) & Lencana Stempel</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Mencatat keaktifan belajar mahasiswa selayaknya buku besar akuntansi: pengumpulan tugas tepat waktu, partisipasi diskusi, dan presensi beruntun. Tanpa animasi berlebihan, tetap berbobot akademis.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>Audit Trail Transparan</span>
                            <span>&middot;</span>
                            <span>Stempel Kompetensi</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 font-mono text-xs space-y-2">
                        <div class="flex justify-between text-[11px] text-ink-muted border-b border-line pb-1.5">
                            <span>BUKU BESAR XP &middot; ARYA PRATAMA</span>
                            <span class="text-ink font-semibold">TOTAL: 650 XP</span>
                        </div>
                        <div class="text-[11px] space-y-1.5 text-ink-muted">
                            <div class="flex justify-between">
                                <span>+100 XP &middot; Tugas 1 tepat waktu</span>
                                <span>12 Okt 2026</span>
                            </div>
                            <div class="flex justify-between">
                                <span>+50 XP &middot; Presensi minggu ke-5 tepat jam</span>
                                <span>10 Okt 2026</span>
                            </div>
                            <div class="flex justify-between text-brand font-semibold">
                                <span>Stempel Terbit: "Disiplin Siklus 1"</span>
                                <span>Terverifikasi</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 04. Presensi QR Dinamis & Geofencing -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">04</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Presensi QR Dinamis Berotasi & Validasi Koordinat</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Mencegah kecurangan titip absen dengan kode QR yang diperbarui otomatis setiap 15 detik pada layar kelas dosen, dipadukan pemeriksaan geofencing radius ruang kuliah.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>Rotasi Interval 15 Detik</span>
                            <span>&middot;</span>
                            <span>Toleransi Radius 50m</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 font-mono text-xs space-y-2">
                        <div class="flex justify-between text-[11px] text-ink-muted border-b border-line pb-1.5">
                            <span>STATUS SESI RUANG LAB 302</span>
                            <span class="text-brand">TERBUKA (8:00 - 10:30)</span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-[11px]">
                            <div>
                                <span class="text-ink-muted block">Hadir Terverifikasi:</span>
                                <span class="text-ink font-semibold text-sm">36 / 38 Mahasiswa</span>
                            </div>
                            <div>
                                <span class="text-ink-muted block">Sisa Waktu Token:</span>
                                <span class="text-signal font-semibold text-sm">11 Detik</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 05. PWA Offline-First -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">05</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Arsitektur Offline-First & Cache Dokumen Mandiri</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Materi kuliah diunduh ke penyimpanan peramban lokal (IndexedDB). Mahasiswa tetap dapat membaca modul dan mengerjakan draf respons saat jaringan kampus atau daerah padat sedang terputus.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>Service Worker Standar PWA</span>
                            <span>&middot;</span>
                            <span>Hemat Bandwidth</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 font-mono text-xs space-y-2">
                        <div class="flex justify-between text-[11px] text-ink-muted border-b border-line pb-1.5">
                            <span>STATUS PENYIMPANAN LOKAL</span>
                            <span class="text-brand">SINKRONISASI AKTIF</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-ink-muted">
                            <p>Modul Tersimpan: 14 Berkas (18.4 MB terkompresi)</p>
                            <p class="text-brand">&check; Terakhir sinkronisasi: 14 menit yang lalu</p>
                        </div>
                    </div>
                </div>

                <!-- 06. Portofolio MBKM & Logbook Mitra -->
                <div class="py-8 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <div class="lg:col-span-1 font-mono text-sm text-brand font-semibold">06</div>
                    <div class="lg:col-span-5 space-y-2">
                        <h3 class="font-display font-medium text-2xl text-ink">Buku Harian MBKM & Portofolio Mitra Industri</h3>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Mendukung verifikasi langsung oleh mentor perusahaan mitra terhadap logbook mingguan mahasiswa magang, dengan konversi otomatis ke ekuivalensi 20 SKS mata kuliah kampus.
                        </p>
                        <div class="pt-2 flex items-center gap-4 text-xs font-mono text-ink-muted">
                            <span>Tanda Tangan Digital Mentor</span>
                            <span>&middot;</span>
                            <span>Konversi 20 SKS</span>
                        </div>
                    </div>
                    <div class="lg:col-span-6 bg-surface border border-line rounded-[4px] p-4 font-mono text-xs space-y-2">
                        <div class="flex justify-between text-[11px] text-ink-muted border-b border-line pb-1.5">
                            <span>LOG MINGGUAN &middot; PT TECH NUSANTARA</span>
                            <span class="text-brand">DISETUJUI MENTOR</span>
                        </div>
                        <div class="text-[11px] space-y-1 text-ink-muted">
                            <p class="text-ink">Aktivitas: Refactoring API Microservices & Penulisan Unit Test</p>
                            <p>Catatan Mentor: "Kode bersih, pemahaman arsitektur sangat baik."</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ==========================================
         4. SIKLUS MUTU OBE (DIAGRAM ALUR SVG BERGARIS TIPIS)
         ========================================== -->
    <section id="siklus-obe" class="py-20 border-b border-line bg-surface">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-2xl mb-12">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Penjaminan Mutu Internal</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Siklus Kualitas Berkelanjutan (CQI Loop)
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Empat tahapan terhubung yang memastikan evaluasi pembelajaran berjalan sistematis di setiap semester.
                </p>
            </div>

            <!-- Diagram Alur Horizontal 4 Simpul (Bukan Kartu Berikon) -->
            <div class="border border-line rounded-[4px] bg-paper p-6 sm:p-8">
                
                <!-- SVG Connector Line (Desktop) -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 relative">
                    
                    <!-- Simpul 1 -->
                    <div class="space-y-3 pb-6 md:pb-0 md:pr-4">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-[2px] bg-ink text-paper font-mono text-xs flex items-center justify-center font-semibold">01</span>
                            <span class="font-mono text-xs uppercase tracking-wider text-ink-muted">Perencanaan</span>
                        </div>
                        <h4 class="font-display font-medium text-lg text-ink">Perumusan Target CPL</h4>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Prodi mendefinisikan capaian pembelajaran lulusan berdasarkan profil alumni dan kebutuhan asosiasi profesi.
                        </p>
                        <div class="font-mono text-[11px] text-ink-muted border-t border-line pt-2">
                            Luaran: Dokumen Kurikulum OBE
                        </div>
                    </div>

                    <!-- Simpul 2 -->
                    <div class="space-y-3 pb-6 md:pb-0 md:pr-4 md:border-l border-line md:pl-6">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-[2px] bg-ink text-paper font-mono text-xs flex items-center justify-center font-semibold">02</span>
                            <span class="font-mono text-xs uppercase tracking-wider text-ink-muted">Pelaksanaan</span>
                        </div>
                        <h4 class="font-display font-medium text-lg text-ink">RPS & Rantai CPMK</h4>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Dosen menyusun RPS digital, mendistribusikan materi, dan menghubungkan setiap pertemuan ke CPMK turunan.
                        </p>
                        <div class="font-mono text-[11px] text-ink-muted border-t border-line pt-2">
                            Luaran: Kontrak Kuliah Terpetakan
                        </div>
                    </div>

                    <!-- Simpul 3 -->
                    <div class="space-y-3 pb-6 md:pb-0 md:pr-4 md:border-l border-line md:pl-6">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-[2px] bg-ink text-paper font-mono text-xs flex items-center justify-center font-semibold">03</span>
                            <span class="font-mono text-xs uppercase tracking-wider text-ink-muted">Pengukuran</span>
                        </div>
                        <h4 class="font-display font-medium text-lg text-ink">Asesmen Berbobot</h4>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Tugas dan ujian dinilai menggunakan rubrik terstandar; nilai langsung dikonversi menjadi skor capaian CPL.
                        </p>
                        <div class="font-mono text-[11px] text-ink-muted border-t border-line pt-2">
                            Luaran: Portofolio Nilai Asli
                        </div>
                    </div>

                    <!-- Simpul 4 -->
                    <div class="space-y-3 md:border-l border-line md:pl-6">
                        <div class="flex items-center gap-3">
                            <span class="w-7 h-7 rounded-[2px] bg-brand text-paper font-mono text-xs flex items-center justify-center font-semibold">04</span>
                            <span class="font-mono text-xs uppercase tracking-wider text-brand">Perbaikan</span>
                        </div>
                        <h4 class="font-display font-medium text-lg text-brand">Audit & Rekomendasi CQI</h4>
                        <p class="text-xs text-ink-muted leading-relaxed">
                            Laporan evaluasi otomatis tersaji untuk reviewer akreditasi guna menentukan perbaikan silabus pada siklus berikutnya.
                        </p>
                        <div class="font-mono text-[11px] text-brand border-t border-line pt-2">
                            Luaran: Laporan Akreditasi 1-Klik
                        </div>
                    </div>

                </div>

            </div>

        </div>
    </section>

    <!-- ==========================================
         5. TABEL KOMPARASI AKADEMIS
         ========================================== -->
    <section id="komparasi" class="py-20 border-b border-line bg-paper">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-2xl mb-12">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Evaluasi Fitur</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Matriks Perbandingan Solusi LMS
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Tinjauan kapabilitas fungsional antara sistem LMS umum dengan arsitektur Arunika Learning OS.
                </p>
            </div>

            <!-- Tabel Bergaris 1px Tanpa Efek Gelombang atau Shadow -->
            <div class="border border-line rounded-[4px] overflow-hidden bg-surface">
                <table class="w-full text-left text-xs font-mono">
                    <thead>
                        <tr class="border-b border-line bg-paper text-ink">
                            <th class="p-4 font-medium uppercase text-[11px]">Parameter Evaluasi</th>
                            <th class="p-4 font-medium uppercase text-[11px] text-ink-muted">LMS Konvensional</th>
                            <th class="p-4 font-medium uppercase text-[11px] text-brand bg-brand-tint/40">Arunika Learning OS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line text-ink">
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Integrasi Kurikulum OBE (CPL/CPMK)</td>
                            <td class="p-4 text-ink-muted font-sans">Tidak ada (Perlu plugin manual / Excel)</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; Native dari penilaian harian</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Penyusunan Rubrik & Kuis Bloom</td>
                            <td class="p-4 text-ink-muted font-sans">Manual sepenuhnya</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; Draf otomatis sesuai silabus</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Bantuan Belajar Mandiri (AI)</td>
                            <td class="p-4 text-ink-muted font-sans">Tidak ada kendali pedagogis</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; Metode Sokrates + Rujukan modul</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Presensi Dinamis & Pencegahan Titip Absen</td>
                            <td class="p-4 text-ink-muted font-sans">Kode statis / manual tanda tangan</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; QR rotasi 15s + Geofencing GPS</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Aksesibilitas Tanpa Internet (Offline Cache)</td>
                            <td class="p-4 text-ink-muted font-sans">Gagal muat tanpa koneksi stabil</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; PWA IndexedDB cache lokal</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Peran Khusus Auditor Akreditasi</td>
                            <td class="p-4 text-ink-muted font-sans">Harus dibuatkan akun admin luas</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; Portal read-only portofolio CPL</td>
                        </tr>
                        <tr>
                            <td class="p-4 font-sans font-medium text-ink">Kepatuhan UU Pelindungan Data Pribadi (UU PDP)</td>
                            <td class="p-4 text-ink-muted font-sans">Seringkali server di luar yurisdiksi RI</td>
                            <td class="p-4 bg-brand-tint/20 text-brand font-semibold">Ada &middot; Pusat data nasional terenkripsi</td>
                        </tr>
                    </tbody>
                </table>
            </div>

        </div>
    </section>

    <!-- ==========================================
         6. PAKET HARGA (3 KOLOM BERGARIS VERTIKAL TANPA KARTU)
         ========================================== -->
    <section id="harga" class="py-20 border-b border-line bg-surface">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            
            <div class="max-w-2xl mb-12">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Struktur Investasi</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Paket Implementasi Kampus
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Skema lisensi transparan disesuaikan dengan skala jumlah mahasiswa aktif dan kesiapan infrastruktur perguruan tinggi.
                </p>
            </div>

            <!-- Tiga Kolom Dipisah Garis Vertikal (Tanpa Kartu Melayang, Tanpa Pill Badge) -->
            <div class="border border-line rounded-[4px] bg-paper overflow-hidden">
                <div class="grid grid-cols-1 md:grid-cols-3 divide-y md:divide-y-0 md:divide-x divide-line">
                    
                    <!-- Paket 1: Fakultas Mandiri -->
                    <div class="p-6 sm:p-8 flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <span class="font-mono text-xs uppercase tracking-wider text-ink-muted block">Paket 01</span>
                            <h3 class="font-display font-medium text-2xl text-ink">Fakultas Mandiri</h3>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                Ideal untuk fakultas pelopor yang sedang mempersiapkan akreditasi internasional mandiri (ASIIN / ABET).
                            </p>
                            <div class="border-y border-line py-3 font-mono">
                                <span class="text-xs text-ink-muted block">Kapasitas:</span>
                                <span class="text-lg font-semibold text-ink">Hingga 2.500 Mahasiswa</span>
                            </div>
                            <ul class="space-y-2.5 text-xs text-ink-muted font-mono">
                                <li>&check; Modul Lengkap Native OBE</li>
                                <li>&check; Pendamping Belajar AI Standar</li>
                                <li>&check; Presensi QR Dinamis Kelas</li>
                                <li>&check; Ekspor Portofolio Akreditasi</li>
                                <li>&check; Dukungan Email Kerja 8x5</li>
                            </ul>
                        </div>
                        <a href="#demo-form" class="block text-center py-2.5 rounded-[4px] border border-line hover:border-ink text-ink font-medium text-xs transition-editorial">
                            Pilih Paket Fakultas
                        </a>
                    </div>

                    <!-- Paket 2: Institusi Kampus -->
                    <div class="p-6 sm:p-8 flex flex-col justify-between space-y-6 bg-surface">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs uppercase tracking-wider text-brand">Paket 02</span>
                                <span class="font-mono text-[10px] uppercase tracking-wider border border-brand text-brand px-1.5 py-0.5 rounded-[2px]">Standar Universitas</span>
                            </div>
                            <h3 class="font-display font-medium text-2xl text-ink">Institusi Kampus</h3>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                Solusi menyeluruh untuk universitas atau institut dengan integrasi penuh ke SIAKAD dan Neo Feeder PDDikti.
                            </p>
                            <div class="border-y border-line py-3 font-mono">
                                <span class="text-xs text-ink-muted block">Kapasitas:</span>
                                <span class="text-lg font-semibold text-ink">2.500 &ndash; 15.000 Mahasiswa</span>
                            </div>
                            <ul class="space-y-2.5 text-xs text-ink-muted font-mono">
                                <li>&check; Seluruh Kapabilitas Fakultas</li>
                                <li>&check; Integrasi API SIAKAD & Neo Feeder</li>
                                <li>&check; Portal Khusus Auditor Eksternal</li>
                                <li>&check; Modul Logbook MBKM Terintegrasi</li>
                                <li>&check; Pelatihan Dosen & Administrator BAAK</li>
                                <li>&check; Dukungan Teknis Prioritas</li>
                            </ul>
                        </div>
                        <a href="#demo-form" class="block text-center py-2.5 rounded-[4px] bg-ink text-paper hover:bg-black font-medium text-xs transition-editorial">
                            Pilih Paket Institusi
                        </a>
                    </div>

                    <!-- Paket 3: Enterprise / Konsorsium -->
                    <div class="p-6 sm:p-8 flex flex-col justify-between space-y-6">
                        <div class="space-y-4">
                            <span class="font-mono text-xs uppercase tracking-wider text-ink-muted block">Paket 03</span>
                            <h3 class="font-display font-medium text-2xl text-ink">Enterprise / Konsorsium</h3>
                            <p class="text-xs text-ink-muted leading-relaxed">
                                Dirancang untuk yayasan perguruan tinggi multi-kampus atau institusi yang membutuhkan instalasi on-premise khusus.
                            </p>
                            <div class="border-y border-line py-3 font-mono">
                                <span class="text-xs text-ink-muted block">Kapasitas:</span>
                                <span class="text-lg font-semibold text-ink">&gt; 15.000 Mahasiswa</span>
                            </div>
                            <ul class="space-y-2.5 text-xs text-ink-muted font-mono">
                                <li>&check; Opsi Deployment On-Premise Kampus</li>
                                <li>&check; Custom Single Sign-On (SSO / LDAP)</li>
                                <li>&check; Fine-tuning Model AI Berbasis Silabus</li>
                                <li>&check; Perjanjian Tingkat Layanan (SLA 99.9%)</li>
                                <li>&check; Dedicated Technical Account Manager</li>
                            </ul>
                        </div>
                        <a href="#demo-form" class="block text-center py-2.5 rounded-[4px] border border-line hover:border-ink text-ink font-medium text-xs transition-editorial">
                            Konsultasi Enterprise
                        </a>
                    </div>

                </div>
            </div>

        </div>
    </section>

    <!-- ==========================================
         7. TANYA JAWAB (FAQ EDITORIAL)
         ========================================== -->
    <section id="faq" class="py-20 border-b border-line bg-paper" x-data="{ activeAccordion: null }">
        <div class="max-w-4xl mx-auto px-4 sm:px-6">
            
            <div class="mb-12">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Informasi Teknis</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Pertanyaan yang Sering Diajukan
                </h2>
            </div>

            <div class="divide-y divide-line border-y border-line">
                
                <!-- FAQ 1 -->
                <div class="py-4">
                    <button @click="activeAccordion = activeAccordion === 1 ? null : 1" class="w-full text-left font-medium text-ink text-sm flex items-center justify-between cursor-pointer py-1">
                        <span>Bagaimana proses migrasi data dari Moodle atau sistem LMS kampus sebelumnya?</span>
                        <span x-text="activeAccordion === 1 ? '−' : '+'" class="font-mono text-base text-ink-muted ml-4"></span>
                    </button>
                    <div x-show="activeAccordion === 1" class="pt-3 pb-2 text-xs text-ink-muted leading-relaxed" style="display: none;">
                        Arunika menyediakan perkakas impor data terstruktur (MBZ parser untuk Moodle, berkas CSV/Excel untuk SIAKAD). Silabus matakuliah, bank soal, dan riwayat mahasiswa dapat dipindahkan secara bertahap tanpa menghentikan kegiatan belajar mengajar yang sedang berlangsung.
                    </div>
                </div>

                <!-- FAQ 2 -->
                <div class="py-4">
                    <button @click="activeAccordion = activeAccordion === 2 ? null : 2" class="w-full text-left font-medium text-ink text-sm flex items-center justify-between cursor-pointer py-1">
                        <span>Bagaimana peran akun Auditor Akreditasi membantu saat visitasi asesor?</span>
                        <span x-text="activeAccordion === 2 ? '−' : '+'" class="font-mono text-base text-ink-muted ml-4"></span>
                    </button>
                    <div x-show="activeAccordion === 2" class="pt-3 pb-2 text-xs text-ink-muted leading-relaxed" style="display: none;">
                        Akun Auditor dirancang khusus dengan izin baca-saja (*read-only*). Asesor atau tim penjaminan mutu dapat langsung memeriksa matriks ketercapaian CPL per program studi, bukti rubrik penilaian tugas dosen, serta mengunduh seluruh dokumen portofolio matakuliah dalam satu bundel PDF terindeks.
                    </div>
                </div>

                <!-- FAQ 3 -->
                <div class="py-4">
                    <button @click="activeAccordion = activeAccordion === 3 ? null : 3" class="w-full text-left font-medium text-ink text-sm flex items-center justify-between cursor-pointer py-1">
                        <span>Apakah sistem ini mematuhi UU Perlindungan Data Pribadi (UU No. 27/2022)?</span>
                        <span x-text="activeAccordion === 3 ? '−' : '+'" class="font-mono text-base text-ink-muted ml-4"></span>
                    </button>
                    <div x-show="activeAccordion === 3" class="pt-3 pb-2 text-xs text-ink-muted leading-relaxed" style="display: none;">
                        Ya. Seluruh data akademik dan rekaman aktivitas mahasiswa dienkripsi (AES-256 saat tersimpan, TLS 1.3 saat transit), dioperasikan pada pusat data tier-4 di wilayah hukum Indonesia, dan tidak dibagikan ke pihak ketiga mana pun.
                    </div>
                </div>

                <!-- FAQ 4 -->
                <div class="py-4">
                    <button @click="activeAccordion = activeAccordion === 4 ? null : 4" class="w-full text-left font-medium text-ink text-sm flex items-center justify-between cursor-pointer py-1">
                        <span>Apakah pengoperasian sistem memerlukan pelatihan teknis khusus bagi dosen?</span>
                        <span x-text="activeAccordion === 4 ? '−' : '+'" class="font-mono text-base text-ink-muted ml-4"></span>
                    </button>
                    <div x-show="activeAccordion === 4" class="pt-3 pb-2 text-xs text-ink-muted leading-relaxed" style="display: none;">
                        Antarmuka dirancang lugas tanpa navigasi bertingkat yang rumit. Dosen dapat menyusun silabus RPS dengan wizard terpandu, dan tim implementasi Arunika menyelenggarakan sesi pelatihan resmi serta pendampingan langsung selama satu semester pertama.
                    </div>
                </div>

            </div>

        </div>
    </section>

    <!-- ==========================================
         8. FORMULIR REQUEST DEMO (1 KOLOM EDITORIAL)
         ========================================== -->
    <section id="demo-form" class="py-20 bg-surface">
        <div class="max-w-3xl mx-auto px-4 sm:px-6">
            
            <div class="mb-10">
                <span class="font-mono uppercase text-xs tracking-wider text-ink-muted block mb-2">Penjadwalan Presentasi</span>
                <h2 class="font-display font-medium text-3xl sm:text-4xl text-ink">
                    Jadwalkan Presentasi & Simulasi OBE
                </h2>
                <p class="text-sm text-ink-muted mt-2">
                    Sampaikan informasi institusi Anda. Tim kurikulum kami akan menghubungi dalam waktu maksimal 1x24 jam kerja untuk menyusun jadwal simulasi.
                </p>
            </div>

            <!-- Form Satu Kolom Berborder 1px -->
            <div class="border border-line rounded-[4px] bg-paper p-6 sm:p-8">
                <form action="{{ route('demo.request.store') }}" method="POST" class="space-y-5">
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Nama Lengkap & Gelar *</label>
                            <input type="text" name="name" required placeholder="Prof. Dr. Ir. Anton Wijaya" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans">
                        </div>
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Email Resmi Institusi *</label>
                            <input type="email" name="email" required placeholder="anton@kampus-negeri.ac.id" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Nama Perguruan Tinggi *</label>
                            <input type="text" name="institution" required placeholder="Institut Teknologi Nusantara" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans">
                        </div>
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Nomor Telepon / WhatsApp</label>
                            <input type="text" name="phone" placeholder="081234567890" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans font-mono">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Jabatan di Kampus</label>
                            <select name="role" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans">
                                <option value="Rektor / Wakil Rektor">Rektor / Wakil Rektor</option>
                                <option value="Dekan / Wakil Dekan">Dekan / Wakil Dekan</option>
                                <option value="Ketua Program Studi (Kaprodi)">Ketua Program Studi (Kaprodi)</option>
                                <option value="Kepala BAAK / IT Kampus">Kepala BAAK / IT Kampus</option>
                                <option value="Lembaga Penjaminan Mutu (LPM)">Lembaga Penjaminan Mutu (LPM)</option>
                                <option value="Dosen Pengampu">Dosen Pengampu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Estimasi Mahasiswa Aktif</label>
                            <select name="students_count" class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans">
                                <option value="< 3.000 Mahasiswa">&lt; 3.000 Mahasiswa</option>
                                <option value="3.000 - 10.000 Mahasiswa">3.000 - 10.000 Mahasiswa</option>
                                <option value="10.000 - 25.000 Mahasiswa">10.000 - 25.000 Mahasiswa</option>
                                <option value="> 25.000 Mahasiswa">&gt; 25.000 Mahasiswa</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-mono uppercase tracking-wider text-ink mb-1.5">Kebutuhan Kurikulum atau Agenda Presentasi</label>
                        <textarea name="message" rows="3" placeholder="Jelaskan kebutuhan akreditasi prodi, target jadwal presentasi, atau integrasi sistem yang diinginkan..." class="w-full px-3 py-2.5 rounded-[4px] bg-surface border border-line text-ink text-sm focus:outline-none focus:border-brand transition-editorial font-sans"></textarea>
                    </div>

                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 rounded-[4px] bg-ink text-paper hover:bg-black font-medium text-sm transition-editorial flex items-center justify-center gap-2 cursor-pointer">
                            <span>Jadwalkan Presentasi Kampus</span>
                            <x-icon name="arrow-right" class="w-4 h-4 text-paper" />
                        </button>
                        <p class="text-center font-mono text-[11px] text-ink-muted mt-2">
                            Data kerahasiaan institusi dijamin penuh sesuai ketentuan hukum.
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </section>

@endsection
