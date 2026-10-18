<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminPlaceholderController extends Controller
{
    public function dashboard(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Beranda Administrator',
            'badge' => 'Batch 1',
            'description' => 'Halaman Dasbor Administrator LMS Arunika.',
        ]);
    }

    public function semester(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Manajemen Semester & Kalender Akademik',
            'badge' => 'Batch 1',
            'description' => 'Halaman Kelola Semester.',
        ]);
    }

    public function mataKuliah(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Manajemen Mata Kuliah',
            'badge' => 'Batch 1',
            'description' => 'Halaman Kelola Mata Kuliah.',
        ]);
    }

    public function kelas(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Manajemen Kelas Perkuliahan',
            'badge' => 'Batch 1',
            'description' => 'Halaman Kelola Kelas Perkuliahan.',
        ]);
    }

    public function pendaftaranMahasiswa(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Pendaftaran Mahasiswa ke Kelas',
            'badge' => 'Batch 1',
            'description' => 'Halaman Pendaftaran Peserta Kelas.',
        ]);
    }

    public function cari(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Pencarian Global Administrator',
            'badge' => 'Batch 1',
            'description' => 'Halaman Hasil Pencarian Global.',
        ]);
    }

    public function dosen(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Manajemen Pengguna: Dosen',
            'badge' => 'Batch 2',
            'description' => 'Fitur pengelolaan akun dosen, sinkronisasi NIDN, penugasan massal, dan pengaturan hak akses sedang dalam tahap pengembangan untuk Batch 2.',
        ]);
    }

    public function mahasiswa(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Manajemen Pengguna: Mahasiswa',
            'badge' => 'Batch 2',
            'description' => 'Fitur import akun mahasiswa, penomoran NIM otomatis, manajemen status akademik, dan riwayat studi sedang dalam tahap pengembangan untuk Batch 2.',
        ]);
    }

    public function logAktivitas(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Audit Log & Riwayat Aktivitas',
            'badge' => 'Batch 2',
            'description' => 'Fitur audit log aktivitas lengkap, penelusuran forensik perubahan data, filter kategori keamanan, dan ekspor riwayat sistem sedang disiapkan untuk Batch 2.',
        ]);
    }

    public function pengaturan(Request $request): View
    {
        return view('pages.admin.placeholder', [
            'title' => 'Pengaturan Sistem & Kampus',
            'badge' => 'Batch 2',
            'description' => 'Fitur konfigurasi identitas universitas, kurikulum OBE global, preferensi notifikasi, dan integrasi SIAKAD sedang disiapkan untuk Batch 2.',
        ]);
    }
}
