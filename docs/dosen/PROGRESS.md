# LOG PROGRESS PENGEMBANGAN DASHBOARD DOSEN (ARUNIKA LMS)
**Universitas Nusantara Indonesia**

Dokumen ini mencatat keputusan arsitektural, skema tabel, komponen UI, route, dan status perkembangan proyek dari fase ke fase.

---

## 1. STATUS UMUM FASE

| Fase | Deskripsi | Status | Catatan / Tanggal Selesai |
| :--- | :--- | :--- | :--- |
| **Fase 1** | Analisis Repo + Audit Mahasiswa + Perencanaan Batch 1 | **SELESAI** | Selesai pada 17 Okt 2026. Dokumen `PLAN.md` telah disahkan. |
| **Fase 2** | Fondasi Data: Migration, Model, Policy, Seeder, Scheduler, Route Placeholder | **SELESAI** | Selesai pada 17 Okt 2026. 2 migration ran, 10 feature tests pass, 100% Pint. |
| **Fase 3** | Layout Dosen (`layouts.dosen`) + Beranda + Kelas Saya (+ Detail Kelas) | **SELESAI** | Selesai pada 17 Okt 2026. Layout + Beranda + Kelas Saya + Detail Kelas + Policy Isolation + Tests 100% Pass. |
| **Fase 4** | Modul Tugas & Ujian + Integrasi Tampil di Mahasiswa | **SELESAI** | Selesai pada 17 Okt 2026. CRUD, Donut SVG, Scheduler auto-publish, student policy & 8 feature tests pass. |
| **Fase 5** | Modul Pengumpulan + Penilaian + Notifikasi + Badge Dinamis | **SELESAI** | Selesai pada 17 Okt 2026. Halaman Penilaian (Screenshot 5), Workspace Penilaian, bidirectional notifications, dynamic badge, 8 feature tests pass. |
| **Fase 6** | Modul Materi Pembelajaran + Pengumuman Kelas & Akademik | **SELESAI** | Selesai pada 17 Okt 2026. Halaman Materi (16 pertemuan), Modal Upload/Edit, Toggle Publish/Draft, Pengumuman Kelas, 9 feature tests pass. |
| **Fase 7** | QA Akhir Batch 1: Testing, Cek N+1, Visual Freeze Verification | Menunggu | - |

---

## 2. KEPUTUSAN TEKNIS UTAMA (FASE 1)

1. **Database Source of Truth**:
   - Tetap menggunakan database MySQL `arunika` yang sudah ada (`DB_DATABASE=arunika`).
   - Tidak ada penghapusan data eksisting. Hanya menggunakan `php artisan migrate` dengan migration penambahan kolom / tabel baru.
2. **Pemetaan Skema**:
   - `semesters` dipetakan ke tabel `terms` eksisting.
   - `mata_kuliah` dipetakan ke tabel `courses` eksisting.
   - `kelas` dipetakan ke tabel `classes` eksisting (ditambahkan kolom `status`).
   - `materi` dipetakan ke tabel `materials` eksisting (ditambahkan kolom `status`, `uploaded_by`, `class_id`).
   - `assessments` dipetakan ke tabel `assignments` eksisting (diperluas dengan kolom `jenis`, `mode`, `metode`, `lampiran_path`, `mulai_at`, `publish_at`, `status`).
   - `pengumpulan` dipetakan ke tabel `submissions` eksisting (ditambahkan kolom `status_waktu`).
   - Tabel baru yang dibuat: `course_assessment_components` (komponen penilaian kelas) dan `announcements` (pengumuman kelas & akademik).
3. **Pemisahan Layout**:
   - Dibuat layout baru `resources/views/layouts/dosen.blade.php` dengan palet warna, sidebar 234px, dan topbar yang berbagi token CSS dengan layout mahasiswa, namun dirancang khusus untuk interaksi dosen.
4. **Isolasi Akses & Keamanan**:
   - Akses dosen dibatasi oleh `lecturer_id` pada kelas. Dosen hanya dapat mengelola kelas dan menilai mahasiswa di kelasnya sendiri.
   - Mahasiswa hanya dapat melihat tugas/materi berstatus `dipublikasikan` pada kelas tempat ia terdaftar (`enrollments`).
5. **Tanggal Acuan Demo**:
   - Senin, 19 Oktober 2026 jam 09:00 WIB didukung via `APP_FAKE_NOW` di lingkungan lokal/pengujian.

---

## 3. PETA ROUTE DOSEN BATCH 1

| Method | URI | Route Name | Keterangan |
| :--- | :--- | :--- | :--- |
| GET | `/dosen` | `dosen.dashboard` | Beranda Dosen |
| GET | `/dosen/kelas` | `dosen.kelas.index` | Daftar Kelas Saya |
| GET | `/dosen/kelas/{class}` | `dosen.kelas.show` | Detail Kelas (Mahasiswa & Silabus) |
| GET | `/dosen/materi` | `dosen.materi.index` | Daftar Materi Pembelajaran |
| POST | `/dosen/materi` | `dosen.materi.store` | Simpan Materi Baru |
| PUT | `/dosen/materi/{material}` | `dosen.materi.update` | Edit Materi |
| DELETE | `/dosen/materi/{material}` | `dosen.materi.destroy` | Hapus Materi |
| GET | `/dosen/tugas` | `dosen.tugas.index` | Daftar Assessment & Status Donut |
| GET | `/dosen/tugas/buat` | `dosen.tugas.create` | Form Buat Tugas / Ujian Baru |
| POST | `/dosen/tugas` | `dosen.tugas.store` | Simpan Assessment Baru |
| GET | `/dosen/penilaian` | `dosen.penilaian.index` | Daftar Penilaian & Ringkasan |
| GET | `/dosen/penilaian/{assignment}` | `dosen.penilaian.show` | Halaman Kerja Penilaian Mahasiswa |
| POST | `/dosen/penilaian/{submission}/grade` | `dosen.penilaian.grade` | Beri Nilai & Feedback |
| GET | `/dosen/cari` | `dosen.search` | Live Search Topbar Dosen |
| GET | `/dosen/presensi` | `dosen.presensi.index` | *Placeholder Batch 2* |
| GET | `/dosen/buku-nilai` | `dosen.buku-nilai.index` | *Placeholder Batch 2* |
| GET | `/dosen/diskusi` | `dosen.diskusi.index` | *Placeholder Batch 2* |
| GET | `/dosen/pengaturan` | `dosen.pengaturan.edit` | *Placeholder Batch 2* |

---

## 4. DATA SEEDER KANONIKAL BATCH 1

- **Dosen Utama**: Dr. Budi Rahardjo, S.T., M.Kom. (`dosen@arunika.test` / password `password`).
- **Dosen Pembanding**: Dr. Rudi Rahardjo, S.T., M.Kom. (`dosen2@arunika.test` / password `password`).
- **Kelas Aktif (3 Kelas, 90 Mahasiswa Unik)**:
  - IF2101 Kelas A (32 mahasiswa, Senin 08:00 - 10:00, Lab 307)
  - IF2101 Kelas B (30 mahasiswa, Senin 10:00 - 12:00, Lab 308)
  - IF2103 Kelas A (28 mahasiswa, Senin 13:00 - 15:00, Lab 201)
- **Status Antrean Penilaian**:
  - Tepat 14 pengumpulan belum dinilai (`score IS NULL`), badge sidebar = 14.
  - 5 pengumpulan teratas di Beranda identik dengan desain acuan.

---

## 5. HASIL VERIFIKASI FASE 2

- **Migration**:
  - `2026_10_17_000001_extend_tables_for_lecturer_dashboard`: Ran successfully (perluasan kolom `users`, `courses`, `classes`, `materials`, `assignments`, `submissions`).
  - `2026_10_17_000002_create_announcements_and_components_tables`: Ran successfully (tabel `course_assessment_components` & `announcements`).
- **Seeder**:
  - `LecturerBatch1Seeder`: Sukses di-seed tanpa merusak data mahasiswa eksisting.
- **Testing**:
  - `tests/Feature/LecturerFoundationTest.php`: 10 passed (28 assertions).
  - `tests/Feature/EndToEndStudentFlowTest.php`: 2 passed (40 assertions) — 0 regresi mahasiswa.
- **Code Style**:
  - Laravel Pint: Passed (100% PSR-12).

---

## 6. HASIL VERIFIKASI FASE 3

- **Layout Dosen (`resources/views/layouts/dosen.blade.php`)**:
  - Sidebar 234px berlatar Navy Academic (`#1A2E5E`), logo Arunika inverse, 9 menu urut sesuai spesifikasi.
  - Badge dinamis merah `14` pada menu Penilaian (sinkron deterministik dari `submissions` dengan `score IS NULL`).
  - Topbar putih bersih dengan live search bar, breadcrumb bertingkat, popover notifikasi riil database (2 unread untuk Dr. Budi Rahardjo), dan menu profil dropdown.
- **Komponen UI Dosen Reusable (`resources/views/components/dosen/`)**:
  - `stat-card.blade.php`: Kartu metrik dengan nilai tebal, label, unit, badge opsional, dan ikon.
  - `status-badge.blade.php`: Kapsul badge terstandardisasi (`Tepat Waktu`, `Terlambat`, `Dipublikasikan`, `Draft`, `Terjadwal`, `Aktif`, `Selesai`).
  - `data-table.blade.php`: Wrapper tabel berbingkai halus, header abu-abu muda, dan alternating hover.
  - `empty-state.blade.php`: Tampilan konsisten saat data kosong dengan ikon dan tombol aksi.
- **Beranda Dosen (`pages/dosen/dashboard.blade.php`)**:
  - Banner selamat datang dengan pill prodi (`S1 Teknik Informatika`), semester (`Semester Ganjil 2026/2027`), dan tanggal acuan (`Senin, 19 Oktober 2026`), plus ilustrasi kampus (`campus-hero.jpg`).
  - 4 Stat Cards: `Kelas Diampu (3)`, `Total Mahasiswa (90)`, `Tugas Aktif (5)`, `Perlu Dinilai (14)` dengan badge merah 14.
  - Jadwal Mengajar Hari Ini: 3 kelas Senin dengan badge pertemuan (`Pertemuan 5` dan `Pertemuan 4`).
  - Pengumpulan Terbaru: 5 mahasiswa teratas (Ahmad Fauzi, Siti Nurhaliza, Rizky Pratama, Dewi Lestari, Fajar Nugroho) dengan badge status waktu (`Terlambat` & `Tepat Waktu`).
  - Sidebar kanan: Pengumuman Terbaru kelas, Tugas dengan Deadline Terdekat (tenggat `3 hari lagi`, `5 hari lagi`, `8 hari lagi`), dan Pengumuman Akademik.
- **Halaman Kelas Saya (`pages/dosen/kelas/index.blade.php`)**:
  - Filter tab status (`Semua Kelas`, `Aktif (3)`, `Selesai (0)`).
  - Pengelompokan mata kuliah: IF2101 (Kelas A: 32 mhs, progress materi 78%; Kelas B: 30 mhs, progress materi 65%) dan IF2103 (Kelas A: 28 mhs, progress materi 72%).
  - Widget samping: Jadwal Hari Ini timeline titik warna, Ringkasan Kelas 2x2 grid (Total Kelas: 3, Total Mahasiswa: 90, Kelas Aktif: 3, Kelas Selesai: 0), dan Pengumuman Terbaru.
- **Halaman Detail Kelas (`pages/dosen/kelas/show.blade.php`)**:
  - Header detail mata kuliah: Kode, Section, Status, SKS, Jadwal, Ruang, dan Mahasiswa Terdaftar.
  - Tab 1: Silabus & Pertemuan (16 pertemuan dengan badge pertemuan, topik, dan ringkasan materi/assessment).
  - Tab 2: Daftar Mahasiswa (Tabel seluruh mahasiswa terdaftar dengan avatar, NIM, nama, prodi, dan status KRS aktif).
  - Tab 3: Komponen Penilaian (Tabel bobot Tugas, Kuis, UTS, UAS dengan validasi total 100%).
- **Policy & Keamanan Multi-Dosen**:
  - `AcademicClassPolicy` memvalidasi `view` dan `manage`: Dr. Budi Rahardjo hanya dapat mengakses kelasnya sendiri. Percobaan mengakses kelas milik dosen lain menghasilkan HTTP 403 Forbidden.
- **Pengujian Otomatis (`tests/Feature/LecturerDashboardAndClassTest.php`)**:
  - 6 feature tests pass (30 assertions):
    1. `test_lecturer_can_view_dashboard_with_canonical_stats`: Passed.
    2. `test_student_cannot_access_lecturer_dashboard`: Passed (403).
    3. `test_guest_is_redirected_to_login`: Passed (Redirect).
    4. `test_lecturer_can_view_classes_list_and_filter_status`: Passed.
    5. `test_lecturer_can_view_detail_of_their_class`: Passed.
    6. `test_lecturer_cannot_view_detail_of_another_lecturer_class`: Passed (403).
  - Total pengujian kumulatif Fase 2 & Fase 3: 18 tests passed (98 assertions) tanpa ada kegagalan.
- **Code Style**:
  - `vendor/bin/pint`: 100% PSR-12 compliant.

---

## 7. HASIL VERIFIKASI FASE 4

- **Halaman Indeks Tugas & Ujian (`pages/dosen/tugas/index.blade.php`)**:
  - Sesuai dengan tangkapan layar acuan (Screenshot 4).
  - 2 Kartu Pembuatan Cepat: *Buat Tugas* (individu/kelompok, lampiran, rubrik) dan *Buat Kuis / Ujian* (bank soal, jadwal, durasi).
  - Filter Tabs (`Semua`, `Tugas`, `Kuis`, `UTS`, `UAS`) serta Dropdown Semester & Filter Mata Kuliah.
  - Tabel Daftar Assessment dengan kolom: `No`, `Judul Assessment`, `Mata Kuliah`, `Kelas`, `Jenis`, `Jadwal`, `Status`, dan tombol `Kelola` (Ubah, Publikasikan, Arsipkan, Hapus).
  - Paginasi navigasi halaman numerik `< [1] [2] >` dan teks penghitung `Menampilkan 1-10 dari 12 assessment`.
  - Widget Kanan 1 (Ringkasan Assessment): Mini live search judul dan 4 metrik tiles (`Total = 12`, `Tugas = 7`, `Kuis/Ujian = 5`, `Menunggu Publikasi = 3`).
  - Widget Kanan 2 (Status Publikasi): Pure SVG Donut Chart responsif dengan angka `12 Total` di tengah dan legenda persentase presisi (`Dipublikasikan 7 (58%)`, `Draft 2 (17%)`, `Terjadwal 1 (8%)`, `Arsip 2 (17%)`).
  - Widget Kanan 3 (Tugas & Ujian Terdekat): 4 baris jadwal tugas/kuis terdekat dengan status waktu.
- **Formulir Buat & Ubah Assessment (`pages/dosen/tugas/create.blade.php` & `edit.blade.php`)**:
  - Sesuai spesifikasi *Unseen Pages* (Sub-halaman 5.1):
    - Pilihan Kelas Dosen, Radio pill jenis (`tugas`, `kuis`, `uts`, `uas`), mode (`individu`/`kelompok`), dan metode pengumpulan (`unggah_berkas`/`input_manual`).
    - Input Judul, Instruksi (textarea), dropzone berkas panduan/soal (PDF/ZIP/DOCX maks 10MB).
    - Pengaturan waktu mulai, tenggat deadline, skor maksimum (default 100), dan toggle toleransi keterlambatan.
    - Panel kanan pilihan publikasi: `Publikasikan Sekarang` (langsung aktif), `Terjadwal` (tanggal rilis otomatis), atau `Draft`.
- **Interkoneksi Dua Arah dengan Mahasiswa**:
  - Mahasiswa yang terdaftar hanya dapat melihat assessment berstatus `dipublikasikan` pada halaman `app.assignments`.
  - Assessment berstatus `draft` atau `terjadwal` otomatis disembunyikan dari mahasiswa.
  - Percobaan mahasiswa membuka formulir submit pada tugas berstatus `draft` diblokir dengan kode HTTP 403 Forbidden via `AssignmentPolicy`.
  - Ketika dosen mempublikasikan tugas (`publishNow`), tugas langsung muncul di dashboard mahasiswa dan mahasiswa dapat mengunggah berkas pengumpulan.
  - Command scheduler `app:publish-scheduled-assessments` otomatis merilis tugas terjadwal saat waktu acuan tiba dan mengirim notifikasi database ke mahasiswa kelas bersangkutan.
- **Pengujian Otomatis (`tests/Feature/LecturerAssessmentTest.php`)**:
  - 8 feature tests pass (30 assertions):
    1. `test_lecturer_can_view_tugas_dan_ujian_index_with_canonical_stats`: Passed.
    2. `test_lecturer_can_filter_assessments_by_jenis`: Passed.
    3. `test_lecturer_can_view_create_form`: Passed.
    4. `test_lecturer_can_create_draft_and_student_cannot_see_it`: Passed (Disembunyikan + 403 pada direct URL).
    5. `test_lecturer_can_publish_assessment_and_student_can_see_it`: Passed (Muncul seketika + 200 pada direct URL).
    6. `test_scheduled_assessment_auto_published_by_artisan_command`: Passed.
    7. `test_lecturer_can_archive_assessment`: Passed.
    8. `test_lecturer_cannot_update_another_lecturer_assessment`: Passed (403 isolasi antar-dosen).
- **Code Style**:
  - `vendor/bin/pint`: 100% PSR-12 compliant.

---

## 8. HASIL VERIFIKASI FASE 5

- **Halaman Indeks Penilaian (`pages/dosen/penilaian/index.blade.php`)**:
  - Tepat sesuai dengan tangkapan layar acuan (Screenshot 5).
  - 4 Kartu Metrik Utama di Atas:
    - `Total Penilaian = 12` (↑ 2 dari semester lalu)
    - `Menunggu Penilaian = 7` (↑ 1 dari semester lalu)
    - `Sudah Dinilai = 5` (↑ 2 dari semester lalu)
    - `Terlambat = 0` (↑ 1 dari semester lalu)
  - Filter Tabs: `Semua`, `Tugas`, `Kuis`, `UTS`, `UAS` serta filter Semester, Mata Kuliah, Kelas, dan Pencarian Judul/Kode.
  - Tabel Penilaian Utama:
    - Kolom: `No`, `Judul Assessment`, `Jenis` (badge pill warna), `Mata Kuliah`, `Kelas` (huruf inisial tebal), `Jumlah Mahasiswa`, `Deadline`, `Status` (badge `Menunggu`, `Sudah Dinilai`, `Draft`), dan tombol aksi kontekstual (`Lihat Detail`, `Mulai Menilai`, `Lihat Hasil`, `Kelola`).
    - Paginasi numerik dan teks penghitung `Menampilkan 1-10 dari 12 assessment`.
  - Widget Kanan 1: Ringkasan Penilaian 4 grid status (`Total: 12`, `Menunggu: 7`, `Sudah Dinilai: 5`, `Terlambat: 0`).
  - Widget Kanan 2: Pure SVG Donut Chart Progress Penilaian menampilkan persentase presisi `42% Selesai` di tengah, cincin hijau (Sudah Dinilai) dan amber (Menunggu), serta legenda metrik detail.
  - Widget Kanan 3: Kelas yang Diampu (IF2101 Kelas A & B, IF2103 Kelas A).
  - Widget Kanan 4: Aksi Cepat (Rekap Nilai, Export Nilai, Kelola Rubrik, Pengaturan).
  - Widget Kanan 5: Pengumuman Terkait Penilaian & Batas Waktu.

- **Workspace Penilaian Detail (`pages/dosen/penilaian/show.blade.php`)**:
  - Breadcrumb bertingkat dan tombol navigasi kembali ke daftar penilaian.
  - Kartu header metadata: Judul assessment, jenis, status, mode, metode, tenggat deadline, skor maksimum (100), toleransi keterlambatan, dan deskripsi instruksi.
  - 4 Mini Stat Cards: `Total Mahasiswa`, `Mengumpulkan`, `Sudah Dinilai`, dan progress bar persentase penyelesaian penilaian.
  - Filter interaktif per status: `Semua`, `Mengumpulkan`, `Belum Kumpul`, `Perlu Dinilai`, `Sudah Dinilai`, serta live search box nama/NIM mahasiswa tanpa reload.
  - Tombol Aksi Cepat `Isi Nilai 0 Tanpa Submission` yang dilengkapi konfirmasi pengamanan untuk memberi nilai 0 otomatis kepada mahasiswa yang tidak mengumpulkan tugas hingga deadline.
  - Tabel Interaktif Mahasiswa:
    - Kolom: `No`, `Mahasiswa` (nama, NIM, avatar inisial), `Status & Waktu` (badge `Tepat Waktu`, `Terlambat`, `Belum Mengumpulkan`), `Berkas & Jawaban` (link download berkas submission jika ada), `Nilai (0-100)` (input nomor), `Feedback / Catatan` (input teks umpan balik), dan tombol `Simpan`.
    - Menyimpan nilai langsung memperbarui status submission menjadi `graded` dan mencatat skor serta catatan dosen.

- **Interkoneksi Dua Arah & Dynamic Badge**:
  - **Mahasiswa Mengumpulkan**: Mahasiswa submit via `assignments.submit` secara otomatis mengkalkulasi `status_waktu` (`tepat_waktu` atau `terlambat`) berdasarkan `due_at` tugas.
  - **Notifikasi ke Dosen**: Ketika mahasiswa mengumpulkan, sistem mengirimkan `SubmissionReceivedNotification` ke dosen, sehingga badge penilaian di sidebar dan notifikasi dosen bertambah.
  - **Dosen Menilai**: Ketika dosen memberi nilai pada submission, status berubah menjadi `graded`, dan sistem mengirimkan `SubmissionGradedNotification` ke mahasiswa yang bersangkutan.
  - **Pengurangan Dinamis Badge Sidebar**: Badge merah pada menu sidebar dosen Penilaian secara deterministik menghitung `Submission::whereNull('score')->count()`. Ketika dosen selesai menilai submission, nilai badge berkurang secara otomatis dan akurat.

- **Pengujian Otomatis (`tests/Feature/LecturerGradingTest.php`)**:
  - 8 feature tests pass (41 assertions):
    1. `test_lecturer_can_view_penilaian_index_with_canonical_stats`: Passed (Status 200, summary 12, 7, 5, 0, progress 42%).
    2. `test_lecturer_can_filter_penilaian_by_jenis`: Passed.
    3. `test_lecturer_can_view_assessment_grading_workspace`: Passed (Status 200, header, tabel mahasiswa & submission).
    4. `test_lecturer_can_grade_submission_and_student_receives_notification`: Passed (Nilai tersimpan, notifikasi terkirim ke mahasiswa).
    5. `test_lecturer_can_grade_student_without_prior_submission`: Passed.
    6. `test_lecturer_can_quick_assign_zeros_to_unsubmitted_students`: Passed.
    7. `test_lecturer_cannot_grade_another_lecturer_submission`: Passed (403 isolasi antar-dosen).
    8. `test_student_submitting_assignment_calculates_status_waktu_and_notifies_lecturer`: Passed (`status_waktu` tepat waktu/terlambat + notifikasi dosen).
  - Total pengujian kumulatif modul dosen: 34 tests passed (137 assertions) dengan 0 kegagalan.

- **Code Style**:
  - `vendor/bin/pint`: 100% PSR-12 compliant.

---

## 9. HASIL VERIFIKASI FASE 6

- **Halaman Manajemen Materi Pembelajaran (`pages/dosen/materi/index.blade.php`)**:
  - Mengimplementasikan spesifikasi *Unseen Pages* (Sub-halaman 5.2):
    - **Header & Kelas Selector**: Tombol navigasi pemilihan kelas aktif (IF2101 Kelas A, IF2101 Kelas B, IF2103 Kelas A).
    - **4 Top Stat Cards**:
      - `Total Materi`: 7 materi (di IF2101 Kelas A).
      - `Terpublikasi`: 6 materi aktif dapat diakses mahasiswa.
      - `Materi Draft`: 1 materi draft (`Dataset Latihan`) tersembunyi dari mahasiswa.
      - `Pertemuan Terisi`: 6 / 16 pertemuan terisi silabus.
    - **Filter Bar**: Filter tipe (`Semua`, `PDF`, `Slide`, `Video`, `Tautan`, `ZIP`), filter status (`Semua`, `Terpublikasi`, `Draft`), dan pencarian teks judul/ringkasan.
    - **Daftar Pertemuan 1 s/d 16**:
      - Setiap pertemuan menampilkan badge minggu, judul topik, tanggal pertemuan, jumlah materi, dan tombol cepat `+ Tambah`.
      - Menampilkan pertemuan UTS (Minggu 8) dan UAS (Minggu 16) dengan styling warna khas.
      - Kartu materi per pertemuan menampilkan ikon tipe materi berwarna (PDF merah, Slide kuning/oranye, Video ungu, Tautan hijau, ZIP biru), judul tebal, ringkasan, tanggal unggah, dan badge status (`Dipublikasikan` / `Draft`).
      - Tombol aksi pada setiap materi: `Unduh`/`Buka URL`, `Publikasikan`/`Draftkan` (toggle status satu klik), `Ubah` (modal edit), dan `Hapus` (konfirmasi pengamanan).
    - **Modal Unggah Materi Baru**:
      - Pilihan pertemuan 1–16, judul materi, tipe materi, deskripsi/ringkasan, berkas lampiran (maks 50MB) atau tautan URL eksternal, pilihan status langsung terbit vs simpan sebagai draft, dan izin pengunduhan.
    - **Modal Ubah Materi**:
      - Form dinamis JavaScript untuk mengedit data materi, mengganti berkas lampiran, atau memindahkan materi ke pertemuan lain.
    - **Widget Samping**:
      - Widget Pengumuman Kelas dengan tombol `+ Tambah` dan tombol hapus untuk pengumuman yang dibuat oleh dosen.
      - Widget Panduan Silabus & RPS (pembagian materi pra-UTS, UTS minggu 8, pasca-UTS, dan UAS minggu 16).
      - Tautan Cepat ke Detail Kelas, Tugas & Ujian, serta Penilaian.

- **Modul Pengumuman Kelas & Akademik**:
  - Dosen dapat membuat pengumuman baru untuk kelas tertentu maupun seluruh kelas yang diampunya.
  - Form modal pengumuman: Target Kelas, Judul Pengumuman, dan Isi Pengumuman (textarea).
  - Pengumuman tersimpan di database dengan relasi `author_id` dan `class_id`, serta otomatis tampil di daftar pengumuman kelas dosen dan kelas mahasiswa.
  - Dosen dapat menghapus pengumuman yang dimilikinya. Dosen lain diblokir dari menghapus pengumuman tersebut (403 Forbidden).

- **Interkoneksi Dua Arah & Proteksi Hak Akses Mahasiswa**:
  - `MaterialService@getSummary` dan `MaterialService@getChapters` diperbarui untuk hanya menyajikan materi dengan `status = 'published'` kepada mahasiswa.
  - Mahasiswa tidak dapat melihat materi berstatus `draft` pada halaman materi perkuliahan (`app.materials`).
  - Percobaan mahasiswa mengakses langsung URL materi yang berstatus `draft` (`app.materials.view`) ditolak secara ketat dengan status HTTP 403 Forbidden via `MaterialPolicy@view`.
  - Ketika dosen mengubah status materi dari draft menjadi published melalui tombol toggle (`dosen.materi.toggle_publish`), materi seketika muncul di dashboard mahasiswa dan halaman materi mahasiswa dapat diakses dengan status HTTP 200 OK.

- **Pengujian Otomatis (`tests/Feature/LecturerMaterialTest.php`)**:
  - 9 feature tests pass (41 assertions):
    1. `test_lecturer_can_view_materi_index_with_canonical_materials`: Passed (Status 200, summary 7, 6, 1, canonical materials verified).
    2. `test_lecturer_can_switch_classes_in_materi_page`: Passed (Active class switched correctly).
    3. `test_lecturer_can_upload_new_material`: Passed (Upload file stored to public disk, record created in DB).
    4. `test_draft_material_is_hidden_from_student_and_blocked_on_direct_access`: Passed (Hidden on list + 403 Forbidden on direct URL).
    5. `test_lecturer_can_toggle_publish_material_and_student_can_now_access_it`: Passed (Toggled to published, visible to student + 200 OK on viewer).
    6. `test_lecturer_can_update_material`: Passed (Material title & type updated).
    7. `test_lecturer_can_delete_material`: Passed (Material removed from DB and storage).
    8. `test_lecturer_cannot_manage_another_lecturer_material`: Passed (403 Forbidden data isolation).
    9. `test_lecturer_can_create_announcement_and_delete_it`: Passed (Announcement created and deleted).
  - Total pengujian kumulatif modul dosen: 43 tests passed (178 assertions) dengan 0 kegagalan.

- **Code Style**:
  - `vendor/bin/pint`: 100% PSR-12 compliant.

