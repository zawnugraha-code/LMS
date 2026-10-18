# Rencana Pembangunan Panel Admin (Batch 1) — LMS Arunika

Dokumen ini merupakan artefak perencanaan dan analisis teknis komprehensif (**Fase A1**) untuk pembangunan **Panel Admin** pada aplikasi LMS Arunika (Universitas Nusantara Indonesia). Seluruh perancangan berpedoman pada sistem yang sudah berjalan (Dashboard Mahasiswa dan Dashboard Dosen Batch 1 & 2), 5 screenshot acuan, dan aturan integritas database MySQL `arunika`.

---

## 1. Audit Kode Lama yang Mengasumsikan Dua Role

Berdasarkan pemeriksaan menyeluruh pada codebase yang ada, ditemukan beberapa titik kode yang mengasumsikan sistem hanya memiliki 2 role aktif (*lecturer* dan *student*):

1. **Alur Login / Redirection Switcher (`app/Http/Controllers/DemoAuthController.php:41`)**:
   ```php
   $targetRoute = $user->role === UserRole::Lecturer ? 'dosen.dashboard' : 'app.dashboard';
   ```
   *Dampak*: Jika akun admin login melalui alur ini, akun admin akan terlempar ke `app.dashboard` (Dashboard Mahasiswa) karena tidak berstatus `UserRole::Lecturer`.
   *Solusi*: Perbarui logika routing pengalihan:
   ```php
   $targetRoute = match ($user->role) {
       UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin => 'admin.dashboard',
       UserRole::Lecturer => 'dosen.dashboard',
       default => 'app.dashboard',
   };
   ```

2. **Enum Role Pengguna (`app/Enums/UserRole.php`)**:
   Saat ini mendefinisikan: `SuperAdmin = 'super_admin'`, `CampusAdmin = 'campus_admin'`, `Lecturer = 'lecturer'`, `Student = 'student'`, `Staff = 'staff'`, `Auditor = 'auditor'`, `IndustryMentor = 'industry_mentor'`.
   *Dampak*: Nilai `'admin'` belum didefinisikan secara eksplisit sebagai enum case.
   *Solusi*: Tambahkan:
   ```php
   case Admin = 'admin';
   ```
   Lengkap dengan label `Administrator`, warna badge `navy`, deskripsi hak akses, dan `defaultRoute() => 'admin.dashboard'`.

3. **Helper Model User (`app/Models/User.php`)**:
   Telah memiliki `isSuperAdmin()`, `isCampusAdmin()`, `isLecturer()`, `isStudent()`.
   *Solusi*: Tambahkan method:
   ```php
   public function isAdmin(): bool
   {
       return in_array($this->role, [UserRole::Admin, UserRole::SuperAdmin, UserRole::CampusAdmin], true);
   }
   ```

4. **Middleware Otorisasi Dosen (`app/Http/Middleware/EnsureUserIsLecturer.php:25`)**:
   Mengecek `in_array($user->role, [UserRole::Lecturer, UserRole::SuperAdmin, UserRole::CampusAdmin])`.
   *Solusi*: Tetap isolasi peran. Admin tidak boleh membuka dashboard dosen tanpa menjadi dosen pengampu terdaftar, dan sebaliknya dosen/mahasiswa dilarang keras mengakses `/admin/*`. Buat middleware baru `EnsureUserIsAdmin` (`role:admin`).

5. **Fallback Student pada Controller Mahasiswa**:
   Beberapa controller mahasiswa (`GradeController`, `ScheduleController`, `MaterialController`, `DiscussionController`, `SettingController`) memiliki fallback:
   ```php
   $defaultUser = User::where('role', UserRole::Student)->first() ?? User::first();
   ```
   *Dampak*: Jika admin membuka route `/app/*` saat login, beberapa controller mahasiswa bisa salah mengidentifikasi atau menolak akses.
   *Solusi*: Buat middleware pembatas peran yang jelas dan redirect guard sehingga admin hanya beroperasi di `/admin/*`.

---

## 2. Audit Semua Query Enrollment di Dosen & Mahasiswa

Ditemukan lebih dari 70 pemanggilan relasi dan query tabel `enrollments` di seluruh aplikasi. Seluruh query ini wajib divalidasi agar hanya memperhitungkan mahasiswa yang berstatus **aktif**:

1. **Dashboard Dosen & Sidebar (`resources/views/layouts/dosen.blade.php`, `DosenDashboardController`)**:
   - `Submission::whereNull('score')->whereHas('assignment.academicClass', fn ($q) => $q->where('lecturer_id', $user->id))`
   - Menghitung total mahasiswa: `$user->academicClasses->sum('students_count')`.
   *Perbaikan*: Pastikan relasi `students_count` menggunakan subquery dengan scope `aktif()`.

2. **Dosen Presensi Service (`app/Services/Dosen/DosenPresensiService.php`)**:
   - Baris 86: `$enrolledStudentIds = $class->enrollments->pluck('user_id');`
   - Baris 264: `'total_students' => $cls->enrollments->count()`
   - Baris 316: `User::whereHas('enrollments', fn ($q) => $q->where('class_id', $selectedClass->id))`
   - Baris 440: `$students = $class->enrollments->map->user->sortBy('identifier')->values();`
   *Perbaikan*: Ubah pemanggilan dari `$class->enrollments` menjadi `$class->enrollments()->aktif()->get()` atau relasi `$class->activeEnrollments`.

3. **Dosen Grade Service (`app/Services/Dosen/DosenGradeService.php`)**:
   - Baris 144: `$studentCount = $class->enrollments->count();`
   - Baris 249: `$enrollments = $class->enrollments;`
   *Perbaikan*: Mengeluarkan mahasiswa dari kelas harus langsung memutakhirkan Buku Nilai dosen tanpa menghapus nilai riwayat yang sudah diinput. Dengan memfilter `$class->enrollments()->aktif()->get()`, mahasiswa yang dikeluarkan tidak lagi muncul di matriks penilaian aktif, namun catatan nilainya tetap tersimpan di baris enrollment tersebut.

4. **Dosen Setting Service (`app/Services/Dosen/DosenSettingService.php:30`)**:
   - `$studentsCount = User::whereHas('enrollments', fn ($q) => $q->whereIn('class_id', $classIds))`
   *Perbaikan*: Ubah menjadi `whereHas('enrollments', fn ($q) => $q->aktif()->whereIn('class_id', $classIds))`.

5. **Dashboard Mahasiswa (`ScheduleService`, `MaterialService`, `GradeService`, `SearchService`, `SettingService`)**:
   - Seluruh query mahasiswa menggunakan filter `whereHas('enrollments', fn ($q) => $q->where('user_id', $user->id))`.
   *Perbaikan*: Ubah menjadi `whereHas('enrollments', fn ($q) => $q->aktif()->where('user_id', $user->id))`.
   *Efek Nyata*: Saat admin mengeluarkan mahasiswa dari kelas, kelas tersebut seketika hilang dari jadwal, materi, tugas, dan buku nilai mahasiswa tersebut. Saat dipulihkan, kelas dan materi langsung muncul kembali.

6. **Strategi Scope Tunggal (`app/Models/Enrollment.php`)**:
   ```php
   public function scopeAktif(Builder $query): Builder
   {
       return $query->whereIn('status', ['aktif', 'enrolled']);
   }

   public function scopeDikeluarkan(Builder $query): Builder
   {
       return $query->where('status', 'keluar');
   }
   ```
   *Catatan*: Mengikutsertakan `'enrolled'` dan `'aktif'` menjamin bahwa 169 test suite dosen dan mahasiswa yang sudah berjalan tetap lulus 100% tanpa regresi.

---

## 3. Cara Pembuatan Kelas, Pertemuan, dan Komponen Penilaian Saat Ini

* **Kondisi Eksisting**:
  - Saat ini, kelas (`AcademicClass`), pertemuan perkuliahan (`Meeting`), dan komponen penilaian (`CourseAssessmentComponent`) **HANYA** dibuat melalui database seeder (`DatabaseSeeder.php`, `LecturerBatch1Seeder.php`, `CanonicalStudentSeeder.php`).
  - Tidak ada UI admin atau dosen untuk membuat kelas atau menambahkan pertemuan baru secara dinamis.
  - Di `DosenGradeService.php`, terdapat logika *lazy-instantiation* fallback: jika komponen penilaian suatu kelas kosong, sistem secara otomatis membuat komponen bawaan (Tugas 30%, Kuis 20%, UTS 25%, UAS 25% = 100%).
* **Mekanisme Baru di Batch 1 Admin (`ClassService` / `CreateClassAction`)**:
  Saat Admin menekan tombol "Simpan Kelas" di form `/admin/kelas/buat`, sebuah transaksi database `DB::transaction` akan mengeksekusi:
  1. Validasi bentrok jadwal dosen & ruang (pada semester yang sama).
  2. Pembuatan baris baru pada tabel `classes` dengan status `'aktif'` dan kapasitas default 40.
  3. Pembuatan otomatis pertemuan mingguan (1..N, default N = 16 dari `config/arunika.php`):
     - Dihitung mulai dari tanggal pertama yang jatuh pada hari kelas (`schedule_day`) pada atau setelah `start_date` semester aktif.
     - Setiap pertemuan diberi judul otomatis: `"Pertemuan 1"`, `"Pertemuan 2"`, dst.
     - Jam mulai dan selesai disalin dari jadwal kelas.
  4. Penyalinan otomatis komponen penilaian default dari `config/arunika.php` (Tugas 30%, Kuis 20%, UTS 25%, UAS 25% = total 100%).
  5. Pencatatan log aktivitas di `activity_logs`.
  6. Pengiriman notifikasi database ke Dosen pengampu yang ditugaskan.

---

## 4. Hasil Pemeriksaan Konfigurasi Test & Keamanan Database

* **Pemeriksaan `phpunit.xml`**:
  Telah diperiksa langsung pada file [`phpunit.xml`](file:///c:/Users/rizza/OneDrive/Dokumen/LMS/phpunit.xml). Ditemukan konfigurasi:
  ```xml
  <env name="DB_CONNECTION" value="sqlite"/>
  <env name="DB_DATABASE" value=":memory:"/>
  ```
* **Pemeriksaan `.env.testing`**: File `.env.testing` tidak ada di workspace.
* **Kesimpulan Keamanan**:
  Seluruh test otomatis (`php artisan test`) menggunakan **SQLite in-memory** (`:memory:`). Tidak ada risiko eksekusi test menghapus database MySQL `arunika`.
* **Prosedur Pengujian Database Kosong (Fase A6)**:
  - Pengujian "database kosong" akan dilakukan pada database MySQL khusus bernama `arunika_kosong`.
  - Sebelum menjalankan perintah migrasi atau pengujian pada database kosong, script wajib memverifikasi:
    ```bash
    php -r "echo config('database.connections.mysql.database');"
    ```
    Jika output bernilai `arunika`, proses langsung dibatalkan demi keamanan data.
* **Pengingat Backup Sebelum Migrasi Pertama**:
  > [!IMPORTANT]
  > Sebelum menjalankan `php artisan migrate` di Fase A2, pengguna wajib menjalankan backup:
  > `mysqldump -u root arunika > backup_sebelum_admin.sql`

---

## 5. Status Registrasi Publik, Lupa Password, dan Alur Autentikasi

* **Registrasi Publik**:
  - Hasil audit pada `routes/web.php` dan controllers membuktikan **TIDAK ADA fitur registrasi publik**. Route `/register` tidak terdaftar.
  - Sesuai prinsip universitas, akun pengguna hanya dapat dibuat oleh Admin (mahasiswa dan dosen tidak mendaftar mandiri). Penonaktifan rute publik bawaan (jika ada scaffold) telah terverifikasi aman.
* **Lupa Password**:
  - Tidak ada route `/forgot-password` atau alur reset password publik via email.
  - Penggantian password dilakukan secara internal melalui menu Pengaturan profil oleh pengguna yang sudah login, atau nantinya direset oleh Admin (Batch 2).
* **Alur Autentikasi Admin**:
  - Akun admin dibuat pertama kali secara interaktif via command CLI:
    `php artisan arunika:buat-admin`
  - Command meminta Nama, Email, dan Password (disembunyikan saat input, tidak dicatat di log).
  - Login admin didukung melalui autentikasi form login serta switcher demo `/demo/login/admin` untuk kemudahan evaluasi pair-programming.

---

## 6. Pemetaan Skema Database (Existing -> Perubahan)

| Tabel | Kolom Existing | Perubahan Skema Batch 1 | Keterangan & Nilai Default |
| :--- | :--- | :--- | :--- |
| **`users`** | `id`, `name`, `gelar`, `email`, `role`, `identifier`, `avatar_path`, `phone`, dll. | Tambah: `is_active`, `must_change_password`, `last_login_at`, `password_changed_at` | `is_active` (boolean, default `true`), `must_change_password` (boolean, default `false`), timestamp login tercatat saat login. Kolom `role` adalah string aman (bukan ENUM kaku). |
| **`terms`** | `id`, `ulid`, `campus_id`, `name`, `academic_year`, `semester_type`, `start_date`, `end_date`, `is_active` | Tambah: `status`, `closed_at`, constraint `unique(['academic_year', 'semester_type'])` | `status` (string 20, default `'belum_aktif'`: `aktif`, `belum_aktif`, `selesai`). Semester aktif ditegakkan hanya satu. |
| **`courses`** | `id`, `ulid`, `curriculum_id`, `code`, `name`, `credits`, `semester`, `description`, `syllabus` | Pastikan constraint `unique('code')` | Kode mata kuliah bersifat unik global. Hapus dicegah jika memiliki kelas. |
| **`classes`** | `id`, `ulid`, `course_id`, `term_id`, `lecturer_id`, `section`, `room`, `schedule_day`, `start_time`, `end_time`, `capacity`, `status` | Tambah constraint `unique(['course_id', 'term_id', 'section'])` | Kolom `status` (`aktif`, `selesai`) sudah ada dari migrasi dosen. Dosen pengampu wajib ada. |
| **`enrollments`** | `id`, `class_id`, `user_id`, `status` (default 'enrolled'), `final_score`, `letter_grade` | Tambah: `dikeluarkan_at`, `dikeluarkan_oleh` | `dikeluarkan_at` (timestamp nullable), `dikeluarkan_oleh` (foreignId nullable ke users). Nilai lama tetap utuh. |
| **`activity_logs`** | *Belum ada* | **Buat tabel baru**: `id`, `user_id`, `aksi`, `kategori`, `subjek_type`, `subjek_id`, `deskripsi`, `properti`, `ip_address`, `created_at` | Index pada: `created_at`, `user_id`, `kategori`. Append-only tanpa edit/delete UI. |

---

## 7. Keputusan Teknis Inti

1. **Enrollments: Status (`aktif` / `keluar`) vs `SoftDeletes`**:
   - **Keputusan**: **Menggunakan Kolom `status` + `dikeluarkan_at` + `dikeluarkan_oleh`**.
   - *Justifikasi*: Tabel `enrollments` memiliki unique constraint majemuk `unique(['class_id', 'user_id'])`. Jika menggunakan `SoftDeletes`, MySQL tetap menganggap baris terhapus sebagai pelanggaran unique key ketika mahasiswa didaftarkan kembali. Dengan kolom `status`, row tetap berada di tempatnya, riwayat nilai dan presensi tidak terganggu, dan proses pulihkan/keluarkan berjalan secara atomik tanpa konflik kunci unik.
2. **Activity Logging**:
   - **Keputusan**: **Implementasi Custom Ringan (`ActivityLog` Model + `ActivityLogger` Service)**.
   - *Justifikasi*: Menghindari ketergantungan pada library eksternal (seperti `spatie/laravel-activitylog`), bebas overhead, schema ringkas sesuai spesifikasi (kategori, aksi, deskripsi, properti JSON bersih tanpa data sensitif), dan performa query log yang sangat cepat.
3. **Pustaka & Pengolahan CSV**:
   - **Keputusan**: **Native PHP Streams (`fgetcsv` / `fputcsv`) dengan Proteksi CSV Injection**.
   - *Justifikasi*: Sudah terbukti andal pada modul Buku Nilai dan Presensi Dosen. Konsumsi memori O(1), mendukung file hingga 2 MB (1000 baris), menangani BOM UTF-8 dan delimiter fleksibel (koma & titik koma), serta sanitasi otomatis formula berbahaya (`=`, `+`, `-`, `@`) dengan prefix tanda kutip tunggal (`'`).
4. **Penanda Semester Selesai**:
   - **Keputusan**: **Kolom `status` ('aktif', 'belum_aktif', 'selesai') dan `closed_at` (timestamp)**.
   - *Justifikasi*: Menjaga kompatibilitas kolom `is_active` (boolean) yang dipakai oleh frontend/layanan lain, sekaligus memberi kejelasan status siklus akademik. "Tutup Semester" mengunci semester dan seluruh kelas di dalamnya menjadi read-only secara permanen.

---

## 8. Analisis 5 Screenshot & Komparasi Spesifikasi

### Screenshot 1: Beranda Admin (`/admin/beranda`)
- **Visual**:
  - Banner selamat datang dengan gedung rektorat, nama admin ("Rina Kusumawati"), badge peran Administrator, badge semester aktif ("Semester Ganjil 2026/2027 (21 Sep 2026 - 15 Jan 2027)"), dan prodi ("S1 Teknik Informatika").
  - 4 Stat Card: Total Dosen (`3`), Total Mahasiswa (`119`), Kelas Semester Aktif (`4`), Mata Kuliah (`3`).
  - Panel "Perlu Perhatian" (Kiri): 4 baris status (Mahasiswa belum terdaftar, Dosen belum mengampu kelas, Kelas tanpa mahasiswa, Kelas penuh) dengan badge oranye "Perlu tindakan" atau hijau "Aman".
  - Panel "Kelas Semester Aktif" (Tengah): Menampilkan mata kuliah, kelas, dosen, dan progress bar terisi/kapasitas (`32/40`, `30/40`, `28/35`, `24/40`).
  - Panel "Aksi Cepat" & "Aktivitas Terbaru" (Kanan): 4 tombol aksi cepat dan linimasa 5 entri log aktivitas terbaru.
- **Selisih / Penyesuaian Batch 1**:
  - Tombol "Tambah Pengguna" pada Aksi Cepat berstatus nonaktif (disabled) karena menu Pengguna masuk di Batch 2.
  - Tautan "Tinjau" pada "Dosen belum mengampu kelas" dinonaktifkan di Batch 1.
  - Tautan "Lihat Semua" pada Aktivitas Terbaru mengarah ke halaman placeholder Log Aktivitas.
  - "Import Mahasiswa" mengarah ke tab import pada Pendaftaran Mahasiswa (`/admin/pendaftaran-mahasiswa#import`).

### Screenshot 2: Semester (`/admin/semester`)
- **Visual**:
  - Header dengan judul "Semester", tombol "+ Tambah Semester".
  - Tab filter: `Semua`, `Aktif`, `Belum Aktif`, `Selesai` + search bar.
  - Tabel: No, Semester, Tahun Ajaran, Jenis, Periode, Jumlah Kelas, Status (Badge), Aksi (Dropdown "Kelola v": Edit, Jadikan Aktif, Hapus).
  - Panel Kanan: Kartu "Semester Aktif" (dengan tombol "Tutup Semester"), kartu "Ringkasan Semester" (grid 2x2: Total, Aktif, Belum Aktif, Selesai), dan kotak info biru.
- **Penyesuaian Teknis**:
  - Tombol "Hapus" pada dropdown Kelola nonaktif jika semester memiliki kelas terdaftar.
  - "Jadikan Aktif" dan "Tutup Semester" dilengkapi modal konfirmasi yang informatif.

### Screenshot 3: Mata Kuliah (`/admin/mata-kuliah`)
- **Visual**:
  - Header dengan tombol "+ Tambah Mata Kuliah".
  - Filter Prodi dropdown (`Semua Prodi`) + search input.
  - Tabel: No, Kode, Nama Mata Kuliah, Prodi, SKS, Jumlah Kelas, Aksi (Edit biru, Hapus abu-abu).
  - Panel Kanan: Ringkasan Mata Kuliah (Total MK, Total SKS, Total Kelas) dan Donut Chart "Mata Kuliah per Prodi" (100% S1 Teknik Informatika).
  - Kartu Bawah: "Daftar Mata Kuliah Berdasarkan Prodi".
- **Penyesuaian Teknis**:
  - Tombol hapus di-disable jika mata kuliah memiliki kelas pada semester mana pun.

### Screenshot 4: Kelas (`/admin/kelas`)
- **Visual**:
  - Header dengan tombol "+ Tambah Kelas".
  - Filter bar lengkap: Tab (Semua, Aktif, Selesai), Dropdown Semester (default semester aktif), Dropdown Mata Kuliah, Dropdown Dosen, dan Search input.
  - Tabel: No, Mata Kuliah (Kode + Nama), Kelas, Dosen Pengampu, Jadwal (Hari & Jam), Ruang, Terisi (progress bar visual), Status, Aksi ("Lihat Detail v").
  - Panel Kanan: Ringkasan Kelas (Total Kelas: 4, Aktif: 4, Mahasiswa Terdaftar: 114, Selesai: 0) dan Donut Chart Kapasitas Kelas (74% Terisi: 114, 26% Tersisa: 41).

### Screenshot 5: Pendaftaran Mahasiswa (`/admin/pendaftaran-mahasiswa`)
- **Visual**:
  - Header dengan 2 tombol: "+ Tambah Mahasiswa" dan "Ekspor CSV".
  - Kartu Kelas Terpilih: Menampilkan ringkasan mata kuliah, kelas, prodi, jumlah mahasiswa, semester, dosen pengampu, jadwal, serta tombol "Ganti Kelas >".
  - Tab Filter: `Terdaftar (32)`, `Dikeluarkan (0)`, `Belum Terdaftar (5)`.
  - Banner Info: "Mengeluarkan mahasiswa tidak menghapus nilai, presensi, maupun pengumpulan tugas."
  - Tabel Peserta: Checkbox massal, No, Checkbox baris, Avatar inisial + Nama + Email, NIM, Prodi, Tanggal Didaftarkan, Status, Aksi ("Keluarkan >").
  - Panel Kanan: Donut Kapasitas Kelas (80% Terisi, 20% Tersisa), Widget "Belum Terdaftar" dengan aksi cepat "Tambah >", dan Widget "Import Peserta" (Unduh Template & Import CSV).

---

## 9. Desain Halaman & Modal yang Belum Ada di Screenshot

Untuk menjaga keselarasan desain (*Navy Academic Design System*), elemen-elemen berikut dirancang dengan token dan komponen Blade yang sama:

1. **Halaman Detail Kelas (`/admin/kelas/{class}`)**:
   - *Header*: Banner kartu kelas berisi kode, nama mata kuliah, dosen pengampu, ruang, hari & jam, status kelas, dan tombol aksi ("Ganti Dosen", "Selesaikan Kelas", "Hapus Kelas").
   - *Tab Navigasi*:
     1. **Peserta Kelas**: Tabel mahasiswa aktif dengan filter dan aksi cepat keluarkan.
     2. **Jadwal & Pertemuan**: Linimasa 16 pertemuan mingguan berisi tanggal, topik pembahasan, mode (offline/online), dan status presensi.
     3. **Komponen Penilaian**: Tampilan persentase bobot penilaian (Tugas, Kuis, UTS, UAS = 100%).
     4. **Ringkasan Aktivitas (Read-only)**: Kartu statistik materi terunggah, tugas aktif, dan total pengumpulan.
2. **Form Tambah / Edit Semester (`/admin/semester/buat`, `/admin/semester/{term}/edit`)**:
   - Layout kartu bersih: Input Nama Semester, Tahun Ajaran (format YYYY/YYYY), Jenis Semester (radio pill: Ganjil, Genap, Pendek), Tanggal Mulai dan Tanggal Selesai (input date native bergaya Arunika).
3. **Form Tambah / Edit Mata Kuliah (`/admin/mata-kuliah/buat`, `/admin/mata-kuliah/{course}/edit`)**:
   - Select Program Studi, Input Kode Mata Kuliah, Input Nama Mata Kuliah, Input SKS (angka 1-6), Input Semester Rekomendasi (1-8), Textarea Deskripsi Ringkas.
4. **Form Tambah / Edit Kelas (`/admin/kelas/buat`, `/admin/kelas/{class}/edit`)**:
   - Layout 2 kolom:
     - *Kiri*: Pilih Mata Kuliah, Pilih Semester, Input Nama Kelas (mis. "Kelas A"), Pilih Dosen Pengampu (hanya dosen berstatus aktif), Pilih Hari (Senin-Sabtu), Input Jam Mulai & Jam Selesai, Input Ruang Kuliah, Input Kapasitas, Input Jumlah Pertemuan (default 16).
     - *Kanan (Live Schedule Checker)*: Panel pengecekan otomatis bentrok jadwal dosen dan bentrok pemakaian ruang.
5. **Modal Tambah Mahasiswa ke Kelas**:
   - Input pencarian NIM/Nama, daftar listbox mahasiswa dengan checkbox, badge penanda jika mahasiswa sudah terdaftar di seksi lain atau mengalami bentrok jadwal (peringatan ramah).
6. **Modal Konfirmasi Keluarkan Mahasiswa**:
   - Modal peringatan berbingkai merah/amber lembut: menampilkan rincian nama mahasiswa, NIM, jumlah tugas yang sudah dikumpulkan, dan penegasan bahwa seluruh nilai & presensi tetap disimpan.
7. **Pratinjau Import CSV Pendaftaran Peserta**:
   - Langkah 1: Upload file CSV (drag & drop atau browse).
   - Langkah 2: Pratinjau tabel hasil validasi baris:
     - Badge hijau: Jumlah baris valid dan siap diproses.
     - Badge merah: Jumlah baris tidak valid beserta alasan spesifik (NIM tidak ditemukan, kapasitas penuh, atau sudah terdaftar).
   - Tombol "Proses Baris Valid" dan link unduh laporan galat.

---

## 10. Usulan Read-Only Policy & Fleksibilitas Pertemuan

1. **Penegakan Kelas Selesai (Read-Only Policy)**:
   - Tambahkan method terpusat pada model `AcademicClass`:
     ```php
     public function isFinished(): bool
     {
         return $this->status === 'selesai' || ($this->term && $this->term->status === 'selesai');
     }
     ```
   - Di seluruh Policy dosen (`MaterialPolicy`, `AssignmentPolicy`, `SubmissionPolicy`, `AttendancePolicy`) dan mahasiswa:
     Sebelum mengizinkan aksi `create`, `update`, `delete`, `submit`, atau `grade`:
     ```php
     if ($class->isFinished()) {
         return false; // Ditolak: Kelas telah berstatus selesai (read-only)
     }
     ```
   - Di antarmuka UI dosen: Kelas selesai diberi label badge abu-abu `"Selesai"` dan form pengunggahan materi/tugas dinonaktifkan secara anggun.

2. **Fleksibilitas Edit Pertemuan & Hari Libur**:
   - Saat ini dosen belum memiliki form untuk mengedit tanggal/topik pertemuan mingguan.
   - *Solusi Minimal*:
     1. Tambahkan hak bagi Admin di halaman Detail Kelas (tab Pertemuan) untuk mengedit tanggal dan judul pertemuan secara perorangan (misal untuk memindahkan jadwal akibat libur nasional).
     2. Menyesuaikan tanggal pertemuan tidak menghapus atau mengubah data presensi yang sudah tercatat pada pertemuan tersebut.
     3. Regenerasi ulang seluruh pertemuan mingguan hanya diizinkan jika kelas bersangkutan belum memiliki riwayat presensi mahasiswa.

---

## 11. Daftar File yang Akan Dibuat dan Diubah

### File Baru:
- **Dokumentasi**: `docs/admin/PLAN.md`, `docs/admin/PROGRESS.md`
- **Konfigurasi**: `config/arunika.php`
- **Database Migrations**:
  - `database/migrations/2026_10_18_000001_add_admin_columns_to_users_table.php`
  - `database/migrations/2026_10_18_000002_extend_terms_table_for_admin.php`
  - `database/migrations/2026_10_18_000003_extend_enrollments_table_for_admin.php`
  - `database/migrations/2026_10_18_000004_create_activity_logs_table.php`
- **Console Command**:
  - `app/Console/Commands/CreateAdminCommand.php` (`arunika:buat-admin`)
- **Models**:
  - `app/Models/ActivityLog.php`
- **Middleware**:
  - `app/Http/Middleware/EnsureUserIsAdmin.php`
- **Services (Domain Logic Admin)**:
  - `app/Services/Admin/ActivityLogger.php`
  - `app/Services/Admin/AdminDashboardService.php`
  - `app/Services/Admin/SemesterService.php`
  - `app/Services/Admin/CourseService.php`
  - `app/Services/Admin/ClassService.php`
  - `app/Services/Admin/EnrollmentAdminService.php`
  - `app/Services/Admin/CsvImporterService.php`
- **Controllers**:
  - `app/Http/Controllers/Admin/AdminDashboardController.php`
  - `app/Http/Controllers/Admin/AdminSemesterController.php`
  - `app/Http/Controllers/Admin/AdminCourseController.php`
  - `app/Http/Controllers/Admin/AdminClassController.php`
  - `app/Http/Controllers/Admin/AdminEnrollmentController.php`
  - `app/Http/Controllers/Admin/AdminSearchController.php`
  - `app/Http/Controllers/Admin/AdminPlaceholderController.php`
- **Seeders**:
  - `database/seeders/DemoSeeder.php`
  - `database/seeders/ProductionSeeder.php`
- **Blade Views & Layout**:
  - `resources/views/layouts/admin.blade.php`
  - `resources/views/pages/admin/dashboard.blade.php`
  - `resources/views/pages/admin/semester/index.blade.php`, `create.blade.php`, `edit.blade.php`
  - `resources/views/pages/admin/mata-kuliah/index.blade.php`, `create.blade.php`, `edit.blade.php`
  - `resources/views/pages/admin/kelas/index.blade.php`, `create.blade.php`, `edit.blade.php`, `show.blade.php`
  - `resources/views/pages/admin/pendaftaran/index.blade.php`
  - `resources/views/pages/admin/cari.blade.php`
  - `resources/views/pages/admin/placeholder.blade.php`
- **Feature Tests**:
  - `tests/Feature/AdminFoundationTest.php`
  - `tests/Feature/AdminSemesterTest.php`
  - `tests/Feature/AdminCourseTest.php`
  - `tests/Feature/AdminClassTest.php`
  - `tests/Feature/AdminEnrollmentTest.php`
  - `tests/Feature/AdminSecurityTest.php`

### File yang Dimodifikasi Secara Minimal:
- `app/Enums/UserRole.php` (menambahkan case `Admin = 'admin'`).
- `app/Models/User.php` (menambahkan fillable `is_active`, `must_change_password`, `last_login_at`, method `isAdmin()`).
- `app/Models/Term.php` (menambahkan relasi, method status semester, scope aktif).
- `app/Models/AcademicClass.php` (menambahkan scope active, method `isFinished()`, relasi activeEnrollments).
- `app/Models/Enrollment.php` (menambahkan scope `aktif()`, `dikeluarkan()`, fillable).
- `app/Models/Assignment.php` & `app/Policies/AssignmentPolicy.php` (memperbaiki kriteria kepemilikan dosen agar berpindah 100% mengikuti `academicClass.lecturer_id`).
- `app/Http/Controllers/DemoAuthController.php` (menambahkan pengalihan role admin).
- `bootstrap/app.php` (mendaftarkan alias middleware `role.admin` / `role:admin`).
- `routes/web.php` (mendaftarkan grup route `/admin/*`).
- `database/seeders/DatabaseSeeder.php` (memanggil `DemoSeeder` hanya jika bukan environment production).

---

## 12. Asumsi & Manajemen Risiko

1. **Asumsi Tombol "Import Mahasiswa" di Dashboard Beranda**:
   - Tombol tersebut merupakan shortcut menuju fitur import CSV pendaftaran mahasiswa ke kelas pada halaman Pendaftaran Mahasiswa (`/admin/pendaftaran-mahasiswa#import`).
   - Fitur import master data akun pengguna mahasiswa akan diintegrasikan pada menu Pengguna di Batch 2.
2. **Risiko Regresi Data Dosen Eksisting**:
   - *Risiko*: Perubahan penamaan atau scope status enrollment berpotensi memengaruhi assertion data canonical dosen (90 mahasiswa, 14 perlu dinilai, 2 notifikasi).
   - *Mitigasi*: Scope `aktif()` secara inklusif menerima status lama `'enrolled'` dan status baru `'aktif'`, serta seeder demo dosen Dr. Budi Rahardjo dipelihara 100% utuh tanpa duplikasi.
3. **Risiko Integritas Akses Ganti Dosen**:
   - *Risiko*: Dosen lama masih dapat mengakses penilaian jika kode lama mengecek `assignments.dosen_id`.
   - *Mitigasi*: Seluruh Policy penilaian dan dashboard diselaraskan ke `academicClass.lecturer_id`.
4. **Keamanan Database MySQL `arunika`**:
   - *Risiko*: Ketidaksengajaan eksekusi perintah destruktif.
   - *Mitigasi*: Peringatan backup mysqldump di awal, larangan keras menjalankan migrate:fresh, dan pengecekan guard aktif pada script test.

---

> **Status Fase A1**: SELESAI & MENUNGGU PERSETUJUAN.
> Coding dan modifikasi file aplikasi **BELUM** dijalankan sebelum pengguna membalas: `"Lanjut Fase A2"`.
