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
        // === 1. ASSIGNMENTS & SUBMISSIONS ===
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->decimal('max_score', 8, 2)->default(100.00);
            $table->dateTime('due_at');
            $table->string('late_policy', 20)->default('allow_penalty'); // allow_penalty, reject, allow_free
            $table->boolean('allow_resubmit')->default(true);
            $table->json('rubric')->nullable();
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->string('status', 20)->default('submitted'); // draft, submitted, graded, returned
            $table->text('feedback')->nullable();
            $table->json('rubric_scores')->nullable();
            $table->dateTime('submitted_at')->nullable();
            $table->dateTime('graded_at')->nullable();
            $table->foreignId('graded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // === 2. QUIZZES & QUESTIONS ===
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('meeting_id')->nullable()->constrained('meetings')->nullOnDelete();
            $table->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(60);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->decimal('pass_score', 8, 2)->default(70.00);
            $table->boolean('is_randomize_questions')->default(true);
            $table->boolean('is_randomize_options')->default(true);
            $table->json('settings')->nullable();
            $table->timestamps();
        });

        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->string('type', 20)->default('multiple_choice'); // multiple_choice, essay, true_false
            $table->longText('prompt');
            $table->json('options')->nullable();
            $table->json('correct_answer')->nullable();
            $table->decimal('points', 6, 2)->default(10.00);
            $table->string('bloom_level', 20)->default('C3'); // C1, C2, C3, C4, C5, C6
            $table->text('explanation')->nullable();
            $table->timestamps();
        });

        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->dateTime('started_at');
            $table->dateTime('completed_at')->nullable();
            $table->decimal('score', 8, 2)->nullable();
            $table->json('answers')->nullable();
            $table->boolean('is_passed')->default(false);
            $table->json('proctoring_logs')->nullable(); // tab switches, focus lost
            $table->timestamps();
        });

        // === 3. OBE (OUTCOME-BASED EDUCATION) ===
        // CPL: Capaian Pembelajaran Lulusan (Program Learning Outcomes)
        Schema::create('cpls', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('program_id')->constrained('programs')->cascadeOnDelete();
            $table->string('code', 20); // e.g. CPL-01, CPL-02
            $table->string('category', 30)->default('special_skills'); // attitude, knowledge, general_skills, special_skills
            $table->text('description');
            $table->decimal('target_score', 5, 2)->default(65.00);
            $table->timestamps();
        });

        // CPMK: Capaian Pembelajaran Mata Kuliah (Course Learning Outcomes)
        Schema::create('cpmks', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('course_id')->constrained('courses')->cascadeOnDelete();
            $table->string('code', 20); // e.g. CPMK-1, CPMK-2
            $table->text('description');
            $table->decimal('weight', 5, 2)->default(25.00); // bobot persentase
            $table->timestamps();
        });

        // Sub-CPMK: Indikator pencapaian CPMK per topik/pertemuan
        Schema::create('sub_cpmks', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('cpmk_id')->constrained('cpmks')->cascadeOnDelete();
            $table->string('code', 20); // e.g. Sub-CPMK-1.1
            $table->text('description');
            $table->timestamps();
        });

        // Pemetaan Matrix CPL ke CPMK dan ke Asesmen
        Schema::create('outcome_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cpl_id')->constrained('cpls')->cascadeOnDelete();
            $table->foreignId('cpmk_id')->constrained('cpmks')->cascadeOnDelete();
            $table->foreignId('sub_cpmk_id')->nullable()->constrained('sub_cpmks')->nullOnDelete();
            $table->nullableMorphs('assessable'); // assignment, quiz, or question
            $table->decimal('weight', 5, 2)->default(1.00);
            $table->timestamps();
        });

        // Skor Capaian Mahasiswa per CPL/CPMK
        Schema::create('outcome_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('cpl_id')->constrained('cpls')->cascadeOnDelete();
            $table->foreignId('cpmk_id')->nullable()->constrained('cpmks')->nullOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->decimal('score', 6, 2)->default(0.00);
            $table->dateTime('calculated_at');
            $table->timestamps();
        });

        // === 4. GAMIFICATION ===
        Schema::create('xp_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->integer('amount');
            $table->string('source_type', 50); // assignment, quiz, attendance, streak, quest
            $table->unsignedBigInteger('source_id')->nullable();
            $table->string('description');
            $table->timestamps();
        });

        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description');
            $table->string('icon', 50)->default('award');
            $table->string('category', 30)->default('academic'); // academic, consistency, collaboration, mastery
            $table->integer('xp_required')->default(0);
            $table->json('criteria')->nullable();
            $table->timestamps();
        });

        Schema::create('badge_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('badge_id')->constrained('badges')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('earned_at');
            $table->timestamps();
            $table->unique(['badge_id', 'user_id']);
        });

        // === 5. ATTENDANCE (PRESENSI) ===
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('meeting_id')->constrained('meetings')->cascadeOnDelete();
            $table->string('qr_secret')->nullable();
            $table->dateTime('qr_expires_at')->nullable();
            $table->json('geofence')->nullable();
            $table->dateTime('opened_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('present'); // present, late, excused, absent
            $table->string('method', 20)->default('qr'); // qr, manual, geofence
            $table->string('ip_address', 45)->nullable();
            $table->dateTime('checked_at');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['attendance_session_id', 'user_id']);
        });

        // === 6. PRODUCT MARKETING DEMO REQUESTS ===
        Schema::create('demo_requests', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->string('name');
            $table->string('email');
            $table->string('institution'); // Nama Kampus / Universitas
            $table->string('phone', 30)->nullable();
            $table->string('role', 50)->nullable(); // Rektor, Warek 1, Dekan, Kaprodi, Kepala BAAK
            $table->string('students_count', 30)->nullable(); // < 3.000, 3.000-10.000, > 10.000
            $table->text('message')->nullable();
            $table->string('status', 20)->default('pending'); // pending, contacted, scheduled, completed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('demo_requests');
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendance_sessions');
        Schema::dropIfExists('badge_user');
        Schema::dropIfExists('badges');
        Schema::dropIfExists('xp_ledgers');
        Schema::dropIfExists('outcome_scores');
        Schema::dropIfExists('outcome_mappings');
        Schema::dropIfExists('sub_cpmks');
        Schema::dropIfExists('cpmks');
        Schema::dropIfExists('cpls');
        Schema::dropIfExists('quiz_attempts');
        Schema::dropIfExists('questions');
        Schema::dropIfExists('quizzes');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignments');
    }
};
