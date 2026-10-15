# Design Decisions Log

> Keputusan arsitektur dan desain yang dibuat selama pengembangan Arunika.

## D1: Multi-tenancy — Soft Tenancy (`campus_id`)

**Tanggal**: 2026-10-15
**Konteks**: Perlu mendukung multi-kampus dalam satu instalasi.
**Opsi**:
1. `stancl/tenancy` (database per tenant atau schema per tenant)
2. Soft tenancy via `campus_id` column scoping

**Keputusan**: Opsi 2 — Soft tenancy
**Alasan**: Lebih sederhana, lebih mudah di-debug, cukup untuk skala awal. Bisa diupgrade ke stancl/tenancy nanti jika diperlukan. Semua query di-scope otomatis via BelongsToCampus trait + global scope.

---

## D2: Primary Key — Dual Column (bigint + ULID)

**Tanggal**: 2026-10-15
**Konteks**: Butuh ID yang efisien untuk database dan aman untuk public API.
**Opsi**:
1. UUID sebagai primary key
2. ULID sebagai primary key
3. Bigint PK + ULID secondary column

**Keputusan**: Opsi 3 — Dual column
**Alasan**: Bigint optimal untuk InnoDB clustering dan join performance. ULID sebagai unique indexed column untuk URL/API exposure — sortable by time, tidak expose record count. Best of both worlds.

---

## D3: Enum Storage — String + PHP Enum Cast

**Tanggal**: 2026-10-15
**Konteks**: Bagaimana menyimpan status/enum values di database.
**Opsi**:
1. MySQL ENUM type
2. String column + PHP Enum cast

**Keputusan**: Opsi 2 — String column
**Alasan**: MySQL ENUM memerlukan ALTER TABLE untuk menambah value baru (mahal di tabel besar). String column + PHP Enum cast lebih fleksibel, portable antar database, dan mudah di-migrate.

---

## D4: Vector Store — App-level Cosine Similarity

**Tanggal**: 2026-10-15
**Konteks**: Perlu menyimpan dan mencari embeddings untuk RAG/AI features.
**Opsi**:
1. pgvector (PostgreSQL) — butuh ganti database
2. MySQL native vector (belum stabil di 8.4)
3. App-level: BLOB storage + cosine similarity di PHP + cache
4. External: Qdrant/Pinecone

**Keputusan**: Opsi 3 sebagai default, Opsi 4 sebagai adapter opsional
**Alasan**: Tidak menambah dependency, cukup untuk skala kelas/mata kuliah (pre-filter drastis mengurangi search space). Interface VectorStore memungkinkan swap ke Qdrant untuk skala besar.

---

## D5: Gamification — Opt-in + Team/Season

**Tanggal**: 2026-10-15
**Konteks**: Gamifikasi harus memotivasi tanpa menimbulkan toxic competition.
**Opsi**:
1. Global leaderboard wajib
2. Opt-in leaderboard per individu
3. Team/season-based leaderboard opt-in

**Keputusan**: Opsi 3
**Alasan**: Team-based mendorong kolaborasi. Season reset mencegah gap permanen antara early adopter dan pendatang baru. Opt-in menghormati preferensi mahasiswa.

---

## D6: AI Output — Always Draft

**Tanggal**: 2026-10-15
**Konteks**: AI bisa menghasilkan output yang salah (hallucination).
**Keputusan**: Semua output AI berstatus "draft" dan harus di-approve oleh dosen sebelum dipublikasikan ke mahasiswa.
**Alasan**: Mencegah dampak hallucination pada proses akademik. Dosen tetap memiliki kontrol penuh.

---

## D7: Chart Library — Chart.js

**Tanggal**: 2026-10-15
**Konteks**: Butuh library chart untuk analytics dan dashboard.
**Opsi**:
1. ApexCharts (~450KB)
2. Chart.js (~200KB)

**Keputusan**: Chart.js
**Alasan**: Lebih ringan, sufficient untuk semua kebutuhan (line, bar, radar, doughnut), integrasi mudah via Alpine.js component.

---

## D8: PDF Generation — DomPDF

**Tanggal**: 2026-10-15
**Konteks**: Butuh generate PDF untuk sertifikat, laporan, transkrip.
**Opsi**:
1. barryvdh/laravel-dompdf
2. Browsershot (headless Chrome)

**Keputusan**: DomPDF sebagai default
**Alasan**: Tidak butuh headless browser, lebih simple deployment. Browsershot tersedia sebagai fallback untuk layout kompleks.

---

## D9: OneDrive Compatibility — Junction Links

**Tanggal**: 2026-10-15
**Konteks**: Project di-develop di folder OneDrive yang memiliki restriksi `is_writable()`.
**Keputusan**: Gunakan Windows junction link dari `bootstrap/cache` ke `C:\temp\arunika-cache`.
**Alasan**: OneDrive menambahkan deny ACL pada subdirectory yang menyebabkan PHP `is_writable()` return false meskipun file write berhasil. Junction ke folder non-OneDrive menyelesaikan masalah.
