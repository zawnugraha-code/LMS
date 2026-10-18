<?php

namespace Database\Seeders;

use App\Models\Campus;
use App\Models\Curriculum;
use App\Models\Faculty;
use App\Models\Program;
use App\Models\Term;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProductionSeeder extends Seeder
{
    /**
     * Seed production baseline data without dummy/demo users.
     * Idempotent: uses firstOrCreate to prevent duplicates.
     */
    public function run(): void
    {
        // 1. Perguruan Tinggi Utama
        $campus = Campus::firstOrCreate(
            ['code' => 'UNI'],
            [
                'name' => 'Universitas Nusantara Indonesia',
                'domain' => 'uni.ac.id',
                'tagline' => 'Unggul dalam Riset, Inovasi, dan Berkarakter Nusantara',
                'address' => 'Jl. Boulevard Kampus Merdeka No. 101, Jakarta Selatan',
                'website' => 'https://uni.ac.id',
                'is_active' => true,
                'settings' => [
                    'timezone' => 'Asia/Jakarta',
                    'primary_color' => '#1A2E5E',
                    'obe_enabled' => true,
                    'gamification_enabled' => true,
                    'ai_copilot_enabled' => true,
                ],
            ]
        );

        // 2. Fakultas
        $fkom = Faculty::firstOrCreate(
            ['code' => 'FKOM', 'campus_id' => $campus->id],
            [
                'name' => 'Fakultas Ilmu Komputer',
                'description' => 'Fakultas Ilmu Komputer dan Teknologi Informasi',
            ]
        );

        // 3. Program Studi
        $prodiIF = Program::firstOrCreate(
            ['code' => 'IF', 'faculty_id' => $fkom->id],
            [
                'name' => 'S1 Teknik Informatika',
                'degree_level' => 'S1',
                'accreditation' => 'Unggul',
            ]
        );

        $prodiSI = Program::firstOrCreate(
            ['code' => 'SI', 'faculty_id' => $fkom->id],
            [
                'name' => 'S1 Sistem Informasi',
                'degree_level' => 'S1',
                'accreditation' => 'Unggul',
            ]
        );

        // 4. Kurikulum Berbasis OBE
        Curriculum::firstOrCreate(
            ['program_id' => $prodiIF->id, 'year' => 2024],
            [
                'name' => 'Kurikulum Berbasis OBE 2024 - Teknik Informatika',
                'is_active' => true,
            ]
        );

        Curriculum::firstOrCreate(
            ['program_id' => $prodiSI->id, 'year' => 2024],
            [
                'name' => 'Kurikulum Berbasis OBE 2024 - Sistem Informasi',
                'is_active' => true,
            ]
        );

        // 5. Semester Aktif Awal (Ganjil 2026/2027)
        Term::firstOrCreate(
            ['academic_year' => '2026/2027', 'semester_type' => 'odd'],
            [
                'name' => 'Semester Ganjil 2026/2027',
                'campus_id' => $campus->id,
                'start_date' => Carbon::create(2026, 9, 1),
                'end_date' => Carbon::create(2027, 1, 31),
                'is_active' => true,
                'status' => 'aktif',
            ]
        );
    }
}
