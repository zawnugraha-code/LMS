# Progress Pembangunan Panel Administrator LMS Arunika
**Universitas Nusantara Indonesia**

Dokumen ini memantau rekam jejak, status eksekusi per fase, verifikasi pengujian, dan keputusan arsitektural selama pembangunan antarmuka dan fondasi sistem **Panel Administrator (Batch 1)**.

---

## Ringkasan Eksekutif Status Proyek

| Fase | Deskripsi | Status | Tanggal Penyelesaian | Catatan Verifikasi |
| :--- | :--- | :--- | :--- | :--- |
| **Fase A1** | Perencanaan & Audit Komprehensif | **SELESAI** | 18 Oktober 2026 | Dokumen `docs/admin/PLAN.md` disetujui. |
| **Fase A2** | Fondasi Data, Keamanan, Layanan Inti, & CLI | **SELESAI** | 18 Oktober 2026 | Backup dibuat, 5 migrasi sukses, middleware & policies aktif, 10/10 test fondasi pass, 80/80 test dosen pass. |
| **Fase A3** | Layout Admin + Beranda + Semester + Mata Kuliah | **SELESAI** | 18 Oktober 2026 | Layout Navy Academic, 3 komponen blade, Beranda, Semester, Mata Kuliah, 26/26 test admin pass, 80/80 test dosen pass. |
| **Fase A4** | Modul Kelas Perkuliahan & Penugasan Dosen | **SELESAI** | 18 Oktober 2026 | Layanan ClassService, Live Schedule Checker, 4 views kelas, 13/13 test kelas pass, total 39/39 admin test pass. |
| **Fase A5** | Modul Pendaftaran Mahasiswa (Enrollment) & Topbar Search | **SELESAI** | 18 Oktober 2026 | Layanan EnrollmentAdminService, Ekspor/Impor CSV, Topbar Search, 11/11 test pendaftaran pass, 4/4 test search pass, total 54/54 admin test pass. |
| **Fase A6** | Pengujian Menyeluruh, Audit Keamanan, & Finalisasi | **SELESAI** | 18 Oktober 2026 | Suite End-to-End akademik, audit keamanan multi-vektor (RBAC, SQLi, XSS, CSV Injection, Session Revocation), 61/61 admin test pass, 82/82 dosen test pass (zero regression terbukti). |

---

## Catatan Rinci Fase A2: Fondasi Data, Keamanan, Layanan Inti, & CLI

### 1. Keamanan & Backup Database
* **Database Target**: MySQL `arunika` (Localhost / XAMPP).
* **Backup Pra-Migrasi**: File dump `backup_sebelum_admin.sql` (597 KB) berhasil digenerasi via `mysqldump`.
* **Prinsip Keamanan**: Tidak ada perintah destruktif (`migrate:fresh`, `db:wipe`) yang dijalankan. Hanya `php artisan migrate` non-destruktif.

### 2. Konfigurasi Global & Enums
* **`config/arunika.php`**:
  - Bobot penilaian default: Tugas 30%, Kuis 20%, UTS 25%, UAS 25% (Total 100%).
  - Pertemuan perkuliahan default: 16 pekan per semester.
  - Batasan import CSV: Maksimal 2048 KB (2 MB) dan 1.000 baris per unggahan berkas.
* **`app/Enums/UserRole.php`**:
  - Menambahkan case `Admin = 'admin'`.
  - Label: `Administrator`.
  - Badge Color: `indigo`.
  - Default Route: `admin.dashboard` (`/admin/beranda`).

### 3. Migrasi Skema Database MySQL `arunika`
Telah berhasil menambahkan 5 file migrasi baru dan dieksekusi tanpa galat:
1. `2026_10_18_000001_add_admin_columns_to_users_table.php`:
   - Menambahkan kolom `is_active` (boolean, default true), `must_change_password` (boolean, default false), `last_login_at` (timestamp nullable), `password_changed_at` (timestamp nullable).
2. `2026_10_18_000002_extend_terms_table_for_admin.php`:
   - Menambahkan kolom `status` (string 20, default `'belum_aktif'`: `aktif`, `belum_aktif`, `selesai`), `closed_at` (timestamp nullable).
   - Menambahkan indeks unik pada pasangan `['academic_year', 'semester_type']`.
3. `2026_10_18_000003_extend_classes_table_for_admin.php`:
   - Menambahkan constraint indeks unik pada tripel `['course_id', 'term_id', 'section']`.
4. `2026_10_18_000004_extend_enrollments_table_for_admin.php`:
   - Menambahkan kolom `dikeluarkan_at` (timestamp nullable) dan `dikeluarkan_oleh` (foreign key nullable ke `users`).
5. `2026_10_18_000005_create_activity_logs_table.php`:
   - Membuat tabel baru `activity_logs`: `id`, `user_id`, `aksi`, `kategori`, `subjek_type`, `subjek_id`, `deskripsi`, `properti` (json), `ip_address`, `created_at`.
   - Indeks pada: `created_at`, `user_id`, `kategori`. Bersifat *append-only*.

### 4. Peningkatan Model Eloquent
* **`app/Models/User.php`**:
  - Menambahkan casts dan fillable baru (`is_active`, `must_change_password`, `last_login_at`, `password_changed_at`).
  - Menambahkan method pembantu peran: `isAdmin(): bool`.
  - Relasi `activityLogs(): HasMany`.
* **`app/Models/Term.php`**:
  - Fillable dan casts `status` serta `closed_at`.
  - Method pembantu: `isCurrentActive(): bool`, `isFinished(): bool`, `isDraft(): bool`.
  - Scopes: `scopeAktif()`, `scopeSelesai()`, `scopeBelumAktif()`.
* **`app/Models/AcademicClass.php`**:
  - Relasi: `activeEnrollments(): HasMany` (filter `['aktif', 'enrolled']`), `activeStudents(): BelongsToMany`.
  - Method pembantu kapasitas: `isFull(): bool`, `remainingCapacity(): int`.
  - Method read-only: `isFinished(): bool` (mengecek status kelas `'selesai'` atau status semester terkait `'selesai'`).
* **`app/Models/Enrollment.php`**:
  - Fillable dan casts `dikeluarkan_at` dan `dikeluarkan_oleh`.
  - Scopes kompatibilitas: `scopeAktif()` (menerima status `'aktif'` dan legacy `'enrolled'`), `scopeDikeluarkan()`.
  - Relasi `dikeluarkanOleh(): BelongsTo`.
* **`app/Models/ActivityLog.php`**:
  - Model audit append-only dengan cast properti JSON, relasi `user()`, morphTo `subjek()`, serta scope `terbaru()` dan `kategori()`.

### 5. Layanan Inti (Services) & Keamanan
* **`app/Services/Admin/ActivityLogger.php`**:
  - Layanan pencatatan aktivitas terpusat.
  - Sanitasi otomatis: memproteksi dan menyamarkan (*redact*) nilai kunci sensitif seperti `password`, `token`, dan `secret` dari properti JSON log.
* **`app/Services/Admin/CsvImporterService.php`**:
  - Parser CSV native berbasis stream PHP (`fgetcsv`) dengan proteksi terhadap *CSV Formula Injection* (karakter `=`, `+`, `-`, `@`, `\t`, `\r` diawali apostrof tunggal aman).
  - Penanganan UTF-8 BOM, validasi header kolom wajib, batas baris, dan penangkapan galat per nomor baris.
* **`app/Http/Middleware/EnsureUserIsAdmin.php`**:
  - Memverifikasi otentikasi pengguna, memastikan peran `admin`, `super_admin`, atau `campus_admin`.
  - Menolak akses pengguna guest (dialihkan ke halaman login) dan pengguna non-admin (HTTP 403 Forbidden).
  - Otomatis melakukan logout dan membatalkan sesi jika akun admin berstatus `is_active = false`.
  - Didaftarkan sebagai alias `role.admin` di `bootstrap/app.php`.
* **`app/Http/Controllers/DemoAuthController.php`**:
  - Mendukung login role switcher instan untuk admin (`admin@arunika.test`) dan mengalihkan langsung ke rute `admin.dashboard`.
  - Mencatat timestamp `last_login_at` saat pergantian peran.
* **Command CLI `app/Console/Commands/CreateAdminCommand.php`**:
  - Perintah `php artisan arunika:buat-admin` untuk membuat akun administrator baru secara interaktif atau via opsi flags (`--name`, `--email`, `--password`).
  - Input password disembunyikan dan divalidasi minimal 8 karakter.
  - Aksi pembuatan admin dicatat ke `activity_logs`.

### 6. Kebijakan Otorisasi (Policies) & Isolasi Dosen
* **`app/Policies/AssignmentPolicy.php`** & **`app/Policies/SubmissionPolicy.php`**:
  - Mengizinkan peran `UserRole::Admin` untuk seluruh hak kelola.
  - Otorisasi dosen diselaraskan secara tegas mengikuti `academicClass.lecturer_id`. Jika Admin mengganti dosen pengampu pada suatu kelas, hak kelola tugas dan penilaian berpindah 100% ke dosen baru.
  - Hak update, delete, dan grading ditutup secara read-only jika kelas berstatus selesai (`isFinished()`).
* **`app/Models/Assignment.php`**:
  - Scope `scopeForDosen()` diselaraskan mengikuti `academicClass.lecturer_id`.

### 7. Pemisahan Seeder (Idempotent)
* **`database/seeders/ProductionSeeder.php`**: Menyiapkan master data perguruan tinggi dasar (Kampus, Fakultas, Program Studi, Kurikulum, Semester Aktif) tanpa akun demo dummy.
* **`database/seeders/DemoSeeder.php`**:
  - Dilengkapi guard penolak otomatis jika dijalankan di environment `production`.
  - Menyiapkan akun Administrator: **Rina Kusumawati** (`admin@arunika.test` / `password`).
  - Menyiapkan semester aktif Ganjil 2026/2027, semester mendatang Genap 2026/2027 (belum aktif), dan semester lampau Genap 2025/2026 (selesai).
  - Menyiapkan Dosen mockup: **Siti Aminah** & **Dr. Hendra Wijaya**.
  - Menyiapkan mata kuliah **IF2105 (Pemrograman Web Lanjut, 3 SKS)**.
  - Menyiapkan kelas **IF2105 Seksi A** dengan kapasitas 40 kursi, 16 pertemuan mingguan, komponen penilaian default, dan 32 mahasiswa terdaftar (tepat 80% keterisian, 8 sisa).
  - Menyiapkan 5 mahasiswa belum terdaftar di kelas manapun untuk widget kartu pendaftaran.
  - Mengisi 6 sampel rekam jejak awal di `activity_logs`.
* **`database/seeders/DatabaseSeeder.php`**: Mengarahkan pemanggilan ke `ProductionSeeder` pada mode production, atau rangkaian seeder demo lengkap pada mode lokal/testing.

### 8. Rute Placeholder Menu
Rute panel admin telah didaftarkan di `routes/web.php` di bawah prefix `/admin` dengan middleware `['web', 'auth', 'role.admin']`:
- `admin.dashboard` (`/admin/beranda`)
- `admin.semester.index` (`/admin/semester`)
- `admin.mata-kuliah.index` (`/admin/mata-kuliah`)
- `admin.kelas.index` (`/admin/kelas`)
- `admin.pendaftaran-mahasiswa.index` (`/admin/pendaftaran-mahasiswa`)
- `admin.cari` (`/admin/cari`)
- `admin.pengguna.dosen.index` (`/admin/pengguna/dosen`) [Batch 2]
- `admin.pengguna.mahasiswa.index` (`/admin/pengguna/mahasiswa`) [Batch 2]
- `admin.log-aktivitas.index` (`/admin/log-aktivitas`) [Batch 2]
- `admin.pengaturan.edit` (`/admin/pengaturan`) [Batch 2]
- Controller placeholder: `app/Http/Controllers/Admin/AdminPlaceholderController.php`.
- View placeholder: `resources/views/pages/admin/placeholder.blade.php`.

### 9. Hasil Verifikasi Pengujian (Test Suite)
1. **`tests/Feature/AdminFoundationTest.php`**: **PASS 10/10 (35 assertions)**.
   - Menguji pengalihan tamu, pemblokiran peran non-admin (403), pemblokiran admin nonaktif, akses admin aktif, sanitasi log aktivitas, proteksi formula injection CSV, command CLI `arunika:buat-admin`, helper model semester & kapasitas kelas, serta pengalihan hak akses saat dosen kelas diganti.
2. **Pengujian Modul Dosen (`php artisan test --filter=Lecturer`)**: **PASS 80/80 (451 assertions)**.
   - Menjamin bahwa canonical data Dosen Dr. Budi Rahardjo (90 mahasiswa terdaftar, 14 pengumpulan perlu dinilai, 2 notifikasi) **100% utuh dan tidak mengalami regresi**.
3. **Pengujian Aplikasi Utama (`php artisan test --filter=ArunikaAppTest`)**: **PASS 8/8 (41 assertions)**.

---

## Catatan Rinci Fase A3: Layout Admin, Beranda, Semester, & Mata Kuliah

### 1. Desain Sistem & Layout Admin Navy Academic
* **Warna Utama**: Deep Navy Academic (`#1A2E5E`), Navy Hover (`#152449`), Slate Blue Accent (`#2A437E`).
* **File Layout**: `resources/views/layouts/admin.blade.php`.
* **Sidebar (240px Fixed/Collapsible)**:
  - Header: Logo Arunika + Badge Administrator Indigo + BAAK Universitas Nusantara Indonesia.
  - Menu Utama: Beranda, Semester, Mata Kuliah, Kelas, Pendaftaran Mahasiswa.
  - Menu Batch 2 (dengan badge abu-abu B2): Dosen, Mahasiswa, Log Aktivitas, Pengaturan.
  - Active State: Background Navy (`#1A2E5E`), teks putih, rounded pill dengan bayangan lembut.
  - Inactive State: Hover abu-abu slate muda (`hover:bg-slate-100 hover:text-slate-900`).
  - Footer Sidebar: Avatar profil pengguna login (Rina Kusumawati), email, dan tombol keluar.
* **Topbar**:
  - Global Search Input dengan shortcut `Ctrl + K`.
  - Badge Semester Aktif (Ganjil 2026/2027) dengan dot pulsasi hijau.
  - Switcher Peran Cepat (Admin, Dosen, Mahasiswa) untuk kemudahan navigasi demonstrasi.
  - Flash Message Banner otomatis untuk notifikasi sukses, peringatan, dan galat.

### 2. Komponen Blade Khusus Admin
1. **`resources/views/components/admin/stat-card.blade.php`**:
   - Kartu metrik dengan variasi warna (indigo, emerald, amber, rose, slate), ikon Lucide, subtitle dinamis, dan tren perubahan.
2. **`resources/views/components/admin/status-badge.blade.php`**:
   - Badge penanda status konsisten untuk:
     - Semester: Aktif (hijau), Belum Aktif (amber/kuning), Selesai (slate).
     - Kelas: Aktif, Selesai, Penuh (merah/rose), Hampir Penuh (amber).
3. **`resources/views/components/admin/donut-chart.blade.php`**:
   - Grafik SVG Donut Chart murni tanpa dependensi JavaScript eksternal untuk menampilkan persentase keterisian kapasitas kelas perkuliahan.

### 3. Modul Beranda (Dashboard)
* **Layanan Inti**: `app/Services/Admin/AdminDashboardService.php`.
* **Controller**: `app/Http/Controllers/Admin/AdminDashboardController.php`.
* **Tampilan**: `resources/views/pages/admin/dashboard.blade.php` (Presisi sesuai **Screenshot 1**):
  - 4 Kartu Metrik Utama: Total Mahasiswa Aktif, Total Dosen Aktif, Kelas Semester Aktif, Katalog Mata Kuliah.
  - Kolom Kiri: Widget "Perlu Perhatian Hari Ini" (4 item aksi cepat: Kelas Tanpa Dosen, Kelas Penuh, Mahasiswa Belum Terdaftar, Permintaan BAAK) & Widget Keterisian Kapasitas Ruang (Donut Chart + Rincian Terisi vs Sisa Kursi).
  - Kolom Kanan: Tabel Kelas Aktif Semester Berjalan dengan bar visual keterisian kuota & Daftar 5 Aktivitas Terbaru Sistem dari `activity_logs`.

### 4. Modul Semester & Kalender Akademik
* **Layanan Inti**: `app/Services/Admin/SemesterService.php`.
* **Controller**: `app/Http/Controllers/Admin/AdminSemesterController.php`.
* **Tampilan**: `resources/views/pages/admin/semester/index.blade.php` (Presisi sesuai **Screenshot 2**):
  - Hero Card: Semester Aktif Berjalan (Ganjil 2026/2027) lengkap dengan rentang tanggal dan status.
  - 4 Kartu Ringkasan (Grid 2x2): Total Semester, Semester Aktif, Semester Mendatang, Semester Selesai.
  - Tabel Riwayat & Daftar Semester: Kolom Nama, Tahun Ajaran, Tipe, Periode Tanggal, Jumlah Kelas, Status, dan Tombol Aksi.
  - Modal Interaktif (Alpine.js):
    - Modal Tambah Semester Baru (validasi regex YYYY/YYYY, urutan tanggal mulai < selesai, pilihan jadikan aktif).
    - Modal Edit Semester.
    - Modal Konfirmasi "Aktifkan Semester" (otomatis menonaktifkan semester lain).
    - Modal Konfirmasi "Tutup Semester" (mengunci semester dan seluruh kelas di bawahnya menjadi 'selesai').
    - Guard Proteksi Hapus: Semester dengan kelas tidak dapat dihapus.

### 5. Modul Mata Kuliah
* **Layanan Inti**: `app/Services/Admin/CourseService.php`.
* **Controller**: `app/Http/Controllers/Admin/AdminCourseController.php`.
* **Tampilan**: `resources/views/pages/admin/mata-kuliah/index.blade.php` (Presisi sesuai **Screenshot 3**):
  - Filter Program Studi dan Search Bar pencarian nama/kode mata kuliah.
  - Tabel Katalog Mata Kuliah: Kode MK (monospaced), Nama MK, Bobot SKS, Semester Kurikulum, Kelas Aktif Dibuka, dan Aksi Kelola.
  - Kolom Kanan: Card Ringkasan Kurikulum (Total MK, Total Beban SKS, Kelas Dibuka) & Card Visualisasi Distribusi per Program Studi.
  - Modal Tambah dan Edit Mata Kuliah dengan validasi kode unik dan normalisasi otomatis huruf kapital (uppercase).
  - Guard Proteksi Hapus: Mata kuliah yang memiliki riwayat kelas diblokir dari penghapusan demi menjaga integritas data akademik.

### 6. Hasil Verifikasi Pengujian (Test Suite)
* **`tests/Feature/AdminDashboardTest.php`**: **PASS 2/2 (13 assertions)**.
* **`tests/Feature/AdminSemesterTest.php`**: **PASS 6/6 (28 assertions)**.
* **`tests/Feature/AdminCourseTest.php`**: **PASS 8/8 (31 assertions)**.
* **`tests/Feature/AdminFoundationTest.php`**: **PASS 10/10 (35 assertions)**.
* **Total Admin Tests**: **PASS 26/26 (107 assertions)**.
* **Verifikasi Regresi Dosen (`tests/Feature/Lecturer*`)**: **PASS 80/80 (451 assertions)** — **ZERO REGRESSION TERBUKTI**.

---

## Catatan Rinci Fase A4: Modul Kelas Perkuliahan & Penugasan Dosen

### 1. Layanan Domain (`app/Services/Admin/ClassService.php`)
* **Pengambilan Data & Filter Komprehensif**:
  - `getClassesList`: Filter status (`semua`, `aktif`, `selesai`), semester (`term_id`), mata kuliah (`course_id`), dosen pengampu (`lecturer_id`), dan kata kunci pencarian seksi, ruang, nama MK, serta nama dosen.
  - `getClassSummary`: Komputasi total kelas, kelas aktif, kelas selesai, total kuota, total mahasiswa terdaftar, persentase keterisian, dan sisa kursi.
* **Pencegahan Bentrok Otomatis (Live Schedule Conflict Checker)**:
  - `checkScheduleConflict`: Memvalidasi bentrok pemakaian ruangan (ruangan sama pada hari yang sama dengan rentang waktu beririsan) dan bentrok dosen (dosen yang sama mengajar kelas lain di rentang waktu beririsan).
* **Siklus Hidup Pembukaan Kelas (`createClass`)**:
  - Validasi keunikan seksi dalam satu semester dan mata kuliah (`unique(['course_id', 'term_id', 'section'])`).
  - Generasi otomatis **16 Pertemuan Mingguan** (`meetings`) terhitung mulai dari tanggal awal semester yang disinkronkan ke hari perkuliahan, termasuk penanda UTS (pekan 8) dan UAS (pekan 16).
  - Inisialisasi otomatis **Komponen Penilaian OBE** (`assessment_components`): Tugas 30%, Kuis 20%, UTS 25%, UAS 25% (Total 100%).
* **Pengalihan Dosen Pengampu (`updateClass`)**:
  - Mendeteksi pergantian dosen pengampu dan menyelaraskan seluruh kolom `assignments.dosen_id` ke dosen baru sehingga hak kelola materi dan buku nilai berpindah 100% tanpa inkonsistensi.
* **Penyelesaian & Kunci Read-Only (`finishClass`)**:
  - Mengubah status kelas menjadi `'selesai'`. Policy secara otomatis mengunci hak update, delete, dan grading.
* **Integritas Hapus Kelas (`deleteClass`)**:
  - Kelas yang telah memiliki mahasiswa terdaftar dilarang keras untuk dihapus guna melindungi rekam akademik.
* **Penyesuaian Pertemuan & Hari Libur (`updateMeeting`)**:
  - Memberi hak bagi Admin untuk mengubah tanggal, judul, dan topik pertemuan jika bertepatan dengan hari libur nasional tanpa merusak presensi yang sudah tercatat.

### 2. Controller & API Bentrok (`app/Http/Controllers/Admin/AdminClassController.php`)
* Method terdaftar: `index`, `create`, `store`, `show`, `edit`, `update`, `finish`, `destroy`, `checkConflict`, `updateMeeting`.
* Endpoint AJAX `/admin/kelas/cek-bentrok` merespons JSON status ketersediaan secara real-time ke antarmuka formulir pembukaan dan pengeditan kelas.

### 3. Antarmuka Blade
* **`resources/views/pages/admin/kelas/index.blade.php`** (Presisi sesuai **Screenshot 4**):
  - Filter bar status tab, dropdown semester (default semester aktif), dropdown mata kuliah, dropdown dosen, dan search input.
  - Tabel kelas perkuliahan: Kode MK (monospaced), Nama MK, Seksi kelas, Dosen Pengampu, Jadwal & Ruang, Bar visual kapasitas kuota (`x/y`), Status badge, dan dropdown kelola.
  - Panel kanan: Kartu Ringkasan Kelas & Donut Chart Kapasitas Kolektif.
* **`resources/views/pages/admin/kelas/create.blade.php`**:
  - Layout 2 kolom: Form lengkap pembukaan kelas + Panel interaktif *Live Schedule Checker* (Alpine.js).
* **`resources/views/pages/admin/kelas/edit.blade.php`**:
  - Form pengubahan seksi, dosen, ruang, jadwal, serta banner peringatan pengalihan dosen pengampu.
* **`resources/views/pages/admin/kelas/show.blade.php`**:
  - Hero card banner kelas lengkap dengan rincian jadwal, dosen, ruang, progress keterisian kuota, dan tombol aksi.
  - 4 Tab Navigasi:
    1. **Peserta Kelas**: Tabel mahasiswa terdaftar, NIM, prodi, tanggal bergabung, dan nilai akhir.
    2. **Jadwal & Pertemuan**: Linimasa 16 pertemuan mingguan + modal penyesuaian tanggal/topik libur nasional.
    3. **Komponen Penilaian (OBE)**: Kartu persentase bobot penilaian (Tugas, Kuis, UTS, UAS = 100%).
    4. **Ringkasan Aktivitas**: Statistik materi terunggah, tugas/penilaian aktif, sesi pertemuan, dan forum diskusi.

### 4. Hasil Verifikasi Pengujian (Test Suite)
* **`tests/Feature/AdminClassTest.php`**: **PASS 13/13 (56 assertions)**:
  - `test_admin_can_view_classes_index`
  - `test_admin_can_filter_classes_by_status_and_search`
  - `test_admin_can_view_create_class_page`
  - `test_admin_can_create_new_class_with_meetings_and_components`
  - `test_conflict_checker_detects_room_conflict`
  - `test_conflict_checker_detects_lecturer_conflict`
  - `test_admin_can_view_class_detail_show_page`
  - `test_admin_can_reassign_lecturer_and_transfer_assignments`
  - `test_admin_can_finish_class`
  - `test_admin_can_delete_empty_class`
  - `test_cannot_delete_class_with_enrolled_students`
  - `test_admin_can_update_meeting_schedule_and_topic`
  - `test_non_admin_cannot_access_or_modify_classes`
* **Total Admin Tests**: **PASS 39/39 (163 assertions)**.
* **Verifikasi Regresi Dosen (`tests/Feature/Lecturer*`) & App**: **PASS** — **ZERO REGRESSION TERBUKTI**.

---

## Catatan Rinci Fase A5: Modul Pendaftaran Mahasiswa & Topbar Search

### 1. Layanan Domain (`app/Services/Admin/EnrollmentAdminService.php`)
* **Pengambilan Data Pendaftaran & Keterisian Kuota**:
  - `getEnrollmentData`: Mengambil data kelas terpilih, daftar kelas switcher, menghitung kuota terdaftar (`countTerdaftar`), dikeluarkan (`countDikeluarkan`), dan mahasiswa aktif yang belum terdaftar di kelas (`countBelumTerdaftar`).
  - Menampilkan data tabel sesuai tab aktif:
    - Tab `terdaftar`: mahasiswa terdaftar aktif (`status IN ('aktif', 'enrolled')`).
    - Tab `dikeluarkan`: mahasiswa yang dikeluarkan lengkap dengan timestamp dan ID admin pengeluar.
    - Tab `belum_terdaftar`: mahasiswa aktif program studi yang belum mengambil kelas ini.
* **Pendaftaran Mahasiswa (`enrollStudent`)**:
  - Pengecekan guard kapasitas penuh (`isFull()`). Menolak pendaftaran jika kelas telah mencapai kuota maksimal.
  - Pengecekan status mahasiswa: mencegah duplikasi pendaftaran jika sudah aktif.
  - Reaktivasi otomatis jika sebelumnya berstatus dikeluarkan (mengembalikan `status = 'aktif'` dan mengosongkan kolom `dikeluarkan_at`).
  - Pencatatan log aktivitas `daftarkan_mahasiswa`.
* **Pendaftaran Massal (`bulkEnrollStudents`)**:
  - Mendaftarkan sejumlah mahasiswa sekaligus dari seleksi checkbox.
* **Pengeluaran Mahasiswa Soft-Remove (`removeStudent`)**:
  - Menetapkan `status = 'dikeluarkan'`, `dikeluarkan_at = now()`, dan `dikeluarkan_oleh = $adminId`.
  - **Integritas Rekam Nilai**: Seluruh nilai tugas, rekam presensi pertemuan, dan pengumpulan tugas tetap tersimpan 100% utuh tanpa penghapusan relasional.
  - Pencatatan log aktivitas `keluarkan_mahasiswa`.
* **Ekspor CSV Terstruktur (`exportCsv`)**:
  - Generasi berkas CSV dengan UTF-8 BOM dan header standar: `No`, `NIM`, `Nama Mahasiswa`, `Email`, `Program Studi`, `Status Pendaftaran`, `Nilai Akhir`, `Nilai Huruf`, `Tanggal Bergabung`.
* **Impor Berkas CSV Mahasiswa (`importCsv`)**:
  - Menggunakan `CsvImporterService` terproteksi CSV formula injection.
  - Memetakan mahasiswa berdasarkan NIM atau Email, memverifikasi ketersediaan kuota kelas, mendaftarkan secara otomatis, dan menyusun laporan galat per baris.
* **Unduh Template CSV (`getTemplateCsv`)**:
  - Menghasilkan file template CSV standar `template_import_mahasiswa_arunika.csv`.

### 2. Layanan & Antarmuka Topbar Search (`app/Http/Controllers/Admin/AdminSearchController.php`)
* Pencarian global lintas entitas sistem melalui shortcut keyboard `Ctrl + K`.
* Mencari dan mengelompokkan hasil secara visual:
  - Kelas Perkuliahan (kode MK, seksi, nama kelas, ruang, dosen).
  - Mata Kuliah (kode MK, nama mata kuliah, prodi).
  - Semester Akademik (nama semester, tahun ajaran).
  - Dosen Pengajar (nama, NIP, email).
  - Mahasiswa (nama, NIM, email).
* Tampilan: [`resources/views/pages/admin/cari.blade.php`](file:///c:/Users/rizza/OneDrive/Dokumen/LMS/resources/views/pages/admin/cari.blade.php).

### 3. Antarmuka Blade Pendaftaran Mahasiswa
* **`resources/views/pages/admin/pendaftaran/index.blade.php`** (Presisi sesuai **Screenshot 5**):
  - Kartu Kelas Terpilih: Menampilkan kode MK, nama kelas, prodi, dosen pengampu, jadwal, ruang, jumlah mahasiswa terdaftar vs kapasitas, dan tombol modal "Ganti Kelas >".
  - Filter Tab: `Terdaftar (N)`, `Dikeluarkan (N)`, `Belum Terdaftar (N)` + input pencarian.
  - Banner Informasi: Menegaskan bahwa pengeluaran mahasiswa tidak menghapus nilai dan presensi.
  - Checkbox Seleksi Massal & Sticky Bulk Action Bar ("+ Daftarkan Sekaligus ke Kelas").
  - Tabel Peserta: No, Avatar Mahasiswa, Nama, Email, NIM, Prodi, Tanggal, Status, dan Tombol Aksi ("Keluarkan >", "+ Daftarkan").
  - Panel Kanan (4 Cols):
    - Donut Chart Kapasitas Kelas (persentase terisi vs sisa kursi).
    - Widget "Mahasiswa Belum Terdaftar" dengan tombol aksi cepat "+ Daftarkan".
    - Widget "Impor Mahasiswa (CSV)" dengan tombol unduh template dan form unggah berkas.
  - Modal Interaktif (Alpine.js):
    - Modal Ganti Kelas Terpilih.
    - Modal Tambah Mahasiswa ke Kelas.
    - Modal Konfirmasi Pengeluaran Mahasiswa.

### 4. Hasil Verifikasi Pengujian (Test Suite)
* **`tests/Feature/AdminEnrollmentTest.php`**: **PASS 11/11 (42 assertions)**:
  - `test_admin_can_view_enrollment_index_with_class`
  - `test_admin_can_enroll_student_to_class`
  - `test_cannot_enroll_student_when_class_capacity_full`
  - `test_cannot_enroll_already_active_student`
  - `test_admin_can_remove_student_without_deleting_history`
  - `test_admin_can_reactivate_previously_removed_student`
  - `test_admin_can_bulk_enroll_students`
  - `test_admin_can_export_csv`
  - `test_admin_can_download_template_csv`
  - `test_admin_can_import_csv`
  - `test_non_admin_cannot_access_or_manage_enrollment`
* **`tests/Feature/AdminSearchTest.php`**: **PASS 4/4 (10 assertions)**:
  - `test_admin_can_perform_global_search_with_results`
  - `test_admin_global_search_finds_users`
  - `test_admin_global_search_short_query_returns_prompt`
  - `test_non_admin_cannot_access_global_search`
* **Total Admin Tests**: **PASS 54/54 (215 assertions)**.
* **Verifikasi Regresi Dosen (`tests/Feature/Lecturer*`) & App**: **PASS** — **ZERO REGRESSION TERBUKTI**.

---

## Catatan Rinci Fase A6: Pengujian Menyeluruh (End-to-End Suite), Audit Keamanan, & Finalisasi Sistem

### 1. Cakupan Pengujian End-to-End (`tests/Feature/AdminSecurityAndEndToEndTest.php`)
Suite pengujian menyeluruh merefleksikan alur kerja operasional administrator kampus (*full academic lifecycle*):
1. **Otentikasi & Akses Portal**: Admin login via switcher instan, disambut di dashboard Navy Academic, metrik operasional dan peringatan perhatian semester tervalidasi.
2. **Siklus Semester**: Pembuatan semester baru, aktivasi semester baru yang secara otomatis menonaktifkan semester sebelumnya, serta pencatatan audit log `buat_semester` & `aktifkan_semester`.
3. **Katalog Kurikulum OBE**: Pembuatan mata kuliah dengan kode CPL dan pemetaan program studi, tervalidasi unik.
4. **Pembukaan Kelas Perkuliahan**: Pembukaan kelas baru dengan pendeteksian bentrok jadwal/ruang, otomasi pembuatan 16 pertemuan perkuliahan, dan inisialisasi bobot OBE 100%.
5. **Pendaftaran Mahasiswa**: Pendaftaran mahasiswa reguler, kuota berkurang secara presisi, penolakan saat kelas penuh, pengeluaran mahasiswa dengan mempertahankan seluruh nilai & presensi (*zero data loss*), serta reaktivasi kembali.
6. **Penutupan & Selesai Kelas**: Admin menyelesaikan kelas perkuliahan, status terkunci menjadi read-only, mencegah mutasi data lebih lanjut.
7. **Pencarian Topbar Global**: Admin menemukan kelas, mata kuliah, dan pengguna melalui indeks pencarian instan.

### 2. Hasil Audit Keamanan Multi-Vektor
* **Isolasi Peran & Otorisasi RBAC**:
  - Seluruh rute administratif (`/admin/*`) diproteksi middleware `role.admin` (`EnsureUserIsAdmin`).
  - Pengguna tamu (guest) dialihkan ke halaman login.
  - Pengguna dengan peran Mahasiswa (`student`) dan Dosen (`lecturer`) ditolak secara tegas dengan respon **HTTP 403 Forbidden**.
* **Pembatalan Sesi Administrator Nonaktif**:
  - Akun administrator yang statusnya diubah menjadi `is_active = false` seketika dilogout, sesi dibatalkan, dan dialihkan keluar portal.
* **Sanitasi Log Audit (`ActivityLogger`)**:
  - Nilai kolom sensitif (`password`, `current_password`, `token`, `secret`, `api_key`) otomatis disamarkan (*redacted*) menjadi `********` sebelum disimpan ke database tabel `activity_logs`.
* **Pencegahan Eksploitasi CSV Formula Injection**:
  - Seluruh nilai masukan CSV yang diawali karakter berbahaya (`=`, `+`, `-`, `@`, tab, newline) dinetralkan secara otomatis oleh `CsvImporterService` dengan penambahan kutip tunggal pengaman (`'`).
* **Ketahanan SQL Injection**:
  - Seluruh filter pencarian teks, parameter dropdown status, program studi, dan semester pada controller dieksekusi melalui Eloquent query builder dan parameterized PDO bindings. Karakter `' OR '1'='1` ditangani dengan aman tanpa celah kebocoran data.
* **Pencegahan Cross-Site Scripting (XSS)**:
  - Seluruh rendering Blade menggunakan escaping standar `{{ $variable }}` untuk menolak eksekusi payload `<script>` berbahaya pada nama kelas, nama mata kuliah, maupun nama semester.

### 3. Matriks Hasil Akhir Test Suite (Zero Regression)
* **Total Pengujian Panel Administrator (Batch 1)**:
  - `AdminFoundationTest`: **PASS 10/10 (35 assertions)**
  - `AdminDashboardTest`: **PASS 2/2 (8 assertions)**
  - `AdminSemesterTest`: **PASS 6/6 (25 assertions)**
  - `AdminCourseTest`: **PASS 8/8 (26 assertions)**
  - `AdminClassTest`: **PASS 13/13 (56 assertions)**
  - `AdminEnrollmentTest`: **PASS 11/11 (42 assertions)**
  - `AdminSearchTest`: **PASS 4/4 (10 assertions)**
  - `AdminSecurityAndEndToEndTest`: **PASS 7/7 (111 assertions)**
  - **Akumulasi Admin Test**: **PASS 61/61 (326 assertions, 0 failures)**.
* **Verifikasi Regresi Modul Dosen (`tests/Feature/Lecturer*`)**:
  - **PASS 82/82 (458 assertions, 0 failures)**.
  - Data Dr. Budi Rahardjo (90 mahasiswa terdaftar, 14 pengumpulan perlu dinilai, 2 notifikasi belum dibaca) **100% UTUH DAN TERPELIHARA**.
* **Verifikasi Regresi Modul Aplikasi & Mahasiswa (`tests/Feature/ArunikaAppTest`)**:
  - **PASS 8/8 (41 assertions, 0 failures)**.
* **Total Keseluruhan Test Lulus**: **151/151 Pengujian (825 assertions, 0 failures)**.

---

## Status Akhir: PANEL ADMINISTRATOR (BATCH 1) SELESAI 100%

Seluruh 5 modul inti Batch 1 (Beranda, Semester, Mata Kuliah, Kelas Perkuliahan, Pendaftaran Mahasiswa), Topbar Global Search, 4 modul placeholder Batch 2, layanan pendukung, migrasi non-destruktif, CLI, layout Navy Academic, dan rangkaian pengujian keamanan telah berhasil diselesaikan dengan presisi tinggi dan kualitas produksi.

---

## Catatan Verifikasi Pasca-A6 & Utang Teknis

### A. Penguatan Keamanan & Integritas Pasca-A6 (Telah Diterapkan)
1. **Lockdown Role Switcher di Production**:
   - Rute `/demo/login/{role}` hanya didaftarkan jika bukan production dan environment `local`/`testing` atau flag aktif.
   - Method `DemoAuthController::switchRole` menghentikan eksekusi dengan `abort(404)` jika `app()->isProduction()`.
   - Whitelist parameter peran: hanya `admin`, `lecturer`, `student`. Parameter lain ditolak 404.
   - Toolbar demo pada view disembunyikan otomatis saat environment `production`.
2. **Pencegahan Commit Berkas Dump Database & Storage**:
   - Menambahkan aturan `*.sql` dan direktori impor/ekspor `/storage/app/imports/*`, `/storage/app/exports/*` pada `.gitignore` tanpa menambah aturan `*.csv` global.
3. **Pencegahan Mahasiswa Mengambil Dua Kelas pada MK & Semester Sama**:
   - `EnrollmentAdminService::enrollStudent` menolak pendaftaran jika mahasiswa sudah aktif di seksi lain untuk mata kuliah dan semester yang sama.
4. **Desain Banner Semester Solid**:
   - Mengganti latar belakang gradien pada kartu semester aktif menjadi warna solid Navy Academic `#1A2E5E` sesuai prinsip desain bebas gradien.
5. **Penambahan Test Suite Pasca-A6 (`tests/Feature/AdminPostVerificationTest.php`)**:
   - Membuktikan lockdown role switcher, restriksi mutasi kelas selesai, penolakan akses mahasiswa dikeluarkan dan pemulihannya, larangan dobel seksi MK yang sama, serta pemindahan hak kelola dan badge "Perlu Dinilai" saat ganti dosen.

### B. Daftar Utang Teknis (Technical Debt)
1. **Optimasi Kueri Agregasi Portal Dosen**:
   - Pada halaman Dosen Dashboard, Tugas, Presensi, dan Diskusi, terjadi kueri agregasi berulang sebanyak 4 kali seiring iterasi tab kelas aktif dosen. Perlu diterapkan eager loading atau kueri batch terpusat pada refactoring berikutnya.
2. **Variasi Warna Tematik Badge SKS Mata Kuliah**:
   - Menyesuaikan warna badge SKS (2 SKS hijau, 3 SKS ungu, 4 SKS biru) pada tabel katalog mata kuliah agar presisi 100% dengan Screenshot 3.
3. **Eksekusi Test Suite di Database MySQL (`arunika_test`)**:
   - Sebelum rilis ke lingkungan staging/production, seluruh test suite perlu dijalankan pada driver database MySQL terpisah (`arunika_test`) untuk memvalidasi kompatibilitas penuh dialek SQL MySQL di samping SQLite in-memory.
4. **Konfigurasi Environment Production & Flag Role Switcher**:
   - Berkas `.env.example` saat ini memakai `APP_ENV=local`, sehingga pada deployment server production wajib memastikan disetel ke `APP_ENV=production`. Selain itu, pertimbangkan untuk mewajibkan flag `demo_role_switcher_enabled=true` secara eksplisit di semua environment (tidak hanya bergantung pada environment `local`/`testing`).
5. **Radius Sudut Banner Semester Aktif**:
   - Kartu banner Semester Aktif saat ini memakai utility class Tailwind `rounded-2xl` (radius 16px), sedangkan panduan spesifikasi desain meminta radius sudut 8px (`rounded-[8px]`).

