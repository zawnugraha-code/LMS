# Aturan Desain Workspace: Navy Academic (Arunika)

Standar ini wajib dipatuhi pada seluruh perubahan visual, penambahan komponen baru, dan penyuntingan view di repositori Arunika berdasarkan gambar acuan `docs/reference/dashboard-mahasiswa.png`.

## 1. Pantangan Mutlak (Zero Tolerance)
- DILARANG menggunakan emoji sebagai ikon, label, atau hiasan visual di seluruh view Blade, CSS, atau basis data.
- DILARANG menggunakan gradasi (`bg-gradient-*`, teks gradasi), aura glow, blob filter, atau glassmorphism (`backdrop-blur-*`).
- DILARANG menggunakan bayangan tebal (`shadow-2xl`, `shadow-xl`, `shadow-lg`, shadow berwarna).
- DILARANG menggunakan radius membulat ekstrem (`rounded-3xl`, `rounded-2xl`). Kartu berukuran `rounded-[8px]`, tombol `rounded-[6px]`, badge pill hanya untuk status tugas.
- DILARANG memuat font dari Google CDN. Seluruh font wajib dimuat mandiri (*self-hosted*) melalui paket `@fontsource-variable/inter`.

## 2. Token & Tipografi Wajib
- Tipografi: Inter Variable (`@fontsource-variable/inter`) untuk seluruh antarmuka. Angka metrik menggunakan `tabular-nums`.
- Palet Warna: 
  - Sidebar: Navy-900 (`#1A2E5E`), Navy-700 (`#2C4A8F`)
  - Tombol & Header: Primary Navy (`#17306B`)
  - Aksen & Bar: Accent Blue (`#2F62C8`), Accent Soft (`#E6EEFB`)
  - Latar Halaman: Canvas (`#F4F7FB`), Surface Putih (`#FFFFFF`), Tile (`#F2F5FA`)
  - Garis Pembatas: Line (`#E3E8F0`), Line Strong (`#C9D2E0`)
  - Teks: Text (`#14213D`), Text Muted (`#5E6B85`), Text Subtle (`#8792A8`)
  - Status: Success (`#1E9B57`), Danger (`#D93636`), Warning (`#E99A1F`)

## 3. Komponen & Penamaan
- Ikonografi: Gunakan `<x-icon name="..." />` dengan Lucide Icons inline SVG 1.75px monokrom (`currentColor`).
- Asisten AI: Diberi nama **Pendamping Belajar** (Slide-Over Drawer).
- Shell: Sidebar 234px navy tua sticky 100vh, Topbar 48px putih sticky top-0.
