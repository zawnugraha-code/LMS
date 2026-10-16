# Sistem Desain: "Navy Academic" Arunika

Dokumen ini mendefinisikan standar visual, tipografi, token warna, dan spesifikasi antarmuka untuk Arunika (Learning Operating System). Standar ini diekstraksi secara presisi dari gambar acuan `docs/reference/dashboard-mahasiswa.png` (viewport 1658×949).

---

## 1. Prinsip Utama Desain

1. **LMS Kampus Terpercaya & Bersih**
   Tata letak terang, profesional, dan tepercaya dengan sidebar navy tua (`#1A2E5E`), latar canvas biru-abu muda (`#F4F7FB`), dan kartu surface putih (`#FFFFFF`) berborder tipis 1px (`#E3E8F0`).
2. **Tipografi Tunggal yang Kokoh: Inter Variable**
   Semua teks menggunakan *Inter Variable* dengan skala hierarki yang jelas, kerning rapi, dan angka tabular (`tabular-nums`) untuk data statistik IPK, SKS, persentase, dan waktu.
3. **Struktur Grid 2 Kolom Asimetris**
   Dashboard mahasiswa dibagi menjadi 2 kolom proporsional (1.48fr : 1fr) yang membedakan aktivitas harian (kiri) dengan metrik capaian jangka panjang (kanan).
4. **Ikon Monokrom Fungsional (Lucide Icons)**
   Ikon garis konsisten dengan ketebalan stroke 1.75px tanpa kotak pastel dekoratif di belakangnya.
5. **Anti-"Tampilan AI"**
   Nol emoji, nol gradient teks, nol glassmorphism (backdrop-blur), nol kartu melayang/shadow berlebihan, dan copywriting akademis yang lugas.

---

## 2. Palet Token Warna (@theme)

### A. Mode Terang (Default)
| Token | Nilai Hex | Peruntukan |
|---|---|---|
| `--color-navy-900` | `#1A2E5E` | Latar sidebar penuh |
| `--color-navy-700` | `#2C4A8F` | Latar item navigasi aktif, avatar inisial fallback |
| `--color-primary` | `#17306B` | Tombol primer, ikon header kartu |
| `--color-primary-hover` | `#1F3F8A` | Hover tombol primer |
| `--color-accent` | `#2F62C8` | Progress bar, link interaktif, fokus ring |
| `--color-accent-soft` | `#E6EEFB` | Latar banner sambutan, badge netral |
| `--color-canvas` | `#F4F7FB` | Latar belakang halaman utama |
| `--color-surface` | `#FFFFFF` | Kartu putih, topbar, modal |
| `--color-tile` | `#F2F5FA` | Tile ringkasan statistik, kartu sertifikat |
| `--color-line` | `#E3E8F0` | Garis pembatas 1px & border kartu |
| `--color-line-strong` | `#C9D2E0` | Border tombol sekunder & pemisah tebal |
| `--color-text` | `#14213D` | Teks utama kontras tinggi |
| `--color-text-muted` | `#5E6B85` | Teks sekunder, deskripsi, metadata |
| `--color-text-subtle` | `#8792A8` | Teks placeholder, separator, chevron |
| `--color-success` | `#1E9B57` | Badge aktif, tren positif |
| `--color-success-soft` | `#DFF4E7` | Latar badge sukses |
| `--color-danger` | `#D93636` | Tenggat mendesak, notifikasi penting |
| `--color-danger-soft` | `#FDE7E7` | Latar badge bahaya |
| `--color-warning` | `#E99A1F` | Peringatan status |
| `--color-warning-soft` | `#FDF1DC` | Latar badge peringatan |
| `--color-neutral-soft` | `#EDF0F5` | Badge netral ("Sudah dinilai") |

### B. Mode Gelap
| Token | Nilai Hex | Peruntukan |
|---|---|---|
| `--color-canvas` | `#0E1526` | Latar belakang kanvas gelap |
| `--color-surface` | `#151E33` | Panel kartu dan topbar |
| `--color-tile` | `#1A2540` | Tile statistik |
| `--color-line` | `#26324D` | Border 1px |
| `--color-text` | `#E8EDF7` | Teks utama |
| `--color-text-muted` | `#9AA7C0` | Teks sekunder |
| `--color-accent` | `#6B96F0` | Aksen biru terang |

### C. Radius & Shadow
- `--radius-sm`: `4px` (badge kecil, input)
- `--radius-md`: `6px` (tombol, input select)
- `--radius-lg`: `8px` (semua panel kartu surface, tile)
- `rounded-full`: hanya untuk badge pill status tugas ("2 hari lagi", "Sudah dinilai") dan avatar bulat.
- `--shadow-card`: `0 1px 2px rgb(20 33 61 / 0.04)`

---

## 3. Tipografi

- **Satu font keluarga**: Inter Variable (`@fontsource-variable/inter`)
- **Ukuran dan Bobot**:
  - Judul Halaman: `24–26px / Bold (700)`
  - Judul Kartu: `15px / Semibold (600)`
  - Subjudul / Body: `13px / Regular (400)`
  - Metadata / Tag / Waktu: `11–12px / Medium (500)`
  - Angka metrik: `font-variant-numeric: tabular-nums`

---

## 4. Struktur Shell Navigasi

- **Sidebar (234px, Navy #1A2E5E)**:
  - Sticky 100vh di desktop
  - Landmark icon + Arunika + "LEARNING SYSTEM" wordmark
  - 8 Item Navigasi: Beranda, Mata Kuliah, Tugas & Ujian, Presensi, Nilai, Materi Pembelajaran, Diskusi, Pengaturan
  - Box Institusi: Universitas Nusantara Indonesia / Maju Bersama Ilmu
  - Profil Pengguna Bawah: Avatar 32px + Nama + Peran + Chevron Down
- **Topbar (48px, Surface Putih)**:
  - Sticky top-0
  - Breadcrumb: Beranda > Dashboard
  - Search Input: "Cari materi, tugas, atau mata kuliah..."
  - Notification Bell dengan badge merah angka 2
  - Profil Avatar pengguna + chevron down

---

## 5. Tata Letak Dashboard Mahasiswa (8 Blok Presisi)

1. **Banner Sambutan**: Judul tebal, subteks, tombol primer "Lihat Jadwal Kuliah", foto kampus bergradasi halus.
2. **Mata Kuliah Hari Ini**: Indikator titik warna (teal/green), jam tabular, nama mata kuliah, kode MK, dan ruang kelas.
3. **Tugas Terbaru**: Metadata kode MK, judul tugas tebal, tenggat, badge pill status, dan tombol aksi ("Kerjakan" / "Lihat Nilai").
4. **Mata Kuliah Terdaftar**: Baris bergaris warna vertikal per mata kuliah, kode, nama, info kelas/SKS, dan badge "Aktif".
5. **Ringkasan Akademik**: Dropdown semester, 3 stat-tile (IPK, SKS Total, Presensi), progress bar mata kuliah aktif.
6. **Capaian Kompetensi**: Daftar CPL dengan progress bar akurat, nilai persentase, dan indikator standar kelulusan minimum 70%.
7. **Sertifikat & Kompetensi**: Kartu grid 3 sertifikat dengan tombol "Lihat".
8. **Buku Besar XP**: Audit log perolehan poin gamifikasi dengan nilai tabular "+50 VP", "+100 VP", "+150 VP".

---

## 6. Halaman Mata Kuliah (`docs/reference/courses-mahasiswa.png`)

1. **Header Halaman**: Ikon `book-open` navy, judul tebal, deskripsi, dan dropdown pemilih semester.
2. **Ringkasan Statistik 5 Segmen**: Kartu surface bergaris pemisah berisi Total MK, SKS Total, SKS Diambil, MK Aktif, MK Selesai.
3. **Toolbar**: Filter tab (Semua, Aktif, Selesai), input pencarian "Cari mata kuliah atau dosen...", tombol Filter.
4. **Grid Kartu Mata Kuliah**: 6 kartu MK dengan strip warna fakultas/kategori, kode MK, judul, dosen, progress bar silabus, jadwal, dan tombol "Masuk Kelas →".

---

## 7. Halaman Tugas & Ujian (`docs/reference/assignments-mahasiswa.png`)

1. **Header Halaman**: Ikon dokumen `file-text` navy besar, judul "Tugas & Ujian", deskripsi panduan penyelesaian tugas.
2. **4 Stat Tiles Horizontal**:
   - 8 Tugas Aktif (lingkaran biru + ikon dokumen)
   - 3 Ujian Mendatang (lingkaran hijau + ikon kalender)
   - 5 Tugas Selesai (lingkaran oranye + ikon centang)
   - 1 Tugas Terlambat (lingkaran ungu + ikon jam)
3. **Kolom Kiri (~70%)**:
   - Toolbar: Tab pills (Semua, Tugas, Ujian, Terlambat) dan dropdown urutan "Terbaru ▾".
   - 6 Kartu Asesmen Lengkap:
     - Kotak ikon persegi berkategori (biru untuk Tugas, ungu untuk Ujian).
     - Kode MK, nama MK, judul tugas/ujian tebal, dan lampiran berkas dokumen (.pdf) dengan ukuran file.
     - Badge jenis ("Tugas" / "Ujian") dan jadwal tanggal + jam batas waktu.
     - Tombol aksi: "Kerjakan →" (primer biru) untuk Tugas, "Lihat Detail →" (outline) untuk Ujian.
     - Menu konteks 3-titik vertikal di sudut kanan atas tiap kartu.
4. **Kolom Kanan (~30%)**:
   - **Kartu Filter**: Dropdown "Semua Mata Kuliah ▾", "Semua Jenis ▾", dan input "Pilih Rentang Tanggal".
   - **Kalender Tugas & Ujian**: Navigasi bulan `< Oktober 2026 >`, grid hari Sen-Min, dan tanggal berpenanda lingkaran warna sorotan (2, 7 biru; 8, 20 ungu).
   - **Tugas & Ujian Terdekat**: Daftar 3 tenggat terdekat dengan bullet warna pembeda dan tautan footer "Lihat Semua →".
5. **Modal Interaktif**:
   - Modal Pengumpulan Tugas dengan input catatan, dropzone upload berkas, dan tombol kirim tugas yang terhubung ke endpoint `assignments.submit`.
   - Modal Detail Ujian lengkap dengan jadwal, lokasi/ruangan CBT, dan tata tertib pengawasan.

---

## 8. Halaman Presensi (`docs/reference/presensi-mahasiswa.png`)

1. **Header Halaman**: Ikon kalender `calendar-check` navy besar, judul "Presensi", deskripsi riwayat absensi.
2. **4 Stat Cards Horizontal**:
   - **Tingkat Kehadiran**: Donut gauge 92% hijau, total 12 sesi perkuliahan, dan badge pill status "✓ Sangat Baik".
   - **Hadir**: Ikon centang hijau, angka tabular `11 / 12 sesi`, tren "↑ 8% dari semester lalu".
   - **Izin**: Ikon user oranye, angka tabular `1 / 12 sesi`, tren "↑ 0% dari semester lalu".
   - **Alfa**: Ikon silang merah, angka tabular `0 / 12 sesi`, tren "↓ 2% dari semester lalu".
3. **Kolom Kiri (~70%) — Riwayat Presensi**:
   - Dropdown semester "Semester 3 (Ganjil 2026/2027) ▾" dengan ikon kalender.
   - Filter tab pills (Semua, Hadir, Izin, Alfa).
   - Tabel riwayat 12 sesi perkuliahan lengkap: Tanggal, Pertemuan (1–12), Topik Silabus RPS, Badge Status Kehadiran (Hadir, Izin, Alfa), Keterangan surat izin dokter, dan tombol aksi "Lihat" berikon mata.
4. **Kolom Kanan (~30%)**:
   - **Rekap Kehadiran**: Donut chart SVG 92% Kehadiran + legenda perincian Hadir 11 (92%), Izin 1 (8%), Alfa 0 (0%), dan pemilih rentang "7 Pertemuan Terakhir ▾".
   - **Jadwal Presensi Berikutnya**: Box banner biru muda berisi ikon kalender, pertemuan 13 (24 Nov 2026), topik perkuliahan "Aplikasi Web dengan Laravel", dan tombol "Lihat Detail".
   - **Kehadiran per Mata Kuliah**: Daftar 5 mata kuliah dengan progress bar aksen biru 4px, persentase kehadiran, dan chevron navigasi.
5. **Modal Detail Presensi**:
   - Modal interaktif inspeksi log kehadiran per pertemuan (tanggal, jam check-in, metode validasi QR di kelas, ruang lab, dan verifikasi dosen).

---

## 9. Halaman Nilai (`docs/reference/grades-mahasiswa.png`)

1. **Header Halaman**:
   - Ikon grafik batang `bar-chart-3` navy (`#17306B`) besar, judul "Nilai", dan deskripsi "Lihat dan kelola nilai dari semua mata kuliah yang kamu ambil di semester ini."
2. **4 Stat Cards Horizontal**:
   - **IPK Semester Ini**: Ikon toga (`graduation-cap`) biru muda, angka tabular `3.72`, dan indikator tren hijau `↑ 0.18 dari semester lalu`.
   - **Total SKS**: Ikon buku (`book-open`) hijau muda, rasio `18 / 24 SKS`, progress bar aksen biru (`#2563EB`) beserta persentase `75%`.
   - **Mata Kuliah Dinilai**: Ikon centang (`check-circle`) ungu muda, rasio `10 / 12 mata kuliah`, progress bar hijau (`#10B981`) beserta persentase `83%`.
   - **Mata Kuliah Belum Selesai**: Ikon jam (`clock`) kuning/oranye muda, rasio `2 / 12 mata kuliah`, progress bar oranye (`#F59E0B`) beserta persentase `17%`.
3. **Kolom Kiri (~68%) — Daftar Nilai Mata Kuliah**:
   - **Header Kartu**: Judul "Daftar Nilai Mata Kuliah" dan pemilih semester `Semester 3 (Ganjil 2026/2027) ▾` berikon kalender.
   - **Filter Tab Pills**: `Semua` (aktif berlatar biru primer), `Semester 1` hingga `Semester 8` (teks abu-abu slate dengan transisi hover).
   - **Tabel 12 Baris Lengkap (Persis Acuan)**:
     - Kolom: `No`, `Kode MK`, `Mata Kuliah`, `SKS`, `Nilai Angka`, `Nilai Huruf`, `Status`, `Aksi`.
     - 11 mata kuliah bernilai (IF2101 sampai IF2111) dengan pill nilai angka hijau muda (`bg-emerald-50 text-emerald-700`), huruf mutu tebal (`A`, `A-`, `B+`, `B`, `B-`, `C+`), status badge `Lulus` hijau.
     - 1 mata kuliah berjalan (IF2112 Pengembangan Aplikasi Mobile) dengan tanda `-` dan badge `Belum Dinilai` oranye.
     - Tombol aksi `Lihat Detail >` bergaris biru muda dengan ikon chevron.
   - **Footer Tabel**: Teks informasi "Menampilkan 1-12 dari 12 mata kuliah" dan kontrol pagination `< [1] >`.
4. **Kolom Kanan (~32%)**:
   - **Distribusi Nilai**: Donut ring SVG 4-warna proporsional dengan label tengah `10 Mata Kuliah`, serta legenda perincian rentang nilai (A 5 [41.7%], B+ 4 [33.3%], B 2 [16.7%], C+ 1 [8.3%], C 0 [0%], D 0 [0%]).
   - **Performa Akademik**: Grafik kurva SVG halus (line curve) dengan gradien aksen biru di bawah garis, sumbu Y skala `0.00 - 4.00`, 6 titik lingkaran putih-biru, dan sumbu X `Mata Kuliah 1` s.d. `Mata Kuliah 6`.
   - **Pengumuman Nilai**: Header berikon pengeras suara (`megaphone`) dan tautan `Lihat Semua →`. Daftar 3 warta penilaian (UTS, Jadwal Pengumuman Nilai Akhir, Perubahan Bobot Penilaian) dengan ikon berwarna dan tanggal rilis.
5. **Modal Interaktif Detail Nilai**:
---

## 10. Halaman Materi Pembelajaran (`docs/reference/materials-mahasiswa.png`)

1. **Header Halaman**:
   - Ikon buku terbuka `book-open` navy (`#17306B`) besar, judul "Materi Pembelajaran", dan deskripsi "Akses semua materi pembelajaran, mulai dari modul, slide, video, hingga referensi yang disediakan oleh dosen untuk mendukung proses belajar Anda."
2. **Active Course Banner Card**:
   - Kotak ikon navy tua dengan simbol kode `</>` (`code`).
   - Kode mata kuliah pill `IF2101`, judul mata kuliah tebal "Pemrograman Berorientasi Objek & Desain Pola", dan subteks "Semester 3 • Teknik Informatika • Kelas A".
   - Foto gedung kampus modern dengan langit cerah dan lanskap hijau di sisi tengah-kanan banner.
   - Sisi kanan: Widget **Progress Materi** dengan judul tebal, progress bar horizontal biru 35.7%, teks "5 / 14 Materi", dan persentase "35.7% Selesai".
3. **Filter Jenis Materi (Tab Pills)**:
   - "Semua Materi" aktif berlatar biru primer (`#2563EB`) berikon dokumen.
   - Tab "Modul", "Video", "Tugas", "Kuis", "Referensi" berteks netral dengan ikon yang sesuai dan transisi hover.
4. **Kolom Kiri (~68%) — Bab & Topik Accordion**:
   - **Bab 1: Pengenalan dan Konsep Dasar OOP**:
     - Circle badge nomor `1` biru, subjudul penjelasan, status `3 / 3 Materi`, dan chevron dropdown.
     - Item 1.1 `Konsep Dasar OOP` (Modul • 45 menit • Dibuat 10 Sep 2026, status `✓ Selesai`).
     - Item 1.2 `Video Pembahasan OOP` (Video • 1 jam • Dibuat 11 Sep 2026, status `✓ Selesai`).
     - Item 1.3 `Studi Kasus dan Latihan` (Referensi • 30 menit • Dibuat 12 Sep 2026, status `✓ Selesai`).
   - **Bab 2: Desain Pola (Design Pattern)**:
     - Circle badge nomor `2` biru, status `2 / 4 Materi`, dan chevron dropdown.
     - Item 2.1 `Singleton Pattern` (Modul • 50 menit • Dibuat 14 Sep 2026, status `✓ Selesai`).
     - Item 2.2 `Factory Pattern` (Modul • 40 menit • Dibuat 15 Sep 2026, status `✓ Selesai`).
     - Item 2.3 `Observer Pattern` (Video • 55 menit • Dibuat 16 Sep 2026, status `● Belum Selesai` berbadge biru).
     - Item 2.4 `Implementasi Design Pattern` (Tugas • Deadline 20 Sep 2026, status `🔒 Tersedia Nanti` berbadge abu-abu).
   - **Bab 3: Framework dan Studi Kasus Lanjutan**:
     - Circle badge nomor `3` biru, status `0 / 3 Materi`, dan chevron kanan (status tertutup/terkunci).
5. **Kolom Kanan (~32%) — Widget & Asisten AI**:
   - **Filter Mata Kuliah**: Input pencarian `Cari materi berdasarkan judul...` + tombol filter, serta 3 pemilih (Semua Mata Kuliah, Semua Jenis Materi, Semua Status).
   - **Ringkasan Materi**: 4 tile horizontal: `14` Total Materi, `5` Selesai, `6` Dalam Proses, `3` Terkunci.
   - **Aktivitas Terbaru**: Header berikon dan tautan "Lihat Semua →", berisi 4 kronologi aktivitas pembelajaran terbaru.
   - **Arunika AI Copilot**: Kartu gradien pastel lembut dengan avatar bot AI, deskripsi kemampuan merangkum materi dan membuat flashcard, serta tombol aksi "Coba Sekarang →" yang terhubung ke drawer asisten AI.
6. **Modal Viewer Materi**:
   - Modal popup saat materi diklik: pratinjau silabus & RPS, dosen pengampu, estimasi durasi, tanggal rilis, tombol unduh berkas PDF materi perkuliahan, dan status penyelesaian.

---

## 11. Halaman Diskusi (`docs/reference/discussions-mahasiswa.png`)

1. **Header Halaman**:
   - Lingkaran navy tua (`#1A2E5E`) berikon chat bubble putih `message-circle`.
   - Judul tebal "Diskusi", dan deskripsi "Diskusi, tanya jawab, dan berbagi pengetahuan bersama dosen dan teman kelas."
   - Sisi kanan: Tombol aksi primer biru solid "+ Buat Diskusi Baru" (`bg-[#2563EB]`) yang membuka modal interaktif pembuatan thread baru.
2. **Filter Tab Pills & Pencarian**:
   - Tab "Semua" aktif (`bg-[#2563EB] text-white`) berikon chat square.
   - Tab "Forum Kelas", "Tanya Jawab", "Pengumuman", dan "Topik Populer" (berikon piala `trophy`).
   - Sisi kanan: Kolom input pencarian topik `Cari topik diskusi...` dan tombol `Filter` berikon corong.
3. **Kolom Kiri (~68%) — Kotak Tulis Cepat & Daftar Thread**:
   - **Kotak Tulis Diskusi Cepat**: Kartu putih dengan ikon dokumen melingkar biru muda `rounded-full bg-blue-50 text-blue-600`, judul pertanyaan "Ada pertanyaan atau ingin berbagi sesuatu?", subteks ajakan "Tulis di sini untuk memulai diskusi baru...", tombol "Lampirkan File" berikon paperclip, dan tombol "Kirim" biru berikon pesawat kertas.
   - **Daftar Thread Diskusi (Single Card Container)**: Satu kontainer kartu putih utuh (`border border-line rounded-[10px] divide-y divide-line/70 shadow-2xs overflow-hidden`) berisi 7 baris thread diskusi dengan hover effect:
     - **Thread 1**: Dr. Budi Rahardjo, S.T., M.Kom. (Dosen Pengampu) — Pinned berikon pin biru, badge "Pengumuman" biru, 142 komentar, 2.4k views, 3 hari yang lalu, menu opsi `...`.
     - **Thread 2**: Andi Saputra — Initials avatar `AS` ungu, dot biru, badge "Tanya Jawab" hijau, 28 komentar, 567 views, 5 jam yang lalu.
     - **Thread 3**: Siti Nurhaliza — Foto avatar mahasiswi, dot biru, badge "Diskusi" ungu, 45 komentar, 892 views, 7 jam yang lalu.
     - **Thread 4**: Raka Maulana — Initials avatar `RM` toska/cyan, dot biru, badge "Forum Kelas" biru, 19 komentar, 342 views, 10 jam yang lalu.
     - **Thread 5**: Dewi Lestari — Foto avatar mahasiswi, dot biru, badge "Diskusi" ungu, 12 komentar, 201 views, 1 hari yang lalu.
     - **Thread 6**: Bayu Pratama — Initials avatar `B` kuning/amber, panah atas biru, badge "Tanya Jawab" hijau, 36 komentar, 674 views, 1 hari yang lalu.
     - **Thread 7**: Fajar Nugroho — Foto avatar mahasiswa, panah atas biru, badge "Berbagi" oranye, 20 komentar, 412 views, 2 hari yang lalu.
4. **Kolom Kanan (~32%) — Statistik, Kategori & Diskusi Terpopuler**:
   - **Statistik Diskusi**: 4 kotak metrik (grid 2x2):
     - `128` Total Diskusi (ikon chat bubble biru muda).
     - `526` Total Komentar (ikon square message biru muda).
     - `8.4k` Total Dilihat (ikon mata biru muda).
     - `247` Pengguna Aktif (ikon users biru muda).
   - **Kategori**: Header dengan tautan "Lihat Semua →", berisi 6 kategori:
     - "Semua" (aktif, 128 diskusi).
     - "Forum Kelas" (42 diskusi).
     - "Tanya Jawab" (38 diskusi).
     - "Pengumuman" (16 diskusi).
     - "Diskusi" (22 diskusi).
     - "Berbagi" (10 diskusi).
   - **Diskusi Terpopuler**: Header dengan tautan "Lihat Semua →", berisi 5 peringkat teratas:
     - Peringkat 1: Badge nomor 1 kuning-amber, foto avatar Dr. Budi, judul thread, ikon `142` • ikon `2.4k` | `3 hari yang lalu`.
     - Peringkat 2: Badge nomor 2 biru muda, initials avatar `AS`, judul thread, ikon `28` • ikon `567` | `5 jam yang lalu`.
     - Peringkat 3: Badge nomor 3 biru muda, foto avatar Siti, judul thread, ikon `45` • ikon `892` | `7 jam yang lalu`.
     - Peringkat 4: Badge nomor 4 biru muda, foto avatar Dewi, judul thread, ikon `12` • ikon `201` | `1 hari yang lalu`.
     - Peringkat 5: Badge nomor 5 biru muda, foto avatar Fajar, judul thread, ikon `36` • ikon `674` | `1 hari yang lalu`.
5. **Modal Interaktif**:
   - **Modal Buat Diskusi Baru**: Form pembuatan thread dengan input judul, pilihan kategori, pilihan mata kuliah, textarea konten diskusi, lampiran file, dan tombol "Publikasikan Diskusi".
   - **Modal Detail Thread & Balasan**: Pratinjau lengkap isi diskusi dosen/mahasiswa beserta daftar balasan komentar, tombol like/upvote, dan form menulis tanggapan cepat.

---

## 12. Halaman Pengaturan (`docs/reference/settings-mahasiswa.png`)

1. **Header Halaman**:
   - Lingkaran navy tua (`#1A2E5E`) berikon gear/settings solid putih `settings`.
   - Judul tebal "Pengaturan", dan deskripsi "Kelola profil, akun, notifikasi, tampilan, dan preferensi lainnya."
   - Sisi kanan: Banner kutipan inspirasional bergradien biru-ungu lembut dengan latar siluet pegunungan & matahari terbit (`public/images/settings-sunrise-art.png`):
     - Teks kutipan: *"Pengaturan yang tepat, pengalaman belajar yang lebih baik."*
     - Penulis: *— Arunika*
2. **Sub-Navigasi Samping Kiri (~18%)**:
   - Kategori **Akun Saya**:
     - "Profil & Informasi" (aktif dengan background biru muda lembut `bg-blue-50/80 text-[#2563EB]` dan border kiri aksen biru).
     - "Keamanan Akun"
     - "Preferensi Pembelajaran"
   - Kategori **Notifikasi**:
     - "Notifikasi"
     - "Jadwal & Pengingat"
   - Kategori **Tampilan**:
     - "Tema & Mode Gelap"
     - "Bahasa & Lokalisasi"
   - Kategori **Integrasi**:
     - "Aplikasi Terhubung"
     - "Webhook & API"
   - Kategori **Lainnya**:
     - "Bantuan & Dukungan"
     - "Tentang Arunika"
3. **Main Content Area (Grid 2 Kolom ~82%)**:
   - **Kolom Kiri**:
     - **Informasi Profil**:
       - Header kartu berikon kartu identitas `id-card` + judul "Informasi Profil" + subteks.
       - Foto avatar mahasiswa Arya Pratama dalam lingkaran besar dengan badge kamera biru tua melingkar di sudut kanan bawah + tombol "Ubah Foto Profil".
       - Form rincian profil: Nama Lengkap (`Arya Pratama`), Email (`arya.pratama@student.unus.ac.id`), NIM (`23060101024`), Program Studi (`S1 Teknik Informatika`), Fakultas (`Fakultas Ilmu Komputer`), masing-masing dilengkapi ikon pensil edit interaktif.
     - **Keamanan Akun**:
       - Header kartu berikon perisai `shield-check` + judul "Keamanan Akun" + subteks.
       - Baris Password: Ikon gembok, status "Terakhir diubah 3 hari yang lalu", tombol outline "Ubah Password".
       - Baris Two-Factor Authentication (2FA): Ikon fingerprint, deskripsi, sakelar toggle (posisi nonaktif).
       - Baris Login dengan Google: Ikon resmi Google 4 warna, deskripsi, tombol "Hubungkan".
     - **Tampilan**:
       - Header kartu berikon monitor `monitor` + judul "Tampilan" + subteks.
       - Pilihan Tema: 3 kartu pilihan (Terang [aktif dengan border biru & centang], Gelap, Otomatis) lengkap dengan ilustrasi ikon matahari/bulan/komputer.
       - Pilihan Ukuran Teks: Radio button (Kecil, Normal [terpilih], Besar).
   - **Kolom Kanan**:
     - **Status Akun**:
       - Header kartu berikon lencana `badge-check` + judul "Status Akun" + subteks.
       - Banner status hijau lembut berikon centang hijau tebal: "Akun Aktif — Anda dapat mengakses semua fitur yang tersedia."
       - Tabel rincian akun: Role (`Mahasiswa`), Kampus (`Universitas Nusantara Indonesia`), Prodi (`S1 Teknik Informatika`), Terdaftar Sejak (`12 Agustus 2025`).
     - **Notifikasi**:
       - Header kartu berikon lonceng `bell` + judul "Notifikasi" + subteks.
       - 5 baris pengaturan dengan sakelar toggle interaktif:
         - Pengumuman Kelas (ikon megaphone, toggle ON)
         - Tugas & Deadline (ikon kalender, toggle ON)
         - Pesan Pribadi (ikon pesan, toggle ON)
         - Aktivitas Diskusi (ikon users, toggle OFF)
         - Notifikasi Sistem (ikon lonceng berdering, toggle ON)
     - **Bahasa & Lokalisasi**:
       - Header kartu berikon globe `globe` + judul "Bahasa & Lokalisasi" + subteks.
       - 4 dropdown pilihan konfigurasi:
         - Bahasa: `Bahasa Indonesia` (disertai ikon bendera merah putih)
         - Zona Waktu: `Asia/Jakarta (WIB)`
         - Format Tanggal: `DD MMM YYYY (contoh: 24 Sep 2026)`
         - Format Waktu: `HH:mm (contoh: 14:30)`
4. **Banner Dukungan Bawah (Full Width)**:
   - Banner baris horizontal berlatar putih dengan border lembut: Ikon info melingkar biru, teks "Butuh bantuan? Jika Anda mengalami kendala atau memiliki pertanyaan, silakan hubungi tim dukungan kami.", serta tombol aksi "Hubungi Support" berikon headset.
5. **Modal & Interaktivitas**:
   - Modal Ubah Password: Form kata sandi saat ini, kata sandi baru, konfirmasi kata sandi baru, dan tombol simpan.
   - Modal Ubah Profil: Form edit nama dan data profil langsung.
   - Toast notifikasi responsif untuk setiap perubahan preferensi dan sakelar toggle.
