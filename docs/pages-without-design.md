# Daftar Halaman Tanpa Desain Acuan (Pages Without Design)
*Dokumentasi Halaman & Sub-Halaman Baru yang Dibangun dengan Komponen & Pola Visual yang Ada*

Sesuai instruksi Aturan Emas, seluruh halaman di bawah ini belum memiliki gambar desain referensi khusus. Halaman-halaman ini dibangun secara konsisten menggunakan fondasi desain yang sudah ada:
- Tipografi: Inter, hierarki Navy Academic.
- Kontainer: Kartu putih berborder tipis (`border border-line rounded-[10px] bg-surface shadow-2xs`).
- Tile Metrik: Stat-tile dengan angka tebal dan label teks abu-abu lembut.
- Tabel & Daftar: Baris dengan padding rapi dan garis pemisah tipis (`divide-y divide-line/70`).
- Skema Warna: Navy-900 `#1A2E5E`, Blue Primary `#2563EB`, Canvas `#F4F7FB`, Line `#E3E8F0`.

---

## Daftar Halaman Baru

| No | Nama Halaman | Route / URL | Tujuan & Komponen yang Dipakai |
| :---: | :--- | :--- | :--- |
| 1 | **Detail Mata Kuliah** | `/app/courses/{class}` | Menampilkan silabus/RPS, profil dosen pengampu, jadwal kuliah lengkap, bobot penilaian, dan daftar CPMK. |
| 2 | **Ruang Kelas Virtual** | `/app/courses/{class}/classroom` | Timeline 14 pertemuan perkuliahan, akses langsung materi, kuis, dan tugas per pertemuan. |
| 3 | **Viewer Materi Kuliah** | `/app/materials/{material}/view` | Viewer inline PDF dokumen materi, pemutar video dengan tracking progres $\ge 90\%$, dan tombol "Tandai Selesai". |
| 4 | **Pengerjaan Tugas Mahasiswa**| `/app/assignments/{assignment}/submit`| Form pengunggahan berkas tugas, input teks/tautan, tampilan kriteria rubrik (baca-saja), simpan draft, dan riwayat revisi. |
| 5 | **Detail & Pengerjaan Ujian** | `/app/quizzes/{quiz}/attempt` | Halaman tata tertib ujian, countdown timer ujian tersinkronisasi server, soal objektif & esai, auto-save jawaban. |
| 6 | **Detail Transkrip Nilai MK** | `/app/grades/{enrollment}` | Rincian nilai komponen perkuliahan (Tugas, Kuis, Presensi, UTS, UAS), kontribusi nilai ke CPMK/CPL, tombol unduh KHS PDF. |
| 7 | **Detail Thread Diskusi** | `/app/discussions/{thread}` | Tampilan thread lengkap, daftar balasan komentar berjenjang, form balasan cepat, tombol upvote, dan penanda jawaban terbaik. |
| 8 | **Jadwal Kuliah Mingguan** | `/app/schedule` | Tabel matriks jadwal perkuliahan mingguan (Senin–Jumat) sesuai kelas yang diambil mahasiswa. |
| 9 | **Capaian Kompetensi CPL** | `/app/cpl` | Penjelasan rincian perolehan CPL prodi, pemetaan CPMK per mata kuliah, dan pembuktian nilai asesmen. |
| 10 | **Buku Besar XP (Audit Log)**| `/app/xp/ledger` | Tabel transaksional perolehan dan riwayat pengurangan XP lengkap dengan timestamp dan sumber aktivitas. |
| 11 | **Semua Notifikasi** | `/app/notifications` | Daftar paginasi seluruh riwayat notifikasi mahasiswa dengan opsi filter per kategori. |
| 12 | **Verifikasi Sertifikat Publik**| `/verifikasi/{code}` | Halaman publik untuk memvalidasi keaslian sertifikat dan kompetensi lulusan tanpa perlu login. |
| 13 | **Form Tiket Support** | Modal / `/app/support/ticket` | Form pengajuan kendala teknis dan bantuan akademik ke tim support Arunika. |
| 14 | **Sub-Halaman Pengaturan** | `/app/settings/{section}` | Halaman sederhana pola kartu untuk preferensi sekunder (Aplikasi Terhubung, Webhook & API, Bantuan & Dukungan). |
