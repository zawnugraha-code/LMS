# RENCANA INDUK PENGEMBANGAN DASHBOARD DOSEN (BATCH 1)
**LMS ARUNIKA — UNIVERSITAS NUSANTARA INDONESIA**
*Dokumen Arsitektur, Analisis Repo, Audit Integrasi, dan Rencana Eksekusi Bertahap*

---

## 1. PENDAHULUAN & PRINSIP UTAMA

Dashboard Dosen dibangun dalam **satu aplikasi Laravel** dan **satu database MySQL (`arunika`)** bersama dengan Dashboard Mahasiswa yang telah berjalan. 

### Prinsip Mutlak:
1. **Single Source of Truth Database**: Menggunakan database MySQL `arunika` yang sudah ada. Dilarang membuat database baru, dilarang menjalankan perintah yang menghapus data (`migrate:fresh`, `migrate:refresh`, `db:wipe`). Semua penyesuaian skema dilakukan melalui migration penambahan (`Schema::table` / `create`).
2. **Keterhubungan Dua Arah (Two-Way Reactive Interconnection)**: Dosen dan Mahasiswa berinteraksi langsung melalui model dan relasi yang sama. Saat dosen mempublikasikan materi atau tugas, mahasiswa kelas terkait langsung melihatnya. Saat mahasiswa mengumpulkan tugas, antrean penilaian dan badge dosen langsung terbarui.
3. **Pemisahan Hak Akses Berbasis Enrollment & Kepemilikan**:
   - Dosen hanya memiliki hak kelola (CRUD) terhadap kelas, materi, pengumuman, dan assessment yang diampunya.
   - Mahasiswa hanya dapat melihat materi/tugas dan mengumpulkan tugas pada kelas yang diikutinya (KRS/Enrollment aktif).
   - Isolasi antar dosen: Dosen A tidak dapat melihat, mengubah, atau menilai kelas/tugas milik Dosen B.
4. **Idempotensi & Keamanan Data**: Seeder menggunakan `firstOrCreate` / `updateOrCreate` agar dapat dijalankan berulang kali tanpa merusak atau menduplikasi data mahasiswa kanonikal (Arya Pratama dkk.).
5. **UI Freeze & Konsistensi Visual**: Menggunakan *Navy Academic Design System* (`#1A2E5E`), font Inter, Lucide outline icon, dan tata letak presisi sesuai dengan 5 tangkapan layar desain Batch 1.

---

## 2. AUDIT LENGKAP DASHBOARD MAHASISWA & TITIK PENGHUBUNG

Berdasarkan audit menyeluruh terhadap kode sumber dashboard mahasiswa (`app/Http/Controllers`, `app/Services`, `resources/views/pages/app`):

### A. Kondisi Terkini Dashboard Mahasiswa
1. **Modul Tugas & Ujian (`app/Http/Controllers/AssignmentController.php`)**:
   - Controller mengambil data kelas terdaftar lewat `AssignmentService::getEnrolledClasses()`.
   - Mengambil tugas melalui `Assignment::whereIn('class_id', $classIds)` dan kuis melalui `Quiz::whereIn('class_id', $classIds)`.
   - **Temuan Audit**: Pada `resources/views/pages/app/assignments.blade.php`, kartu-kartu tugas pada tab "Semua" sempat terikat dengan variabel `$cardTask1`, `$cardTask2`, dst. untuk mencocokkan visual freeze.
   - **Tindakan Penyesuaian**: Pada Fase 4, kita pastikan daftar tugas mahasiswa me-looping koleksi `$assignments` yang berstatus `dipublikasikan` secara dinamis, sehingga saat dosen mempublikasikan tugas baru di kelas mahasiswa, kartu tugas baru langsung muncul tanpa merusak styling kartu yang sudah ada.
2. **Modul Materi Pembelajaran (`app/Http/Controllers/MaterialController.php` & `MaterialService.php`)**:
   - `MaterialService` mengambil materi terstruktur per bab/pertemuan via `AcademicClass::meetings()->with('materials')`.
   - **Temuan Audit**: Tabel `materials` saat ini belum memiliki kolom `status` (`draft` | `published`) dan `uploaded_by`. Akibatnya, semua baris di tabel `materials` dianggap tampil ke mahasiswa.
   - **Tindakan Penyesuaian**: Menambahkan kolom `status` (default: `'published'`) dan `uploaded_by` pada `materials`. Di `MaterialService`, ditambahkan scope `where('status', 'published')` agar materi bertipe `draft` yang dibuat dosen tidak terlihat oleh mahasiswa hingga dipublikasikan.
3. **Modul Pengumpulan & Penilaian (`app/Http/Controllers/AcademicActionController.php`)**:
   - Mahasiswa mengunggah submission ke `submissions` via method `submitAssignment()`.
   - Dosen menilai via method `gradeSubmission()`.
   - **Temuan Audit**: Pada implementasi Tahap K sebelumnya, validasi otorisasi telah ditambahkan agar hanya dosen/admin yang dapat menilai (`abort(403)` jika bukan dosen). Namun, belum ada pencatatan kolom `status_waktu` (`tepat_waktu` / `terlambat`) yang dihitung otomatis di sisi server.
   - **Tindakan Penyesuaian**: Menambahkan kolom `status_waktu` di `submissions` dan menghitung otomatis selisih `submitted_at` terhadap `deadline_at` saat mahasiswa mengumpulkan berkas.
4. **Modul Pengumuman**:
   - Di `GradeService.php`, pengumuman nilai sebelumnya berupa array statis/hardcoded.
   - **Tindakan Penyesuaian**: Membuat tabel `announcements` (pengumuman) yang mendukung pengumuman tingkat kelas (`class_id != null`) dan pengumuman tingkat akademik kampus (`class_id == null`). Dosen dapat menerbitkan pengumuman yang otomatis tampil di dashboard dosen dan mahasiswa kelas bersangkutan.
5. **Modul Notifikasi**:
   - Sudah menggunakan tabel standar Laravel `notifications` (Database Channel).
   - Ketika dosen menerbitkan tugas/materi, sistem mengirimkan notifikasi database ke seluruh mahasiswa di kelas tsb. Ketika mahasiswa mengumpulkan tugas, sistem mengirimkan notifikasi ke dosen pengampu kelas.

---

## 3. PEMETAAN SKEMA DATABASE (EXISTING -> TARGET BATCH 1)

Database `arunika` sudah memiliki skema awal yang solid dari Fase Mahasiswa. Berikut pemetaan persis antara tabel yang ada dengan kebutuhan target:

| Target Konsep | Tabel Eksisting | Status | Tindakan Migrasi Batch 1 |
| :--- | :--- | :--- | :--- |
| **users** | `users` | Eksisting | Tambah kolom: `gelar` (string, nullable), `prodi` (string, nullable), `jabatan` (string, nullable). `role`, `identifier`, `avatar_path` sudah ada. |
| **semesters** | `terms` | Eksisting | Gunakan tabel `terms`. Field `name`, `academic_year`, `semester_type`, `start_date`, `end_date`, `is_active` sudah lengkap. Buat accessor/model alias jika diperlukan. |
| **mata_kuliah**| `courses` | Eksisting | Tambah kolom: `cover_path` (string, nullable). Field `code`, `name`, `credits`, `semester`, `description`, `syllabus` sudah ada. |
| **kelas** | `classes` | Eksisting | Tambah kolom: `status` (enum: `aktif`, `selesai`, default: `aktif`). Field `course_id`, `term_id`, `lecturer_id`, `section`, `room`, `schedule_day`, `start_time`, `end_time`, `capacity` sudah ada. |
| **enrollments**| `enrollments` | Eksisting | Sudah lengkap (`class_id`, `user_id`, `status`, `final_score`, `letter_grade`, unique constraint). |
| **pertemuan** | `meetings` | Eksisting | Sudah lengkap (`class_id`, `week`, `title`, `description`, `date`, `start_time`, `end_time`, `mode`, `is_published`). |
| **materi** | `materials` | Eksisting | Tambah kolom: `class_id` (foreignId ke `classes`, nullable), `status` (enum: `draft`, `published`, default: `published`), `uploaded_by` (foreignId ke `users`, nullable). |
| **assessments**| `assignments` | Eksisting | Perluas tabel `assignments`: tambah `dosen_id` (foreignId ke `users`, nullable), `jenis` (enum: `tugas`, `kuis`, `uts`, `uas`, default: `tugas`), `mode` (enum: `individu`, `kelompok`, default: `individu`), `metode` (enum: `unggah_berkas`, `input_manual`, default: `unggah_berkas`), `lampiran_path` (string, nullable), `mulai_at` (dateTime, nullable), `publish_at` (dateTime, nullable), `status` (enum: `draft`, `terjadwal`, `dipublikasikan`, `arsip`, default: `dipublikasikan`). Catatan: `due_at` berfungsi sebagai `deadline_at`, `max_score` sebagai `nilai_maks`. |
| **pengumpulan**| `submissions` | Eksisting | Tambah kolom: `status_waktu` (enum: `tepat_waktu`, `terlambat`, nullable). Tambah unique index `['assignment_id', 'user_id']` jika belum ada. Field `score`, `feedback`, `graded_at`, `graded_by` sudah ada. |
| **komponen_penilaian** | *Belum ada* | **Baru** | Buat tabel `course_assessment_components` / `komponen_penilaian` (`id`, `class_id`, `jenis`, `bobot`, timestamps). Validasi total bobot per kelas = 100%. |
| **pengumuman** | *Belum ada* | **Baru** | Buat tabel `announcements` (`id`, `ulid`, `class_id` nullable, `author_id`, `title`, `content`, `published_at`, timestamps). |
| **notifications** | `notifications` | Eksisting | Gunakan tabel notifikasi bawaan Laravel yang sudah aktif. |

---

## 4. DESIGN SYSTEM & SHELL LAYOUT DOSEN (`layouts.dosen`)

Layout dosen mengadopsi standar visual Arunika dengan struktur shell:

### A. Sidebar (Lebar 234px, Navy Tua `#1A2E5E`)
- **Header**: Logo gedung berpilar putih + teks "Arunika" + subteks "LEARNING SYSTEM".
- **Menu Utama (9 Menu Berurutan)**:
  1. `Beranda` (`dosen.dashboard`) — Icon: `house`
  2. `Kelas Saya` (`dosen.kelas.index`) — Icon: `users`
  3. `Materi Pembelajaran` (`dosen.materi.index`) — Icon: `folder-open`
  4. `Tugas & Ujian` (`dosen.tugas.index`) — Icon: `clipboard-list`
     - Submenu (accordion):
       - `Semua Assessment` (`dosen.tugas.index`)
       - `Buat Tugas / Ujian` (`dosen.tugas.create`)
       - `Bank Soal` (badge / status nonaktif: "Backlog")
  5. `Penilaian` (`dosen.penilaian.index`) — Icon: `clipboard-check` + **Badge Dinamis Merah** (berisi jumlah pengumpulan yang belum dinilai, misal `14`).
  6. `Presensi` (`dosen.presensi.index`) — Icon: `calendar-check` *(Placeholder Batch 2)*
  7. `Buku Nilai` (`dosen.buku-nilai.index`) — Icon: `bar-chart-3` *(Placeholder Batch 2)*
  8. `Diskusi` (`dosen.diskusi.index`) — Icon: `message-circle` *(Placeholder Batch 2)*
  9. `Pengaturan` (`dosen.pengaturan.edit`) — Icon: `settings` *(Placeholder Batch 2)*
- **Footer Sidebar**:
  - Box Kampus: Icon landmark + "Universitas Nusantara Indonesia" / "Maju Bersama Ilmu".
  - Profil Ringkas: Foto avatar dosen + Nama "Dr. Budi Rahardjo" + Label "Dosen" + chevron dropdown.

### B. Topbar (Latar Putih `#FFFFFF`, Border Bawah Tipis `#E2E8F0`)
- **Breadcrumb Dinamis**: Format `Beranda > [Nama Halaman]`.
- **Kolom Pencarian Global**: Input berikon kaca pembesar: *"Cari materi, tugas, atau mata kuliah..."* yang mengarah ke live filter / modal pencarian `/dosen/cari`.
- **Lonceng Notifikasi**: Icon `bell` dengan badge merah jumlah unread notifikasi (misal: `2`), dilengkapi popover dropdown 5 notifikasi terbaru dan aksi "Tandai Semua Dibaca".
- **Profil User Dropdown**: Avatar dosen + Nama lengkap + menu dropdown: "Profil Saya", "Pengaturan Akun", dan "Keluar" (POST `/logout`).

### C. Komponen UI Reusable
- `x-dosen.stat-card`: Kartu metrik dengan angka tebal, label, dan ikon lingkaran berwarna lembut.
- `x-dosen.status-badge`: Badge status kapsul (`Dipublikasikan`, `Draft`, `Terjadwal`, `Arsip`, `Tepat Waktu`, `Terlambat`, `Menunggu`, `Sudah Dinilai`).
- `x-dosen.donut-chart`: Komponen chart donut pure SVG/CSS responsive tanpa dependensi berat luar, dengan persentase di tengah dan legenda di samping.
- `x-dosen.data-table`: Wrapper tabel dengan border tipis, header abu-abu muda, hover effect per baris, dan pagination.
- `x-dosen.empty-state`: Tampilan konsisten jika data kosong atau untuk menu placeholder Batch 2.

---

## 5. RANCANGAN HALAMAN YANG BELUM DIDESAIN (UNSEEN PAGES)

Agar arsitektur Batch 1 tuntas dan tidak ada tautan buntu, berikut spesifikasi tata letak untuk 4 sub-halaman yang tidak ada di tangkapan layar utama:

### 1. Halaman / Modal Form Buat & Edit Assessment (`/dosen/tugas/buat` / `dosen.tugas.create`)
- **Tujuan**: Memungkinkan dosen membuat tugas baru, kuis, atau jadwal ujian.
- **Tata Letak**:
  - **Breadcrumb**: `Beranda > Tugas & Ujian > Buat Assessment Baru`.
  - **Header**: Judul "Buat Tugas / Ujian Baru" + Subtitle "Tentukan kelas, jenis assessment, instruksi, dan tenggat pengumpulan."
  - **Grid 2 Kolom (70% Form Kiri, 30% Panel Panduan Kanan)**:
    - *Kolom Kiri (Form)*:
      1. Pilihan Mata Kuliah & Kelas (Dropdown select kelas dosen).
      2. Jenis Assessment (Pills: Tugas, Kuis, UTS, UAS).
      3. Mode (Individu vs Kelompok) & Metode Pengumpulan (Unggah Berkas vs Input Nilai Manual).
      4. Judul Assessment & Deskripsi / Instruksi Soal (Textarea dengan preview markdown).
      5. Unggah Berkas Lampiran / Soal Panduan (Dropzone file PDF/ZIP/DOCX maks 10MB).
      6. Pengaturan Waktu: Tanggal Mulai, Tenggat Waktu (Deadline), dan Tanggal Publikasi (Publish At - untuk status Terjadwal).
      7. Nilai Maksimum (Default: 100) & Opsi "Izinkan Pengumpulan Terlambat" (Checkbox toggle).
    - *Kolom Kanan (Informasi & Aksi)*:
      - Kartu Ringkasan Status: Pilihan Simpan sebagai "Draft", "Jadwalkan", atau "Publikasikan Sekarang".
      - Tombol Aksi: `Batal` (sekunder) dan `Simpan Assessment` (primer navy).

### 2. Halaman Menilai Submission per Assessment (`/dosen/penilaian/{assessment}` / `dosen.penilaian.show`)
- **Tujuan**: Dosen memeriksa berkas mahasiswa, memasukkan skor, memberi feedback, dan merilis nilai.
- **Tata Letak**:
  - **Breadcrumb**: `Beranda > Penilaian > [Judul Tugas] - [Kelas]`.
  - **Header Informasi Assessment**:
    - Judul Tugas, Kelas, Deadline, Nilai Maksimal, dan Progress Penilaian (misal: "18 / 32 Mahasiswa Sudah Dinilai").
    - Tombol Aksi Cepat: `Isi Nilai 0 untuk Mahasiswa Tanpa Submission` (dengan konfirmasi modal) dan `Kembali ke Daftar Penilaian`.
  - **Tabel Daftar Mahasiswa & Submission**:
    - Kolom: `No`, `NIM`, `Nama Mahasiswa`, `Waktu Submit`, `Status Keterlambatan` (Badge: Tepat Waktu / Terlambat / Belum Mengumpulkan), `Berkas Submission` (Link download berkas via route private ber-Policy), `Input Nilai` (Input number 0..100), `Feedback Dosen` (Input text singkat / modal), `Aksi` (Tombol Simpan Nilai).
  - **Drawer / Modal Penilaian Cepat**:
    - Mode fokus: Menampilkan submission mahasiswa secara berurutan dengan tombol "Simpan & Lanjut ke Mahasiswa Berikutnya" tanpa perlu me-reload halaman berulang-ulang.

### 3. Modal / Form Tambah Materi Pembelajaran (`dosen.materi.store`)
- **Tujuan**: Dosen mengunggah materi modul/slide/video untuk pertemuan tertentu.
- **Tata Letak (Modal Responsif)**:
  - Dropdown Kelas (misal: `IF2101 - Kelas A`).
  - Dropdown Pertemuan Perkuliahan (Pertemuan 1 s/d 16).
  - Judul Materi & Deskripsi Singkat.
  - Tipe Materi (Tabs/Radio: PDF, PPT, Video, Tautan URL, Dokumen, Zip).
  - File Dropzone (jika berkas fisik) atau Input URL Video/Tautan.
  - Status Publikasi: Radio pill `Published (Langsung Aktif)` atau `Draft (Disimpan Sementara)`.
  - Tombol: `Batal` dan `Simpan Materi`.

### 4. Halaman Detail Kelas Dosen (`/dosen/kelas/{class}` / `dosen.kelas.show`)
- **Tujuan**: Dosen melihat detail lengkap sebuah kelas yang diampunya.
- **Tata Letak**:
  - **Header**: Nama Mata Kuliah, Kode, Kelas, Jadwal, Ruangan, SKS, dan Total Mahasiswa.
  - **Tab Navigasi**:
    1. *Tab Silabus & Pertemuan*: Timeline 16 pertemuan, status publish pertemuan, daftar materi & assessment di tiap pertemuan.
    2. *Tab Daftar Mahasiswa*: Tabel 32 mahasiswa terdaftar (Avatar, NIM, Nama, Angkatan, Kehadiran %, Nilai Sementara, Status KRS).
    3. *Tab Komponen Penilaian*: Tabel bobot Tugas (30%), Kuis (20%), UTS (25%), UAS (25%) dengan validasi total 100%.

---

## 6. ATURAN STATUS TURUNAN & FORMULA METRIK

Semua angka dan status dihitung secara deterministik dari database MySQL:

### A. Status Assessment (Tugas & Ujian)
- **Draft**: `status == 'draft'`. Tidak terlihat sama sekali oleh mahasiswa.
- **Terjadwal**: `status == 'terjadwal'` ATAU (`status == 'dipublikasikan'` AND `publish_at > APP_NOW`). Belum terlihat oleh mahasiswa. Scheduler tiap menit akan mempublikasikannya otomatis saat `APP_NOW >= publish_at`.
- **Dipublikasikan (Aktif)**: `status == 'dipublikasikan'` AND (`publish_at IS NULL` OR `publish_at <= APP_NOW`). Terlihat oleh mahasiswa kelas bersangkutan.
- **Arsip**: `status == 'arsip'`. Dipindahkan dari daftar aktif.

### B. Status Penilaian (Halaman Penilaian)
- **Draft**: Assessment belum dipublikasikan.
- **Terjadwal**: Assessment belum dimulai.
- **Menunggu**: Ada berkas pengumpulan mahasiswa dengan `score IS NULL` (perlu dinilai) ATAU batas deadline sudah lewat dan masih ada tugas yang belum dinilai.
- **Sudah Dinilai**: Seluruh submission mahasiswa yang masuk telah diberi nilai (`score IS NOT NULL`).

### C. Status Keterlambatan Pengumpulan (Server-Computed)
- Saat mahasiswa men-submit tugas pada waktu `submitted_at`:
  - Jika `submitted_at <= deadline_at` -> `status_waktu = 'tepat_waktu'` (Badge Hijau).
  - Jika `submitted_at > deadline_at` -> `status_waktu = 'terlambat'` (Badge Oranye/Kuning).
- Klien dilarang memanipulasi `submitted_at` atau `status_waktu`.

### D. Definisi 4 Stat Card Beranda Dosen
1. **Kelas Diampu**:
   ```sql
   COUNT(*) FROM classes WHERE lecturer_id = :dosen_id AND term_id = :active_term_id AND status = 'aktif'
   ```
2. **Total Mahasiswa**:
   ```sql
   COUNT(DISTINCT user_id) FROM enrollments WHERE class_id IN (kelas-kelas aktif dosen)
   ```
3. **Tugas Aktif**:
   ```sql
   COUNT(*) FROM assignments WHERE class_id IN (kelas-kelas aktif dosen) AND jenis = 'tugas' AND status = 'dipublikasikan' AND due_at >= APP_NOW
   ```
4. **Perlu Dinilai**:
   ```sql
   COUNT(*) FROM submissions s 
   JOIN assignments a ON s.assignment_id = a.id 
   WHERE a.class_id IN (kelas-kelas aktif dosen) AND s.score IS NULL
   ```
   *Angka ini identik persis dengan badge merah pada menu Penilaian di sidebar.*

---

## 7. STRATEGI SEEDER KANONIKAL & DATA UJI

Seeder dirancang bersifat **idempotent** (`updateOrCreate` / `firstOrCreate`) dengan data tetap sesuai spesifikasi instruksi:

### A. Entitas Pengguna & Hak Akses
1. **Dosen Utama (Dr. Budi Rahardjo)**:
   - Email: `dosen@arunika.test` (atau `budi.raharjo@unus.ac.id`)
   - Password: `password`
   - NIDN: `0021037502`
   - Gelar: `Dr. Budi Rahardjo, S.T., M.Kom.`
   - Prodi: `S1 Teknik Informatika`
   - Role: `lecturer`
2. **Dosen Pembanding (Dr. Rudi Rahardjo)**:
   - Email: `dosen2@arunika.test` (password: `password`)
   - Memiliki 1 kelas sendiri untuk memvalidasi Policy isolasi data.
3. **Mahasiswa (90 Mahasiswa Unik)**:
   - Mahasiswa kanonikal eksisting (`Arya Pratama` - NIM `23060101024`) terdaftar di IF2101 Kelas A.
   - Sesuai desain:
     - `Ahmad Fauzi`, `Siti Nurhaliza`, `Fajar Nugroho` di IF2101 Kelas A.
     - `Dewi Lestari` di IF2101 Kelas B.
     - `Rizky Pratama` di IF2103 Kelas A.
     - Serta rekan mahasiswa lainnya hingga total 90 mahasiswa unik tanpa duplikasi kelas.

### B. Kelas Perkuliahan (Semester Ganjil 2026/2027, Hari Acuan: Senin, 19 Okt 2026)
1. **IF2101 - Pemrograman Berorientasi Objek & Desain Pola**:
   - **Kelas A**: 32 mahasiswa, Senin 08:00 - 10:00, Ruang Lab 307 (Hari ini: Pertemuan 5).
   - **Kelas B**: 30 mahasiswa, Senin 10:00 - 12:00, Ruang Lab 308 (Hari ini: Pertemuan 5).
   - Komponen Nilai: Tugas 30%, Kuis 20%, UTS 25%, UAS 25%.
2. **IF2103 - Rancang Bangun Mini Core Banking System (OOP)**:
   - **Kelas A**: 28 mahasiswa, Senin 13:00 - 15:00, Ruang Lab 201 (Hari ini: Pertemuan 4).
   - Komponen Nilai: Tugas 40%, Kuis 10%, UTS 25%, UAS 25%.

### C. 12 Assessment & 14 Pengumpulan Perlu Dinilai
- Di-seed 12 assessment dengan ragam jenis (`tugas`, `kuis`, `uts`, `uas`) dan status (`dipublikasikan`, `draft`, `terjadwal`, `arsip`).
- Wajib ada:
  1. `Tugas 3 - Polimorfisme` (IF2101 Kelas A, Deadline: 21 Okt 2026 23:59 -> Status: 3 hari lagi).
  2. `Tugas 2 - OOP` (IF2101 Kelas B, Deadline: 23 Okt 2026 23:59 -> Status: 5 hari lagi).
  3. `Tugas 1 - Analisis Sistem` (IF2103 Kelas A, Deadline: 26 Okt 2026 23:59 -> Status: 8 hari lagi).
  4. `Tugas 2 - OOP` dan `Tugas 1 - Konfigurasi IP` di IF2101 Kelas A.
- Di-seed **tepat 14 pengumpulan belum dinilai** (`score = null`), dengan 5 teratas persis tangkapan layar Beranda:
  1. Ahmad Fauzi — IF2101 Kelas A — Tugas 2 - OOP — 18 Okt 2026, 21:34 — `Terlambat`
  2. Siti Nurhaliza — IF2101 Kelas A — Tugas 2 - OOP — 18 Okt 2026, 19:12 — `Tepat Waktu`
  3. Rizky Pratama — IF2103 Kelas A — Tugas 1 - Analisis Sistem — 18 Okt 2026, 16:20 — `Tepat Waktu`
  4. Dewi Lestari — IF2101 Kelas B — Tugas 2 - OOP — 17 Okt 2026, 23:17 — `Terlambat`
  5. Fajar Nugroho — IF2101 Kelas A — Tugas 1 - Konfigurasi IP — 17 Okt 2026, 20:05 — `Tepat Waktu`

### D. Materi Pembelajaran (7 Materi di IF2101 Kelas A)
1. Pertemuan 1: `Pengantar OOP dan Konsep Dasar` (PDF, 12 Okt, Published)
2. Pertemuan 1: `Video Pembelajaran - Class & Object` (Tautan, 12 Okt, Published)
3. Pertemuan 2: `Slide Pertemuan 2 - Inheritance` (PPT, 14 Okt, Published)
4. Pertemuan 3: `Modul Praktikum - Polimorfisme` (PDF, 16 Okt, Published)
5. Pertemuan 4: `Video - Interface dan Abstract Class` (Video, 17 Okt, Published)
6. Pertemuan 5: `Dataset Latihan` (Zip, 18 Okt, **Draft**)
7. Pertemuan 6: `Materi Ulangan Harian` (PDF, 19 Okt, Published)

---

## 8. RENCANA PELAKSANAAN FASE DEMI FASE (BATCH 1)

```
[FASE 1: Analisis + Rencana] <--- SEKARANG
       |
       v
[FASE 2: Fondasi Data (Migration, Model, Policy, Seeder, Scheduler, Placeholder)]
       |
       v
[FASE 3: Shell Dosen + Beranda + Kelas Saya (+ Detail Kelas)]
       |
       v
[FASE 4: Tugas & Ujian (CRUD, Publish, Jadwal, Arsip) + Interkoneksi Mahasiswa]
       |
       v
[FASE 5: Pengumpulan Mahasiswa + Penilaian Dosen + Badge Dinamis + Notifikasi]
       |
       v
[FASE 6: Materi Pembelajaran + Pengumuman Kelas/Akademik]
       |
       v
[FASE 7: QA Lengkap Batch 1 + Visual Freeze Check + Kesiapan Batch 2]
```

### Rincian Tiap Fase Mendatang:
- **FASE 2**:
  - Buat migration penambahan kolom `users`, `materials`, `assignments`, `classes`, dan tabel baru `course_assessment_components`, `announcements`.
  - Update model Eloquent (`User`, `AcademicClass`, `Assignment`, `Submission`, `Material`, `Announcement`, `CourseAssessmentComponent`).
  - Buat Policy otorisasi (`AcademicClassPolicy`, `AssignmentPolicy`, `SubmissionPolicy`, `MaterialPolicy`).
  - Buat middleware `role:dosen` / `EnsureUserIsLecturer`.
  - Konfigurasi `APP_FAKE_NOW` di service provider untuk pengujian waktu deterministik (19 Okt 2026).
  - Buat seeder idempotent `LecturerBatch1Seeder`.
  - Buat 4 route placeholder Batch 2 (`dosen.presensi.index`, `dosen.buku-nilai.index`, `dosen.diskusi.index`, `dosen.pengaturan.edit`).
- **FASE 3**:
  - Buat master layout `resources/views/layouts/dosen.blade.php`.
  - Implementasikan halaman Beranda Dosen (`pages/dosen/dashboard.blade.php`).
  - Implementasikan halaman Kelas Saya (`pages/dosen/kelas/index.blade.php`) dan halaman detail kelas (`pages/dosen/kelas/show.blade.php`).
- **FASE 4**:
  - Implementasikan halaman Tugas & Ujian Dosen (`pages/dosen/tugas/index.blade.php`).
  - Implementasikan form Buat & Edit Assessment (`pages/dosen/tugas/create.blade.php`).
  - Pasang command scheduler untuk auto-publish tugas terjadwal.
  - Hubungkan ke halaman tugas mahasiswa agar membaca assessment baru.
- **FASE 5**:
  - Sempurnakan alur pengumpulan tugas oleh mahasiswa dengan perhitungan `status_waktu`.
  - Implementasikan halaman Penilaian Dosen (`pages/dosen/penilaian/index.blade.php`).
  - Implementasikan halaman kerja penilaian (`pages/dosen/penilaian/grade.blade.php`).
  - Pasang notifikasi database dan badge dinamis di sidebar.
- **FASE 6**:
  - Implementasikan halaman Materi Pembelajaran Dosen (`pages/dosen/materi/index.blade.php`) beserta form tambah materi.
  - Hubungkan modul pengumuman kelas dan akademik ke beranda serta halaman mahasiswa.
- **FASE 7**:
  - Eksekusi feature test otomatis (PHPUnit).
  - Audit N+1 query dengan eager loading.
  - Verifikasi kesesuaian visual dengan 5 tangkapan layar.
  - Perbarui dokumen `docs/dosen/PROGRESS.md`.

---

## 9. DAFTAR FILE YANG AKAN DIBUAT & DIUBAH (PREVIEW)

### File Baru yang Akan Dibuat:
1. `docs/dosen/PLAN.md` (Dokumen ini)
2. `docs/dosen/PROGRESS.md` (Catatan pelacakan progres per fase)
3. `database/migrations/2026_10_17_000001_extend_tables_for_lecturer_dashboard.php`
4. `database/migrations/2026_10_17_000002_create_announcements_and_components_tables.php`
5. `app/Models/Announcement.php`
6. `app/Models/CourseAssessmentComponent.php`
7. `app/Policies/LecturerAcademicPolicy.php`
8. `app/Http/Middleware/EnsureUserIsLecturer.php`
9. `app/Http/Controllers/Dosen/DosenDashboardController.php`
10. `app/Http/Controllers/Dosen/DosenClassController.php`
11. `app/Http/Controllers/Dosen/DosenMaterialController.php`
12. `app/Http/Controllers/Dosen/DosenAssignmentController.php`
13. `app/Http/Controllers/Dosen/DosenGradeController.php`
14. `app/Http/Controllers/Dosen/DosenSearchController.php`
15. `app/Services/Dosen/DosenDashboardService.php`
16. `app/Services/Dosen/DosenClassService.php`
17. `app/Services/Dosen/DosenAssignmentService.php`
18. `app/Services/Dosen/DosenGradeService.php`
19. `resources/views/layouts/dosen.blade.php`
20. `resources/views/pages/dosen/dashboard.blade.php`
21. `resources/views/pages/dosen/kelas/index.blade.php`
22. `resources/views/pages/dosen/kelas/show.blade.php`
23. `resources/views/pages/dosen/materi/index.blade.php`
24. `resources/views/pages/dosen/tugas/index.blade.php`
25. `resources/views/pages/dosen/tugas/create.blade.php`
26. `resources/views/pages/dosen/penilaian/index.blade.php`
27. `resources/views/pages/dosen/penilaian/grade.blade.php`
28. `resources/views/pages/dosen/placeholder.blade.php`
29. `database/seeders/LecturerBatch1Seeder.php`
30. `tests/Feature/LecturerDashboardFlowTest.php`

### File Eksisting yang Akan Diperluas (Tanpa Merusak Data Mahasiswa):
1. `app/Models/User.php`: Relasi kelas yang diampu (`classesTaught`), relasi pengumuman, dan helper role `isLecturer()`.
2. `app/Models/AcademicClass.php`: Relasi ke assessment components dan announcements.
3. `app/Models/Assignment.php`: Relasi dosen pengampu, casting field baru (`publish_at`, `mulai_at`), scope `published()`.
4. `app/Models/Submission.php`: Casting `status_waktu`.
5. `app/Models/Material.php`: Scope `published()`, relasi uploader.
6. `routes/web.php`: Penambahan grup route `/dosen` (middleware `auth` + `role:dosen`).
7. `app/Http/Controllers/DemoAuthController.php`: Penyesuaian redirect role dosen menuju `route('dosen.dashboard')`.
8. `app/Services/MaterialService.php`: Penambahan filter `where('status', 'published')` saat mengambil materi untuk mahasiswa.
9. `app/Services/AssignmentService.php`: Penambahan filter `where('status', 'dipublikasikan')` dan waktu `publish_at <= now()`.

---

## 10. KESIMPULAN FASE 1
Dokumen ini menjadi acuan tunggal dan kontrak teknis bagi seluruh fase pengerjaan Batch 1. Tidak ada modifikasi kode aplikasi atau migrasi database yang dilakukan pada Fase 1. Pengerjaan implementasi teknis akan dimulai pada **Fase 2: Fondasi Data** setelah persetujuan.
