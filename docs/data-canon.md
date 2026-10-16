# Dataset Kanonik Arunika LMS (Data Canon)
*Satu Sumber Kebenaran Data Demo Mahasiswa untuk Database & Seeder*

Dokumen ini mendefinisikan dataset kanonik terpadu untuk memastikan konsistensi angka, nama, tanggal, dan relasi di seluruh antarmuka Mahasiswa (Beranda, Mata Kuliah, Tugas & Ujian, Presensi, Nilai, Materi Pembelajaran, Diskusi, Pengaturan, dan Topbar).

---

## 1. Identitas Mahasiswa Utama

| Atribut | Nilai Kanonik | Keterangan / Sumber Desain |
| :--- | :--- | :--- |
| **Nama Lengkap** | `Arya Pratama` | Sesuai semua halaman & kartu profil |
| **NIM** | `23060101024` | Halaman Pengaturan & Kartu Identitas |
| **Email** | `arya.pratama@student.unus.ac.id` | Domain resmi kampus UNUS |
| **Jenjang & Program Studi** | `S1 Teknik Informatika` | Program Studi Fasilkom |
| **Fakultas** | `Fakultas Ilmu Komputer` | Fakultas Ilmu Komputer UNUS |
| **Perguruan Tinggi** | `Universitas Nusantara Indonesia` | Kampus ID: UNI / UNUS |
| **Terdaftar Sejak** | `12 Agustus 2025` | Angkatan 2025/2026 |
| **Status Akun** | `Akun Aktif` | Badge Hijau Bercentang |
| **Role Pengguna** | `student` (`Mahasiswa`) | Role bawaan untuk portal mahasiswa |
| **Foto Avatar** | `/images/avatar-arya.jpg` | Foto mahasiswa jas almamater |
| **Level Gamifikasi** | `Level 12` | Ambisius / Mahasiswa Berprestasi |
| **Total XP** | `7,450 XP` | Rumus level: `floor(sqrt(7450 / 50)) + 1 = 12` |
| **Streak Presensi** | `5 Hari Berturut-turut` | Menghadiri kelas aktif tanpa absen |

---

## 2. Struktur Periode Akademik & Semester

### 2.1 Semester Berjalan (Aktif)
- **Nama Periode**: `Semester Ganjil 2026/2027` (Semester 3)
- **Tipe**: `odd`
- **Tanggal Mulai**: `now()->subMonths(2)->startOfMonth()`
- **Tanggal Selesai**: `now()->addMonths(4)->endOfMonth()`
- **Status**: `is_active = true`
- **Kuota Beban Maksimal**: `24 SKS` (berdasarkan IPS semester lalu $\ge 3.50$)

### 2.2 Riwayat Semester Lampau (Untuk Tab Semester, IPK & Grafik)
1. **Semester 1 (Ganjil 2025/2026)**:
   - SKS Diambil: `20 SKS`
   - SKS Lulus: `20 SKS`
   - Total Mutu: `76.5`
   - **IPS**: `3.82`
   - Mata Kuliah:
     - `IF1101` Algoritma & Pemrograman Dasar (4 SKS) -> Nilai: 92 (A, 4.00)
     - `IF1102` Matematika Diskrit (3 SKS) -> Nilai: 88 (A, 4.00)
     - `IF1103` Pengantar Teknologi Informasi (3 SKS) -> Nilai: 83 (A-, 3.75)
     - `UN1101` Bahasa Indonesia (2 SKS) -> Nilai: 87 (A, 4.00)
     - `UN1102` Bahasa Inggris Akademik (2 SKS) -> Nilai: 89 (A, 4.00)
     - `IF1104` Kalkulus 1 (3 SKS) -> Nilai: 78 (B+, 3.50)
     - `UN1103` Pendidikan Kewarganegaraan (3 SKS) -> Nilai: 86 (A, 4.00)

2. **Semester 2 (Genap 2025/2026)**:
   - SKS Diambil: `20 SKS`
   - SKS Lulus: `20 SKS`
   - Total Mutu: `77.5`
   - **IPS**: `3.88`
   - Mata Kuliah:
     - `IF1201` Arsitektur & Organisasi Komputer (3 SKS) -> Nilai: 90 (A, 4.00)
     - `IF1202` Aljabar Linier & Matriks (3 SKS) -> Nilai: 86 (A, 4.00)
     - `IF1203` Sistem Operasi (3 SKS) -> Nilai: 82 (A-, 3.75)
     - `IF1204` Pemrograman Berbasis Web (3 SKS) -> Nilai: 94 (A, 4.00)
     - `IF1205` Probabilitas & Statistika (3 SKS) -> Nilai: 79 (B+, 3.50)
     - `UN1201` Pendidikan Agama (2 SKS) -> Nilai: 91 (A, 4.00)
     - `IF1206` Kalkulus 2 (3 SKS) -> Nilai: 85 (A, 4.00)

3. **Akumulasi Riwayat (Semester 1 + 2)**:
   - Total SKS Riwayat Selesai: `40 SKS`
   - Total Bobot Mutu: `154.0`
   - **IPK Kumulatif Riwayat**: $\frac{154.0}{40} = 3.85$

---

## 3. Paket Mata Kuliah Semester 3 (Ganjil 2026/2027)

Satu set mata kuliah dan dosen konsisten digunakan di seluruh halaman:

| Kode MK | Nama Mata Kuliah | SKS | Dosen Pengampu | Jadwal & Ruang | Progres Materi | Status Kelas |
| :--- | :--- | :---: | :--- | :--- | :---: | :--- |
| **IF2101** | Pemrograman Berorientasi Objek | 3 | Dr. Budi Rahardjo, S.T., M.Kom. | Senin, 08:00 - 10:30 \| Lab Komputer 2 | 8 / 14 (57%) | `Aktif` |
| **IF2102** | Struktur Data & Algoritma | 3 | Siti Aminah, M.Kom. | Selasa, 10:45 - 13:15 \| Lab RPL | 6 / 14 (43%) | `Aktif` |
| **IF2103** | Basis Data Lanjut | 3 | Hendra Gunawan, Ph.D. | Rabu, 13:30 - 16:00 \| Lab Basis Data | 9 / 14 (64%) | `Aktif` |
| **IF2104** | Rekayasa Perangkat Lunak | 3 | Dewi Sartika, M.T. | Kamis, 08:00 - 10:30 \| R. 401 | 7 / 14 (50%) | `Aktif` |
| **IF2105** | Jaringan Komputer | 3 | Ahmad Fauzi, M.Kom. | Jumat, 08:00 - 10:30 \| Lab Jaringan | 5 / 14 (36%) | `Aktif` |
| **IF2106** | Kecerdasan Buatan | 3 | Dr. Ir. Rina Wulandari, M.Sc. | Belum terjadwal \| Online | 0 / 14 (0%) | `Akan Datang` (Terkunci) |

### 3.1 Definisi Metrik Bar Ringkasan (Halaman Mata Kuliah)
- **Total MK**: `6` (Seluruh MK dalam kurikulum semester 3 yang ditawarkan/diambil).
- **SKS Total**: `18 SKS` ($6 \times 3$ SKS).
- **SKS Diambil**: `15 SKS` (5 MK yang telah disetujui & aktif berjalan).
- **MK Aktif**: `5` (Mata kuliah yang sedang berlangsung perkuliahannya).
- **MK Selesai**: `0` (Belum ada MK semester 3 yang final/selesai).

---

## 4. Skala Penilaian & Distribusi Nilai

### 4.1 Tabel Skala Nilai (`grade_scales`)
Berdasarkan standar penilaian SN-Dikti / Universitas Nusantara Indonesia:

| Huruf Mutu | Rentang Nilai | Bobot Indeks | Status Kelulusan | Keterangan |
| :---: | :---: | :---: | :---: | :--- |
| **A** | 85.00 – 100.00 | 4.00 | Lulus | Sangat Istimewa |
| **A-** | 80.00 – 84.99 | 3.75 | Lulus | Istimewa |
| **B+** | 75.00 – 79.99 | 3.50 | Lulus | Sangat Baik |
| **B** | 70.00 – 74.99 | 3.00 | Lulus | Baik |
| **B-** | 65.00 – 69.99 | 2.75 | Lulus | Cukup Baik |
| **C+** | 60.00 – 64.99 | 2.50 | Lulus | Lebih dari Cukup |
| **C** | 55.00 – 59.99 | 2.00 | Lulus | Cukup |
| **D** | 40.00 – 54.99 | 1.00 | Tidak Lulus | Kurang |
| **E** | 0.00 – 39.99 | 0.00 | Tidak Lulus | Gagal |

### 4.2 Legenda Donut Distribusi Nilai (`config/grading.php`)
Sesuai desain 6 irisan pada donut chart:
1. **A (Sangat Baik)**: Nilai A dan A- (Biru Primer `#2563EB`)
2. **B (Baik)**: Nilai B+ dan B (Toska / Cyan `#06B6D4`)
3. **B- (Cukup Baik)**: Nilai B- (Kuning / Amber `#F59E0B`)
4. **C (Cukup)**: Nilai C+ dan C (Oranye `#F97316`)
5. **D (Kurang)**: Nilai D (Merah Muda `#EC4899`)
6. **E (Gagal)**: Nilai E (Merah `#EF4444`)

---

## 5. Bobot Komponen Penilaian & Nilai Semester 3

Rumus Nilai Akhir Per MK:
$$\text{Nilai Akhir} = (\text{Tugas} \times 25\%) + (\text{Kuis} \times 15\%) + (\text{Presensi} \times 10\%) + (\text{UTS} \times 25\%) + (\text{UAS} \times 25\%)$$

Nilai sementara semester 3 (setelah komponen berjalan dinilai):
1. **IF2101 Pemrograman Berorientasi Objek**: Nilai Sementara `88.50` (`A`, Bobot 4.00, Status: `Belum Selesai / Berjalan`)
2. **IF2102 Struktur Data & Algoritma**: Nilai Sementara `84.00` (`A-`, Bobot 3.75, Status: `Belum Selesai / Berjalan`)
3. **IF2103 Basis Data Lanjut**: Nilai Sementara `91.20` (`A`, Bobot 4.00, Status: `Belum Selesai / Berjalan`)
4. **IF2104 Rekayasa Perangkat Lunak**: Nilai Sementara `78.50` (`B+`, Bobot 3.50, Status: `Belum Selesai / Berjalan`)
5. **IF2105 Jaringan Komputer**: Nilai Sementara `82.00` (`A-`, Bobot 3.75, Status: `Belum Selesai / Berjalan`)
6. **IF2106 Kecerdasan Buatan**: Nilai `-` (`Belum Dinilai`, SKS 3, Status: `Belum Mulai`)

**Metrik Nilai Semester 3 (Tile Nilai)**:
- **IPS Semester Ini**: `3.80` (Proyeksi dari 5 MK aktif: $\frac{3(4.00) + 3(3.75) + 3(4.00) + 3(3.50) + 3(3.75)}{15} = \frac{57.0}{15} = 3.80$)
- **Selisih dari Semester Lalu**: `-0.08` (dibandingkan IPS semester 2 = 3.88)
- **Total SKS Diambil**: `15 SKS` dari Kuota `24 SKS`
- **MK Dinilai**: `5 MK` (Komponen tugas/kuis telah memiliki nilai)
- **MK Belum Selesai**: `6 MK` (UAS belum selesai)

---

## 6. Presensi & Kehadiran

- **Persentase Presensi Semester Ini**: `94.1%` (32 hadir dari 34 sesi perkuliahan yang telah dibuka)
- **Rincian Presensi**:
  - Hadir: `32 Pertemuan`
  - Izin: `1 Pertemuan` (Surat izin kegiatan lomba mahasiswa)
  - Sakit: `1 Pertemuan` (Surat dokter terlampir)
  - Alpa: `0 Pertemuan`
- **Rincian Per Mata Kuliah**:
  - IF2101: 8 hadir / 8 sesi (100%)
  - IF2102: 6 hadir, 1 izin / 7 sesi (85.7%)
  - IF2103: 9 hadir / 9 sesi (100%)
  - IF2104: 6 hadir, 1 sakit / 7 sesi (85.7%)
  - IF2105: 3 hadir / 3 sesi (100%)

---

## 7. Tugas & Ujian

Semua tanggal bersifat relatif terhadap `now()`:

| Kode MK | Judul Tugas / Ujian | Tipe | Tenggat Waktu | Status Pengerjaan | Nilai | Lampiran |
| :--- | :--- | :---: | :--- | :--- | :---: | :--- |
| **IF2101** | Tugas 1: Desain Arsitektur Class Diagram | Tugas | `now()->subDays(10)` | `Dinilai` | 95.00 | `tugas1_class_diagram.pdf` |
| **IF2101** | Tugas 2: Implementasi Design Pattern Factory | Tugas | `now()->addDays(2)` (23:59) | `Belum Dikerjakan` | - | `soal_factory_pattern.pdf` |
| **IF2102** | Tugas Algoritma Greedy & Dynamic Programming | Tugas | `now()->addDays(4)` (23:59) | `Draft Tersimpan` | - | `modul_dynamic_prog.pdf` |
| **IF2103** | Tugas Query Optimization & Indexing B-Tree | Tugas | `now()->subDays(2)` (23:59) | `Terkumpul` | - | `schema_ecommerce.sql` |
| **IF2104** | Ujian Tengah Semester (UTS): Rekayasa PL | Ujian | `now()->addDays(6)` (09:00 - 11:00) | `Mendatang` | - | `aturan_uts_rpl.pdf` |
| **IF2105** | Kuis Konfigurasi Subnetting & Routing OSPF | Ujian/Kuis | `now()->subDays(5)` | `Dinilai` | 88.00 | - |

---

## 8. Silabus & Materi Perkuliahan (IF2101)

Terdiri dari 3 Bab dengan 14 Materi Pembelajaran:
- **Bab 1: Pengenalan dan Konsep Dasar OOP** (3 Materi, Status: `3 / 3 Selesai`)
  - 1.1 `Konsep Dasar OOP` (Modul • 45 menit • Dibuat `now()->subWeeks(6)`) -> `Selesai`
  - 1.2 `Video Pembahasan OOP` (Video • 1 jam • Dibuat `now()->subWeeks(6)`) -> `Selesai`
  - 1.3 `Studi Kasus dan Latihan` (Referensi • 30 menit • Dibuat `now()->subWeeks(5)`) -> `Selesai`
- **Bab 2: Desain Pola (Design Pattern)** (4 Materi, Status: `2 / 4 Selesai`)
  - 2.1 `Singleton Pattern` (Modul • 50 menit • Dibuat `now()->subWeeks(4)`) -> `Selesai`
  - 2.2 `Factory Pattern` (Modul • 40 menit • Dibuat `now()->subWeeks(3)`) -> `Selesai`
  - 2.3 `Observer Pattern` (Video • 55 menit • Dibuat `now()->subWeeks(2)`) -> `Belum Selesai`
  - 2.4 `Implementasi Design Pattern` (Tugas • Deadline `now()->addDays(2)`) -> `Terkunci / Tersedia Nanti`
- **Bab 3: Framework dan Studi Kasus Lanjutan** (3 Materi, Status: `0 / 3 Selesai`, Terkunci)
  - 3.1 `MVC Architecture pada Framework Modern` (Modul • 60 menit) -> `Terkunci`
  - 3.2 `Dependency Injection & Service Container` (Video • 45 menit) -> `Terkunci`
  - 3.3 `Proyek Mini Aplikasi Kampus` (Tugas Proyek) -> `Terkunci`

---

## 9. Thread Diskusi Komunitas Kelas

7 Thread Utama pada Halaman Diskusi:
1. **Dr. Budi Rahardjo, S.T., M.Kom.** (Dosen Pengampu)
   - Judul: `[PENTING] Ketentuan Pengumpulan Tugas Besar Pemrograman Berorientasi Objek & Jadwal Presentasi`
   - Kategori: `Pengumuman` (Pinned, Badge Biru)
   - Metrik: 142 komentar, 2.4k views, `now()->subDays(3)`
2. **Andi Saputra** (Mahasiswa)
   - Judul: `Tanya implementasi Interface Segregation Principle (ISP) di arsitektur microservices`
   - Kategori: `Tanya Jawab` (Badge Hijau)
   - Metrik: 28 komentar, 567 views, `now()->subHours(5)`
3. **Siti Nurhaliza** (Mahasiswa)
   - Judul: `Diskusi kelompok praktikum Struktur Data pertemuan ke-7 materi Binary Search Tree`
   - Kategori: `Diskusi` (Badge Ungu)
   - Metrik: 45 komentar, 892 views, `now()->subHours(7)`
4. **Raka Maulana** (Mahasiswa)
   - Judul: `Rekomendasi extension VS Code terbaik untuk Docker dan Laravel devcontainer`
   - Kategori: `Forum Kelas` (Badge Biru)
   - Metrik: 19 komentar, 342 views, `now()->subHours(10)`
5. **Dewi Lestari** (Mahasiswa)
   - Judul: `Catatan kuliah Basis Data Lanjut materi Query Execution Plan & Indeks Covering`
   - Kategori: `Diskusi` (Badge Ungu)
   - Metrik: 12 komentar, 201 views, `now()->subDays(1)`
6. **Bayu Pratama** (Mahasiswa)
   - Judul: `Solusi error Connection refused pada konfigurasi PostgreSQL Docker di WSL 2`
   - Kategori: `Tanya Jawab` (Badge Hijau)
   - Metrik: 36 komentar, 674 views, `now()->subDays(1)`
7. **Fajar Nugroho** (Mahasiswa)
   - Judul: `Berbagi template starter kit REST API Clean Architecture dengan PHP 8.4`
   - Kategori: `Berbagi` (Badge Oranye)
   - Metrik: 20 komentar, 412 views, `now()->subDays(2)`

---

## 10. Notifikasi Pengguna

Jumlah belum dibaca: **2 Notifikasi** (sesuai badge lonceng topbar).
1. `Tugas Baru`: *Dr. Budi Rahardjo menerbitkan Tugas 2: Implementasi Factory Pattern pada IF2101* (`now()->subHours(2)`, Belum dibaca).
2. `Diskusi`: *Andi Saputra membalas pertanyaan Anda di thread OOP Design Patterns* (`now()->subHours(4)`, Belum dibaca).
3. `Nilai Diumumkan`: *Nilai Kuis Subnetting Jaringan Komputer telah dipublikasikan (Skor: 88.00)* (`now()->subDays(1)`, Sudah dibaca).
4. `Presensi Berhasil`: *Check-in presensi Pertemuan 8 Pemrograman Berorientasi Objek berhasil dicatat* (`now()->subDays(2)`, Sudah dibaca).
5. `Pengingat Deadline`: *Tugas 1 Basis Data Lanjut akan ditutup dalam 24 jam* (`now()->subDays(3)`, Sudah dibaca).

---

## 11. Sertifikat & Kompetensi (Halaman Beranda)

1. **Sertifikat Kelulusan Pemrograman Web Lanjut**:
   - Nomor SK: `CERT-UNI-IF1204-2026-081`
   - Tanggal Terbit: `28 Juli 2026`
   - Nilai Akhir: `94.00 (A)`
   - Verifikasi: `/verifikasi/CERT-UNI-IF1204-2026-081`
2. **Sertifikat Kompetensi Algoritma & Struktur Data Dasar**:
   - Nomor SK: `CERT-UNI-IF1101-2026-014`
   - Tanggal Terbit: `15 Januari 2026`
   - Nilai Akhir: `92.00 (A)`
   - Verifikasi: `/verifikasi/CERT-UNI-IF1101-2026-014`

---

## 12. Preferensi Pengaturan Pengguna

- **Bahasa**: `id` (`Bahasa Indonesia`)
- **Zona Waktu**: `Asia/Jakarta` (`WIB, UTC+7`)
- **Format Tanggal**: `DD MMM YYYY` (contoh: `24 Sep 2026`)
- **Format Waktu**: `HH:mm` (contoh: `14:30`)
- **Tema**: `light` (`Terang`)
- **Ukuran Teks**: `normal` (16px)
- **Preferensi Notifikasi**:
  - Pengumuman Kelas: `true` (ON)
  - Tugas & Deadline: `true` (ON)
  - Pesan Pribadi: `true` (ON)
  - Aktivitas Diskusi: `false` (OFF)
  - Notifikasi Sistem: `true` (ON)
- **Keamanan**:
  - Two-Factor Authentication (2FA): `false` (Nonaktif)
  - Google SSO Terhubung: `false`
