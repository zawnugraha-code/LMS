# Wiring Audit — Arunika LMS (Portal Mahasiswa)
*Audit Komprehensif Elemen Antarmuka, Status Keterhubungan, Sumber Data & Aksi*

Dokumen ini memetakan seluruh elemen antarmuka pada 8 halaman Mahasiswa dan elemen global.
Status keterhubungan didefinisikan sebagai:
- **`Dummy`**: Masih menggunakan data hardcode / statis di Blade, belum tersambung ke database.
- **`Setengah Jalan`**: Sebagian data diambil dari DB / controller, namun aksi, filter, atau kalkulasi masih dummy.
- **`Jalan`**: Sepenuhnya tersambung ke database, kalkulasi real-time via service, event-driven, dan aksi tervalidasi.

---

## 1. Elemen Global & Topbar Layout (`resources/views/layouts/app.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Brand Logo & Wordmark** | `Jalan` | Komponen `<x-logo variant="inverse" />` | Menautkan ke `route('app.dashboard')`. |
| **Sidebar 8 Menu Navigasi** | `Jalan` | Route name matching (`request()->route()->getName()`) | Highlighting aktif `bg-navy-700 text-white shadow-xs` saat di rute utama maupun sub-halaman. |
| **Blok Institusi Kampus** | `Setengah Jalan` | `Auth::user()->campus` (Fallback string) | Mengambil nama kampus, logo, dan tagline dari model `Campus`. |
| **Profil Pengguna Sidebar Bawah** | `Setengah Jalan` | `Auth::user()` | Menampilkan foto avatar mahasiswa asli, nama, role. Klik membuka popover menu profil. |
| **Breadcrumb Dinamis** | `Setengah Jalan` | `@section('page_breadcrumb')` | Menampilkan hirarki dinamis (misal: `Beranda > Mata Kuliah > IF2101`). |
| **Kotak Pencarian Topbar (`Ctrl+K`)** | `Dummy` | `SearchService` (Scout / Indexed LIKE) | Modal pencarian global: debounce 250ms, filter kategori (MK, Materi, Tugas, Diskusi), navigasi panah keyboard, scoped ke enrollment mahasiswa. |
| **Lonceng Notifikasi & Badge Angka** | `Dummy` (Hardcode "2") | `NotificationService`, tabel `notifications` | Menampilkan badge jumlah belum dibaca real-time. Klik membuka dropdown 10 notifikasi terbaru, aksi "Tandai Sudah Dibaca", dan tautan ke halaman target. |
| **Menu Dropdown Profil Topbar** | `Setengah Jalan` (Membuka AI drawer) | `Auth::user()` | Popover profil: menu "Profil Saya" (`/app/settings`), "Pengaturan" (`/app/settings`), dan tombol "Keluar" (POST logout). |
| **Mobile Drawer Sidebar** | `Jalan` | Alpine.js `mobileSidebar` | Responsive drawer dengan overlay untuk layar smartphone/tablet. |
| **Slide-over Panel AI Copilot** | `Setengah Jalan` | `AiCopilotController`, `Gemini` API | Tutor cerdas RPS: tombol toggle Socratic Mode, topik cepat, riwayat chat, konteks materi aktif. |

---

## 2. Halaman Beranda (`resources/views/pages/app/dashboard.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Banner Sambutan Mahasiswa** | `Setengah Jalan` | `Auth::user()->name`, `now()` | Sapaan waktu dinamis (Pagi/Siang/Sore), tanggal hari ini, tombol "Lihat Jadwal Kuliah" menaut ke jadwal mingguan. |
| **Mata Kuliah Hari Ini** | `Dummy` | `ScheduleService`, `AcademicClass` | Query kelas yang memiliki jadwal di hari ini (`schedule_day = now()->locale('id')->dayName`). Chevron menuju ruang kelas. Empty state jika tidak ada jadwal hari ini. |
| **Tugas & Ujian Terbaru** | `Setengah Jalan` | `AssignmentService`, `Submission` | Mengambil 3 tugas terdekat dengan badge waktu dinamis ("2 hari lagi", "Besok", "Terlambat"). Tombol "Kerjakan" / "Lihat Nilai". |
| **Mata Kuliah Terdaftar** | `Setengah Jalan` | `Enrollment`, `CourseProgressService` | Daftar 5 MK aktif dengan indikator progres materi persentase asli. Tombol "Lihat Semua" menautkan ke `/app/courses`. |
| **Tile Ringkasan Akademik** | `Dummy` (Angka statis) | `GradeService`, `AttendanceService` | 4 Metrik: IPK Kumulatif (3.85), Total SKS (40 SKS), Persentase Presensi (94.1%), dan MK Aktif (5 MK). Dropdown semester tersinkronisasi. |
| **Capaian Kompetensi CPL** | `Setengah Jalan` | `OutcomeScore`, `Cpl` | Bar persentase capaian CPL program studi dari asesmen yang telah dinilai. Tombol "Lihat Detail" membuka halaman rincian CPL. |
| **Sertifikat & Kompetensi** | `Dummy` | `CertificateService` | Daftar sertifikat mahasiswa: tombol "Lihat" membuka modal viewer, unduh PDF sertifikat, dan verifikasi publik. |
| **Buku Besar XP (XP Ledger)** | `Setengah Jalan` | `XpLedger`, `XpService` | Riwayat penambahan XP dari submit tugas, kuis, presensi. Tombol "Audit Log" membuka modal riwayat transaksional. |

---

## 3. Halaman Mata Kuliah (`resources/views/pages/app/courses.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Bar Ringkasan Metrik (5 Tile)** | `Dummy` | `CourseService`, `Enrollment` | Menghitung otomatis: Total MK (6), SKS Total (18), SKS Diambil (15), MK Aktif (5), MK Selesai (0). |
| **Dropdown Pemilih Semester** | `Dummy` (Statis di Blade) | `Term`, session user preference | Mengganti semester aktif, memperbarui seluruh kartu MK dan bar ringkasan seketika. |
| **Tab Status Filter (Semua, Aktif, Selesai, Arsip)** | `Dummy` (Alpine client-only) | Filter status query `enrollments.status` | Memfilter kartu kelas secara instan dengan indikator tab aktif. |
| **Pencarian Nama / Kode MK** | `Dummy` | Input search query | Debounce 250ms memfilter kartu mata kuliah berdasarkan kode atau nama. |
| **Tombol Filter Popover** | `Dummy` | Form filter kustom | Popover filter multi-kriteria: dosen pengampu, hari kuliah, dan status kelas. |
| **Kartu Mata Kuliah Aktif** | `Setengah Jalan` | `AcademicClass`, `CourseProgressService` | Menampilkan data riil: Kode MK, nama, badge status, dosen pengampu, hari, jam, ruang, progress bar persentase materi selesai / total pertemuan. |
| **Aksi Tombol "Lihat Detail"** | `Dummy` (Alpine alert) | `Route::get('/app/courses/{class}')` | Membuka halaman/modal RPS & Silabus, daftar dosen, bobot penilaian, dan CPMK. |
| **Aksi Tombol "Masuk Kelas"** | `Dummy` (Alpine alert) | `Route::get('/app/courses/{class}/classroom')`| Membuka ruang kelas virtual: timeline pertemuan 1-14 berisi materi, tugas, dan kuis. |
| **Kartu Terkunci "Akan Datang" (IF2106)** | `Dummy` | Flag status kurikulum / semester | Tombol dinamis berstatus `disabled`, tooltip tanggal pembukaan KRS, penolakan akses di sisi server. |

---

## 4. Halaman Tugas & Ujian (`resources/views/pages/app/assignments.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **4 Tile Metrik Statistik** | `Dummy` | `AssignmentService`, `Submission` | Hitungan real-time: Tugas Aktif (3), Ujian Mendatang (1), Tugas Selesai (5), Tugas Terlambat (0). Klik tile otomatis menerapkan filter list. |
| **Tab Filter Jenis (Semua, Tugas, Ujian, Terlambat)** | `Dummy` (Alpine) | Query filter tab | Memfilter daftar item sesuai kategori asesmen. |
| **Dropdown Pengurutan & Filter Tanggal** | `Dummy` | Query sorting & date range | Pilihan sort: "Tenggat Terdekat", "Terbaru Dibuat", "Terlama". Date range picker self-hosted. |
| **Baris Item Tugas & Ujian** | `Setengah Jalan` | `Assignment`, `Quiz`, `Submission` | Badge tipe, judul, nama MK, deadline relatif (`now()`), status pengerjaan (Belum Dikerjakan, Draft, Terkumpul, Dinilai, Terlambat). |
| **Tombol Aksi "Kerjakan" / "Lihat Nilai"** | `Dummy` (Alpine alert) | `Route::get('/app/assignments/{assignment}')` | Mengarahkan ke halaman pengerjaan tugas (upload file, teks jawaban, rubrik baca-saja, draft simpan). |
| **Menu Titik Tiga Baris Tugas** | `Dummy` (Alpine popover) | Signed storage download URL | Aksi: "Lihat Detail", "Unduh Lampiran Tugas", dan "Tambahkan ke Kalender (.ics)". |
| **Mini Kalender Bulanan Interaktif** | `Dummy` (Grid tanggal statis) | `AssignmentService` calendar events | Penanda dot tugas & ujian pada tanggal terkait; klik tanggal memfilter daftar tugas di sebelah kiri. Navigasi bulan Sebelumnya/Berikutnya. |
| **Widget Tugas & Ujian Terdekat** | `Dummy` | `AssignmentService` next deadlines | 3 tenggat terdekat berurutan waktu. Tombol "Lihat Semua" mengaktifkan filter tenggat terdekat. |

---

## 5. Halaman Presensi (`resources/views/pages/app/attendance.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **4 Tile Metrik Kehadiran** | `Dummy` | `AttendanceService`, `AttendanceLog` | Persentase Kehadiran Total (94.1%), Hadir (32), Izin/Sakit (2), Alpa (0). Dihitung dari seluruh sesi yang dibuka. |
| **Tombol "Scan QR Presensi"** | `Dummy` (Alpine modal) | `AttendanceService::checkInByQr()` | Membuka kamera pemindai QR token dinamis (HMAC 15 detik) dengan validasi kedaluwarsa & cegah kecurangan lokasi/perangkat. |
| **Tombol "Input Kode Presensi"** | `Dummy` (Alpine modal) | `AttendanceService::checkInByCode()` | Fallback input 6 digit token angka saat kamera bermasalah. |
| **Tombol "Ajukan Izin / Sakit"** | `Dummy` (Alpine modal) | `AttendanceService::requestLeave()` | Form pengajuan izin/sakit disertai unggahan bukti dokumen/foto surat dokter. |
| **Rekapitulasi Kehadiran per Mata Kuliah** | `Dummy` | Query per kelas | Tabel/daftar progress kehadiran per MK dengan status persentase dan batas minimal kehadiran 75% untuk ikut UAS. |
| **Riwayat Log Presensi** | `Dummy` | `AttendanceLog` | Feed linimasa kehadiran lengkap: tanggal, jam check-in, metode (QR/Manual), dan status validasi dosen. |

---

## 6. Halaman Nilai (`resources/views/pages/app/grades.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **4 Tile Metrik Indeks Prestasi** | `Dummy` | `GradeService` | IPS Semester Ini (3.80), Selisih IPS (-0.08), SKS Diambil (15 / 24 Kuota), MK Dinilai (5), MK Belum Selesai (6). |
| **Dropdown Semester & Tab Semester 1-8** | `Dummy` (Alpine state) | `Term`, `Enrollment` | Berganti semester menampilkan transkrip semester terpilih atau tab "Semua" untuk seluruh riwayat. |
| **Tabel Transkrip Mata Kuliah** | `Dummy` | `Enrollment`, `GradeService` | Kolom: No, Kode, Mata Kuliah, SKS, Nilai Angka, Huruf Mutu, Status (Lulus / Belum Selesai). |
| **Tombol Aksi "Lihat Detail" Nilai** | `Dummy` (Alpine alert) | `Route::get('/app/grades/{enrollment}')` | Modal/halaman rincian komponen nilai (Tugas 25%, Kuis 15%, Presensi 10%, UTS 25%, UAS 25%), skor CPMK, dan tombol unduh PDF KHS. |
| **Donut Chart Distribusi Nilai** | `Dummy` (Inline SVG statis) | `GradeService::distribution()` | 6 irisan nilai (A, B, B-, C, D, E) dari config grading, dengan angka total MK di tengah lingkaran. |
| **Grafik Batang Performa Akademik** | `Dummy` (CSS bars statis) | `GradeService::performanceHistory()` | Visualisasi perbandingan IPS per semester dan target IPK kampus. Dropdown filter grafik ikut berfungsi. |
| **Feed Pengumuman Nilai Terbaru** | `Dummy` | `GradePublished` event logs | Feed pengumuman nilai baru yang dipublikasikan dosen beserta tanggal relatif dan tombol "Lihat Semua". |

---

## 7. Halaman Materi Pembelajaran (`resources/views/pages/app/materials.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Header MK & Bar Progres Materi** | `Dummy` (Hardcode IF2101) | `CourseProgressService` | Kode MK, nama, semester, kelas, progres materi (5/14 Materi • 35.7% Selesai). Memilih MK lain mengganti seluruh isi halaman. |
| **Pills Filter Jenis Materi** | `Dummy` (Alpine) | Query filter tipe | Filter jenis: Semua, Modul, Video, Tugas, Kuis, Referensi. |
| **Pencarian Materi & Dropdown Filter** | `Dummy` | Input search & filter status | Pencarian judul materi (debounce) dan filter status (Selesai, Dalam Proses, Belum Mulai, Terkunci). |
| **Akordeon Bab Perkuliahan** | `Setengah Jalan` | `Meeting`, `Material` | Accordion buka-tutup bab (state tersimpan), circle badge nomor bab, counter jumlah materi selesai/total. |
| **Baris Item Materi Perkuliahan** | `Setengah Jalan` | `Material`, `MaterialProgress` | Durasi, tanggal dibuat, badge status (Selesai / Belum Selesai / Terkunci). Klik chevron membuka viewer materi. |
| **Viewer Materi Perkuliahan** | `Belum Ada` | Halaman / Modal viewer | Modul PDF inline dengan opsi unduh, Video player dengan pelacakan progres (otomatis selesai saat $\ge 90\%$), link referensi eksternal, dan tombol "Tandai Selesai". |
| **Ringkasan Materi (4 Tile Kotak)** | `Dummy` | `MaterialService` stats | Total Materi (14), Selesai (5), Dalam Proses (6), Terkunci (3). |
| **Feed Aktivitas Pembelajaran Terbaru** | `Dummy` | `ActivityLog` / `MaterialProgress` | Linimasa 4 riwayat pembelajaran terakhir dengan timestamp relatif. Tombol "Lihat Semua". |
| **Kartu Arunika AI Copilot** | `Setengah Jalan` | `AiCopilotController` | Tombol "Coba Sekarang" membuka drawer AI Copilot dengan konteks materi yang sedang dibuka. |

---

## 8. Halaman Diskusi (`resources/views/pages/app/discussions.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Tombol "+ Buat Diskusi Baru"** | `Setengah Jalan` (Membuka modal) | `DiscussionService::createThread()` | Form pembuatan thread: judul, kategori, pemilihan mata kuliah, textarea isi, lampiran file, dan publish. |
| **Pills Kategori & Tab Filter** | `Dummy` (Alpine) | Query category filter | Tab: Semua, Forum Kelas, Tanya Jawab, Pengumuman, Topik Populer. |
| **Pencarian Topik Diskusi & Filter** | `Dummy` | Search query & filter popover | Pencarian judul/isi diskusi dengan debounce dan filter per mata kuliah / status terjawab. |
| **Kotak Tulis Cepat Diskusi** | `Setengah Jalan` (Buka modal) | Quick composer form | Klik area membuka form cepat, validasi input, pencegahan XSS, dan rate limit pengiriman. |
| **Daftar 7 Thread Diskusi** | `Setengah Jalan` (Data di Blade) | `DiscussionThread`, `DiscussionReply` | Author foto/inisial, pin badge, badge kategori, kutipan isi, jumlah komentar, view counter unik, waktu relatif. |
| **Halaman Detail Thread Diskusi** | `Belum Ada` | `Route::get('/app/discussions/{thread}')`| Thread lengkap, daftar balasan bertingkat, form komentar cepat, tombol upvote, tandai jawaban terbaik pada Tanya Jawab. |
| **4 Tile Statistik Diskusi** | `Dummy` | `DiscussionService::stats()` | Total Diskusi (128), Total Komentar (526), Total Dilihat (8.4k), Pengguna Aktif (247). Scope kelas yang diikuti. |
| **Widget Daftar Kategori Berpenghitung** | `Dummy` | `DiscussionCategory` count | Menampilkan 6 kategori beserta jumlah thread aktif; klik kategori memfilter daftar thread. |
| **Widget 5 Diskusi Terpopuler** | `Dummy` | Query popularitas algoritma | Skor popularitas $= (\text{Komentar} \times 3) + (\text{Views} \times 0.1)$ dalam jendela 30 hari terakhir. |

---

## 9. Halaman Pengaturan (`resources/views/pages/app/settings.blade.php`)

| Komponen / Elemen UI | Status Saat Ini | Sumber Data (Model / Service) | Aksi & Interaksi yang Diperlukan |
| :--- | :---: | :--- | :--- |
| **Sub-Navigasi Samping Kiri (5 Grup)** | `Setengah Jalan` (Alpine scroll-spy) | `PreferenceService` | Navigasi smooth-scroll ke kartu di halaman ini dan membuka sub-halaman untuk item tanpa kartu langsung. |
| **Kartu Informasi Profil** | `Setengah Jalan` | `Auth::user()` | Tampilan nama, email, NIM, prodi, fakultas. Ikon pensil untuk edit nama/email. Ajukan perubahan data untuk NIM/Prodi. |
| **Tombol "Ubah Foto Profil"** | `Dummy` (Alpine modal) | Upload service | Unggah gambar (JPG/PNG/WEBP, max 2MB), crop/resize 512px, update avatar otomatis ke sidebar, topbar, dan diskusi. |
| **Kartu Status Akun** | `Setengah Jalan` | `User`, `Campus`, `Program` | Banner akun aktif, rincian role mahasiswa, kampus UNI, prodi S1 TI, terdaftar sejak 12 Agustus 2025. |
| **Keamanan: Ubah Password** | `Setengah Jalan` (Modal form) | `Auth::user()`, `Hash` | Validasi password saat ini, konfirmasi password baru, opsi "Keluarkan sesi lain". |
| **Keamanan: 2FA Authentication** | `Dummy` (Switch visual) | `TwoFactorService` (TOTP) | Toggle menginisiasi setup QR code TOTP, verifikasi 6-digit code, recovery codes, label switch berubah Aktif/Nonaktif. |
| **Keamanan: Login dengan Google** | `Dummy` (Tombol visual) | Laravel Socialite | Tombol hubungkan/putuskan akun Google OAuth, pengaman pencegahan pemutusan jika user belum memiliki password. |
| **Preferensi Notifikasi (5 Sakelar)** | `Setengah Jalan` (Switch visual) | `user_preferences` / JSON settings | Switch Pengumuman, Deadline, Pesan, Diskusi, Sistem disimpan otomatis via AJAX/API + konfirmasi toast. |
| **Tampilan: Mode Terang / Gelap / Auto**| `Setengah Jalan` (Alpine visual) | Theme script + localStorage / User pref | Mengubah kelas tema secara instan tanpa kedip (inline script in `<head>`), persist ke preferensi pengguna. |
| **Tampilan: Ukuran Teks (Radio 3)** | `Dummy` (Alpine visual) | Root font size handler | Memilih Kecil (14px), Normal (16px), Besar (18px) langsung pada `<html>` font-size. |
| **Bahasa & Lokalisasi (4 Dropdown)** | `Dummy` (Select visual) | `PreferenceService`, Locale middleware | Pilihan Bahasa (ID/EN), Zona Waktu (WIB/WITA/WIT), Format Tanggal, Format Waktu disimpan dan berlaku di seluruh aplikasi. |
| **Banner Bantuan Bawah "Hubungi Support"** | `Dummy` (Tombol visual) | Support ticket service | Membuka modal form tiket support (subjek, pesan kendala, lampiran) dan mengirimkan notifikasi ke tim helpdesk. |

---

## 10. Audit String Hardcode & Persiapan i18n (`lang/id` & `lang/en`)

Seluruh string tampilan yang saat ini tertulis hardcode di Blade akan dipindahkan ke file bahasa:
- `lang/id/app.php` & `lang/en/app.php`: Label navigasi, breadcrumb, tombol aksi, status, pesan feedback.
- `lang/id/academic.php` & `lang/en/academic.php`: Istilah perkuliahan, SKS, IPS, IPK, CPMK, CPL, skala nilai.
- `lang/id/gamification.php` & `lang/en/gamification.php`: Istilah baku **XP** (*Experience Points*), level, streak, lencana.
- `lang/id/settings.php` & `lang/en/settings.php`: Kategori preferensi, keamanan, lokalisasi, bantuan.
Semua key bahasa di Blade akan menggunakan `{{ __('...') }}` dengan default string bahasa Indonesia yang **100% identik** dengan tampilan yang ada saat ini sehingga **Nol perubahan visual**.
