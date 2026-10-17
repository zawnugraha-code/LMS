<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class DosenPlaceholderController extends Controller
{
    public function presensi(): View
    {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Presensi Perkuliahan',
            'menuDescription' => 'Kelola kehadiran mahasiswa per sesi pertemuan perkuliahan, kode QR, dan perizinan.',
            'badgeBatch' => 'Batch 2',
        ]);
    }

    public function bukuNilai(): View
    {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Buku Nilai (Gradebook)',
            'menuDescription' => 'Rekapitulasi nilai akhir mahasiswa, perhitungan bobot komponen OBE, dan ekspor transkrip.',
            'badgeBatch' => 'Batch 2',
        ]);
    }

    public function diskusi(): View
    {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Forum Diskusi Akademik',
            'menuDescription' => 'Interaksi tanya jawab materi perkuliahan bersama mahasiswa di kelas yang Anda ampu.',
            'badgeBatch' => 'Batch 2',
        ]);
    }

    public function pengaturan(): View
    {
        return view('pages.dosen.placeholder', [
            'menuTitle' => 'Pengaturan Akun & Preferensi',
            'menuDescription' => 'Kelola profil dosen, preferensi notifikasi, keamanan akun, dan sinkronisasi SIAKAD.',
            'badgeBatch' => 'Batch 2',
        ]);
    }
}
