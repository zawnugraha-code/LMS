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
        // 1. Extend users table
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'gelar')) {
                $table->string('gelar')->nullable()->after('name');
            }
            if (! Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->nullable()->after('identifier');
            }
            if (! Schema::hasColumn('users', 'jabatan')) {
                $table->string('jabatan')->nullable()->after('prodi');
            }
        });

        // 2. Extend courses table
        Schema::table('courses', function (Blueprint $table) {
            if (! Schema::hasColumn('courses', 'cover_path')) {
                $table->string('cover_path')->nullable()->after('description');
            }
        });

        // 3. Extend classes table
        Schema::table('classes', function (Blueprint $table) {
            if (! Schema::hasColumn('classes', 'status')) {
                $table->string('status', 20)->default('aktif')->after('capacity'); // aktif, selesai
            }
        });

        // 4. Extend materials table
        Schema::table('materials', function (Blueprint $table) {
            if (! Schema::hasColumn('materials', 'class_id')) {
                $table->foreignId('class_id')->nullable()->after('meeting_id')->constrained('classes')->nullOnDelete();
            }
            if (! Schema::hasColumn('materials', 'status')) {
                $table->string('status', 20)->default('published')->after('is_downloadable'); // draft, published
            }
            if (! Schema::hasColumn('materials', 'uploaded_by')) {
                $table->foreignId('uploaded_by')->nullable()->after('status')->constrained('users')->nullOnDelete();
            }
        });

        // 5. Extend assignments table (Unified Assessment)
        Schema::table('assignments', function (Blueprint $table) {
            if (! Schema::hasColumn('assignments', 'dosen_id')) {
                $table->foreignId('dosen_id')->nullable()->after('class_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('assignments', 'jenis')) {
                $table->string('jenis', 20)->default('tugas')->after('dosen_id'); // tugas, kuis, uts, uas
            }
            if (! Schema::hasColumn('assignments', 'mode')) {
                $table->string('mode', 20)->default('individu')->after('jenis'); // individu, kelompok
            }
            if (! Schema::hasColumn('assignments', 'metode')) {
                $table->string('metode', 20)->default('unggah_berkas')->after('mode'); // unggah_berkas, input_manual
            }
            if (! Schema::hasColumn('assignments', 'lampiran_path')) {
                $table->string('lampiran_path')->nullable()->after('description');
            }
            if (! Schema::hasColumn('assignments', 'mulai_at')) {
                $table->dateTime('mulai_at')->nullable()->after('max_score');
            }
            if (! Schema::hasColumn('assignments', 'publish_at')) {
                $table->dateTime('publish_at')->nullable()->after('due_at');
            }
            if (! Schema::hasColumn('assignments', 'status')) {
                $table->string('status', 20)->default('dipublikasikan')->after('publish_at'); // draft, terjadwal, dipublikasikan, arsip
            }
        });

        // 6. Extend submissions table
        Schema::table('submissions', function (Blueprint $table) {
            if (! Schema::hasColumn('submissions', 'status_waktu')) {
                $table->string('status_waktu', 20)->nullable()->after('status'); // tepat_waktu, terlambat
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('submissions', function (Blueprint $table) {
            $table->dropColumn(['status_waktu']);
        });

        Schema::table('assignments', function (Blueprint $table) {
            $table->dropForeign(['dosen_id']);
            $table->dropColumn([
                'dosen_id',
                'jenis',
                'mode',
                'metode',
                'lampiran_path',
                'mulai_at',
                'publish_at',
                'status',
            ]);
        });

        Schema::table('materials', function (Blueprint $table) {
            $table->dropForeign(['class_id']);
            $table->dropForeign(['uploaded_by']);
            $table->dropColumn(['class_id', 'status', 'uploaded_by']);
        });

        Schema::table('classes', function (Blueprint $table) {
            $table->dropColumn(['status']);
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn(['cover_path']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gelar', 'prodi', 'jabatan']);
        });
    }
};
