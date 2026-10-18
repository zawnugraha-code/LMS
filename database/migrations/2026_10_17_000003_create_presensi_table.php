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
        if (! Schema::hasTable('presensi')) {
            Schema::create('presensi', function (Blueprint $table) {
                $table->id();
                $table->foreignId('pertemuan_id')->constrained('meetings')->cascadeOnDelete();
                $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
                $table->string('status', 20)->default('hadir'); // hadir, izin, sakit, tidak_hadir
                $table->text('catatan')->nullable();
                $table->foreignId('dicatat_oleh')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['pertemuan_id', 'mahasiswa_id']);
                $table->index(['pertemuan_id', 'status']);
                $table->index(['mahasiswa_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi');
    }
};
