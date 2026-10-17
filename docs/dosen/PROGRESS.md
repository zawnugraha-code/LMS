# LOG PROGRESS PENGEMBANGAN DASHBOARD DOSEN (ARUNIKA LMS)
**Universitas Nusantara Indonesia**

Dokumen ini mencatat keputusan arsitektural, skema tabel, komponen UI, route, dan status perkembangan proyek dari fase ke fase.

---

## 1. STATUS UMUM FASE

| Fase | Deskripsi | Status | Catatan / Tanggal Selesai |
| :--- | :--- | :--- | :--- |
| **Fase 1** | Analisis Repo + Audit Mahasiswa + Perencanaan Batch 1 | **SELESAI** | Selesai pada 17 Okt 2026. Dokumen `PLAN.md` telah disahkan. |
| **Fase 2** | Fondasi Data: Migration, Model, Policy, Seeder, Scheduler, Route Placeholder | **SELESAI** | Selesai pada 17 Okt 2026. 2 migration ran, 10 feature tests pass, 100% Pint. |
| **Fase 3** | Layout Dosen (`layouts.dosen`) + Beranda + Kelas Saya (+ Detail Kelas) | Menunggu | Siap dikerjakan setelah persetujuan Fase 2 |
| **Fase 4** | Modul Tugas & Ujian + Integrasi Tampil di Mahasiswa | Menunggu | - |
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

