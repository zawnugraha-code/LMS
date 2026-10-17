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
| **Fase 5** | Modul Pengumpulan + Penilaian + Notifikasi + Badge Dinamis | Menunggu | - |
| **Fase 6** | Modul Materi Pembelajaran + Pengumuman Kelas & Akademik | Menunggu | - |
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
  - Total pengujian kumulatif Fase 2, 3, dan 4: 26 tests passed (128 assertions) dengan 0 kegagalan.
- **Code Style**:
  - `vendor/bin/pint`: 100% PSR-12 compliant.



