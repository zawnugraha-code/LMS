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
        // 1. Course Assessment Components (Komponen Penilaian Kelas)
        if (! Schema::hasTable('course_assessment_components')) {
            Schema::create('course_assessment_components', function (Blueprint $table) {
                $table->id();
                $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $table->string('jenis', 20); // tugas, kuis, uts, uas
                $table->unsignedTinyInteger('bobot'); // persen: misal 30, 20, 25, 25
                $table->timestamps();
                $table->unique(['class_id', 'jenis']);
            });
        }

        // 2. Announcements (Pengumuman Kelas & Pengumuman Akademik)
        if (! Schema::hasTable('announcements')) {
            Schema::create('announcements', function (Blueprint $table) {
                $table->id();
                $table->string('ulid', 26)->unique();
                $table->foreignId('class_id')->nullable()->constrained('classes')->cascadeOnDelete(); // null = Akademik Kampus
                $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
                $table->string('title');
                $table->text('content');
                $table->dateTime('published_at')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('course_assessment_components');
    }
};
