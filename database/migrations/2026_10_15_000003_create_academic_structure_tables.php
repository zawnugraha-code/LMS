<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Faculties (Fakultas)
        Schema::create('faculties', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('name'); // e.g. Fakultas Ilmu Komputer & Teknologi Informasi
            $table->string('code', 20); // e.g. FKOM
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Study Programs (Program Studi)
        Schema::create('programs', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('faculty_id')->constrained('faculties')->cascadeOnDelete();
            $table->string('name'); // e.g. S1 Teknik Informatika
            $table->string('code', 20); // e.g. IF
            $table->string('degree_level', 10)->default('S1'); // S1, S2, D3, D4
            $table->string('accreditation', 30)->default('Unggul');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 3. Academic Terms (Periode Semester)
        Schema::create('terms', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('campus_id')->constrained('campuses')->cascadeOnDelete();
            $table->string('name'); // e.g. Semester Ganjil 2026/2027
            $table->string('academic_year', 20); // e.g. 2026/2027
            $table->string('semester_type', 15)->default('odd'); // odd, even, short
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 4. Curricula (Kurikulum)
        Schema::create('curricula', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->year('year'); // e.g. 2024
            $table->string('name'); // e.g. Kurikulum Berbasis OBE 2024
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 5. Courses (Mata Kuliah)
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('curriculum_id')->constrained('curricula')->cascadeOnDelete();
            $table->string('code', 20); // e.g. IF2104
            $table->string('name'); // e.g. Algoritma dan Pemrograman Lanjut
            $table->unsignedTinyInteger('credits')->default(3); // SKS
            $table->unsignedTinyInteger('semester')->default(1);
            $table->text('description')->nullable();
            $table->json('syllabus')->nullable();
            $table->timestamps();
        });

        // 6. Classes (Kelas Paralel)
        Schema::create('classes', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->foreignId('term_id')->constrained('terms')->cascadeOnDelete();
            $table->foreignId('lecturer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('section', 20); // e.g. IF-3A
            $table->string('room', 50)->nullable(); // e.g. Lab Multimedia 2
            $table->string('schedule_day', 20)->nullable(); // e.g. Senin
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->unsignedSmallInteger('capacity')->default(40);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        // 7. Enrollments (KRS Mahasiswa di Kelas)
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('enrolled'); // enrolled, dropped, completed
            $table->decimal('final_score', 8, 2)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->timestamps();
            $table->unique(['class_id', 'user_id']);
        });

        // 8. Meetings (Pertemuan Perkuliahan Mingguan)
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->unsignedTinyInteger('week')->default(1); // Minggu ke-1 s/d 16
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('date')->nullable();
            $table->time('start_time')->nullable();
            $table->time('end_time')->nullable();
            $table->string('mode', 20)->default('offline'); // offline, online, hybrid
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        // 9. Materials (Materi Perkuliahan)
        Schema::create('materials', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('title');
            $table->string('type', 20)->default('pdf'); // pdf, slide, video, document, link
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('external_url')->nullable();
            $table->boolean('is_downloadable')->default(true);
            $table->unsignedSmallInteger('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materials');
        Schema::dropIfExists('meetings');
        Schema::dropIfExists('enrollments');
        Schema::dropIfExists('classes');
        Schema::dropIfExists('courses');
        Schema::dropIfExists('curricula');
        Schema::dropIfExists('terms');
        Schema::dropIfExists('programs');
        Schema::dropIfExists('faculties');
    }
};
