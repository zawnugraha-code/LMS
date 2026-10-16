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
        // 1. Grade Scales (Standar Skala Nilai Huruf & Mutu)
        Schema::create('grade_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained('campuses')->cascadeOnDelete();
            $table->string('letter', 5); // A, A-, B+, B, B-, C+, C, D, E
            $table->decimal('min_score', 5, 2);
            $table->decimal('max_score', 5, 2);
            $table->decimal('gpa_point', 4, 2); // 4.00, 3.75, 3.50, dst
            $table->boolean('is_passing')->default(true);
            $table->string('legend_group', 30)->default('A'); // Grouping 6 legenda chart
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 2. Material Progress (Pelacakan Progres Materi Pembelajaran)
        Schema::create('material_progress', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials')->cascadeOnDelete();
            $table->string('status', 20)->default('not_started'); // completed, in_progress, not_started
            $table->unsignedTinyInteger('watch_percentage')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->unique(['user_id', 'material_id']);
        });

        // 3. Discussion Categories (Kategori Forum & Diskusi)
        Schema::create('discussion_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50);
            $table->string('slug', 50)->unique();
            $table->string('icon', 50)->default('message-circle');
            $table->string('color', 50)->default('blue');
            $table->timestamps();
        });

        // 4. Discussion Threads (Topik / Utas Diskusi)
        Schema::create('discussion_threads', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('class_id')->nullable()->constrained('classes')->nullOnDelete();
            $table->foreignId('category_id')->constrained('discussion_categories')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content');
            $table->string('attachment_path')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_locked')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
        });

        // 5. Discussion Replies (Balasan Komentar Diskusi)
        Schema::create('discussion_replies', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('thread_id')->constrained('discussion_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('discussion_replies')->cascadeOnDelete();
            $table->text('content');
            $table->boolean('is_solution')->default(false);
            $table->unsignedInteger('upvotes_count')->default(0);
            $table->timestamps();
        });

        // 6. Discussion Unique Views (Penghitungan View Unik per User per Hari)
        Schema::create('discussion_views', function (Blueprint $table) {
            $table->id();
            $table->foreignId('thread_id')->constrained('discussion_threads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('viewed_date');
            $table->timestamps();
            $table->unique(['thread_id', 'user_id', 'viewed_date']);
        });

        // 7. Certificates (Sertifikat & Portofolio Mahasiswa)
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('certificate_number', 100)->unique();
            $table->string('title');
            $table->decimal('final_score', 6, 2)->nullable();
            $table->string('letter_grade', 5)->nullable();
            $table->date('issued_date');
            $table->string('verification_code', 50)->unique();
            $table->string('pdf_path')->nullable();
            $table->timestamps();
        });

        // 8. User Preferences (Preferensi Tampilan, Notifikasi, Lokalisasi, & Semester)
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('active_term_id')->nullable()->constrained('terms')->nullOnDelete();
            $table->string('theme', 20)->default('light'); // light, dark, auto
            $table->string('font_size', 20)->default('normal'); // small, normal, large
            $table->string('locale', 10)->default('id');
            $table->string('timezone', 50)->default('Asia/Jakarta');
            $table->string('date_format', 30)->default('DD MMM YYYY');
            $table->string('time_format', 20)->default('HH:mm');
            $table->json('notifications')->nullable();
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->json('two_factor_recovery_codes')->nullable();
            $table->string('google_id')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });

        // 9. Support Tickets (Form Bantuan & Dukungan Pengguna)
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('subject');
            $table->string('category', 50)->default('teknis');
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('status', 20)->default('open'); // open, in_progress, resolved
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
        Schema::dropIfExists('user_preferences');
        Schema::dropIfExists('certificates');
        Schema::dropIfExists('discussion_views');
        Schema::dropIfExists('discussion_replies');
        Schema::dropIfExists('discussion_threads');
        Schema::dropIfExists('discussion_categories');
        Schema::dropIfExists('material_progress');
        Schema::dropIfExists('grade_scales');
    }
};
