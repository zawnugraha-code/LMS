<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case CampusAdmin = 'campus_admin';
    case Lecturer = 'lecturer';
    case Student = 'student';
    case Staff = 'staff';
    case Auditor = 'auditor';
    case IndustryMentor = 'industry_mentor';

    /**
     * Get the human-readable label in Indonesian.
     */
    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin (System)',
            self::CampusAdmin => 'Admin Kampus / BAAK',
            self::Lecturer => 'Dosen Pengampu',
            self::Student => 'Mahasiswa',
            self::Staff => 'Tenaga Kependidikan / Dosen PA',
            self::Auditor => 'Auditor / Reviewer Akreditasi',
            self::IndustryMentor => 'Mentor Industri / MBKM',
        };
    }

    /**
     * Get color token for badges and UI.
     */
    public function badgeColor(): string
    {
        return match ($this) {
            self::SuperAdmin => 'purple',
            self::CampusAdmin => 'indigo',
            self::Lecturer => 'blue',
            self::Student => 'emerald',
            self::Staff => 'amber',
            self::Auditor => 'rose',
            self::IndustryMentor => 'cyan',
        };
    }

    /**
     * Get role description and privileges summary.
     */
    public function description(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Akses penuh ke konfigurasi multi-kampus dan sistem global',
            self::CampusAdmin => 'Kelola kurikulum, kelas, data dosen, mahasiswa, dan sinkronisasi SIAKAD',
            self::Lecturer => 'Kelola RPS, materi, tugas, kuis, nilai OBE, dan interaksi kelas',
            self::Student => 'Mengikuti perkuliahan, akses materi offline, kerjakan tugas, kuis, dan pantau CPL',
            self::Staff => 'Monitoring mahasiswa bimbingan, early warning risk, dan administrasi akademik',
            self::Auditor => 'Evaluasi capaian CPL/CPMK, audit rubrik penilaian, dan export data akreditasi',
            self::IndustryMentor => 'Verifikasi logbook magang MBKM, evaluasi proyek industri, dan feedback',
        };
    }

    /**
     * Get dashboard default route for this role.
     */
    public function defaultRoute(): string
    {
        return match ($this) {
            self::SuperAdmin => 'admin.dashboard',
            self::CampusAdmin => 'campus.dashboard',
            self::Lecturer => 'lecturer.dashboard',
            self::Student => 'student.dashboard',
            self::Staff => 'staff.dashboard',
            self::Auditor => 'auditor.dashboard',
            self::IndustryMentor => 'mentor.dashboard',
        };
    }
}
