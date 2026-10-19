<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Arunika Learning System Configuration
    |--------------------------------------------------------------------------
    */

    // Komponen Penilaian Default Saat Kelas Baru Dibuat (Total Wajib 100%)
    'assessment_components' => [
        'tugas' => 30,
        'kuis' => 20,
        'uts' => 25,
        'uas' => 25,
    ],

    // Jumlah Pertemuan Perkuliahan Default per Kelas (1..N mingguan)
    'default_meetings_count' => 16,

    // Konfigurasi Impor CSV
    'import' => [
        'max_file_size_kb' => 2048, // 2 MB
        'max_rows' => 1000,
    ],

    // Konfigurasi Penyimpanan Berkas Privat
    'storage' => [
        'disk' => 'private',
        'import_path' => 'imports',
        'export_path' => 'exports',
    ],

    // Demo Role Switcher Flag (Hanya diizinkan di non-production)
    'demo_role_switcher_enabled' => env('DEMO_ROLE_SWITCHER_ENABLED', false),
];
